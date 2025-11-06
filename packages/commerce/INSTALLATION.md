# Commerce Installation Guide

This guide explains how to install the Commerce package suite in your Laravel application.

## Quick Start

```bash
# Install with interactive prompts
php artisan commerce:install
```

The installer will:
1. ✅ Always install Cart, Stock, and Vouchers (required)
2. ✅ Prompt you to select optional packages (CHIP, JNT, Filament)
3. ✅ Publish configuration files
4. ✅ Run migrations
5. ✅ Set up environment variables

## Installation Options

### Interactive Mode (Recommended)
```bash
php artisan commerce:install
```
You'll be prompted to select which optional packages to install using a beautiful multiselect interface.

### Install All Packages
```bash
php artisan commerce:install --all
```
Installs all required and optional packages without prompts.

### Install Specific Packages
```bash
# Install with CHIP payment gateway
php artisan commerce:install --chip

# Install with J&T Express shipping
php artisan commerce:install --jnt

# Install with Filament UI components
php artisan commerce:install --filament

# Combine multiple options
php artisan commerce:install --chip --jnt --filament
```

### Force Overwrite
```bash
php artisan commerce:install --force
```
Overwrites existing configuration files. Use with caution!

## Package Details

### Required Packages (Always Installed)

#### Cart Management
- Configuration: `config/cart.php`
- Migrations: Auto-run
- Features: Shopping cart, sessions, conditions

#### Stock Management
- Configuration: `config/stock.php`
- Migrations: Auto-run
- Features: Inventory tracking, stock levels

#### Voucher System
- Configuration: `config/vouchers.php`
- Migrations: Auto-run
- Features: Discount codes, promotions

### Optional Packages

#### CHIP Payment Gateway (`--chip`)
- Configuration: `config/chip.php`
- Migrations: Auto-run
- Environment Variables:
  ```env
  CHIP_COLLECT_API_KEY=your_chip_collect_api_key
  CHIP_COLLECT_BRAND_ID=your_chip_brand_id
  CHIP_SEND_API_KEY=your_chip_send_api_key
  CHIP_SEND_API_SECRET=your_chip_send_api_secret
  ```
- Features: Payment processing, webhooks, refunds

#### J&T Express Shipping (`--jnt`)
- Configuration: `config/jnt.php`
- Migrations: Auto-run
- Environment Variables:
  ```env
  JNT_CUSTOMER_CODE=your_jnt_customer_code
  JNT_PASSWORD=your_jnt_password
  JNT_PRIVATE_KEY=your_jnt_private_key
  ```
- Features: Shipping orders, tracking, webhooks

#### Filament UI Components (`--filament`)
- Configuration: `config/filament-cart.php`, `config/filament-chip.php`
- Migrations: Auto-run
- Features: Admin panels for Cart and CHIP

## Post-Installation Steps

After installation, follow these steps:

### 1. Configure Environment Variables
Open your `.env` file and update the placeholder values:

```env
# Update Cart settings
CART_STORAGE_DRIVER=database
CART_DEFAULT_CURRENCY=MYR

# If you installed CHIP
CHIP_COLLECT_API_KEY=your_actual_api_key
CHIP_COLLECT_BRAND_ID=your_actual_brand_id

# If you installed J&T
JNT_CUSTOMER_CODE=your_actual_customer_code
JNT_PASSWORD=your_actual_password
JNT_PRIVATE_KEY=your_actual_private_key
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Review Configuration Files
Check the published config files in your `config/` directory:
- `config/cart.php` - Cart settings
- `config/stock.php` - Stock settings
- `config/vouchers.php` - Voucher settings
- `config/chip.php` - CHIP settings (if installed)
- `config/jnt.php` - J&T settings (if installed)

### 4. Set Up Webhooks (If Applicable)

#### CHIP Webhooks
```bash
# Register webhook endpoint
POST https://gate.chip-in.asia/api/v1/webhooks/
```
Point to: `https://your-domain.com/webhooks/chip/{webhook_id}`

#### J&T Webhooks
Configure in J&T Express dashboard:
- Webhook URL: `https://your-domain.com/webhooks/jnt`

### 5. Test Your Installation
```bash
# Check configurations
php artisan config:clear
php artisan config:cache

# If you installed CHIP
php artisan chip:health-check

# If you installed JNT
php artisan jnt:config:check

# Test cart functionality
php artisan tinker
>>> Cart::add('product-123', 'Test Product', 99.99, 1);
>>> Cart::content();
```

## Troubleshooting

### Configuration Errors
If you see errors about missing configuration:
```
RuntimeException: Required configuration key [cart.storage] is not set.
Please publish the configuration file with: php artisan vendor:publish --tag=cart-config
```

**Solution**: Run the suggested publish command.

### Migration Errors
If migrations fail:
```bash
# Check migration status
php artisan migrate:status

# Rollback and retry
php artisan migrate:rollback
php artisan migrate
```

### Package Not Found
If package classes aren't found:
```bash
# Clear and rebuild autoload
composer dump-autoload
php artisan clear-compiled
php artisan config:clear
```

## Uninstalling Packages

To remove a package:

```bash
# 1. Remove from composer
composer remove aiarmada/chip

# 2. Remove published config
rm config/chip.php

# 3. Remove migrations (optional)
# Be careful - this will delete data!
php artisan migrate:rollback --path=vendor/aiarmada/chip/database/migrations
```

## Getting Help

- **Documentation**: Check package-specific docs in each package's README
- **Issues**: Report issues on GitHub
- **Support**: Contact support team

## Version Information

- Laravel: 12.x
- PHP: 8.4+
- All packages: Latest stable versions

## Additional Resources

- [Cart Documentation](../cart/README.md)
- [CHIP Documentation](../chip/README.md)
- [J&T Documentation](../jnt/README.md)
- [Stock Documentation](../stock/README.md)
- [Vouchers Documentation](../vouchers/README.md)
