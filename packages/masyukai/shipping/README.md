# 🚚 MasyukAI Shipping Package

A comprehensive shipping management package for Laravel with multi-carrier support, tracking, and notifications.

## ✨ Features

- 📦 **Multi-Provider Support**: Extensible architecture for multiple shipping providers
- 💰 **Rate Calculation**: Calculate shipping costs with weight-based surcharges  
- 📋 **Quote Comparison**: Get quotes from multiple providers
- 🔍 **Shipment Tracking**: Real-time tracking with automated updates
- 📧 **Notifications**: Email and database notifications for status changes
- 🎯 **Event-Driven**: Rich event system for custom integrations
- ⚡ **Background Jobs**: Async tracking updates via Laravel queues
- 🗄️ **Database Storage**: Full shipment and tracking history

## 🚀 Installation

Install the package via Composer:

```bash
composer require masyukai/shipping
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=shipping-config
```

Run the migrations:

```bash
php artisan migrate
```

## 🔧 Configuration

The package comes with a local shipping provider by default. Configure additional providers in `config/shipping.php`:

```php
'providers' => [
    'local' => [
        'driver' => 'local',
        'methods' => [
            'standard' => [
                'name' => 'Standard Shipping',
                'price' => 500, // RM5 in cents
                'estimated_days' => '3-5',
            ],
        ],
    ],
],
```

## 📖 Usage

### Basic Shipping Operations

```php
use MasyukAI\Shipping\Facades\Shipping;

// Get available shipping methods
$methods = Shipping::getShippingMethods();

// Calculate shipping cost
$cost = Shipping::calculateCost($items, 'standard', $destination);

// Get quotes from all providers
$quotes = Shipping::getQuotes($items, $destination);
```

### Creating Shipments

```php
use MasyukAI\Shipping\Models\Shipment;

$shipment = Shipment::create([
    'shippable_type' => 'App\Models\Order',
    'shippable_id' => $order->id,
    'provider' => 'local',
    'method' => 'standard',
    'destination_address' => [
        'name' => 'John Doe',
        'line1' => '123 Main St',
        'city' => 'Kuala Lumpur',
        'state' => 'WP',
        'postal_code' => '50000',
        'country' => 'MY',
    ],
    'weight' => 1500, // 1.5kg in grams
    'cost' => 500,
]);

// Create shipment with provider
$result = Shipping::createShipment($shipment);
```

### Tracking Shipments

```php
use MasyukAI\Shipping\Contracts\TrackingServiceInterface;

$tracking = app(TrackingServiceInterface::class);

// Update tracking information
$info = $tracking->updateTracking('LOCAL-ABC123');

// Get current status
$status = $tracking->getStatus('LOCAL-ABC123');

// Get tracking events
$events = $tracking->getTrackingEvents('LOCAL-ABC123');
```

## 🎯 Events

The package dispatches events for key shipment lifecycle moments:

```php
use MasyukAI\Shipping\Events\ShipmentCreated;
use MasyukAI\Shipping\Events\ShipmentStatusUpdated;
use MasyukAI\Shipping\Events\ShipmentDelivered;

Event::listen(ShipmentCreated::class, function (ShipmentCreated $event) {
    // Send confirmation email
    $event->shipment->shippable->user->notify(
        new ShipmentConfirmationNotification($event->shipment)
    );
});

Event::listen(ShipmentDelivered::class, function (ShipmentDelivered $event) {
    // Process post-delivery actions
    ProcessDeliveryCompletion::dispatch($event->shipment);
});
```

## ⚡ Background Jobs

Automatic tracking updates using Laravel queues:

```php
use MasyukAI\Shipping\Jobs\UpdateShipmentTracking;

// Dispatch tracking update job
UpdateShipmentTracking::dispatch($trackingNumber);

// Schedule regular updates
$this->command('schedule:run')->everyMinute();
```

## 🔗 Integration with Cart Package

Seamless integration with the MasyukAI Cart package:

```php
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Shipping\Facades\Shipping;

// Calculate shipping for cart contents
$cartItems = Cart::content()->map(function ($item) {
    return [
        'id' => $item->id,
        'quantity' => $item->quantity,
        'weight' => $item->model->weight ?? 100,
    ];
})->toArray();

$shippingCost = Shipping::calculateCost($cartItems, 'standard');

// Add shipping to cart
Cart::addFee('shipping', $shippingCost);
```

## 🏗️ Extending Providers

Create custom shipping providers by implementing the `ShippingProviderInterface`:

```php
use MasyukAI\Shipping\Contracts\ShippingProviderInterface;

class FedExProvider implements ShippingProviderInterface
{
    public function getShippingMethods(): array
    {
        // Implement FedEx API integration
    }

    public function calculateCost(array $items, string $method, array $destination = []): int
    {
        // Calculate FedEx shipping costs
    }

    // ... implement other methods
}
```

## 📧 Notifications

Automatic notifications for shipment status changes:

```php
// Configure in config/shipping.php
'notifications' => [
    'enabled' => true,
    'channels' => ['mail', 'database'],
    'events' => [
        'shipment_created' => true,
        'shipment_dispatched' => true,
        'shipment_delivered' => true,
    ],
],
```

## 🧪 Testing

Run the package tests:

```bash
cd packages/masyukai/shipping
composer test
```

## 📝 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.