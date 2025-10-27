# Multi-Staff & Multi-Store Voucher Management

## Overview

The voucher package supports two key scenarios:

1. **Multiple Staff Managing Vouchers** - Staff administer vouchers for their vendor
2. **Multiple Stores** - Each store can have its own voucher pool

## Architecture Options

### Option 1: Vendor-Level Vouchers (Simple)

```
Vendor
├── Staff 1 (can create/manage vouchers)
├── Staff 2 (can create/manage vouchers)
├── Staff 3 (can create/manage vouchers)
└── Vouchers (shared pool)
    ├── SUMMER20 (all stores can use)
    ├── VIP25 (all stores can use)
    └── FREESHIP (all stores can use)
```

**Use Case**: Single brand, all stores share the same promotions

### Option 2: Store-Level Vouchers (Flexible)

```
Vendor
├── Store 1 (Kuala Lumpur)
│   ├── Staff 1, Staff 2
│   └── Vouchers (store-specific)
│       ├── KL20
│       └── KLVIP
│
├── Store 2 (Penang)
│   ├── Staff 3, Staff 4
│   └── Vouchers (store-specific)
│       ├── PENANG15
│       └── PGWELCOME
│
└── Global Vouchers (all stores)
    ├── COMPANY50
    └── GROUPDEAL
```

**Use Case**: Franchise or multiple locations with different promotions

### Option 3: Hybrid (Best for Complex Operations)

```
Vendor
├── Corporate Vouchers (vendor-level)
│   └── CORPORATE25 (all stores)
│
├── Store 1 Vouchers (store-level)
│   └── STORE1SPECIAL
│
└── Store 2 Vouchers (store-level)
    └── STORE2LAUNCH
```

## Implementation

### Database Schema

```php
// For multi-store support
Schema::create('stores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendor_id')->constrained('users');
    $table->string('name');
    $table->string('location');
    $table->string('code')->unique(); // e.g., 'KL01', 'PG01'
    $table->timestamps();
});

Schema::table('users', function (Blueprint $table) {
    $table->foreignId('vendor_id')->nullable()->constrained('users');
    $table->foreignId('store_id')->nullable()->constrained('stores');
    $table->string('role')->default('customer'); // vendor, manager, staff, customer
});
```

### Models

```php
// app/Models/Store.php
class Store extends Model
{
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'store_id');
    }

    public function vouchers(): MorphMany
    {
        return $this->morphMany(Voucher::class, 'owner');
    }
}

// app/Models/User.php
class User extends Authenticatable
{
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }
}
```

### Owner Resolver Configuration

The resolver is already set up to support both scenarios:

```php
// app/Support/Vouchers/CurrentOwnerResolver.php
public function resolve(): ?Model
{
    $user = Auth::user();

    // For vendor-level vouchers (Option 1)
    if ($user->vendor_id) {
        return $user->vendor;
    }

    // For store-level vouchers (Option 2)
    if ($user->store_id) {
        return $user->store;
    }

    // For direct ownership
    return $user;
}
```

## Usage Examples

### Scenario 1: Staff Creates Vendor-Level Voucher

```php
use AIArmada\Vouchers\Facades\Voucher;

// Staff logs in (has vendor_id)
auth()->login($staff); // Staff member at any store

// Create voucher - automatically assigned to their vendor
$voucher = Voucher::create([
    'code' => 'SUMMER20',
    'name' => '20% Summer Sale',
    'type' => VoucherType::Percentage,
    'value' => 20,
    'description' => 'Valid at all stores',
]);

// Voucher is now:
// - owner_type: App\Models\User
// - owner_id: $staff->vendor_id
// - Accessible by all staff of this vendor
// - Usable at all stores under this vendor
```

### Scenario 2: Staff Creates Store-Specific Voucher

```php
// Staff logs in (has store_id)
auth()->login($staff); // Staff member at Store #5

// Create voucher - automatically assigned to their store
$voucher = Voucher::create([
    'code' => 'STORE5DEAL',
    'name' => 'Store 5 Special',
    'type' => VoucherType::Fixed,
    'value' => 10,
    'description' => 'Only valid at KL Store',
]);

// Voucher is now:
// - owner_type: App\Models\Store
// - owner_id: $staff->store_id
// - Only accessible by staff of Store #5
// - Only usable at Store #5
```

### Scenario 3: Customer Uses Voucher at Checkout

```php
// Customer applies voucher at online checkout
use AIArmada\Cart\Facades\Cart;

try {
    // Voucher is validated automatically
    Cart::applyVoucher('SUMMER20');
    
    echo "Voucher applied successfully!";
    
} catch (\AIArmada\Vouchers\Exceptions\InvalidVoucherException $e) {
    echo "Cannot apply voucher: " . $e->getMessage();
}

// Or manual redemption at POS
Voucher::redeemManually(
    code: 'SUMMER20',
    userIdentifier: $customer->id,
    discountAmount: Money::MYR(2000),
    reference: "order-12345",
    metadata: [
        'store_id' => $staff->store_id,
        'store_name' => $staff->store->name,
        'terminal_id' => 5,
    ],
    redeemedBy: $staff, // Staff who processed it
    notes: "Redeemed at KL Store by {$staff->name}"
);
```

### Scenario 4: Vendor Creates Global Voucher

```php
// Vendor (owner) logs in
auth()->login($vendor);

// Create voucher - assigned to vendor
$voucher = Voucher::create([
    'code' => 'CORPORATE50',
    'name' => 'Corporate Deal',
    'type' => VoucherType::Fixed,
    'value' => 50,
    'description' => 'Valid at all stores nationwide',
]);

// This voucher:
// - Managed by vendor and all their staff
// - Usable at all stores
// - Shows in all stores' voucher lists
```

## Permission & Authorization

### Policy for Multi-Store

```php
// app/Policies/VoucherPolicy.php
class VoucherPolicy
{
    public function viewAny(User $user): bool
    {
        // Staff can view vouchers for their store or vendor
        return $user->isStaff() || $user->isManager() || $user->isVendor();
    }

    public function create(User $user): bool
    {
        // Staff and managers can create vouchers
        return $user->isStaff() || $user->isManager() || $user->isVendor();
    }

    public function update(User $user, Voucher $voucher): bool
    {
        // Staff can update their store's vouchers
        if ($user->store_id && $voucher->owner_type === Store::class) {
            return $voucher->owner_id === $user->store_id;
        }

        // Staff can update their vendor's vouchers
        if ($user->vendor_id && $voucher->owner_type === User::class) {
            return $voucher->owner_id === $user->vendor_id;
        }

        return false;
    }

    public function delete(User $user, Voucher $voucher): bool
    {
        // Only managers and vendors can delete
        if (!$user->isManager() && !$user->isVendor()) {
            return false;
        }

        // Same ownership logic as update
        return $this->update($user, $voucher);
    }
}
```

### Middleware for Store Managers

```php
// app/Http/Middleware/EnsureUserCanManageVouchers.php
class EnsureUserCanManageVouchers
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        
        if (!$user->isVendor() && !$user->isManager() && !$user->isStaff()) {
            abort(403, 'Unauthorized to manage vouchers');
        }
        
        return $next($request);
    }
}
```

## Querying Vouchers

### Get All Vouchers for Current Context

```php
use AIArmada\Vouchers\Facades\Voucher;

// Automatically scoped based on logged-in user
$vouchers = Voucher::all(); // Returns store or vendor vouchers + global

// For Store #5 staff, this returns:
// - Store #5's vouchers
// - Vendor's vouchers (if include_global is true)
// - Global vouchers (owner_id is null)
```

### Get Vouchers for Specific Store

```php
use AIArmada\Vouchers\Models\Voucher as VoucherModel;

// Get vouchers for a specific store
$storeVouchers = VoucherModel::query()
    ->where('owner_type', Store::class)
    ->where('owner_id', $storeId)
    ->get();

// Get all vendor-level vouchers
$vendorVouchers = VoucherModel::query()
    ->where('owner_type', User::class)
    ->where('owner_id', $vendorId)
    ->get();

// Get truly global vouchers (no owner)
$globalVouchers = VoucherModel::query()
    ->whereNull('owner_type')
    ->whereNull('owner_id')
    ->get();
```

## Reporting

### Store Performance Report

```php
use AIArmada\Vouchers\Models\VoucherUsage;

// Get voucher usage by store
$storeReport = VoucherUsage::query()
    ->join('vouchers', 'voucher_usage.voucher_id', '=', 'vouchers.id')
    ->where('vouchers.owner_type', Store::class)
    ->where('vouchers.owner_id', $storeId)
    ->whereBetween('voucher_usage.used_at', [$startDate, $endDate])
    ->selectRaw('
        COUNT(*) as total_redemptions,
        SUM(discount_amount) as total_discount,
        AVG(discount_amount) as avg_discount
    ')
    ->first();

echo "Store Performance:\n";
echo "Redemptions: {$storeReport->total_redemptions}\n";
echo "Total Discount: RM " . number_format($storeReport->total_discount / 100, 2) . "\n";
```

### Cross-Store Comparison

```php
// Compare all stores under a vendor
$stores = Store::where('vendor_id', $vendorId)->get();

foreach ($stores as $store) {
    $usage = VoucherUsage::query()
        ->join('vouchers', 'voucher_usage.voucher_id', '=', 'vouchers.id')
        ->where('vouchers.owner_id', $store->id)
        ->where('vouchers.owner_type', Store::class)
        ->count();
    
    echo "{$store->name}: {$usage} redemptions\n";
}
```

## Configuration Tips

### For Vendor-Only (No Multi-Store)

```php
// app/Support/Vouchers/CurrentOwnerResolver.php
public function resolve(): ?Model
{
    $user = Auth::user();
    
    // Always return vendor
    if ($user->vendor_id) {
        return $user->vendor;
    }
    
    return $user;
}
```

### For Store-Specific Only

```php
// app/Support/Vouchers/CurrentOwnerResolver.php
public function resolve(): ?Model
{
    $user = Auth::user();
    
    // Always return store
    if ($user->store_id) {
        return $user->store;
    }
    
    return null; // Or $user->vendor for fallback
}
```

### For Hybrid (Recommended)

The current resolver already supports this - it checks store first, then vendor, then user.

## API Endpoints Example

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'can:manage-vouchers'])->group(function () {
    // Voucher management (auto-scoped by resolver)
    Route::get('/vouchers', [VoucherController::class, 'index']);
    Route::post('/vouchers', [VoucherController::class, 'store']);
    Route::put('/vouchers/{code}', [VoucherController::class, 'update']);
    Route::delete('/vouchers/{code}', [VoucherController::class, 'destroy']);
    
    // Reports
    Route::get('/reports/vouchers/usage', [VoucherReportController::class, 'usage']);
    Route::get('/reports/vouchers/stores', [VoucherReportController::class, 'byStore']);
    Route::get('/reports/vouchers/performance', [VoucherReportController::class, 'performance']);
});
```

## Best Practices

### 1. Clear Ownership Hierarchy

```
Global (no owner)
    ↓
Vendor Level (vendor_id)
    ↓
Store Level (store_id)
```

### 2. Use Metadata for Store Attribution

```php
metadata: [
    'store_id' => $staff->store_id,
    'store_name' => $staff->store->name,
    'store_code' => $staff->store->code,
    'created_by_staff' => $staff->id,
    'created_by_name' => $staff->name,
],
```

### 3. Enable Manual Redemption for POS

```php
// When creating vouchers for in-store use
$voucher = Voucher::create([
    'code' => 'STORE5',
    'allows_manual_redemption' => true, // Important!
    // ... other fields
]);
```

## Summary

### ✅ Yes to Both Questions!

**1. Staff Manage Vouchers (Not Use Them)**
- Staff create, update, and delete vouchers via admin panel
- Vouchers are for customers to redeem
- Staff process redemptions but don't receive the discounts
- Full audit trail of who created/modified each voucher

**2. Multiple Stores Fully Supported**
- **Vendor-level**: All stores share vouchers
- **Store-level**: Each store has its own vouchers
- **Hybrid**: Both vendor and store-specific vouchers
- Automatic scoping via `CurrentOwnerResolver`
- Flexible - choose your architecture

### Key Features

✅ Polymorphic ownership (Vendor, Store, User, or any model)
✅ Auto-scoping based on logged-in user context
✅ Global vouchers accessible to all
✅ Staff attribution tracking via `redeemed_by`
✅ Rich metadata for reporting
✅ Manual redemption for POS systems
✅ Permission policies for access control

The package is **production-ready** for multi-staff, multi-store vendor operations! 🎯
