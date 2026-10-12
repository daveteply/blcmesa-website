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

## 🚀 Phase 3: Pushing Your Revamped Site to Live Production

When leadership reviews and approves the new site:

### 1. Generate the Production Hosting Package
Run the automated export script from your **WSL terminal**:

```bash
bash scripts/export-for-hosting.sh
```

This creates `backups/blcmesa_migration_package_<timestamp>.zip` containing:
- `database.sql`: Sanitized database export with localhost URLs replaced with `https://blcmesa.org`.
- `wp-content/themes/blcmesa-theme`: Complete Gutenberg FSE theme.
- `wp-content/plugins`: Active plugins.
- `wp-content/uploads`: Synchronized images and media.
- `README.txt`: Host-specific deployment instructions.

### 2. Deploy to Production Host
- **Option A (Standard Hosting File Manager / SFTP + phpMyAdmin)**:
  1. Upload `wp-content/themes/blcmesa-theme` to your live host's `wp-content/themes/`.
  2. Upload `wp-content/uploads/` to `wp-content/uploads/`.
  3. Import `database.sql` into the host's MySQL database via phpMyAdmin.
- **Option B (WP-CLI on Host / SSH)**:
  ```bash
  wp db import database.sql
  wp theme activate blcmesa-theme
  wp rewrite flush --hard
  ```
- **Option C (Migration Plugin)**:
  Install *All-in-One WP Migration* or *Duplicator* to import the package with one click.

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

