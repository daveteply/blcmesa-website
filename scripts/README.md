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
| [`import-cloudways-db.sh`](file://./import-cloudways-db.sh) | Bash / WP-CLI | Imports a manual Cloudways DB export (`.sql`/`.sql.gz`/`.zip`) into local dev with auto URL rewrite & safety rollback |
| [`export-for-hosting.sh`](file://./export-for-hosting.sh) | Bash / Zip | Generates a one-time initial Cloudways migration zip (DB + custom theme + uploads) |
| [`export-theme.sh`](file://./export-theme.sh) | Bash / Zip | Generates a safe theme-only zip (`blcmesa-theme`) for ongoing Cloudways re-uploads without touching the live DB |

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

## 4b. Import Cloudways Database Export (`import-cloudways-db.sh`)

Imports a manually exported database from **Cloudways Database Manager** (or phpMyAdmin) into your local Docker WordPress environment for debugging live/staging content changes.

### Step 1: Export from Cloudways Admin Portal
1. Open your **Cloudways** application -> **Access Details** -> **Launch Database Manager**.
2. Click **Export** in the left sidebar.
3. Choose **Output: `gzip`** (or `save`), **Format: `SQL`**, keep all tables checked, and click **Export**.
4. Save the downloaded `.sql.gz` (or `.sql` / `.zip`) file into your local `backups/` folder.

### Step 2: Import into Local Dev (VS Code Dev Container terminal)

```bash
# Import a specific downloaded export
bash scripts/import-cloudways-db.sh backups/<your-cloudways-export>.sql.gz

# Or auto-select the newest Cloudways export in backups/
bash scripts/import-cloudways-db.sh --latest

# Preview what would happen without modifying local DB
bash scripts/import-cloudways-db.sh --dry-run --latest

# Archive a download from outside backups/ without importing locally
bash scripts/import-cloudways-db.sh --backup-only ~/Downloads/export.sql
```

### What It Handles Automatically
- **Safety rollback**: Saves a compressed snapshot of your current local DB (`backups/pre_import_local_<timestamp>.sql.gz`) before importing.
- **SQL compatibility**: Strips Cloudways `CREATE DATABASE` / `USE` lines, `DEFINER` clauses, MariaDB sandbox headers, and MySQL 8 `utf8mb4_0900_ai_ci` collations.
- **Table prefix sync**: Auto-detects custom Cloudways table prefixes (e.g., `wp_abc123_`) and updates local `wp-config.php` so WordPress loads the imported tables immediately.
- **Safe URL rewriting**: Auto-detects the remote Cloudways `siteurl`/`home` and runs serialized-safe `wp search-replace` to `http://localhost:8080`.
- **Local hygiene**: Deactivates Cloudways-only cache/security plugins (`breeze`, `malcare-security`, `object-cache-pro`, etc.) locally and flushes permalinks/caches.
- **Optional media sync**: Pass `--uploads <path-to-uploads.zip-or-dir>` to merge newly uploaded Cloudways media into `wp-content/uploads/`.

---

## 5. Hosting Migration Packager (`export-for-hosting.sh`)

Bundles `blcmesa-theme`, non-default plugins, media uploads, and a MariaDB 10.11-compatible database dump into a one-time initial launch `.zip` archive.

```bash
bash scripts/export-for-hosting.sh
```

- Output: `backups/blcmesa_migration_package_<timestamp>.zip`
- Includes `README_MIGRATION.txt` with Cloudways deployment steps.
- **Warning**: Use for initial launch only; never re-import `database.sql` after church admins begin editing live.

---

## 6. Theme-Only Update Packager (`export-theme.sh`)

Packages **only** `wp-content/themes/blcmesa-theme` for safe ongoing Cloudways re-uploads while church admins continue editing pages, sermons, and uploads in WordPress.

```bash
bash scripts/export-theme.sh
```

- Output: `backups/blcmesa-theme_<version>_<timestamp>.zip`
