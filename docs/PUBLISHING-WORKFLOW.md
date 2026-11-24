# BLC Mesa WordPress Publishing & Synchronization Workflow

This document provides a comprehensive guide for managing, synchronizing, and deploying the **Bethlehem Lutheran Church (blcmesa.org)** website between your local development environment and production hosting.

## Table of Contents

- [Architecture Overview](#-architecture-overview)
- [Two-Way Synchronization Lifecycle](#-two-way-synchronization-lifecycle)
- [Phase 1: Pulling Live Site Updates into Local](#-phase-1-pulling-live-site-updates-into-local)
  - [1. Export Content XML](#1-export-content-xml)
  - [2. Synchronize Media Attachments](#2-synchronize-media-attachments)
- [Phase 2: Local Page Creation & Block Patterns](#-phase-2-local-page-creation--block-patterns)
  - [1. Gutenberg Block Patterns as Templates](#1-gutenberg-block-patterns-as-templates)
  - [2. Publishing Pages to the Local Database](#2-publishing-pages-to-the-local-database)
  - [3. Checkpoint Database Backups](#3-checkpoint-database-backups)
- [Phase 3: Pushing Your Revamped Site to Cloudways Production](#-phase-3-pushing-your-revamped-site-to-cloudways-production)
  - [1. Initial One-Time Launch Package](#1-initial-one-time-launch-package)
  - [2. Initial Deploy to Cloudways](#2-initial-deploy-to-cloudways)
  - [3. Ongoing Code & Theme Updates (Safe While Church Admins Edit Live)](#3-ongoing-code--theme-updates-safe-while-church-admins-edit-live)
  - [4. Pulling the Cloudways Database to Local (Debugging Live/Staging Content)](#4-pulling-the-cloudways-database-to-local-debugging-livestaging-content)
- [Phase 4: Volunteer & Church Staff Maintenance Guide](#-phase-4-volunteer--church-staff-maintenance-guide)
  - [1. Editing the "About Us" Page](#1-editing-the-about-us-page)
  - [2. Posting Weekly Sermons](#2-posting-weekly-sermons)
  - [3. Contact Form & Email Notifications](#3-contact-form--email-notifications)

---

## 🏛️ Architecture Overview

The development site is built as a modern, containerized WordPress Full Site Editing (FSE) platform:

| Component | Technology | Purpose |
| :--- | :--- | :--- |
| **Theme** | Gutenberg FSE (`blcmesa-theme`) | Custom block theme in `wp-content/themes/blcmesa-theme/` |
| **Runtime** | Docker Compose (`wordpress`, `db`, `wpcli`) | Isolated Apache + PHP 8, MariaDB 11, and WP-CLI containers |
| **Dev Environment** | WSL2 / VS Code Dev Containers | Local code editing with live filesystem mount (`/workspace`) |
| **Single Source of Truth** | Git repository (`blcmesa.org`) | All templates, patterns, styles, scripts, and documentation |

---

## 🔄 Two-Way Synchronization Lifecycle

```
┌────────────────────────────────────────────────────────┐
│               LIVE PRODUCTION SITE                     │
│                 (blcmesa.org)                          │
└──────────────────────────┬─────────────────────────────┘
                           │
       1. PULL (Content & Media Sync)
          Tools > Export XML  +  scripts/sync-media.py
                           │
                           ▼
┌────────────────────────────────────────────────────────┐
│            LOCAL DEVELOPMENT (WSL / Docker)            │
│  - Gutenberg FSE Theme Development                     │
│  - Page Conversion & Pattern Creation                  │
│  - Database Snapshots (scripts/backup-db.sh)           │
└──────────────────────────┬─────────────────────────────┘
                           │
       2. PUSH (Production Deployment Package)
          scripts/export-for-hosting.sh
                           │
                           ▼
┌────────────────────────────────────────────────────────┐
│               LIVE PRODUCTION HOST                     │
│  - Clean MySQL Database Import                         │
│  - Active blcmesa-theme Deployment                     │
│  - Uploads & Media Assets Synchronized                 │
└────────────────────────────────────────────────────────┘
```

---

## 📥 Phase 1: Pulling Live Site Updates into Local

When church staff upload new sermon videos, bulletins, or images to the existing live site during redesign, use these steps to pull them into your local environment:

### 1. Export Content XML
1. Log in to the live WordPress Admin (`https://blcmesa.org/wp-admin`).
2. Navigate to **Tools > Export**.
3. Select **All content** and click **Download Export File**.
4. Place the downloaded `.xml` file into the `backups/` directory (e.g. `backups/bethlehemlutheranchurch.WordPress.YYYY-MM-DD.xml`).

### 2. Synchronize Media Attachments
From your **WSL terminal**, run the automated media downloader:

```bash
# Test run on first 5 media files
python3 scripts/sync-media.py --limit 5

# Full sync (auto-detects newest XML in backups/)
python3 scripts/sync-media.py

# Verify file sizes and re-download if remote files changed
python3 scripts/sync-media.py --verify-remote
```

All downloaded assets are saved into `wp-content/uploads/` with crash-recovery (resumes from where it left off).

---

## 🛠️ Phase 2: Local Page Creation & Block Patterns

To ensure church staff can maintain pages visually after launch without touching code or risking broken templates:

### 1. Gutenberg Block Patterns as Templates
- Page layouts are stored as modular block patterns in `wp-content/themes/blcmesa-theme/patterns/` (e.g., `page-about.php`, `sermon-post.php`).
- These are registered under custom categories (`BLC Church Layouts`, `BLC Sermons & Media`).
- Church staff can insert or reset these anytime from the visual editor: **`+` (Block Inserter) > Patterns > BLC Church Layouts**.

### 2. Publishing Pages to the Local Database
When a page pattern is finalized, publish it to the local WordPress database using the automated script from your **WSL terminal**:

```bash
bash scripts/create-about-page.sh
```

**What the script does**:
- Checks if a page with slug `about` exists.
- Inserts or updates the post with clean Gutenberg block markup.
- Sets the page status to `publish`.
- Flushes WordPress rewrite rules so the URL resolves immediately.

### 3. Checkpoint Database Backups
Create compressed database snapshots whenever you reach a development milestone:

```bash
# Create a snapshot in backups/blcmesa_db_YYYYMMDD_HHMMSS.sql.gz
bash scripts/backup-db.sh

# Restore a snapshot if needed
bash scripts/restore-db.sh backups/blcmesa_db_YYYYMMDD_HHMMSS.sql.gz
```

---

## 🚀 Phase 3: Pushing Your Revamped Site to Cloudways Production

> [!CAUTION]
> **Initial Launch vs. Ongoing Updates**: Only import `database.sql` **once** during the initial Cloudways launch. Once church admins begin editing pages, posting sermons, or uploading media in WordPress on Cloudways, **never re-import a local `database.sql`**—doing so will overwrite their live changes. For all subsequent code/theme updates, deploy **only the theme** using `scripts/export-theme.sh` (or Cloudways Git Deployment).

### 1. Initial One-Time Launch Package
Generate the initial full migration package (DB sanitized for MariaDB 10.11+, custom theme, non-default plugins, uploads):

```bash
bash scripts/export-for-hosting.sh
```

This creates `backups/blcmesa_migration_package_<timestamp>.zip` containing:
- `database.sql`: Full database export (with MariaDB 11+ sandbox comments stripped for Cloudways MariaDB 10.11 compatibility).
- `wp-content/themes/blcmesa-theme`: Complete Gutenberg FSE theme.
- `wp-content/plugins`: Non-default plugins (default `akismet`/`hello.php` excluded).
- `wp-content/uploads`: Synchronized images and media.
- `README_MIGRATION.txt`: Cloudways deployment checklist.

### 2. Initial Deploy to Cloudways
1. Provision a WordPress application on Cloudways with **PHP 8.3 or 8.4** and **MariaDB 10.11+**.
2. Upload `wp-content/themes/blcmesa-theme`, `wp-content/uploads/`, and any custom plugins to `public_html/wp-content/` via SFTP/SSH.
3. Import `database.sql` via SSH/WP-CLI (or Cloudways Database Manager) and run URL replacement:
   ```bash
   wp db import database.sql
   wp search-replace "http://localhost:8080" "https://blcmesa.org" --all-tables --precise
   wp theme activate blcmesa-theme
   wp rewrite flush --hard
   wp cache flush
   ```
4. **Cloudways Post-Launch Settings**:
   - **Exclude `/contact/` from Breeze & Varnish full-page caching** so contact form CSRF nonces do not expire.
   - **Configure SMTP**: Cloudways disables PHP `mail()` by default; install **FluentSMTP** (or Cloudways Elastic Email) authenticated for `office@blcmesa.org` with SPF/DKIM/DMARC.

### 3. Ongoing Code & Theme Updates (Safe While Church Admins Edit Live)
After launch, when you update theme styles, templates, or scripts in Git:
1. Bump `Version:` in `wp-content/themes/blcmesa-theme/style.css` (busts Cloudways Varnish/Breeze CSS & JS caches).
2. Generate a **theme-only** zip (touches neither the database nor uploads):
   ```bash
   bash scripts/export-theme.sh
   ```
3. Deploy the theme using **either** method:
   - **Cloudways Deployment via Git (Recommended):**
     - Merging changes in `wp-content/themes/blcmesa-theme/` into `main` automatically runs `.github/workflows/sync-deploy-branch.yml`, which validates the theme and publishes **only** the theme root files to the `deploy-theme` branch.
     - In Cloudways (**Application → Deployment via Git**), connect using Cloudways' generated SSH key (added as a read-only GitHub Deploy Key), select branch **`deploy-theme`**, and set **Deployment Path** to `public_html/wp-content/themes/blcmesa-theme/`.
     - Click **Pull** in Cloudways to update the theme without touching WordPress core, plugins, uploads, or the database.
   - **Manual Theme Zip Upload:**
     - Upload `backups/blcmesa-theme_<version>_<timestamp>.zip` via **WP Admin → Appearance → Themes → Add New → Upload Theme** (*Replace current with uploaded*).
4. Purge Breeze & Varnish caches on Cloudways.
5. Perform **WordPress core & plugin updates** directly on Cloudways (ideally via a Cloudways Staging clone or after taking an on-demand backup), and periodically run `docker compose pull` locally to stay in sync.

> [!NOTE]
> If church admins edit the Header, Footer, or Templates inside **Appearance → Editor**, WordPress saves overrides in the database that take precedence over theme `.html` files. Keep church admins editing **Pages & Posts**, while layout/template updates live in Git.

### 4. Pulling the Cloudways Database to Local (Debugging Live/Staging Content)
Once church staff and volunteers are actively editing pages on Cloudways, pull a snapshot down to your local Docker environment whenever you need to reproduce layout issues, test theme changes against real content, or keep a local backup:

1. **Export from Cloudways Admin Portal**:
   - Open your **Cloudways** application → **Access Details** → **Launch Database Manager**.
   - Click **Export** in the left sidebar.
   - Select **Format: `SQL`** and **Output: `gzip`** (or `save`), keep all tables selected, and click **Export**.
   - Save the downloaded `.sql.gz` (or `.sql` / `.zip`) file into the repository's `backups/` directory (gitignored).
2. **Import into Local WordPress** (in your **VS Code Dev Container terminal**):
   ```bash
   # Optional: inspect the dump and preview planned actions without modifying local DB
   bash scripts/import-cloudways-db.sh --dry-run --latest

   # Import the newest Cloudways export in backups/
   bash scripts/import-cloudways-db.sh --latest

   # Or target a specific file (files outside backups/ are automatically archived to backups/)
   bash scripts/import-cloudways-db.sh backups/<cloudways-export>.sql.gz

   # Optional: also merge a downloaded wp-content/uploads archive or directory
   bash scripts/import-cloudways-db.sh --latest --uploads backups/uploads.zip
   ```
3. **What `import-cloudways-db.sh` handles automatically**:
   - **Pre-import local safety snapshot**: Saves your current local database to `backups/pre_import_local_<timestamp>.sql.gz` before overwriting (skip with `--no-local-backup`).
   - **SQL sanitization**: Strips `CREATE DATABASE`/`USE` statements, `DEFINER` clauses, MariaDB sandbox headers, and maps MySQL 8 `utf8mb4_0900_ai_ci` collations for MariaDB compatibility.
   - **Table prefix sync**: Auto-detects non-default Cloudways table prefixes and updates local `wp-config.php` if needed.
   - **Serialized-safe URL rewrite**: Auto-detects the remote `siteurl`/`home` and runs `wp search-replace` to `http://localhost:8080` (override with `--remote-url <URL>`).
   - **Local development hygiene**: Deactivates Cloudways-only caching/security plugins locally (`breeze`, `object-cache-pro`, `redis-cache`, `malcare-security`, etc.), sets `blog_public` to `0`, and flushes caches and rewrite rules.
   - **Archive-only mode**: Pass `--backup-only` to compress and store a download into `backups/` without touching your local database.
4. **Post-import notes & rollback**:
   - Your local WordPress Admin (`http://localhost:8080/wp-admin`) logins will match the **Cloudways site's users** after import.
   - To revert to your previous local database at any time, run the printed rollback command:
     ```bash
     bash scripts/restore-db.sh backups/pre_import_local_<timestamp>.sql.gz
     ```
   - Never push this local database back up to Cloudways—continue deploying theme changes via Git (`deploy-theme`) or `scripts/export-theme.sh`.

---

## 👥 Phase 4: Volunteer & Church Staff Maintenance Guide

Once the site is live, staff and volunteers do not need access to Docker, Git, or terminal commands.

### 1. Editing the "About Us" Page
1. Log into WP Admin > **Pages** > **About Us**.
2. Click directly into any text block to edit text, update phone numbers, or modify descriptions.
3. **Leadership Accordions**:
   - Built on native WordPress `Details` blocks (`<details>`).
   - Click any leader’s name to edit their role, bio, or quote.
   - To add a new leader: select an accordion block and click **Duplicate**, then update the name and bio.
   - The smooth expand/collapse animation and gold rotating chevrons work automatically on the front end.
4. Click the blue **Update** button to save changes.

### 2. Posting Weekly Sermons
1. Go to **Posts > Add New Post**.
2. Set post title (e.g. *"Sunday Worship — October 11, 2026"*).
3. Click the **`+` (Block Inserter)** in the top left > **Patterns** > **BLC Sermons & Media** > **Weekly Sermon Video Post**.
4. Paste the week's Vimeo or YouTube link into the video block.
5. Update Scripture readings and preacher name.
6. Click **Publish**.

### 3. Contact Form & Email Notifications
- The `/contact/` page routes inquiries to the selected department (*Church Office, Pastor, Preschool, Music, Care, Nurse, Youth, Outreach*).
- **Email Delivery**: Uses standard WordPress `wp_mail()` connected via a free SMTP plugin (e.g., FluentSMTP) to the church's existing mail host.
- **Local Dev Testing**: Form submissions on local Docker environments mock email delivery by logging to `wp-content/debug.log` without sending live emails. Developers can optionally override destination delivery with `BLC_TEST_EMAIL=dev@example.com`.

