# Automation & Maintenance Scripts

This directory contains automation utilities for local development, media synchronization, database management, and production hosting deployment for **Bethlehem Lutheran Church** (`blcmesa.org`).

---

## 📑 Script Directory Overview

| Script | Runtime | Purpose |
| :--- | :--- | :--- |
| [`sync-media.py`](file://./sync-media.py) / [`sync-media.sh`](file://./sync-media.sh) | Python 3 / Bash | Downloads media files from WordPress XML exports with crash recovery and checksums |
| [`init-site.sh`](file://./init-site.sh) | Bash / WP-CLI | Initializes WordPress core, applies site settings, and activates the theme |
| [`backup-db.sh`](file://./backup-db.sh) | Bash / Docker | Exports a compressed SQL backup snapshot from MariaDB to `backups/` |
| [`restore-db.sh`](file://./restore-db.sh) | Bash / Docker | Restores a database snapshot from a `.sql` or `.sql.gz` file |
| [`export-for-hosting.sh`](file://./export-for-hosting.sh) | Bash / Zip | Generates a standalone production zip package ready for any web host |

---

## 1. Media Sync Utility (`sync-media.py` & `sync-media.sh`)

Downloads all church media attachments (images, PDFs, bulletins, newsletters, documents) extracted from WordPress XML export files directly into `wp-content/uploads/`.

### Key Features
- **Crash Recovery & Resume**: Automatically checkpoints progress into `backups/media_sync_manifest.json` every 10 downloads. If the script is stopped or times out, re-running resumes from where it left off.
- **Instant Skip**: Non-empty files already present on disk are skipped in sub-millisecond time.
- **Remote Verification (`--verify-remote`)**: Issues lightweight HTTP `HEAD` checks to compare server file size and modification date, detecting if a file with the same name was re-uploaded on the live site.
- **SHA-256 Checksums**: Calculates and stores cryptographic SHA-256 hashes for all downloads, with offline integrity verification (`--verify-checksums`).
- **Atomic Writes**: Downloads to `.part` temporary files before atomically replacing them, eliminating corrupt half-downloaded files.
- **Server Friendly**: Mimics modern browser User-Agents and includes gentle rate-limiting (default `0.25s` delay) to prevent hammering slow church hosts.

### CLI Usage & Options

```bash
# 1. Quick dry-run (scans XML and shows discovered files without downloading)
python3 scripts/sync-media.py --dry-run

# 2. Test run on first 5 files
python3 scripts/sync-media.py --limit 5

# 3. Full sync (auto-detects the newest XML file in backups/)
python3 scripts/sync-media.py

# 4. Sync with remote HEAD verification (checks for re-uploaded files with the same name)
python3 scripts/sync-media.py --verify-remote

# 5. Offline checksum verification (verifies local files against stored SHA-256 hashes)
python3 scripts/sync-media.py --verify-checksums

# 6. Re-attempt any previously failed files
python3 scripts/sync-media.py --retry-failed
```

### Full Options Reference

| Flag | Default | Description |
| :--- | :--- | :--- |
| `[xml_file]` | Newest in `backups/*.xml` | Specific path to WordPress XML export file |
| `--output-dir` | `wp-content/uploads` | Local destination directory for downloaded media |
| `--manifest` | `backups/media_sync_manifest.json` | JSON state manifest path |
| `--limit N` | `0` (all) | Limit execution to first `N` files |
| `--delay SECONDS` | `0.25` | Delay in seconds between downloads |
| `--timeout SECONDS` | `25` | HTTP request timeout |
| `--max-retries N` | `3` | Maximum retry attempts per failed file |
| `--verify-remote` | `false` | Issue HTTP HEAD check to detect updated files with same name |
| `--verify-checksums`| `false` | Verify local disk files against stored SHA-256 hashes |
| `--retry-failed` | `false` | Re-attempt downloads previously marked as failed |
| `--attachments-only`| `false` | Only download canonical attachments (skip content thumbnail scans) |
| `--dry-run` | `false` | Scan XML and display file list without downloading |

---

## 2. Site Initialization (`init-site.sh`)

Automates container startup, database readiness polling, WordPress core setup, and theme activation.

```bash
bash scripts/init-site.sh
```

**Workflow**:
1. Ensures containers are running (`docker compose up -d`).
2. Polls MariaDB container until health check status is `(healthy)`.
3. Runs `wp core install` using credentials defined in [`.env`](file://../.env).
4. Configures site options (timezone: `America/Phoenix`, permalinks: `/%postname%/`).
5. Activates the custom Gutenberg block theme (`blcmesa-theme`).

---

## 3. Database Backup (`backup-db.sh`)

Creates a point-in-time compressed SQL snapshot from the running MariaDB container.

```bash
bash scripts/backup-db.sh
```

- Output: `backups/blcmesa_db_<timestamp>.sql.gz`
- Automatically excluded from Git via `.gitignore`.

---

## 4. Database Restore (`restore-db.sh`)

Restores a previous SQL or `.sql.gz` database dump into MariaDB.

```bash
# View available backups
bash scripts/restore-db.sh

# Restore a specific backup
bash scripts/restore-db.sh backups/blcmesa_db_YYYYMMDD_HHMMSS.sql.gz
```

---

## 5. Hosting Migration Packager (`export-for-hosting.sh`)

Bundles the active theme, custom plugins, media uploads, and a fresh database dump into a production-ready `.zip` archive.

```bash
bash scripts/export-for-hosting.sh
```

- Output: `backups/blcmesa_migration_package_<timestamp>.zip`
- Includes `README_MIGRATION.txt` with step-by-step instructions for importing into standard hosts (Bluehost, DreamHost, SiteGround, WP Engine).
