# Filament Cart Plugin

A powerful Filament plugin for managing shopping carts with **normalized cart items and conditions** for enhanced performance, search capabilities, and analytics.

## ✨ Features

### 🛒 **Core Cart Management**
- Complete CRUD operations for shopping carts
- Support for multiple cart instances (default, wishlist, comparison, etc.)
- Real-time cart updates with polling
- Comprehensive cart metadata management

### ⚡ **Performance-Optimized Normalized Models**
- **Normalized CartItem model** - Individual cart items as separate database records
- **Normalized CartCondition model** - Discounts, taxes, fees as searchable records
- **Event-driven synchronization** - Automatic sync with cart package operations
- **Enhanced search & filtering** - Fast queries on normalized data structures

### 🎯 **Advanced Filtering & Search**
- Search items by name, price range, quantity
- Filter conditions by type (discount, tax, fee, shipping)
- Instance-based filtering (default, wishlist, bulk orders)
- Cart-level vs item-level condition filtering
- Real-time performance without JSON parsing overhead

### 📊 **Analytics & Insights**
- Comprehensive cart analytics through normalized data
- Track promotional code usage patterns
- Monitor cart abandonment with detailed item tracking
- Performance metrics for cart operations

### 🔄 **Event-Driven Architecture**
- Automatic synchronization via cart package events
- Queue-based processing for scalability
- Robust error handling and retry mechanisms
- Maintains data consistency across all operations

## Installation

You can install the package via composer:

```bash
composer require masyukai/filament-cart
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
use MasyukAI\FilamentCart\FilamentCart;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ... other configuration
            ->plugins([
                FilamentCart::make(),
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