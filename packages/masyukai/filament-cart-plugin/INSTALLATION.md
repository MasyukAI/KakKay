# Installation Guide

This guide shows how to install and use the Filament Cart Plugin as a standalone package.

## Installation

### Step 1: Install via Composer

```bash
composer require masyukai/filament-cart-plugin
```

### Step 2: Register the Plugin

Add the plugin to your Filament panel in `app/Providers/Filament/AdminPanelProvider.php`:

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
            ->default()
            ->id('admin')
            ->path('admin')
            // ... other configuration
            ->plugins([
                FilamentCartPlugin::make(),
                // ... other plugins
            ]);
    }
}
```

### Step 3: Ensure Cart Package is Installed

Make sure you have the MasyukAI Cart package installed:

```bash
composer require masyukai/cart
```

And that your `carts` table migration has been run:

```bash
php artisan migrate
```

## Usage

Once installed, the plugin will automatically:

1. **Add Cart Resource**: A complete CRUD interface for managing carts
2. **Dashboard Widget**: Real-time cart statistics showing:
   - Total number of carts
   - Active (non-empty) carts
   - Total items across all carts
   - Total cart value

3. **Navigation Item**: "Carts" menu item in the "E-commerce" group

## Features

### Cart Management
- View all carts in a sortable, filterable table
- Create new carts with multiple sections
- Edit existing carts with validation
- Delete carts with confirmation
- Bulk operations for cart management

### Advanced Filtering
- Filter by cart instance (default, wishlist, comparison, etc.)
- Filter by cart status (empty/active)
- Search by cart identifier
- Date range filtering

### Real-time Updates
- Live polling every 30 seconds
- Visual indicators for cart status
- Color-coded instance badges

## Configuration

You can publish the configuration file:

```bash
php artisan vendor:publish --tag="filament-cart-plugin-config"
```

Then customize the settings in `config/filament-cart-plugin.php`:

```php
return [
    'navigation_group' => 'E-commerce',
    'navigation_icon' => 'heroicon-o-shopping-cart',
    'polling_interval' => 30,
    'per_page_options' => [10, 25, 50, 100],
    'default_instance' => 'default',
    'instances' => [
        'default' => 'Default',
        'wishlist' => 'Wishlist',
        'comparison' => 'Comparison',
        // ... add more as needed
    ],
];
```

## Requirements

- Laravel 12+
- Filament v4+
- MasyukAI Cart package
- PHP 8.2+

## Testing

To test cart functionality with sample data:

```php
// Create test carts
use MasyukAI\FilamentCartPlugin\Models\Cart;

Cart::factory()->count(10)->create();
Cart::factory()->instance('wishlist')->count(3)->create();
```

## Troubleshooting

### Plugin Not Appearing
- Ensure the plugin is properly registered in your panel provider
- Clear cache: `php artisan cache:clear`
- Regenerate autoload: `composer dump-autoload`

### Navigation Issues
- Check that the navigation group is correctly configured
- Verify permissions if using authorization

### Database Issues
- Ensure the `carts` table exists and is migrated
- Check that the Cart model can access the database