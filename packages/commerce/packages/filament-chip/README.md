# AIArmada Filament CHIP

> A Filament admin plugin for exploring CHIP payment data ingested by the [aiarmada/chip](https://github.com/aiarmada/chip) package.

[![Packagist](https://img.shields.io/packagist/v/aiarmada/filament-chip.svg?style=flat-square)](https://packagist.org/packages/aiarmada/filament-chip)

## Features

- 📊 **Purchase Management** – View, filter, and manage CHIP payment purchases
- 💰 **Transaction Explorer** – Explore payment transactions with detailed information
- 🔍 **Webhook Logs** – Monitor webhook events and their processing status
- 📈 **Analytics Dashboard** – Track payment metrics and trends
- 🔐 **Secure** – Built on Filament v4 with proper authorization

## Requirements

- PHP ^8.2
- Laravel ^12.0
- Filament ^4.0
- aiarmada/chip ^1.0

## Installation

```bash
composer require aiarmada/filament-chip
```

The service provider will be automatically registered.

### Configuration

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag="filament-chip-config"
```

### Register the Plugin

Add the plugin to your Filament panel in `app/Providers/Filament/AdminPanelProvider.php`:

```php
use AIArmada\FilamentChip\FilamentChipPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(FilamentChipPlugin::make());
}
```

## Usage

### Viewing Purchases

Navigate to the CHIP section in your Filament admin panel to view:

- **Purchases** – All payment purchases with status, amount, and customer details
- **Transactions** – Individual transaction records
- **Webhooks** – Webhook event logs and processing status

### Filtering & Search

The package provides comprehensive filtering options:

- Filter by status (paid, pending, failed, refunded)
- Search by reference, customer email, or transaction ID
- Date range filtering
- Amount range filtering

### Actions

Available actions on purchase records:

- **View Details** – View complete purchase information
- **Refund** – Process refunds for paid purchases
- **Cancel** – Cancel pending purchases
- **Resend Invoice** – Re-send purchase invoices

## Configuration

The plugin can be customized in `config/filament-chip.php`:

```php
return [
    // Default navigation settings
    'navigation' => [
        'group' => 'CHIP Payments',
        'sort' => 10,
    ],
    
    // Resource configuration
    'resources' => [
        'purchase' => [
            'enabled' => true,
            'label' => 'Purchases',
        ],
        'webhook' => [
            'enabled' => true,
            'label' => 'Webhooks',
        ],
    ],
];
```

## Documentation

For detailed CHIP API documentation, see the [aiarmada/chip package docs](https://github.com/aiarmada/chip).

For Filament documentation, visit [filamentphp.com](https://filamentphp.com).

## Testing

```bash
composer test
```

Run with coverage:

```bash
composer test-coverage
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](../../CONTRIBUTING.md) for details.

### Development Setup

```bash
git clone https://github.com/aiarmada/filament-chip.git
cd filament-chip
composer install
composer test
```

## Security

If you discover any security issues, please email security@aiarmada.com instead of using the issue tracker.

## Credits

- [AIArmada Team](https://aiarmada.com)
- [All Contributors](https://github.com/aiarmada/filament-chip/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
