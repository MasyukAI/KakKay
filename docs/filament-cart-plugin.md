# Filament Cart Plugin Documentation

This plugin provides a comprehensive Filament admin interface for managing shopping carts created by the MasyukAI Cart package.

## Features

### 🛒 Cart Management
- **Complete CRUD Operations**: Create, view, edit, and delete carts
- **Real-time Updates**: Live polling every 30 seconds to show current cart status
- **Bulk Operations**: Clear multiple carts or delete empty carts at once
- **Export Functionality**: Export cart data as JSON for analysis or backup

### 📊 Advanced Filtering & Search
- **Instance Filtering**: Filter by cart types (default, wishlist, comparison, quote)
- **Status Filtering**: Show only carts with items or empty carts
- **Date Filtering**: Recent carts (7 days) or created today
- **Search**: Full-text search on cart identifiers

### 🎨 Rich UI Components
- **Status Indicators**: Visual icons showing empty vs filled carts
- **Instance Badges**: Color-coded badges for different cart types
- **Navigation Badge**: Shows count of non-empty carts
- **Responsive Design**: Works on desktop and mobile devices

### 📈 Data Visualization
- **Cart Statistics**: Items count, total quantity, subtotal calculations
- **Detailed Item View**: Complete breakdown of cart contents
- **Condition Management**: Handle discounts, taxes, shipping, and fees
- **Metadata Support**: Store and display additional cart information

## Installation

The plugin is already integrated into the application. Simply navigate to the admin panel and access the "Carts" section under the E-commerce navigation group.

## Usage

### Accessing Carts
1. Log into the Filament admin panel
2. Navigate to **E-commerce → Carts**
3. View the list of all carts with their current status

### Managing Carts

#### Viewing Cart Details
- Click the **View** action (eye icon) to see complete cart information
- Review cart items, conditions, metadata, and timestamps
- Export cart data using the **Export Cart** button

#### Editing Carts
- Click the **Edit** action (pencil icon) to modify cart contents
- Add/remove items, adjust quantities and prices
- Manage conditions (discounts, taxes, shipping)
- Update metadata and cart settings

#### Clearing Carts
- Use the **Clear Cart** action to remove all items while keeping the cart record
- Available in both list view and detail view
- Requires confirmation to prevent accidental clearing

#### Bulk Operations
- Select multiple carts using checkboxes
- **Clear Selected Carts**: Remove items from multiple carts
- **Delete Empty Carts**: Remove carts that have no items

### Cart Instances

The plugin supports different cart instances:
- **Default**: Regular shopping cart
- **Wishlist**: Save for later items
- **Comparison**: Product comparison lists
- **Quote**: Request for quote carts
- **Bulk**: Bulk order carts
- **Subscription**: Recurring order carts

Each instance is color-coded for easy identification.

## Database Schema

The plugin works with the `carts` table created by the MasyukAI Cart package:

```sql
CREATE TABLE carts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    instance VARCHAR(255) DEFAULT 'default' NOT NULL,
    items LONGTEXT NULL,
    conditions LONGTEXT NULL,
    metadata LONGTEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY carts_identifier_instance_unique (identifier, instance),
    KEY carts_identifier_index (identifier),
    KEY carts_instance_index (instance)
);
```

### Fields Description

- **identifier**: Unique cart identifier (usually session ID or user ID)
- **instance**: Cart type/purpose (default, wishlist, comparison, etc.)
- **items**: JSON array of cart items with product details
- **conditions**: JSON array of cart conditions (discounts, taxes, shipping)
- **metadata**: JSON object for additional cart information

## Model Features

### Cart Model (`App\Models\Cart`)

#### Computed Attributes
- `items_count`: Number of different products in cart
- `total_quantity`: Sum of all item quantities
- `subtotal`: Total price of all items (before conditions)
- `formatted_subtotal`: Currency-formatted subtotal

#### Helper Methods
- `isEmpty()`: Check if cart has no items
- `getFormattedSubtotalAttribute()`: Get formatted subtotal string

#### Query Scopes
- `instance($instance)`: Filter by cart instance
- `byIdentifier($identifier)`: Find cart by identifier
- `notEmpty()`: Only carts with items
- `recent($days)`: Carts updated within specified days

## Testing

### Factory Support
Use the `CartFactory` to create test data:

```php
// Create random carts
Cart::factory()->count(10)->create();

// Create empty carts
Cart::factory()->empty()->count(5)->create();

// Create wishlist carts
Cart::factory()->instance('wishlist')->count(3)->create();

// Create expensive carts
Cart::factory()->expensive()->count(2)->create();

// Create carts with many items
Cart::factory()->withManyItems(10)->count(2)->create();
```

### Seeder
Run the `CartSeeder` to populate test data:

```bash
php artisan db:seed --class=CartSeeder
```

## Integration with MasyukAI Cart Package

This plugin is designed to work seamlessly with the MasyukAI Cart package:

- **Data Compatibility**: Reads/writes data in the same format as the cart package
- **Instance Support**: Fully supports multiple cart instances
- **Condition Handling**: Compatible with cart conditions (discounts, taxes, etc.)
- **Event Integration**: Works with cart events and listeners

## Customization

### Adding Custom Actions
You can extend the cart resource with custom actions by modifying the table or page classes:

```php
// In CartsTable.php
Actions\Action::make('custom_action')
    ->label('Custom Action')
    ->icon(Heroicon::OutlinedStar)
    ->action(function ($record) {
        // Custom logic here
    }),
```

### Custom Filters
Add custom filters to the table:

```php
// In CartsTable.php
Filter::make('custom_filter')
    ->label('Custom Filter')
    ->query(fn (Builder $query): Builder => 
        $query->where('custom_field', 'value')
    ),
```

### Styling Customization
The plugin uses Filament's theming system. You can customize colors and styling through your Filament theme configuration.

## Troubleshooting

### Common Issues

1. **Cart data not displaying correctly**
   - Check that the `carts` table exists and has the correct schema
   - Verify JSON fields contain valid data

2. **Navigation not showing**
   - Ensure the CartResource is in the correct namespace
   - Check that Filament is discovering resources properly

3. **Performance issues with large datasets**
   - Consider adding database indexes on frequently queried fields
   - Implement pagination limits in the table configuration

### Debug Information
Use the model's debug methods to inspect cart data:

```php
$cart = Cart::find(1);
dd($cart->toArray()); // See all cart data
dd($cart->items_count); // Check computed attributes
```

## Support

For issues related to the cart plugin, please check:
1. Filament documentation for UI components
2. MasyukAI Cart package documentation for data structures
3. Laravel documentation for model and database features