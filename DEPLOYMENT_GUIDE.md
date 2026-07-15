# TMail v8.0.1 — GoogieHost Deployment Guide

## Overview

TMail can be deployed to GoogieHost shared hosting using **GitHub Actions** (auto-deploy) or **manual upload** via File Manager.

---

## Option A: GitHub Actions Auto-Deploy (Recommended)

### Step 1: Create MySQL Database in GoogieHost cPanel

1. Log in to your GoogieHost cPanel (`cloud3.googiehost.com:2222`)
2. Click **"Databases"** under ACCOUNT MANAGER
3. Create a new database:
   - **Database Name**: `hjhxcekh_tmail` (or any name)
   - **Username**: Create a new MySQL user
   - **Password**: Set a strong password and **SAVE IT**
4. Make sure the user is **assigned to the database** with **ALL PRIVILEGES**

### Step 2: Create GitHub Repository

1. Go to [github.com](https://github.com) → New Repository
2. Name it `tmail` (or any name)
3. Push your `TMail v8.0.1` project to the `main` branch:
   ```bash
   cd "c:/Users/Abhix/Desktop/TMail v8.0.1"
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/YOUR_USERNAME/tmail.git
   git branch -M main
   git push -u origin main
   ```

### Step 3: Add GitHub Secrets

Go to your GitHub repo → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**

Add these secrets:

| Secret Name | Value |
|-------------|-------|
| `FTP_HOST` | `tempumail.cu.ma` |
| `FTP_USERNAME` | `hjhxcekh` |
| `FTP_PASSWORD` | Your GoogieHost panel password |
| `FTP_REMOTE_PATH` | `/public_html` |
| `APP_KEY` | (leave empty — installer generates this) |
| `APP_URL` | `https://tempumail.cu.ma` |
| `DB_HOST` | `localhost` |
| `DB_DATABASE` | `hjhxcekh_tmail` (your database name) |
| `DB_USERNAME` | `hjhxcekh_youruser` (your DB username) |
| `DB_PASSWORD` | `your_mysql_password` |
| `SESSION_DOMAIN` | `.cu.ma` |
| `MAIL_FROM_ADDRESS` | `hello@tempumail.cu.ma` |

### Step 4: Deploy!

1. Push any change to `main` branch, or
2. Go to **Actions** tab → **Deploy to GoogieHost** → **Run workflow**

The workflow will:
- Install PHP & Node dependencies
- Build frontend assets
- Upload everything to GoogieHost via FTP

### Step 5: Set Up Cron Job

1. In GoogieHost cPanel, search for **"Cron Jobs"**
2. Add this cron job:
   ```
   * * * * * cd /home/hjhxcekh/public_html && php artisan schedule:run >> /dev/null 2>&1
   ```
3. Set frequency to **Every minute**

---

## Option B: Manual Upload via File Manager

### Step 1: Create MySQL Database

Same as Option A, Step 1.

### Step 2: Zip and Upload

1. Zip your entire `TMail v8.0.1` project on your computer
2. Open **GoogieHost File Manager** → navigate to `public_html/`
3. **Delete** `index.html` and `cgi-bin` folder
4. Click **"Upload"** → upload your zip file
5. **Extract** the zip inside `public_html/`
6. If extracted into a subfolder, move all contents up to `public_html/`

### Step 3: Directory Structure

```
public_html/
├── .htaccess          (redirects to public/)
├── artisan
├── composer.json
├── composer.lock
├── .env
├── app/
├── bootstrap/
├── config/
├── database/
├── lang/
├── public/            (contains index.php, .htaccess, build/)
├── resources/
├── routes/
├── storage/
└── vendor/
```

### Step 4: Update .env

1. Open `.env` in File Manager editor
2. Update these values:
   ```
   APP_URL=https://tempumail.cu.ma
   DB_HOST=localhost
   DB_DATABASE=hjhxcekh_tmail
   DB_USERNAME=hjhxcekh_youruser
   DB_PASSWORD=your_mysql_password
   SESSION_DOMAIN=.cu.ma
   MAIL_FROM_ADDRESS="hello@tempumail.cu.ma"
   ```

### Step 5: Set Permissions

Set these directories to **755**:
- `storage/`
- `storage/app/`
- `storage/app/public/`
- `storage/framework/`
- `storage/framework/cache/`
- `storage/framework/sessions/`
- `storage/framework/views/`
- `storage/logs/`
- `bootstrap/cache/`

### Step 6: Run Installer

1. Visit **https://tempumail.cu.ma**
2. The TMail installer will appear
3. **Step 1 — Database Setup**: Enter your MySQL credentials
4. **Step 2 — Admin Account**: Create your admin login
5. **Step 3 — Finish**: Complete installation

### Step 7: Cron Job

Same as Option A, Step 5.

---

## Troubleshooting

### 500 Internal Server Error
- Check `storage/logs/laravel.log` for errors
- Make sure `.env` has correct DB credentials
- Ensure `APP_KEY` is set (the installer generates this)
- Verify directory permissions are 755

### Installer Not Loading
- Make sure `storage/installed` file does NOT exist
- Check that `.htaccess` files are properly uploaded
- Verify `public/.htaccess` exists

### Cron Job Not Working
- Verify the cron job path is correct
- Check if PHP CLI is available on your hosting

---

## Your GoogieHost Details

| Setting | Value |
|---------|-------|
| Domain | tempumail.cu.ma |
| cPanel | cloud3.googiehost.com:2222 |
| FTP Host | tempumail.cu.ma |
| FTP Username | hjhxcekh |
| Server Path | /home/hjhxcekh/ |
| Disk | 1 GB |
| Bandwidth | 100 GB |
