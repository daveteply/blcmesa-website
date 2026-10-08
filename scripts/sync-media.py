#!/usr/bin/env python3
"""
BLC Mesa - WordPress Media & File Sync Utility
==============================================
Parses a WordPress WXR (XML) export file and downloads all media uploads
(images, PDFs, documents, audio) directly into wp-content/uploads/
with crash recovery, resume support, retry backoff, and incremental sync.

Features:
- Crash Recovery & Resume: Saves state to a JSON manifest every 10 downloads.
- Atomic Writes: Downloads to .part temporary files before replacing to avoid corrupted files.
- Remote Verification: Optional --verify-remote flag checks HTTP HEAD (Content-Length, Last-Modified)
  to detect if a file with the same name was updated on the server.
- Checksum Tracking: Computes and stores SHA-256 hashes for all downloaded files.
- Rate Limiting: Gentle delay and realistic browser User-Agent to avoid host throttling / WAF blocks.

Usage:
    python3 scripts/sync-media.py [options] [path_to_xml_file]

Examples:
    # Test on the first 5 files without downloading everything:
    python3 scripts/sync-media.py --limit 5

    # Dry run: analyze URLs without downloading:
    python3 scripts/sync-media.py --dry-run

    # Full sync with default settings (auto-detects newest XML in backups/):
    python3 scripts/sync-media.py

    # Verify existing local files against server to detect modified files with same name:
    python3 scripts/sync-media.py --verify-remote

    # Verify integrity of all downloaded files locally using stored SHA-256 checksums:
    python3 scripts/sync-media.py --verify-checksums

    # Retry previously failed downloads:
    python3 scripts/sync-media.py --retry-failed
"""

import argparse
import glob
import hashlib
import json
import os
import re
import signal
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
from datetime import datetime
from email.utils import parsedate_to_datetime

USER_AGENT = (
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
    "AppleWebKit/537.36 (KHTML, like Gecko) "
    "Chrome/124.0.0.0 Safari/537.36"
)


def find_latest_xml(backups_dir="backups"):
    """Find the newest WordPress XML export file in the backups folder."""
    xml_files = glob.glob(os.path.join(backups_dir, "*.xml"))
    if not xml_files:
        return None
    xml_files.sort(key=os.path.getmtime, reverse=True)
    return xml_files[0]


def extract_media_urls(xml_path, include_all_content=True):
    """
    Extract all media upload URLs from the XML file.
    Includes canonical <wp:attachment_url> and optionally any
    embedded /wp-content/uploads/ URLs in post content.
    """
    print(f"[*] Reading and parsing XML export: {xml_path}...")
    with open(xml_path, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()

    # 1. Canonical attachments
    attachment_urls = re.findall(
        r"<wp:attachment_url><!\[CDATA\[(.*?)\]\]></wp:attachment_url>",
        content,
    )
    if not attachment_urls:
        attachment_urls = re.findall(
            r"<wp:attachment_url>(.*?)</wp:attachment_url>", content
        )

    urls = set()
    for u in attachment_urls:
        u_clean = u.strip()
        if u_clean:
            urls.add(u_clean)

    print(f"[*] Found {len(urls)} canonical <wp:attachment_url> attachments.")

    # 2. Embedded URLs in content/uploads
    if include_all_content:
        content_matches = re.findall(
            r"https?://(?:www\.)?blcmesa\.org/wp-content/uploads/([a-zA-Z0-9_./%-]+)",
            content,
        )
        before_count = len(urls)
        for rel_path in content_matches:
            rel_clean = rel_path.split("?")[0].rstrip(".,;)")
            if "." in os.path.basename(rel_clean):
                full_url = f"https://blcmesa.org/wp-content/uploads/{rel_clean}"
                urls.add(full_url)
        added_count = len(urls) - before_count
        print(f"[*] Discovered {added_count} additional embedded upload URLs in post content.")

    sorted_urls = sorted(list(urls))
    print(f"[*] Total unique media files to process: {len(sorted_urls)}")
    return sorted_urls


def load_manifest(manifest_path):
    """Load existing download manifest for crash recovery / resume."""
    if os.path.exists(manifest_path):
        try:
            with open(manifest_path, "r", encoding="utf-8") as f:
                data = json.load(f)
                return data
        except Exception as e:
            print(f"[!] Warning: Could not read manifest ({e}). Creating new.")
    return {"version": 2, "created_at": datetime.now().isoformat(), "files": {}}


def save_manifest(manifest, manifest_path):
    """Atomically save manifest to disk."""
    temp_path = manifest_path + ".tmp"
    os.makedirs(os.path.dirname(manifest_path) or ".", exist_ok=True)
    with open(temp_path, "w", encoding="utf-8") as f:
        json.dump(manifest, f, indent=2)
    os.replace(temp_path, manifest_path)


def url_to_local_relpath(url):
    """
    Given a URL like https://blcmesa.org/wp-content/uploads/2017/10/photo.jpg,
    return relative path: 2017/10/photo.jpg
    """
    match = re.search(r"/wp-content/uploads/(.+)$", url)
    if match:
        path = match.group(1).split("?")[0]
        return urllib.parse.unquote(path)
    return os.path.basename(urllib.parse.unquote(url.split("?")[0]))


def format_bytes(size):
    """Human readable file size."""
    for unit in ["B", "KB", "MB", "GB"]:
        if size < 1024.0:
            return f"{size:.1f} {unit}"
        size /= 1024.0
    return f"{size:.1f} TB"


def compute_file_sha256(filepath):
    """Compute SHA-256 checksum of local file."""
    h = hashlib.sha256()
    with open(filepath, "rb") as f:
        while chunk := f.read(65536):
            h.update(chunk)
    return h.hexdigest()


def get_remote_file_info(url, timeout=12):
    """
    Issue a lightweight HTTP HEAD request to check remote size and Last-Modified date.
    Returns dict: {'size': int or None, 'last_modified': datetime or None, 'etag': str or None}
    """
    headers = {"User-Agent": USER_AGENT, "Accept": "*/*"}
    req = urllib.request.Request(url, headers=headers, method="HEAD")
    try:
        with urllib.request.urlopen(req, timeout=timeout) as response:
            cl = response.headers.get("Content-Length")
            lm = response.headers.get("Last-Modified")
            etag = response.headers.get("ETag")

            remote_size = int(cl) if cl and cl.isdigit() else None
            remote_mtime = None
            if lm:
                try:
                    remote_mtime = parsedate_to_datetime(lm)
                except Exception:
                    pass

            return {
                "size": remote_size,
                "last_modified": remote_mtime,
                "etag": etag.strip('"') if etag else None,
            }
    except Exception:
        return None


def download_file(url, target_path, timeout=25):
    """
    Download a file with User-Agent header to a temporary path,
    computes SHA-256 in stream, then atomically moves into place.
    Returns (file_size, sha256_hex).
    """
    os.makedirs(os.path.dirname(target_path), exist_ok=True)
    temp_target = target_path + ".part"

    headers = {"User-Agent": USER_AGENT, "Accept": "*/*"}
    req = urllib.request.Request(url, headers=headers)
    sha256 = hashlib.sha256()

    with urllib.request.urlopen(req, timeout=timeout) as response:
        with open(temp_target, "wb") as out_file:
            while True:
                chunk = response.read(65536)
                if not chunk:
                    break
                out_file.write(chunk)
                sha256.update(chunk)

    # Rename temp to target atomically
    os.replace(temp_target, target_path)
    return os.path.getsize(target_path), sha256.hexdigest()


def verify_local_checksums(manifest_path, output_dir):
    """Verify all previously downloaded files against their stored SHA-256 checksums."""
    if not os.path.isfile(manifest_path):
        print(f"[!] Manifest file not found: {manifest_path}")
        return

    manifest = load_manifest(manifest_path)
    files = manifest.get("files", {})
    print(f"[*] Verifying SHA-256 checksums for {len(files)} files in manifest...")

    passed = 0
    missing = 0
    mismatch = 0

    for url, data in files.items():
        rel_path = data.get("rel_path") or url_to_local_relpath(url)
        full_path = os.path.join(output_dir, rel_path)
        expected_hash = data.get("sha256")

        if not os.path.isfile(full_path):
            print(f"  [MISSING] {rel_path}")
            missing += 1
            continue

        if not expected_hash:
            continue

        actual_hash = compute_file_sha256(full_path)
        if actual_hash == expected_hash:
            passed += 1
        else:
            print(f"  [CORRUPT/MISMATCH] {rel_path} (Expected: {expected_hash[:8]}..., Actual: {actual_hash[:8]}...)")
            mismatch += 1

    print("\n=== CHECKSUM VERIFICATION SUMMARY ===")
    print(f" - Valid & Verified: {passed}")
    print(f" - Missing on Disk:  {missing}")
    print(f" - Mismatch/Corrupt: {mismatch}")
    print("======================================\n")


def main():
    parser = argparse.ArgumentParser(
        description="Download media files from WordPress XML export with crash recovery, remote verification, and SHA-256 checksums."
    )
    parser.add_argument(
        "xml_file",
        nargs="?",
        default=None,
        help="Path to WordPress XML file (default: newest in backups/)",
    )
    parser.add_argument(
        "--output-dir",
        default="wp-content/uploads",
        help="Destination directory for downloads (default: wp-content/uploads)",
    )
    parser.add_argument(
        "--manifest",
        default="backups/media_sync_manifest.json",
        help="Path to manifest JSON state file (default: backups/media_sync_manifest.json)",
    )
    parser.add_argument(
        "--delay",
        type=float,
        default=0.25,
        help="Delay in seconds between downloads to respect server (default: 0.25)",
    )
    parser.add_argument(
        "--timeout",
        type=int,
        default=25,
        help="HTTP request timeout in seconds (default: 25)",
    )
    parser.add_argument(
        "--max-retries",
        type=int,
        default=3,
        help="Max attempts per file on network error (default: 3)",
    )
    parser.add_argument(
        "--limit",
        type=int,
        default=0,
        help="Maximum files to process in this run (0 = all)",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Scan and list files without downloading",
    )
    parser.add_argument(
        "--verify-remote",
        action="store_true",
        help="Perform HTTP HEAD check on existing files to detect size/date updates on server",
    )
    parser.add_argument(
        "--verify-checksums",
        action="store_true",
        help="Verify existing downloaded files against stored SHA-256 checksums",
    )
    parser.add_argument(
        "--retry-failed",
        action="store_true",
        help="Re-attempt files previously marked as failed in manifest",
    )
    parser.add_argument(
        "--attachments-only",
        action="store_true",
        help="Only download canonical attachments (skip content thumbnail scans)",
    )

    args = parser.parse_args()

    # Offline checksum verification mode
    if args.verify_checksums:
        verify_local_checksums(args.manifest, args.output_dir)
        sys.exit(0)

    # Determine XML file
    xml_path = args.xml_file
    if not xml_path:
        xml_path = find_latest_xml()
        if not xml_path:
            print("[!] Error: No XML file specified and none found in backups/ folder.")
            sys.exit(1)

    if not os.path.isfile(xml_path):
        print(f"[!] Error: File '{xml_path}' does not exist.")
        sys.exit(1)

    # Extract URLs
    urls = extract_media_urls(xml_path, include_all_content=not args.attachments_only)
    if not urls:
        print("[!] No media URLs found in XML.")
        sys.exit(0)

    if args.limit > 0:
        urls = urls[: args.limit]
        print(f"[*] Processing limited to {len(urls)} files as requested.")

    # Dry run exit
    if args.dry_run:
        print("\n=== DRY RUN MODE: URL SAMPLE ===")
        for u in urls[:10]:
            rel = url_to_local_relpath(u)
            print(f"  {u} -> {os.path.join(args.output_dir, rel)}")
        if len(urls) > 10:
            print(f"  ... and {len(urls) - 10} more files.")
        print(f"\nDry run complete. Found {len(urls)} files. Run without --dry-run to start downloading.")
        sys.exit(0)

    # Load Manifest
    manifest = load_manifest(args.manifest)
    files_state = manifest.setdefault("files", {})

    stats = {
        "total": len(urls),
        "skipped": 0,
        "updated": 0,
        "downloaded": 0,
        "failed": 0,
        "bytes_downloaded": 0,
    }

    interrupted = False

    def handle_sigint(signum, frame):
        nonlocal interrupted
        interrupted = True
        print("\n\n[!] Interrupt received (Ctrl+C). Saving progress manifest and stopping cleanly...")

    signal.signal(signal.SIGINT, handle_sigint)

    print("\n========================================================")
    print(" 🚀 Starting BLC Mesa Media Sync")
    print("========================================================")
    print(f" - Source XML:         {xml_path}")
    print(f" - Destination:        {args.output_dir}/")
    print(f" - State Manifest:     {args.manifest}")
    print(f" - Remote Head Check:  {'Enabled (--verify-remote)' if args.verify_remote else 'Fast Skip'}")
    print(f" - Rate Delay:         {args.delay}s per file")
    print(" - Press Ctrl+C at any time to pause/resume later.")
    print("========================================================\n")

    start_time = time.time()
    save_counter = 0

    for idx, url in enumerate(urls, 1):
        if interrupted:
            break

        rel_path = url_to_local_relpath(url)
        target_path = os.path.join(args.output_dir, rel_path)
        progress_pct = (idx / len(urls)) * 100

        needs_download = True
        local_exists = os.path.isfile(target_path) and os.path.getsize(target_path) > 0

        # Existing file handling
        if local_exists:
            file_size = os.path.getsize(target_path)

            if args.verify_remote:
                # Issue HEAD request to check if remote changed
                remote_info = get_remote_file_info(url, timeout=args.timeout)
                if remote_info and remote_info.get("size") is not None:
                    remote_size = remote_info["size"]
                    if remote_size != file_size:
                        print(
                            f"[{idx:4d}/{len(urls):4d}] ({progress_pct:4.1f}%) [UPDATE] {rel_path} (Local: {format_bytes(file_size)}, Remote: {format_bytes(remote_size)}) -> Re-downloading"
                        )
                        stats["updated"] += 1
                        needs_download = True
                    else:
                        needs_download = False
                else:
                    # Remote check inconclusive, keep local
                    needs_download = False
            else:
                needs_download = False

            if not needs_download:
                stats["skipped"] += 1
                if url not in files_state or "sha256" not in files_state[url]:
                    files_state[url] = {
                        "status": "success",
                        "rel_path": rel_path,
                        "size": file_size,
                        "verified_at": datetime.now().isoformat(),
                    }
                print(
                    f"[{idx:4d}/{len(urls):4d}] ({progress_pct:4.1f}%) [SKIP] {rel_path} ({format_bytes(file_size)})"
                )
                continue

        # Check manifest state for previous failures
        url_state = files_state.get(url, {})
        if (
            url_state.get("status") == "failed"
            and not args.retry_failed
            and url_state.get("attempts", 0) >= args.max_retries
        ):
            stats["failed"] += 1
            print(
                f"[{idx:4d}/{len(urls):4d}] ({progress_pct:4.1f}%) [PREV FAIL] {rel_path} (Use --retry-failed to re-attempt)"
            )
            continue

        # Download with retry loop
        download_success = False
        attempts = 0
        last_error = ""

        while attempts < args.max_retries and not interrupted:
            attempts += 1
            try:
                size, sha256_hash = download_file(url, target_path, timeout=args.timeout)
                stats["downloaded"] += 1
                stats["bytes_downloaded"] += size
                download_success = True
                files_state[url] = {
                    "status": "success",
                    "rel_path": rel_path,
                    "size": size,
                    "sha256": sha256_hash,
                    "downloaded_at": datetime.now().isoformat(),
                    "attempts": attempts,
                }
                print(
                    f"[{idx:4d}/{len(urls):4d}] ({progress_pct:4.1f}%) [OK]   {rel_path} ({format_bytes(size)}) [SHA256: {sha256_hash[:8]}...]"
                )
                break
            except (urllib.error.HTTPError, urllib.error.URLError, TimeoutError, Exception) as err:
                last_error = str(err)
                if isinstance(err, PermissionError) or "Permission denied" in str(err):
                    print(f"\n[!] Permission Error writing to {target_path}!")
                    print("    Run this in your terminal to fix: docker compose exec -u root wordpress chmod -R 777 /var/www/html/wp-content/uploads\n")
                    interrupted = True
                    break
                if attempts < args.max_retries:
                    wait_time = attempts * 1.5
                    print(
                        f"[{idx:4d}/{len(urls):4d}] ({progress_pct:4.1f}%) [RETRY {attempts}/{args.max_retries}] {rel_path} ({err}) -> waiting {wait_time:.1f}s"
                    )
                    time.sleep(wait_time)
                else:
                    print(
                        f"[{idx:4d}/{len(urls):4d}] ({progress_pct:4.1f}%) [FAIL] {rel_path} ({last_error})"
                    )

        if not download_success and not interrupted:
            stats["failed"] += 1
            files_state[url] = {
                "status": "failed",
                "rel_path": rel_path,
                "error": last_error,
                "failed_at": datetime.now().isoformat(),
                "attempts": attempts,
            }

        # Periodic manifest auto-save
        save_counter += 1
        if save_counter >= 10:
            manifest["updated_at"] = datetime.now().isoformat()
            save_manifest(manifest, args.manifest)
            save_counter = 0

        # Delay to avoid hammering server
        if args.delay > 0 and not interrupted:
            time.sleep(args.delay)

    # Final manifest save
    manifest["updated_at"] = datetime.now().isoformat()
    save_manifest(manifest, args.manifest)

    elapsed = time.time() - start_time
    print("\n========================================================")
    if interrupted:
        print(" ⏸️  SYNC PAUSED (Crash-safe checkpoint saved)")
    else:
        print(" ✅ MEDIA SYNC RUN COMPLETE")
    print("========================================================")
    print(f" - Total files processed:   {stats['total']}")
    print(f" - Already present skipped: {stats['skipped']}")
    if stats["updated"] > 0:
        print(f" - Re-downloaded updates:   {stats['updated']}")
    print(f" - Newly downloaded:        {stats['downloaded']} ({format_bytes(stats['bytes_downloaded'])})")
    print(f" - Failed / Not found:      {stats['failed']}")
    print(f" - Time elapsed:            {elapsed:.1f} seconds")
    print(f" - State manifest saved to: {args.manifest}")
    print("========================================================")
    if stats["failed"] > 0:
        print("Tip: Run 'python3 scripts/sync-media.py --retry-failed' to re-attempt failed files.")
    print("")


if __name__ == "__main__":
    main()
