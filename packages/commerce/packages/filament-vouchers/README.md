# AIArmada Filament Vouchers Plugin

A Filament v4 plugin that provides a futuristic admin experience for managing vouchers powered by the `aiarmada/vouchers` package. It ships with rich resources, usage analytics, and optional deep links to the Filament Cart plugin when it is present.

## Features

- Manage vouchers with an opinionated Filament resource.
- Review voucher usage history, including manual redemptions.
- Stats overview widget with live metrics for redemptions and discount totals.
- Optional integration with the Filament Cart plugin to jump directly from voucher usage records to matching cart snapshots.
- Configurable owner selectors that honor the voucher ownership resolver in your application.

## Installation

```bash
composer require aiarmada/filament-vouchers
```

Register the plugin inside your Filament panel provider:

```php
use AIArmada\FilamentVouchers\FilamentVouchers;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentVouchers::make(),
        ]);
}
```

### Optional Filament Cart integration

If `aiarmada/filament-cart` is installed, the plugin automatically surfaces cart shortcuts in voucher usage tables and infolists. No additional configuration is required.

## Configuration

Publish the configuration to customize navigation groups, owner selectors, and default currencies:

```bash
php artisan vendor:publish --tag=filament-vouchers-config
```

The config file ships with extensive inline documentation to help you tailor the experience to your domain (e.g. vendor / store ownership models).

## Testing

```bash
vendor/bin/pest --filter=FilamentVouchers
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
