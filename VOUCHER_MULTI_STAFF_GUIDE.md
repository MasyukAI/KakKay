# Multi-Staff Vendor Voucher Management Guide

This guide shows how to use the voucher package for vendors with multiple staff members.

## Architecture Overview

The voucher package supports multi-staff operations through:

1. **Owner Scoping** - Vouchers belong to the vendor (parent account)
2. **Manual Redemption** - Staff can redeem vouchers at POS/checkout
3. **Attribution Tracking** - Records which staff member processed each redemption
4. **Rich Metadata** - Captures terminal, location, and contextual data

## Database Schema

### User/Staff Structure (Example)

```php
users
├── id (vendor owner)
├── name
├── email
└── role (vendor/staff)

users (staff)
├── id
├── name
├── email
├── vendor_id (foreign key to parent vendor)
└── role (staff)
```

### Voucher Relationships

```
Vendor (User) ──┬── Voucher 1
                ├── Voucher 2
                └── Voucher 3

Staff 1 ─┐
Staff 2 ─┼── All access vendor's vouchers
Staff 3 ─┘

VoucherUsage
├── redeemed_by_id (Staff ID)
├── redeemed_by_type (App\Models\User)
└── metadata (contains staff details)
```

## Implementation Examples

### 1. Basic Vendor-Staff Setup

```php
// Migration: Add vendor_id to users table
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('vendor_id')->nullable()->constrained('users');
    $table->string('role')->default('customer'); // vendor, staff, customer
});

// User Model
class User extends Authenticatable
{
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'vendor_id');
    }

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }
}
```

### 2. Creating Vendor Vouchers

```php
use AIArmada\Vouchers\Facades\Voucher;
use AIArmada\Vouchers\Enums\VoucherType;

// Login as vendor owner
auth()->login($vendor);

// Create voucher - automatically assigned to vendor
$voucher = Voucher::create([
    'code' => 'STORE20',
    'name' => '20% Store Discount',
    'type' => VoucherType::Percentage,
    'value' => 20,
    'allows_manual_redemption' => true, // Important for staff use
    'usage_limit' => 100,
]);

// All staff with this vendor_id can now access this voucher
```

### 3. Staff Manual Redemption (POS/Checkout)

```php
use AIArmada\Vouchers\Facades\Voucher;
use Akaunting\Money\Money;

// Staff logs in and processes a sale
$staff = auth()->user(); // Staff member
$customer = Customer::find($customerId);

// At checkout, apply voucher manually
try {
    Voucher::redeemManually(
        code: 'STORE20',
        userIdentifier: $customer->id, // Customer who gets the discount
        discountAmount: Money::MYR(2500), // 25.00 discount
        reference: "order-{$order->id}",
        metadata: [
            'terminal_id' => config('pos.terminal_id'),
            'location' => 'Store #5, Kuala Lumpur',
            'staff_name' => $staff->name,
            'staff_email' => $staff->email,
            'order_id' => $order->id,
            'payment_method' => 'cash',
        ],
        redeemedBy: $staff, // Important: tracks which staff member
        notes: "Redeemed by {$staff->name} at terminal {config('pos.terminal_id')}"
    );
    
    // Success
    return response()->json(['message' => 'Voucher applied successfully']);
    
} catch (\AIArmada\Vouchers\Exceptions\ManualRedemptionNotAllowedException $e) {
    return response()->json(['error' => 'This voucher cannot be manually redeemed'], 403);
} catch (\AIArmada\Vouchers\Exceptions\VoucherNotFoundException $e) {
    return response()->json(['error' => 'Voucher not found'], 404);
}
```

### 4. Automatic Redemption (Online Orders)

```php
// In your RecordVoucherUsage listener or checkout process
Voucher::recordUsage(
    code: 'STORE20',
    userIdentifier: (string) $order->user_id,
    discountAmount: Money::MYR(2500),
    cartIdentifier: $order->id,
    cartSnapshot: [
        'order_id' => $order->id,
        'subtotal' => $order->subtotal,
        'total' => $order->total,
    ],
    channel: VoucherUsage::CHANNEL_AUTOMATIC, // or API
    metadata: [
        'order_id' => $order->id,
        'source' => 'online',
        'payment_method' => 'credit_card',
    ],
    redeemedBy: null, // No staff involved in online orders
    notes: 'Applied automatically during online checkout'
);
```

## Reporting & Analytics

### 1. Staff Performance Report

```php
use AIArmada\Vouchers\Models\VoucherUsage;

// Get redemptions by staff member
$staffRedemptions = VoucherUsage::query()
    ->where('channel', VoucherUsage::CHANNEL_MANUAL)
    ->where('redeemed_by_type', User::class)
    ->where('redeemed_by_id', $staffId)
    ->whereBetween('used_at', [$startDate, $endDate])
    ->with(['voucher', 'redeemedBy'])
    ->get();

// Calculate stats
$totalRedemptions = $staffRedemptions->count();
$totalDiscounts = $staffRedemptions->sum('discount_amount');
$averageDiscount = $staffRedemptions->avg('discount_amount');

echo "Staff Member: {$staff->name}\n";
echo "Total Redemptions: {$totalRedemptions}\n";
echo "Total Discounts Given: RM " . number_format($totalDiscounts / 100, 2) . "\n";
echo "Average Discount: RM " . number_format($averageDiscount / 100, 2) . "\n";
```

### 2. Vendor Voucher Usage Report

```php
// Get all vouchers for a vendor
$vendor = User::find($vendorId);

$vouchers = Voucher::query()
    ->where('owner_type', User::class)
    ->where('owner_id', $vendor->id)
    ->with(['usages' => function ($query) use ($startDate, $endDate) {
        $query->whereBetween('used_at', [$startDate, $endDate]);
    }])
    ->get();

foreach ($vouchers as $voucher) {
    $manualRedemptions = $voucher->usages
        ->where('channel', VoucherUsage::CHANNEL_MANUAL)
        ->count();
    
    $automaticRedemptions = $voucher->usages
        ->where('channel', VoucherUsage::CHANNEL_AUTOMATIC)
        ->count();
    
    echo "{$voucher->code}: {$manualRedemptions} manual, {$automaticRedemptions} automatic\n";
}
```

### 3. Terminal/Location Report

```php
// Find which terminals/locations are using vouchers most
$terminalStats = VoucherUsage::query()
    ->where('channel', VoucherUsage::CHANNEL_MANUAL)
    ->get()
    ->groupBy(fn($usage) => $usage->metadata['terminal_id'] ?? 'unknown')
    ->map(fn($group) => [
        'count' => $group->count(),
        'total_discount' => $group->sum('discount_amount'),
        'location' => $group->first()->metadata['location'] ?? 'Unknown',
    ]);

foreach ($terminalStats as $terminalId => $stats) {
    echo "Terminal {$terminalId} ({$stats['location']}): ";
    echo "{$stats['count']} redemptions, ";
    echo "RM " . number_format($stats['total_discount'] / 100, 2) . " total\n";
}
```

## Permission & Authorization

### Middleware Example

```php
// app/Http/Middleware/EnsureStaffCanRedeemVouchers.php
class EnsureStaffCanRedeemVouchers
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        // Check if user is vendor or staff
        if (!$user->isVendor() && !$user->isStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return $next($request);
    }
}
```

### Policy Example

```php
// app/Policies/VoucherPolicy.php
class VoucherPolicy
{
    public function redeem(User $user, Voucher $voucher): bool
    {
        // Staff can redeem their vendor's vouchers
        if ($user->isStaff() && $user->vendor_id === $voucher->owner_id) {
            return true;
        }
        
        // Vendors can redeem their own vouchers
        if ($user->isVendor() && $user->id === $voucher->owner_id) {
            return true;
        }
        
        return false;
    }
    
    public function create(User $user): bool
    {
        // Only vendors can create vouchers
        return $user->isVendor();
    }
}
```

## API Endpoints Example

```php
// routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    // Vendor routes
    Route::middleware('can:vendor')->group(function () {
        Route::get('/vouchers', [VoucherController::class, 'index']);
        Route::post('/vouchers', [VoucherController::class, 'store']);
        Route::put('/vouchers/{code}', [VoucherController::class, 'update']);
        Route::delete('/vouchers/{code}', [VoucherController::class, 'destroy']);
    });
    
    // Staff routes (can read and redeem)
    Route::middleware('can:staff')->group(function () {
        Route::get('/vouchers', [VoucherController::class, 'index']);
        Route::post('/vouchers/{code}/redeem', [VoucherController::class, 'redeem']);
    });
    
    // Reports (vendor and staff)
    Route::get('/vouchers/reports/usage', [VoucherReportController::class, 'usage']);
    Route::get('/vouchers/reports/staff', [VoucherReportController::class, 'staffPerformance']);
});
```

## Controller Example

```php
// app/Http/Controllers/VoucherController.php
class VoucherController extends Controller
{
    public function redeem(Request $request, string $code)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'discount_amount' => 'required|numeric|min:0',
            'order_id' => 'required|exists:orders,id',
            'terminal_id' => 'nullable|string',
        ]);
        
        $staff = auth()->user();
        
        try {
            Voucher::redeemManually(
                code: $code,
                userIdentifier: $validated['customer_id'],
                discountAmount: Money::MYR((int)($validated['discount_amount'] * 100)),
                reference: "order-{$validated['order_id']}",
                metadata: [
                    'terminal_id' => $validated['terminal_id'],
                    'staff_name' => $staff->name,
                    'order_id' => $validated['order_id'],
                ],
                redeemedBy: $staff,
                notes: "Redeemed by {$staff->name}"
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Voucher redeemed successfully'
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
```

## Best Practices

### 1. Always Enable Manual Redemption Flag

```php
// When creating vouchers for POS use
Voucher::create([
    'code' => 'STAFF10',
    'allows_manual_redemption' => true, // Critical!
    // ... other fields
]);
```

### 2. Validate Staff Permissions

```php
// Before allowing redemption
if (!$user->can('redeem', $voucher)) {
    throw new UnauthorizedException('You cannot redeem this voucher');
}
```

### 3. Track Everything in Metadata

```php
metadata: [
    'terminal_id' => 5,
    'location' => 'Store #12',
    'staff_id' => $staff->id,
    'staff_name' => $staff->name,
    'staff_email' => $staff->email,
    'order_id' => $order->id,
    'payment_method' => 'cash',
    'customer_name' => $customer->name,
    'shift_start' => $shift->started_at,
    'notes' => 'Customer was very happy',
],
```

### 4. Use Queues for Reporting

```php
// Dispatch analytics jobs after redemption
RedemptionAnalytics::dispatch($voucherUsage);
StaffPerformanceUpdate::dispatch($staff);
```

### 5. Audit Logging

```php
// Log all manual redemptions for audit
Log::info('Voucher manually redeemed', [
    'voucher_code' => $code,
    'staff_id' => $staff->id,
    'staff_name' => $staff->name,
    'customer_id' => $customer->id,
    'discount_amount' => $discountAmount,
    'timestamp' => now(),
]);
```

## Security Considerations

1. **Rate Limiting**: Limit redemption attempts to prevent abuse
2. **Session Validation**: Ensure staff sessions are valid and recent
3. **IP Whitelisting**: Restrict manual redemptions to specific IPs (POS terminals)
4. **Transaction Logging**: Log all voucher operations for audit trails
5. **Role-Based Access**: Use Laravel's authorization features

## Testing

```php
// tests/Feature/VoucherStaffTest.php
test('staff can redeem vendor vouchers manually', function () {
    $vendor = User::factory()->vendor()->create();
    $staff = User::factory()->staff()->create(['vendor_id' => $vendor->id]);
    
    $voucher = Voucher::create([
        'code' => 'TEST20',
        'owner_type' => User::class,
        'owner_id' => $vendor->id,
        'allows_manual_redemption' => true,
    ]);
    
    auth()->login($staff);
    
    Voucher::redeemManually(
        code: 'TEST20',
        userIdentifier: 'customer-123',
        discountAmount: Money::MYR(2000),
        redeemedBy: $staff
    );
    
    $usage = VoucherUsage::where('voucher_id', $voucher->id)->first();
    
    expect($usage->redeemed_by_id)->toBe($staff->id);
    expect($usage->channel)->toBe(VoucherUsage::CHANNEL_MANUAL);
});
```

## Summary

The voucher package is **perfectly suited** for multi-staff vendor operations:

✅ **Owner Scoping**: Vouchers belong to vendors, shared by all staff
✅ **Manual Redemption**: Staff can redeem vouchers with full attribution
✅ **Rich Metadata**: Track terminal, location, staff, and contextual data
✅ **Channels**: Separate automatic (online) from manual (POS) redemptions
✅ **Analytics Ready**: Complete data for performance and audit reports
✅ **Flexible Architecture**: Easy to extend for complex scenarios

The `redeemed_by` relationship and manual redemption features were specifically designed for this use case!
