# Quick Start Guide

Get started with J&T Express integration in 5 minutes.

## 1. Installation

```bash
composer require masyukai/jnt
```

## 2. Publish Configuration

```bash
php artisan vendor:publish --tag=jnt-config
```

## 3. Configure Environment

Add to `.env`:

```env
JNT_ENVIRONMENT=testing
JNT_API_ACCOUNT=640826271705595946
JNT_PRIVATE_KEY=8e88c8477d4e4939859c560192fcafbc
JNT_CUSTOMER_CODE=your_customer_code
JNT_PASSWORD=your_password
```

> **Note:** The testing credentials above are for sandbox only. Get production credentials from your J&T PIC.

## 4. Create Your First Order

```php
use MasyukAI\Jnt\Facades\JntExpress;
use MasyukAI\Jnt\Data\{AddressData, ItemData, PackageInfoData};

// Sender address
$sender = new AddressData(
    name: 'Your Company',
    phone: '60123456789',
    address: 'No 1, Jalan Example',
    postCode: '50000',
    countryCode: 'MYS'
);

// Receiver address
$receiver = new AddressData(
    name: 'Customer Name',
    phone: '60198765432',
    address: '123 Customer Street',
    postCode: '10000',
    countryCode: 'MYS'
);

// Item details
$item = new ItemData(
    itemName: 'Product Name',
    number: '1',
    weight: '1',
    itemValue: '100.00'
);

// Package info
$packageInfo = new PackageInfoData(
    packageQuantity: '1',
    weight: '1',
    packageValue: '100',
    goodsType: 'ITN8' // ITN8 for parcel, ITN2 for document
);

// Build and create order
$orderData = JntExpress::createOrderBuilder()
    ->txlogisticId('ORDER-' . time())
    ->sender($sender)
    ->receiver($receiver)
    ->addItem($item)
    ->packageInfo($packageInfo)
    ->build();

try {
    $order = JntExpress::createOrder($orderData);
    
    echo "Success! Tracking number: " . $order->billCode;
    echo "\nSorting code: " . $order->sortingCode;
} catch (\MasyukAI\Jnt\Exceptions\JntException $e) {
    echo "Error: " . $e->getMessage();
}
```

## 5. Track Your Order

```php
// Track by your order reference
$tracking = JntExpress::trackParcel(txlogisticId: 'ORDER-123456');

// Or track by J&T bill code
$tracking = JntExpress::trackParcel(billCode: '630002864925');

// Display tracking events
foreach ($tracking->details as $detail) {
    echo $detail->scanTime . ' - ' . $detail->desc . "\n";
}
```

## 6. Set Up Webhook (Optional)

Create a controller:

```php
// app/Http/Controllers/JntWebhookController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MasyukAI\Jnt\Facades\JntExpress;

class JntWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verify signature
        $bizContent = $request->input('bizContent');
        $digest = $request->header('digest');
        
        if (!JntExpress::verifyWebhookSignature($bizContent, $digest)) {
            return response()->json([
                'code' => '0',
                'msg' => 'Invalid signature',
                'data' => 'FAILED',
            ], 403);
        }

        // Process tracking updates
        $updates = JntExpress::parseWebhookPayload($request->all());
        
        foreach ($updates as $tracking) {
            // Your business logic
            \Log::info('Tracking update', [
                'billCode' => $tracking->billCode,
                'events' => count($tracking->details),
            ]);
        }

        // Return success response
        return response()->json([
            'code' => '1',
            'msg' => 'success',
            'data' => 'SUCCESS',
            'requestId' => uniqid(),
        ]);
    }
}
```

Register route in `routes/api.php`:

```php
Route::post('/jnt/webhook', [JntWebhookController::class, 'handle']);
```

## Common Operations

### Cancel Order

```php
JntExpress::cancelOrder(
    txlogisticId: 'ORDER-123456',
    reason: 'Customer requested cancellation'
);
```

### Get AWB Label

```php
$label = JntExpress::printOrder(txlogisticId: 'ORDER-123456');
$pdfUrl = $label['urlContent'];

// Download or display the PDF
return redirect($pdfUrl);
```

### Add Insurance

```php
$orderData = JntExpress::createOrderBuilder()
    ->txlogisticId('ORDER-001')
    // ... other fields
    ->insurance('500.00') // Insure for RM500
    ->build();
```

### Enable COD (Cash on Delivery)

```php
$orderData = JntExpress::createOrderBuilder()
    ->txlogisticId('ORDER-001')
    // ... other fields
    ->cod('150.00') // Collect RM150 on delivery
    ->build();
```

## Express Types

Choose the right service level:

- **EZ** (Economy/Standard) - Regular domestic delivery
- **EX** (Express) - Next-day delivery
- **FD** (Fresh) - For perishable items

```php
->expressType('EZ') // or 'EX', 'FD'
```

## Payment Types

- **PP_PM** - Prepaid by merchant (most common)
- **PP_CASH** - Prepaid in cash
- **CC_CASH** - Cash on delivery (requires cod amount)

```php
->payType('PP_PM')
```

## Next Steps

- Read the [README.md](README.md) for detailed usage
- Check [TECHNICAL_DOCS.md](TECHNICAL_DOCS.md) for architecture details
- Review [CHANGELOG.md](CHANGELOG.md) for version history
- Run tests: `cd packages/masyukai/jnt && vendor/bin/pest`

## Need Help?

- Check the troubleshooting section in TECHNICAL_DOCS.md
- Review API error codes in the README
- Contact your J&T Distribution Partner for credentials

## Testing Your Integration

The package uses Laravel's HTTP Client, making it easy to test your integration code:

```php
use Illuminate\Support\Facades\Http;

test('creates order successfully', function () {
    Http::fake([
        '*/api/order/addOrder' => Http::response([
            'code' => '1',
            'data' => ['txlogisticId' => 'TXN-001', 'billCode' => 'JT123'],
        ], 200),
    ]);

    $result = JntExpress::createOrderFromArray([...]);

    expect($result->billCode)->toBe('JT123');
});
```

### Before Production

1. ✅ Test in sandbox environment first
2. ✅ Verify all order fields are correct
3. ✅ Test webhook signature verification
4. ✅ Check AWB label generation
5. ✅ Test tracking updates
6. ✅ Run package tests: `cd packages/masyukai/jnt && vendor/bin/pest`
7. ✅ Contact J&T PIC for production credentials
8. ✅ Update `.env` to production settings

```env
JNT_ENVIRONMENT=production
JNT_API_ACCOUNT=your_production_account
JNT_PRIVATE_KEY=your_production_key
JNT_CUSTOMER_CODE=your_production_customer_code
JNT_PASSWORD=your_production_password
```
