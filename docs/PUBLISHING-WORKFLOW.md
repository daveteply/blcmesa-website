# BLC Mesa WordPress Publishing & Synchronization Workflow

This document provides a comprehensive guide for managing, synchronizing, and deploying the **Bethlehem Lutheran Church (blcmesa.org)** website between your local development environment and production hosting.

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
3. Upload `backups/blcmesa-theme_<version>_<timestamp>.zip` via **WP Admin → Appearance → Themes → Add New → Upload Theme** (*Replace current with uploaded*) or sync `wp-content/themes/blcmesa-theme/` via **Cloudways Deployment via Git** / SFTP.
4. Purge Breeze & Varnish caches on Cloudways.
5. Perform **WordPress core & plugin updates** directly on Cloudways (ideally via a Cloudways Staging clone or after taking an on-demand backup), and periodically run `docker compose pull` locally to stay in sync.

> [!NOTE]
> If church admins edit the Header, Footer, or Templates inside **Appearance → Editor**, WordPress saves overrides in the database that take precedence over theme `.html` files. Keep church admins editing **Pages & Posts**, while layout/template updates live in Git.

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

