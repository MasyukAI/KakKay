# Filament Shipping Plugin

A comprehensive Filament admin interface for managing shipments and shipping operations created by the MasyukAI Shipping package.

## Features

- 🚚 **Complete CRUD Operations**: Create, view, edit, and delete shipments through an intuitive Filament interface
- 📊 **Dashboard Widget**: Real-time shipping statistics showing total shipments, in-transit, delivered, and revenue
- 🔍 **Advanced Filtering**: Filter by status, provider, date range with comprehensive search
- 📱 **Responsive Design**: Works seamlessly on desktop and mobile devices
- 📋 **Detailed Views**: View tracking events and complete shipment history
- 🎨 **Rich UI Components**: Professional interface with status indicators, badges, and color coding

## Installation

You can install the package via composer:

```bash
composer require masyukai/filament-shipping-plugin
```

The plugin will automatically register itself with Laravel's package discovery.

## Usage

Add the plugin to your Filament admin panel:

```php
// app/Providers/Filament/AdminPanelProvider.php

use MasyukAI\FilamentShippingPlugin\FilamentShippingPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->plugins([
            FilamentShippingPlugin::make(),
        ]);
}
```

## Features Overview

### Shipment Management
- View all shipments in a professional table interface
- Create new shipments with detailed forms for addresses and package details
- Edit existing shipments with full form validation
- Delete shipments with confirmation dialogs
- Advanced filtering by status, provider, and date ranges

### Dashboard Widget
The plugin includes a dashboard widget showing:
- Total number of shipments
- Number of in-transit shipments
- Total delivered shipments
- Revenue from delivered shipments

### Advanced Filtering
- Filter by shipment status (created, dispatched, in-transit, delivered, etc.)
- Filter by shipping provider
- Date range filtering for creation dates
- Search by tracking number and recipient name

### Detailed Shipment Views
- Complete shipment information display
- Destination address details
- Tracking events timeline
- Package details including weight and dimensions

## Customization

The plugin follows Filament's conventions and can be customized by extending the provided classes or overriding the default configuration.

### Extending the Shipment Resource

```php
use MasyukAI\FilamentShippingPlugin\Resources\ShipmentResource as BaseShipmentResource;

class ShipmentResource extends BaseShipmentResource
{
    // Override methods to customize behavior
}
```

### Adding Custom Actions

```php
protected function getHeaderActions(): array
{
    return array_merge(parent::getHeaderActions(), [
        Action::make('printLabel')
            ->label('Print Label')
            ->icon('heroicon-o-printer')
            ->action(function (Shipment $record) {
                // Custom printing logic
            }),
    ]);
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.