# Windows Setup Guide for TMail v8.0.1

This guide provides step-by-step instructions for setting up TMail on Windows 11.

## Prerequisites Check

You currently have:
- ✅ Node.js 11.8.0 (installed)
- ✅ NPM (installed)
- ❌ PHP (not installed)
- ❌ Composer (not installed)

## Step 1: Install PHP 8.2+

### Option A: Using XAMPP (Recommended)

1. **Download XAMPP**
   - Visit: https://www.apachefriends.org/
   - Download XAMPP with PHP 8.2+ for Windows
   - File: `xampp-windows-x64-8.2.x-x.xx.x-installer.exe`

2. **Install XAMPP**
   - Run the installer
   - Accept default installation path: `C:\xampp`
   - Install Apache, MySQL, PHP, phpMyAdmin, and FileZilla
   - Complete the installation

3. **Verify Installation**
   ```bash
   # Open Command Prompt and run:
   C:\xampp\php\php --version
   ```

### Option B: Manual PHP Installation

1. **Download PHP**
   - Visit: https://windows.php.net/download/
   - Download PHP 8.2.x x64 Non-Thread Safe (NTS) ZIP
   - Example: `php-8.2.x-nts-Win32-vs16-x64.zip`

2. **Extract PHP**
   - Create folder: `C:\php`
   - Extract ZIP contents to `C:\php`

3. **Configure php.ini**
   - Copy `php.ini-development` to `php.ini`
   - Edit `php.ini` and enable these extensions (remove `;`):
   ```ini
   extension=bcmath
   extension=ctype
   extension=fileinfo
   extension=gd
   extension=imap
   extension=mbstring
   extension=mysqli
   extension=openssl
   extension=pdo_mysql
   extension=tokenizer
   extension=xml
   extension=json
   extension=intl
   extension=zip
   ```

4. **Add PHP to PATH**
   - Press `Win + S`, search "Environment Variables"
   - Click "Edit the system environment variables"
   - Click "Environment Variables"
   - Under "System variables", find "Path", click "Edit"
   - Click "New", add: `C:\php`
   - Click OK on all dialog boxes

5. **Restart Command Prompt** (important!)

6. **Verify Installation**
   ```bash
   php --version
   ```

## Step 2: Install Composer

1. **Download Composer Setup**
   - Visit: https://getcomposer.org/download/
   - Download: `Composer-Setup.exe`

2. **Install Composer**
   - Run the installer
   - When asked for PHP location:
     - If using XAMPP: Browse to `C:\xampp\php\php.exe`
     - If manual PHP: Browse to `C:\php\php.exe`
   - Leave other options as default
   - Complete the installation

3. **Verify Installation**
   ```bash
   composer --version
   ```

## Step 3: Setup MySQL Database

### If Using XAMPP

1. **Start XAMPP Control Panel**
   - Open XAMPP Control Panel
   - Click "Start" next to Apache
   - Click "Start" next to MySQL

2. **Create Database**
   - Open browser, go to: http://localhost/phpmyadmin
   - Click "New" in left sidebar
   - Enter database name: `tmail`
   - Click "Create"
   - Set collation to: `utf8mb4_unicode_ci`

### If Using MySQL Server

1. **Start MySQL Service**
   ```bash
   net start MySQL80
   ```

2. **Create Database**
   - Open MySQL Command Line Client
   ```sql
   CREATE DATABASE tmail CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   EXIT;
   ```

## Step 4: Configure TMail Project

1. **Navigate to Project Directory**
   ```bash
   cd "c:/Users/Abhix/Desktop/adirender/play2earn/temp-mail-app/TMail v8.0.1"
   ```

2. **Update .env File**
   - Open `.env` in your text editor
   - Update database settings:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tmail
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   - If you set a MySQL password, add it after `DB_PASSWORD=`

3. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

## Step 5: Install PHP Dependencies

```bash
composer install
```

This may take a few minutes.

## Step 6: Run Database Migrations

```bash
php artisan migrate
```

## Step 7: Seed Database

```bash
php artisan db:seed
```

## Step 8: Create Storage Link

```bash
php artisan storage:link
```

## Step 9: Build Frontend Assets (Already Done ✅)

Assets were already built successfully. If you need to rebuild:

```bash
npm run build
```

## Step 10: Start Development Server

```bash
php artisan serve
```

The application will start at: **http://localhost:8000**

## Step 11: Complete Installation

1. **Open Browser**
   - Visit: http://localhost:8000/installer

2. **Follow Installation Wizard**
   - Complete all required fields
   - Create admin account
   - Configure email engine (IMAP or Delivery)
   - Save settings

3. **Access Admin Panel**
   - Visit: http://localhost:8000/admin
   - Login with your admin credentials

## Troubleshooting Windows-Specific Issues

### Issue: "php is not recognized"

**Solution:**
1. Make sure PHP is installed correctly
2. Restart Command Prompt after adding to PATH
3. Verify path: `where php`
4. If using XAMPP: use full path: `C:\xampp\php\php --version`

### Issue: "composer is not recognized"

**Solution:**
1. Restart Command Prompt after Composer installation
2. Verify installation: `where composer`
3. Try: `C:\ProgramData\ComposerSetup\bin\composer --version`

### Issue: MySQL Connection Failed

**Solution:**
1. Make sure MySQL service is running:
   ```bash
   # If using XAMPP, check XAMPP Control Panel
   # If using MySQL Server:
   net start MySQL80
   ```
2. Verify credentials in `.env`
3. Test connection:
   ```bash
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

### Issue: Permissions Denied on Storage

**Solution:**
```bash
# In elevated Command Prompt (Run as Administrator)
icacls storage /grant Everyone:(OI)(CI)F
icacls bootstrap/cache /grant Everyone:(OI)(CI)F
```

### Issue: Port 8000 Already in Use

**Solution:**
```bash
# Use different port
php artisan serve --port=8080
```

## Development Workflow

### Start All Services (XAMPP)
1. Open XAMPP Control Panel
2. Start Apache
3. Start MySQL

### Start Development Server
```bash
cd "c:/Users/Abhix/Desktop/adirender/play2earn/temp-mail-app/TMail v8.0.1"
php artisan serve
```

### For Development with Hot Reload
In a new terminal:
```bash
npm run dev
```

### Stop Development Server
Press `Ctrl + C` in the terminal

## Production Deployment

1. **Set Environment**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Optimize Application**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan optimize
   ```

3. **Build Assets**
   ```bash
   npm run build
   ```

## Useful Commands

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Run Tests
```bash
php artisan test
```

### Check System Requirements
```bash
php artisan about
```

### Queue Worker (if needed)
```bash
php artisan queue:work
```

## File Locations

### PHP Locations
- **XAMPP PHP**: `C:\xampp\php\php.exe`
- **Manual PHP**: `C:\php\php.exe`
- **Composer**: `C:\ProgramData\ComposerSetup\bin\composer.bat`

### Database Locations
- **XAMPP MySQL Data**: `C:\xampp\mysql\data`
- **XAMPP phpMyAdmin**: http://localhost/phpmyadmin

### Project Locations
- **Project Root**: `c:/Users/Abhix/Desktop/adirender/play2earn/temp-mail-app/TMail v8.0.1`
- **Storage**: `storage/` directory
- **Logs**: `storage/logs/laravel.log`

## Security Notes

1. **Change Default Passwords**
   - Update database password in `.env`
   - Generate new APP_KEY if exposing to public

2. **File Permissions**
   - Ensure `.env` is not publicly accessible
   - Restrict access to `storage/` and `bootstrap/cache/`

3. **HTTPS in Production**
   - Configure SSL certificate
   - Force HTTPS in production

## Additional Resources

- **Laravel on Windows**: https://laravel.com/docs/installation#windows
- **XAMPP Documentation**: https://www.apachefriends.org/faq_windows.html
- **PHP Windows Config**: https://www.php.net/manual/en/install.windows.configuration.php
- **MySQL on Windows**: https://dev.mysql.com/doc/refman/8.0/en/windows-installation.html

## Quick Reference

### Start Development (XAMPP)
```bash
# 1. Start Apache & MySQL in XAMPP Control Panel
# 2. Open Command Prompt
cd "c:/Users/Abhix/Desktop/adirender/play2earn/temp-mail-app/TMail v8.0.1"
# 3. Start server
php artisan serve
# 4. Visit http://localhost:8000
```

### Start Development (Manual)
```bash
# 1. Start MySQL service
net start MySQL80
# 2. Open Command Prompt
cd "c:/Users/Abhix/Desktop/adirender/play2earn/temp-mail-app/TMail v8.0.1"
# 3. Start server
php artisan serve
# 4. Visit http://localhost:8000
```

---

**Status:** Ready for PHP/Composer installation

**Next Step:** Install PHP 8.2+ and Composer, then follow steps 4-11 above.