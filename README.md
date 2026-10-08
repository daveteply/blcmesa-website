# Bethlehem Lutheran Church (blcmesa.org)

Modern, containerized WordPress development platform and Gutenberg Full Site Editing (FSE) block theme for **Bethlehem Lutheran Church** in Mesa, Arizona.

This repository serves as the **single source of truth** for rebuilding the church website, training volunteers on weekly sermon updates, and exporting production packages once hosting budgets are finalized with church leadership.

---

## 🚀 Quick Access & Port Reference

All services and credentials are managed in [`.env`](file://./.env). Below are the active local endpoints:

| Service | Browser URL / Endpoint | Port | Default Credentials | Description |
| :--- | :--- | :--- | :--- | :--- |
| **WordPress Site** | [http://localhost:8080](http://localhost:8080) | `8080` | *Public access* | Live front-end church website |
| **WordPress Admin** | [http://localhost:8080/wp-admin](http://localhost:8080/wp-admin) | `8080` | User: `admin`<br>Pass: `admin_password` | Gutenberg Block Editor & Site Editor |
| **phpMyAdmin** | [http://localhost:8081](http://localhost:8081) | `8081` | User: `wordpress`<br>Pass: `wordpress_dev_password` | Web database management interface |
| **MariaDB Server** | `localhost:3306` | `3306` | User: `wordpress`<br>Root: `wordpress_root_dev_password` | Direct SQL database connection |

> [!TIP]
> Need to change passwords, database names, or port numbers? Edit [`.env`](file://./.env). All Docker Compose services and initialization scripts read directly from it.

---

## 💻 Running with WSL2 & Windows 11

This environment is purpose-built to run seamlessly from **Windows Subsystem for Linux (WSL2)** under Windows 11.

### Option 1: VS Code Dev Containers (Recommended)

1. Open this project directory in VS Code from your WSL terminal:
   ```bash
   code /home/daveteply/git/blcmesa.org
   ```
2. When prompted by VS Code, click **"Reopen in Container"** (or press `F1` / `Ctrl+Shift+P` and choose `Dev Containers: Reopen in Container`).
3. VS Code will build the container environment, launch WordPress, MariaDB, and phpMyAdmin, and connect your terminal inside the `dev` container at `/workspace`.
4. **Port Forwarding**: Ports `8080` and `8081` are automatically forwarded to Windows 11 localhost. Open [http://localhost:8080](http://localhost:8080) in your Windows browser (Chrome, Edge, Firefox).
5. **Filesystem Synchronization**: Code changes made inside the container or in VS Code are mounted cached (`.:/workspace:cached`), instantly syncing to your WSL filesystem and Git.

### Option 2: Command Line (Docker Compose)

If you prefer starting services without opening VS Code Dev Containers:

```bash
# 1. Start all containers in the background
docker compose up -d

# 2. Run the automated site setup & theme activator
bash scripts/init-site.sh
```

---

## 🎨 Gutenberg Block Theme (`blcmesa-theme`)

The site is built on a clean, modern **Gutenberg Full Site Editing (FSE)** block theme located in [`wp-content/themes/blcmesa-theme/`](file://./wp-content/themes/blcmesa-theme/).

### Key Theme Features:
- **`theme.json`**: Controls the design system—typography, fluid font sizes, and church color palette (Navy `#1A365D`, Desert Gold `#D69E2E`, Warm White `#F7FAFC`, Slate `#2D3748`).
- **Full Site Editing (FSE)**: Edit the Header, Footer, and page layouts visually from `http://localhost:8080/wp-admin` under **Appearance > Editor**.
- **Block Patterns**: Pre-designed blocks that volunteers can drop into pages with one click.

---

## 🤝 Volunteer Training Guide: Weekly Sermon Video Posts

Gutenberg makes weekly sermon posting straightforward for church volunteers without needing technical knowledge or HTML coding:

1. **Log In**: Open [http://localhost:8080/wp-admin](http://localhost:8080/wp-admin) and log in.
2. **Create New Post**: Click **Posts** in the left sidebar, then click **Add New Post**.
3. **Add Sermon Title**: Type the sermon title (e.g., *"Sunday Worship - Grace and Hope - October 5, 2026"*).
4. **Insert the Sermon Pattern**:
   - In the block editor, click the **`+` (Block Inserter)** icon in the top left.
   - Click the **Patterns** tab.
   - Select the category **"BLC Sermons & Media"**.
   - Click **"Weekly Sermon Video Post"**.
5. **Update Video & Details**:
   - Click the video embed block and click **Edit URL** to paste this week's YouTube or Vimeo link.
   - Click into the text boxes to edit the Preacher name, Scripture readings, and brief sermon summary.
6. **Publish**: Click the blue **Publish** button in the top right corner. The sermon is now live on the website!

---

## 🗄️ Database Tools & Production Migration

When you are ready to review hosting options with Pastor Brian and deploy to a production host, use these built-in scripts:

### 1. Database Backups
Create a compressed snapshot of the database at any time:
```bash
bash scripts/backup-db.sh
```
Snapshots are saved to `backups/blcmesa_db_<timestamp>.sql.gz`.

### 2. Database Restore
Restore an existing database snapshot:
```bash
bash scripts/restore-db.sh backups/blcmesa_db_YYYYMMDD_HHMMSS.sql.gz
```

### 3. Production Hosting Export
Generate a ready-to-upload migration package:
```bash
bash scripts/export-for-hosting.sh
```
This produces `backups/blcmesa_migration_package_<timestamp>.zip` containing:
- Full MySQL database export (`database.sql`)
- Custom theme (`wp-content/themes/blcmesa-theme`)
- Any active plugins (`wp-content/plugins`)
- Media uploads (`wp-content/uploads`)
- Migration instructions for standard hosts (Bluehost, DreamHost, SiteGround, etc.)

### 4. Media & File Sync from Existing Live Site
Download all media attachments from the WordPress XML export file into `wp-content/uploads/` with crash-recovery:
```bash
# Test on first 5 files
python3 scripts/sync-media.py --limit 5

# Full sync (auto-detects newest XML in backups/)
python3 scripts/sync-media.py

# Verify if remote files were modified with the same name
python3 scripts/sync-media.py --verify-remote
```

> [!NOTE]
> For detailed documentation on all available flags, architecture, and CLI options, see [**`scripts/README.md`**](file://./scripts/README.md).

---

## 🛠️ Handy WP-CLI Commands

You can run WP-CLI commands either inside the Dev Container terminal or from the host using Docker Compose:

```bash
# Check WordPress status
docker compose run --rm wpcli wp core is-installed

# List active themes
docker compose run --rm wpcli wp theme list

# List active plugins
docker compose run --rm wpcli wp plugin list

# Search and replace URLs (useful when migrating to live domain)
docker compose run --rm wpcli wp search-replace "http://localhost:8080" "https://blcmesa.org" --all-tables
```
