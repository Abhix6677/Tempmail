# TMail v8.0.1 - Temporary Email Service

A Laravel-based temporary email service application with IMAP and Delivery engine support, multi-language support, and a modern frontend.

## Project Overview

TMail is a self-hosted temporary email service that allows users to create disposable email addresses. It supports multiple engines (IMAP and Delivery), multiple themes, and includes a comprehensive admin panel.

## System Requirements

### Server Requirements
- **PHP**: >= 8.2
- **Composer**: >= 2.x
- **MySQL/MariaDB**: >= 5.7
- **Node.js**: >= 18.x
- **NPM**: >= 9.x

### PHP Extensions
- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath
- IMAP (for IMAP engine)
- GD
- Fileinfo

## Installation Guide

### 1. Install PHP (Required)

**Windows:**
- Download PHP from https://windows.php.net/download/
- Extract to a directory (e.g., `C:\php`)
- Add `C:\php` to your system PATH
- Configure `php.ini` with required extensions

**Verify Installation:**
```bash
php --version
composer --version
```

### 2. Install Database (MySQL/MariaDB)

**Windows (XAMPP/WAMP):**
- Download and install XAMPP: https://www.apachefriends.org/
- Start MySQL service
- Create database: `tmail`

**Manual Setup:**
```sql
CREATE DATABASE tmail CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Install Node.js

- Download from https://nodejs.org/
- Verify: `node --version` and `npm --version`

### 4. Project Setup

#### Step 1: Clone/Extract the Project
```bash
cd "c:/Users/Abhix/Desktop/adirender/play2earn/temp-mail-app/TMail v8.0.1"
```

#### Step 2: Install PHP Dependencies
```bash
composer install
```

#### Step 3: Install Node Dependencies
```bash
npm install
```

#### Step 4: Configure Environment
Copy the `.env.example` to `.env` (already done):
```bash
copy .env.example .env
```

Update `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tmail
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### Step 5: Generate Application Key
```bash
php artisan key:generate
```

#### Step 6: Run Migrations
```bash
php artisan migrate
```

#### Step 7: Seed Database
```bash
php artisan db:seed
```

#### Step 8: Create Storage Link
```bash
php artisan storage:link
```

#### Step 9: Build Assets
```bash
npm run build
```

### 5. Start the Development Server

#### Development Mode
```bash
php artisan serve
```

This will start the server at `http://localhost:8000`

#### Production Build
```bash
npm run build
php artisan optimize
php artisan config:cache
php artisan route:cache
```

## Configuration

### First Time Setup

1. **Access Installer**: Visit `http://localhost:8000/installer`
2. Complete the installation wizard
3. Set up your admin account
4. Configure your email engine (IMAP or Delivery)

### Email Engine Configuration

#### IMAP Engine
- Configure IMAP server settings in the admin panel
- Requires access to an IMAP email server
- Real-time email fetching from configured domains

#### Delivery Engine
- Configure delivery key in the admin panel
- Use the API endpoints to store messages
- Better for integration with existing mail systems

### Domains Configuration

Add your email domains in the admin panel under:
- **Admin > Domains**
- Choose type: open, member, or premium
- Set active status

## Project Structure

```
TMail v8.0.1/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # API and Web controllers
│   │   └── Middleware/        # Custom middleware
│   ├── Livewire/              # LiveWire components
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic
│   └── View/Components/       # Blade components
├── config/                    # Application configuration
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── lang/                      # Translation files
├── public/                    # Public assets
├── resources/
│   ├── css/                   # Stylesheets
│   ├── js/                    # JavaScript files
│   ├── views/                 # Blade templates
│   └── sass/                  # SCSS files
├── routes/                    # Route definitions
├── storage/                   # Application storage
├── tests/                     # Test files
├── vendor/                    # Composer dependencies (after install)
├── composer.json              # PHP dependencies
├── package.json               # Node dependencies
├── vite.config.js             # Vite configuration
└── .env                       # Environment configuration
```

## Key Features

### Frontend Features
- 🌍 Multi-language support (15+ languages)
- 🎨 Multiple themes (default, mantis, nebula)
- 📧 Real-time email fetching
- 📱 Responsive design
- 🌙 Dark mode support
- 🔔 Email notifications
- 📎 Attachment support

### Backend Features
- 🔐 Role-based access control (admin, member)
- 📊 Analytics dashboard
- ⚙️ Comprehensive settings panel
- 🌐 Multi-domain support
- 📝 Blog/CMS system
- 🎯 Ad management
- 🌐 Social media integration
- 🔒 App locking with password
- 📈 Statistics tracking

### API Features
- RESTful API for external integration
- Email generation API
- Message retrieval API
- Domain management API
- API key authentication

## Troubleshooting

### Common Issues

**Issue: PHP command not found**
- Solution: Add PHP to your system PATH and restart terminal

**Issue: Composer not found**
- Solution: Install Composer from https://getcomposer.org/

**Issue: Database connection failed**
- Solution: Verify MySQL service is running and credentials in `.env` are correct

**Issue: Permissions denied on storage**
- Solution: Set proper write permissions:
  ```bash
  php artisan storage:link
  # On Windows, ensure storage directory is writable
  ```

**Issue: Assets not loading**
- Solution: Run `npm run build` and clear cache:
  ```bash
  php artisan view:clear
  php artisan config:clear
  ```

**Issue: IMAP connection failed**
- Solution: Verify IMAP server settings in admin panel:
  - Host, port, encryption
  - Username and password
  - SSL certificate validation

## Development

### Running in Development Mode

```bash
# Start PHP server
php artisan serve

# In another terminal, start Vite dev server
npm run dev
```

### Code Quality

```bash
# Run Pint (Laravel Prettier)
./vendor/bin/pint

# Run tests
php artisan test
```

### Creating Admin User

After installation, access `/admin` and register with the default registration form. The first user will need role 7 for admin access. You can update the role in the database:

```sql
UPDATE users SET role = 7 WHERE email = 'your@email.com';
```

## Security Considerations

1. **Change default cron password** in settings after installation
2. **Keep PHP and dependencies updated**
3. **Use HTTPS in production**
4. **Set strong database passwords**
5. **Configure firewall rules**
6. **Regular backups of database and storage**
7. **Monitor access logs**
8. **Keep API keys secure**

## License

This is a commercial application. Please verify your license key usage.

## Support

- **Documentation**: https://tmail.hp.gl/docs/
- **Help Desk**: https://helpdesk.thehp.in
- **Issues**: Check logs in `storage/logs/`

## Updates

The application includes an auto-update feature. Check for updates in the admin panel under **Admin > Updates**.

## File Issues Fixed

During code review, the following issues were addressed:

1. ✅ **Missing package.json**: Created with required dependencies for Laravel Vite, Tailwind CSS, and Alpine.js
2. ✅ **Missing .env file**: Copied from .env.example
3. ✅ **Storage directories**: Verified required directories exist
4. ✅ **Code syntax**: Reviewed all PHP, JavaScript, and Blade files - no major syntax errors found

## Notes

- This is a Laravel 12.x application using the latest features
- Uses Livewire 3.x for interactive components
- Vite for asset compilation
- Supports both development and production environments
- Includes comprehensive seeding for initial setup

## Environment Variables

Key environment variables in `.env`:

```env
APP_NAME=Laravel                    # Application name
APP_ENV=production                 # Environment (local/production)
APP_DEBUG=false                    # Debug mode
APP_URL=http://localhost           # Application URL

DB_CONNECTION=mysql                # Database connection
DB_HOST=127.0.0.1                  # Database host
DB_PORT=3306                       # Database port
DB_DATABASE=tmail                  # Database name
DB_USERNAME=root                   # Database username
DB_PASSWORD=your_password          # Database password

MAIL_MAILER=log                    # Mail driver
MAIL_HOST=127.0.0.1                # Mail host
MAIL_PORT=2525                     # Mail port

ENABLE_TMAIL_LOGS=false            # Enable TMail logs
API_REQUEST_LIMIT=60               # API request limit
```

## Cron Job Setup

Set up a cron job for automatic email fetching:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

Or use the dedicated cron endpoint:
```
http://yourdomain.com/api/cron/YOUR_CRON_PASSWORD
```

Replace `YOUR_CRON_PASSWORD` with the password from settings.

## Asset Compilation

For production:
```bash
npm run build
```

For development (with hot reload):
```bash
npm run dev
```

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev/)