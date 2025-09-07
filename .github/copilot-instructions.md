# KakKay Laravel Application

KakKay is a Laravel 12 e-commerce application with Filament v4 admin panel, Livewire v3/Volt v1 for interactivity, and comprehensive cart/payment integration. It includes custom packages for cart management and CHIP payment gateway integration.

**ALWAYS reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.**

## Working Effectively

### Bootstrap, Build, and Test the Repository

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Install PHP dependencies - NEVER CANCEL: Takes 5-6 minutes. Set timeout to 10+ minutes.
composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# 3. Generate application key
php artisan key:generate

# 4. Install Node dependencies - Takes ~10 seconds
npm install

# 5. Build assets - Takes ~3 seconds
npm run build

# 6. Create database and run migrations - Takes ~1 second
touch database/database.sqlite
php artisan migrate

# 7. Run tests - NEVER CANCEL: Takes ~9 seconds. Set timeout to 2+ minutes.
./vendor/bin/pest

# 8. Run linter - Takes <1 second
vendor/bin/pint --dirty
```

### Start the Application

```bash
# For development (requires LARAVEL_BYPASS_ENV_CHECK=1 in CI)
LARAVEL_BYPASS_ENV_CHECK=1 composer run dev

# For basic testing/validation
php artisan serve
# Application will be available at http://127.0.0.1:8000

# Laravel Herd integration (when available)
# Application auto-available at https://kakkay.test
```

## System Requirements

### Required
- **PHP**: 8.4+ (packages require 8.4, main app works with 8.3.6 using --ignore-platform-reqs)
- **Node.js**: 20+ (tested with 20.19.4)
- **Composer**: 2.0+
- **SQLite**: For database (default configuration)

### Platform Workarounds
- **PHP Version Mismatch**: Use `--ignore-platform-reqs` with composer install if PHP 8.3.x available instead of 8.4+
- **CI Environment**: Set `LARAVEL_BYPASS_ENV_CHECK=1` for Vite in CI/testing environments

## Testing & Quality Assurance

### Run Tests
```bash
# Full test suite - NEVER CANCEL: Takes ~9 seconds. Set timeout to 5+ minutes.
./vendor/bin/pest

# Specific test categories
php artisan test --filter="CartShipping"     # Cart functionality
php artisan test tests/Feature/              # Feature tests only
php artisan test tests/Unit/                 # Unit tests only

# Test statistics: 57+ tests with high coverage
```

### Code Quality
```bash
# Format code - Takes <1 second
vendor/bin/pint --dirty

# Clear caches when needed
php artisan config:clear
php artisan route:clear  
php artisan view:clear
```

## Validation Scenarios

### ALWAYS test these scenarios after making changes:

1. **Basic Application Health**:
   ```bash
   php artisan route:list    # Verify routes load
   php artisan serve         # Start server successfully
   ```

2. **Cart Functionality**: 
   ```bash
   php artisan test --filter="CartShipping"
   ```

3. **Database Operations**:
   ```bash
   php artisan migrate:status    # Check migrations
   touch database/database.sqlite && php artisan migrate  # Fresh setup
   ```

4. **Asset Building**:
   ```bash
   npm run build    # Verify assets compile
   ```

## Project Structure & Key Components

### Core Laravel Structure (v12 Streamlined)
- **app/**: Application logic (Models, Controllers, Services, Livewire components)
- **bootstrap/app.php**: Main application bootstrap and middleware registration
- **bootstrap/providers.php**: Service provider registration
- **config/**: Configuration files
- **database/**: Migrations, seeders, factories
- **resources/**: Views, CSS, JS assets
- **routes/**: Route definitions (web.php, auth.php, console.php)
- **tests/**: Pest v4 test suite

### Local Packages
- **packages/masyukai/cart/**: Comprehensive cart management system
- **packages/masyukai/chip/**: CHIP payment gateway integration

### Frontend Stack
- **Livewire v3**: Server-side UI framework
- **Volt v1**: Single-file Livewire components  
- **Flux UI v2**: Component library (free edition)
- **Tailwind CSS v4**: Utility-first CSS framework
- **Filament v4**: Admin panel framework

### Key Directories
- **app/Filament/**: Admin panel resources and pages
- **app/Livewire/**: Livewire components
- **app/Services/**: Business logic services
- **resources/views/**: Blade templates

## Common Commands Reference

### Artisan Commands
```bash
# Development helpers
php artisan route:list           # List all routes
php artisan tinker              # Interactive shell
php artisan queue:work          # Process queues
php artisan serve              # Start development server

# Cache management  
php artisan config:cache       # Cache configuration
php artisan route:cache        # Cache routes
php artisan view:cache         # Cache views

# Filament specific
php artisan filament:upgrade   # Upgrade Filament assets
php artisan make:filament-resource ModelName  # Create admin resource
```

### Package Management
```bash
# Composer operations
composer install --no-interaction --prefer-dist --optimize-autoloader
composer update --no-interaction
composer dump-autoload

# NPM operations  
npm install                    # Install dependencies
npm run build                  # Build for production
npm run dev                    # Development build with watching
```

## Important Notes

### Build Timing Expectations
- **NEVER CANCEL** these operations - use proper timeouts:
  - Composer install: 5-6 minutes (timeout: 10+ minutes)
  - Test suite: 9 seconds (timeout: 5+ minutes for safety)
  - Asset build: 3 seconds
  - NPM install: 10 seconds

### Environment Variables
- Copy `.env.example` to `.env` before any operations
- Uses SQLite database by default (`database/database.sqlite`)
- Key environment configs:
  - `APP_ENV=local`
  - `DB_CONNECTION=sqlite`
  - `QUEUE_CONNECTION=database`

### Development Workflow
1. Always run the complete bootstrap process for fresh clones
2. Use `vendor/bin/pint --dirty` before committing changes
3. Run relevant tests after making changes
4. Build assets with `npm run build` for production

### Troubleshooting
- **Composer fails**: Use `--ignore-platform-reqs` if PHP version mismatch
- **Vite fails in CI**: Set `LARAVEL_BYPASS_ENV_CHECK=1`
- **Tests fail**: Ensure database is migrated and .env exists
- **Server won't start**: Check if `.env` exists and key is generated

This application is production-ready with comprehensive testing (57+ tests) and follows Laravel best practices.