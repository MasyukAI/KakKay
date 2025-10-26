# KakKay Application Setup Guide

Complete setup instructions for the KakKay e-commerce application.

## Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Development Tools](#development-tools)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)

## Requirements

### PHP & Extensions
- PHP 8.4+
- Required extensions:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

### Node.js & npm
- Node.js 18+
- npm 9+

### Database
- PostgreSQL 15+ (primary)
- MySQL 8.0+ (supported)

### Other
- Composer 2.7+
- Redis 7+ (for queues and caching)

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/MasyukAI/KakKay.git
cd KakKay
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

**IMPORTANT:** This step is required for PDF generation (invoices, receipts).

```bash
npm install
```

This installs:
- Vite (for frontend bundling)
- Tailwind CSS v4
- **Puppeteer** (required for PDF generation via Spatie Laravel PDF)
- Other frontend dependencies

### 4. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure:

```env
# Application
APP_NAME=KakKay
APP_ENV=local
APP_DEBUG=true
APP_URL=https://kakkay.test

# Database (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=kakkay
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Redis (for queues & cache)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

# CHIP Payment Gateway
CHIP_COLLECT_API_KEY=your_chip_api_key
CHIP_COLLECT_BRAND_ID=your_brand_id
CHIP_COLLECT_ENVIRONMENT=sandbox # or production
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Seed Database (Optional)

```bash
php artisan db:seed
```

### 7. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

## Configuration

### Cart Storage

Configure cart storage driver in `config/cart.php`:

```php
'storage' => env('CART_STORAGE', 'database'), // session, cache, database
```

### PDF Generation

The application uses Spatie Laravel PDF for generating invoices and receipts. This requires:

1. **Puppeteer** - Installed via `npm install` (already included in package.json)
2. **Node.js** - Version 18+ must be available in PATH

To verify PDF generation works:

```bash
php artisan test tests/Feature/Orders/OrderInvoiceTest.php
```

If you see "Cannot find module 'puppeteer'" error:
- Run `npm install` to install dependencies
- Ensure Node.js is in your PATH

### Cloudflare Tunnel (for Webhook Testing)

For testing webhooks locally (CHIP payment gateway callbacks):

1. Install cloudflared:
```bash
brew install cloudflared
```

2. Create tunnel configuration (see `docs/CHIP_WEBHOOK_PAYLOADS.md` for details)

3. Start tunnel:
```bash
cloudflared tunnel run kakkay-local
```

## Development Tools

### Code Quality

```bash
# Format code with Laravel Pint
composer format
# or
vendor/bin/pint

# Run PHPStan static analysis
composer analyse
# or
vendor/bin/phpstan analyse

# Run Rector for automated refactoring
vendor/bin/rector process
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Feature/Orders/OrderInvoiceTest.php

# Run with filter
php artisan test --filter=invoice

# Parallel execution (faster)
php artisan test --parallel
```

### Package Tests

For monorepo packages, run tests from package directory:

```bash
cd packages/commerce
vendor/bin/pest --parallel
```

### Local Development

```bash
# Start development server (Vite)
npm run dev

# In another terminal, run queue worker
php artisan queue:work

# Optional: Run schedule
php artisan schedule:work
```

## Troubleshooting

### PDF Generation Fails

**Error:** `Cannot find module 'puppeteer'`

**Solution:**
```bash
npm install
```

### Node.js Version Issues

**Error:** Various npm or puppeteer errors

**Solution:**
```bash
# Check Node.js version
node --version  # Should be 18+

# Update Node.js if needed (using nvm)
nvm install 18
nvm use 18
```

### Missing node_modules

If `node_modules` directory is missing after git clone:

```bash
npm install
```

### Webhook Tests Failing

If webhook tests fail with signature verification errors:

1. Check Cloudflare tunnel is running
2. Verify webhook URLs in tests match tunnel configuration
3. Disable signature verification for tests:
```php
config(['chip.webhooks.verify_signature' => false]);
```

### Database Connection Issues

**PostgreSQL connection refused:**
```bash
# Check PostgreSQL is running
brew services start postgresql@15

# Or with Docker
docker-compose up -d postgres
```

### Redis Connection Issues

```bash
# Check Redis is running
brew services start redis

# Or with Docker
docker-compose up -d redis
```

### Permission Issues

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

## Production Deployment

### 1. Environment Setup

```bash
composer install --optimize-autoloader --no-dev
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Queue Workers

Set up Supervisor or similar process manager:

```ini
[program:kakkay-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/worker.log
stopwaitsecs=3600
```

### 3. Cron Job

```bash
* * * * * cd /path/to/kakkay && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Web Server

Configure Nginx or Apache to serve the application from the `public` directory.

Example Nginx configuration:

```nginx
server {
    listen 80;
    server_name kakkay.my;
    root /var/www/kakkay/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Additional Resources

- [CHIP Webhook Payloads](CHIP_WEBHOOK_PAYLOADS.md) - Webhook testing and payload examples
- [Cart Architecture](CART_ARCHITECTURE_VERIFICATION.md) - Cart system documentation
- [Order Invoice Implementation](ORDER_INVOICE_IMPLEMENTATION.md) - Invoice generation guide
- [Laravel Documentation](https://laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)

## Support

For issues or questions:
- Check existing documentation in `docs/` directory
- Review package READMEs in `packages/commerce/packages/`
- Check Laravel logs: `storage/logs/laravel.log`
