# J&T Express Malaysia API Integration Package

A modern, type-safe Laravel package for integrating with J&T Express Malaysia Open API.

## Features

- ✅ Create orders with comprehensive validation
- ✅ Query order details
- ✅ Cancel orders
- ✅ Print AWB labels
- ✅ Track parcels
- ✅ Webhook support for tracking updates
- ✅ Automatic signature generation and verification
- ✅ Retry logic for failed requests (including 5xx errors)
- ✅ Comprehensive logging with sensitive data masking
- ✅ Laravel HTTP Client integration for better testing
- ✅ Type-safe data objects with PHP 8.4
- ✅ Testing and production environments
- ✅ PHPStan level 6 compliant
- ✅ Comprehensive test suite with Pest

## Installation

```bash
composer require masyukai/jnt
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=jnt-config
```

## Configuration

Add the following to your `.env` file:

```env
JNT_ENVIRONMENT=testing
JNT_API_ACCOUNT=your_api_account
JNT_PRIVATE_KEY=your_private_key
JNT_CUSTOMER_CODE=your_customer_code
JNT_PASSWORD=your_password

# Optional
JNT_LOGGING_ENABLED=true
JNT_LOGGING_CHANNEL=stack
JNT_WEBHOOK_ENABLED=true
JNT_WEBHOOK_VERIFY_SIGNATURE=true
```

### Testing Credentials

For sandbox testing, use:

```env
JNT_API_ACCOUNT=640826271705595946
JNT_PRIVATE_KEY=8e88c8477d4e4939859c560192fcafbc
```

You'll need to obtain `JNT_CUSTOMER_CODE` and `JNT_PASSWORD` from your J&T Distribution Partner.

## Usage

### Creating an Order

```php
use MasyukAI\Jnt\Facades\JntExpress;
use MasyukAI\Jnt\Data\AddressData;
use MasyukAI\Jnt\Data\ItemData;
use MasyukAI\Jnt\Data\PackageInfoData;

// Create sender address
$sender = new AddressData(
    name: 'John Sender',
    phone: '60123456789',
    address: 'No 32, Jalan Kempas 4',
    postCode: '81930',
    countryCode: 'MYS',
    prov: 'Johor',
    city: 'Bandar Penawar',
    area: 'Taman Desaru Utama'
);

// Create receiver address
$receiver = new AddressData(
    name: 'Jane Receiver',
    phone: '60987654321',
    address: '4678, Laluan Sentang 35',
    postCode: '31000',
    countryCode: 'MYS',
    prov: 'Perak',
    city: 'Batu Gajah',
    area: 'Kampung Seri Mariah'
);

// Create items
$item = new ItemData(
    itemName: 'Basketball',
    number: '2',
    weight: '10',
    itemValue: '50.00',
    englishName: 'Basketball',
    itemDesc: 'Sports equipment',
    itemCurrency: 'MYR'
);

// Create package info
$packageInfo = new PackageInfoData(
    packageQuantity: '1',
    weight: '10',
    packageValue: '50',
    goodsType: 'ITN8', // ITN2 for documents, ITN8 for packages
    length: '30',
    width: '20',
    height: '15'
);

// Build and create order
$orderData = JntExpress::createOrderBuilder()
    ->txlogisticId('ORDER-'.time())
    ->expressType('EZ') // EZ: Domestic, EX: Next Day, FD: Fresh
    ->serviceType('1') // 1: Door-to-door, 6: Walk-in
    ->payType('PP_PM') // PP_PM, PP_CASH, CC_CASH
    ->sender($sender)
    ->receiver($receiver)
    ->addItem($item)
    ->packageInfo($packageInfo)
    ->insurance('50.00') // Optional
    ->cod('100.00') // Optional
    ->remark('Handle with care') // Optional
    ->build();

$order = JntExpress::createOrder($orderData);

// Access order details
echo "Bill Code: " . $order->billCode;
echo "Sorting Code: " . $order->sortingCode;
```

### Query Order

```php
$orderDetails = JntExpress::queryOrder('ORDER-123456789');
```

### Cancel Order

```php
$result = JntExpress::cancelOrder(
    txlogisticId: 'ORDER-123456789',
    reason: 'Customer requested cancellation',
    billCode: '630002864925' // Optional
);
```

### Print AWB Label

```php
$label = JntExpress::printOrder(
    txlogisticId: 'ORDER-123456789',
    billCode: '630002864925',
    templateName: null // Optional
);

// Get PDF URL
$pdfUrl = $label['urlContent'];
```

### Track Parcel

```php
// Track by txlogisticId
$tracking = JntExpress::trackParcel(txlogisticId: 'ORDER-123456789');

// Or track by billCode
$tracking = JntExpress::trackParcel(billCode: '630002864925');

// Access tracking details
foreach ($tracking->details as $detail) {
    echo $detail->scanTime . ': ' . $detail->desc;
}
```

### Webhook Handling

Create a controller to handle J&T webhooks:

```php
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
            ], 400);
        }

        // Parse webhook payload
        $trackingUpdates = JntExpress::parseWebhookPayload($request->all());

        foreach ($trackingUpdates as $tracking) {
            // Process tracking update
            $billCode = $tracking->billCode;
            $details = $tracking->details;
            
            // Your business logic here
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

Register the webhook route in `routes/api.php`:

```php
Route::post('/jnt/webhook', [JntWebhookController::class, 'handle']);
```

## Scan Type Codes

- `10` - Pick Up
- `20` - Departure
- `30` - Arrival
- `94` - Delivery (Out for delivery)
- `100` - Delivery Signature
- `110` - Problematic
- `172` - Return
- `173` - Return Delivery Signature
- `200` - Collected
- `300-306` - Terminal statuses (Damaged/Lost/Disposed/Rejected/Customs/Expired/Crossborder Disposal)

## Testing

Run the test suite:

```bash
cd packages/masyukai/jnt
vendor/bin/pest
```

Run with coverage:

```bash
composer test:coverage
```

Run static analysis:

```bash
composer analyse
```

Format code:

```bash
composer format
```

Run all checks:

```bash
composer check
```

The package uses Laravel's `Http::fake()` for testing, making it easy to test your integration code without hitting the actual J&T API.

## API Response Codes

- `1` - Success
- `0` - Failed
- `145003052` - digest is empty
- `145003051` - apiAccount is empty
- `145003053` - timestamp is empty
- `145003010` - API account does not exist
- `145003012` - API account has no interface permissions
- `145003030` - headers signature verification failed
- `145003050` - Illegal parameters

## License

MIT

## Technical Details

- **PHP Version**: 8.4+
- **Laravel Version**: 12.x
- **HTTP Client**: Laravel HTTP (built on Guzzle 7)
- **Testing**: Pest v4
- **Static Analysis**: PHPStan (Larastan) Level 6
- **Code Style**: Laravel Pint

## Contributing

Pull requests are welcome! Please ensure:
- All tests pass (`composer test`)
- Code is formatted (`composer format`)
- Static analysis passes (`composer analyse`)

## Support

For issues and feature requests, please use the [GitHub issue tracker](https://github.com/masyukai/jnt/issues).
