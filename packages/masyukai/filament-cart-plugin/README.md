# Filament Cart Plugin

A comprehensive Filament admin interface for managing shopping carts created by the MasyukAI Cart package.

## Features

- 🛒 **Complete CRUD Operations**: Create, view, edit, and delete carts through an intuitive Filament interface
- 📊 **Dashboard Widget**: Real-time cart statistics showing total carts, active carts, items count, and total value
- 🔄 **Live Updates**: Automatic polling every 30 seconds to show current cart status
- 🎯 **Advanced Filtering**: Filter by cart types, status, creation date with comprehensive search
- 📱 **Responsive Design**: Works seamlessly on desktop and mobile devices
- 🎨 **Rich UI Components**: Professional interface with status indicators, badges, and color coding

## Installation

You can install the package via composer:

```bash
composer require masyukai/filament-cart-plugin
```

The plugin will automatically register itself with Laravel's package discovery.

## Usage

### Register the Plugin

Add the plugin to your Filament panel in your `app/Providers/Filament/AdminPanelProvider.php`:

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use MasyukAI\FilamentCartPlugin\FilamentCartPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ... other configuration
            ->plugins([
                FilamentCartPlugin::make(),
            ]);
    }
}
```

### Requirements

This plugin requires:
- Laravel 12+
- Filament v4+
- MasyukAI Cart package

### Configuration

The plugin works out of the box with the existing `carts` table from the MasyukAI Cart package. No additional configuration is required.

## Features Overview

### Cart Management
- View all carts in a professional table interface
- Create new carts with multiple sections for items and conditions
- Edit existing carts with full form validation
- Delete carts with confirmation dialogs
- Bulk operations for clearing multiple carts

### Dashboard Widget
The plugin includes a dashboard widget showing:
- Total number of carts
- Number of active (non-empty) carts
- Total items across all carts
- Total value of all carts

### Advanced Filtering
- Filter by cart instance (default, wishlist, comparison, quote)
- Filter by cart status (empty/active)
- Search by cart identifier
- Date range filtering

## Customization

The plugin follows Filament's conventions and can be customized by extending the provided classes or overriding the default configuration.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.