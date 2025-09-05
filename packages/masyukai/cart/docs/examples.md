# 🎯 Real-World Examples

Comprehensive examples showing how to implement MasyukAI Cart in various real-world scenarios.

## 🛍️ E-commerce Store

### Complete Shopping Cart Implementation

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Coupon;
use App\Services\TaxService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use MasyukAI\Cart\Facades\Cart;

class CartController extends Controller
{
    public function index()
    {
        return view('cart.index', [
            'cart' => Cart::content(),
            'recommendations' => $this->getRecommendations()
        ]);
    }
    
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:10',
            'variants' => 'array',
            'variants.size' => 'sometimes|string',
            'variants.color' => 'sometimes|string',
        ]);
        
        $product = Product::with(['category', 'variants'])->find($validated['product_id']);
        
        // Check inventory
        if (!$this->checkInventory($product, $validated['quantity'], $validated['variants'] ?? [])) {
            return response()->json(['error' => 'Insufficient stock'], 422);
        }
        
        // Calculate dynamic pricing
        $price = $this->calculatePrice($product, auth()->user());
        
        // Add to cart with comprehensive attributes
        Cart::add(
            id: $product->id . '_' . $this->getVariantKey($validated['variants'] ?? []),
            name: $product->name,
            price: $price,
            quantity: $validated['quantity'],
            attributes: [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'image' => $product->getImageUrl(),
                'category' => $product->category->name,
                'weight' => $product->weight,
                'dimensions' => $product->dimensions,
                'variants' => $validated['variants'] ?? [],
                'is_digital' => $product->is_digital,
                'requires_shipping' => !$product->is_digital,
            ]
        );
        
        // Apply automatic discounts and taxes
        $this->applyAutomaticConditions();
        
        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!',
            'cart' => Cart::content(),
            'recommendations' => $this->getCartBasedRecommendations()
        ]);
    }
    
    public function update(Request $request, string $itemId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0|max:99'
        ]);
        
        if ($validated['quantity'] === 0) {
            Cart::remove($itemId);
            $message = 'Item removed from cart';
        } else {
            Cart::update($itemId, ['quantity' => $validated['quantity']]);
            $message = 'Cart updated successfully';
        }
        
        $this->applyAutomaticConditions();
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'cart' => Cart::content()
        ]);
    }
    
    public function applyCoupon(Request $request)
    {
        $code = $request->validate(['code' => 'required|string'])['code'];
        
        $coupon = Coupon::where('code', strtoupper($code))
            ->where('active', true)
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$coupon) {
            return response()->json(['error' => 'Invalid or expired coupon code'], 422);
        }
        
        // Check usage limits
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['error' => 'Coupon usage limit exceeded'], 422);
        }
        
        // Check minimum cart value
        if ($coupon->minimum_amount && Cart::subtotal() < $coupon->minimum_amount) {
            return response()->json([
                'error' => "Minimum cart value of $" . $coupon->minimum_amount . " required"
            ], 422);
        }
        
        // Remove existing coupon
        Cart::removeCondition('coupon');
        
        // Apply new coupon
        $value = $coupon->is_percentage ? $coupon->value . '%' : $coupon->value;
        Cart::addDiscount('coupon', $value, [
            'description' => $coupon->description,
            'coupon_id' => $coupon->id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'cart' => Cart::content()
        ]);
    }
    
    public function updateShipping(Request $request)
    {
        $option = $request->validate(['shipping_option' => 'required|string'])['shipping_option'];
        
        $shippingCost = ShippingService::calculateCost(
            Cart::content(),
            $option,
            auth()->user()->shippingAddress ?? null
        );
        
        // Remove existing shipping
        Cart::removeCondition('shipping');
        
        // Add new shipping option
        Cart::addFee('shipping', $shippingCost, [
            'description' => ShippingService::getOptionName($option),
            'estimated_delivery' => ShippingService::getEstimatedDelivery($option)
        ]);
        
        return response()->json([
            'success' => true,
            'cart' => Cart::content()
        ]);
    }
    
    private function applyAutomaticConditions()
    {
        $user = auth()->user();
        
        // Clear existing automatic conditions
        Cart::removeCondition(['vip-discount', 'first-time-buyer', 'bulk-discount', 'sales-tax']);
        
        // VIP customer discount
        if ($user?->membership_tier === 'vip') {
            Cart::addDiscount('vip-discount', '15%', ['description' => 'VIP Member Discount']);
        }
        
        // First-time buyer discount
        if ($user && $user->orders()->count() === 0) {
            Cart::addDiscount('first-time-buyer', '10%', ['description' => 'Welcome! First-time buyer discount']);
        }
        
        // Bulk purchase discount
        if (Cart::count() >= 5) {
            Cart::addDiscount('bulk-discount', '5%', ['description' => 'Bulk purchase discount (5+ items)']);
        }
        
        // Apply tax based on user location
        if ($user?->billing_address) {
            $taxRate = TaxService::getTaxRate($user->billing_address);
            if ($taxRate > 0) {
                Cart::addTax('sales-tax', $taxRate . '%', [
                    'description' => 'Sales Tax (' . $user->billing_address->state . ')'
                ]);
            }
        }
    }
    
    private function checkInventory(Product $product, int $quantity, array $variants): bool
    {
        if ($product->is_digital) {
            return true; // Digital products don't have inventory limits
        }
        
        if (empty($variants)) {
            return $product->stock >= $quantity;
        }
        
        // Check variant-specific inventory
        $variant = $product->variants()
            ->where('size', $variants['size'] ?? null)
            ->where('color', $variants['color'] ?? null)
            ->first();
            
        return $variant && $variant->stock >= $quantity;
    }
    
    private function calculatePrice(Product $product, $user): float
    {
        $basePrice = $product->price;
        
        // Volume discounts for bulk buyers
        if ($user?->company && $user->company->is_wholesale) {
            return $product->wholesale_price ?? $basePrice * 0.85; // 15% wholesale discount
        }
        
        // Membership tier pricing
        if ($user?->membership_tier) {
            $discount = match($user->membership_tier) {
                'bronze' => 0.05,  // 5%
                'silver' => 0.10,  // 10%
                'gold' => 0.15,    // 15%
                'platinum' => 0.20, // 20%
                default => 0
            };
            return $basePrice * (1 - $discount);
        }
        
        return $basePrice;
    }
    
    private function getVariantKey(array $variants): string
    {
        if (empty($variants)) {
            return 'default';
        }
        
        ksort($variants);
        return md5(serialize($variants));
    }
}
```

### Frontend Cart Component (Livewire)

```php
<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use MasyukAI\Cart\Facades\Cart;

class ShoppingCart extends Component
{
    public $showCartDetails = false;
    public $couponCode = '';
    public $shippingOption = 'standard';
    public $loading = false;
    
    protected $listeners = [
        'cart-updated' => 'refreshCart',
        'product-added' => 'refreshCart'
    ];
    
    public function mount()
    {
        $this->refreshCart();
    }
    
    public function addToCart($productId, $quantity = 1, $variants = [])
    {
        $this->loading = true;
        
        try {
            $product = Product::findOrFail($productId);
            
            Cart::add(
                id: $productId . '_' . md5(serialize($variants)),
                name: $product->name,
                price: $product->price,
                quantity: $quantity,
                attributes: array_merge($variants, [
                    'image' => $product->image_url,
                    'category' => $product->category->name,
                ])
            );
            
            $this->dispatch('cart-updated');
            $this->dispatch('show-notification', 'Product added to cart!', 'success');
            
        } catch (\Exception $e) {
            $this->dispatch('show-notification', 'Failed to add product to cart', 'error');
        } finally {
            $this->loading = false;
        }
    }
    
    public function updateQuantity($itemId, $quantity)
    {
        if ($quantity <= 0) {
            Cart::remove($itemId);
        } else {
            Cart::update($itemId, ['quantity' => $quantity]);
        }
        
        $this->refreshCart();
        $this->dispatch('cart-updated');
    }
    
    public function removeItem($itemId)
    {
        Cart::remove($itemId);
        $this->refreshCart();
        $this->dispatch('cart-updated');
        $this->dispatch('show-notification', 'Item removed from cart', 'info');
    }
    
    public function applyCoupon()
    {
        if (empty($this->couponCode)) {
            return;
        }
        
        // This would typically make an API call
        $this->dispatch('apply-coupon', $this->couponCode);
        $this->couponCode = '';
    }
    
    public function updateShipping()
    {
        $this->dispatch('update-shipping', $this->shippingOption);
    }
    
    public function clearCart()
    {
        Cart::clear();
        $this->refreshCart();
        $this->dispatch('cart-updated');
        $this->dispatch('show-notification', 'Cart cleared', 'info');
    }
    
    public function toggleCartDetails()
    {
        $this->showCartDetails = !$this->showCartDetails;
    }
    
    public function refreshCart()
    {
        // Force refresh of cart data
        $this->dispatch('$refresh');
    }
    
    public function getCartProperty()
    {
        return Cart::content();
    }
    
    public function render()
    {
        return view('livewire.shopping-cart', [
            'cart' => $this->cart,
            'isEmpty' => Cart::isEmpty(),
            'itemCount' => Cart::count(),
            'subtotal' => Cart::subtotal(),
            'total' => Cart::total(),
        ]);
    }
}
```

---

## 🏢 Multi-Vendor Marketplace

### Vendor-Specific Cart Management

```php
<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\Product;
use MasyukAI\Cart\Facades\Cart;

class MarketplaceCartService
{
    public function addProductToVendorCart(Product $product, int $quantity, array $attributes = [])
    {
        $vendorCart = Cart::instance("vendor_{$product->vendor_id}");
        
        $vendorCart->add(
            id: $product->id,
            name: $product->name,
            price: $product->price,
            quantity: $quantity,
            attributes: array_merge($attributes, [
                'vendor_id' => $product->vendor_id,
                'vendor_name' => $product->vendor->name,
                'vendor_logo' => $product->vendor->logo_url,
                'commission_rate' => $product->vendor->commission_rate,
            ])
        );
        
        // Apply vendor-specific conditions
        $this->applyVendorConditions($vendorCart, $product->vendor);
        
        return $vendorCart->content();
    }
    
    public function getAllVendorCarts(): array
    {
        $vendorCarts = [];
        $userVendorIds = $this->getUserVendorIds();
        
        foreach ($userVendorIds as $vendorId) {
            $cart = Cart::instance("vendor_{$vendorId}");
            if (!$cart->isEmpty()) {
                $vendor = Vendor::find($vendorId);
                $vendorCarts[] = [
                    'vendor' => $vendor,
                    'cart' => $cart->content(),
                    'shipping_cost' => $this->calculateVendorShipping($cart, $vendor),
                ];
            }
        }
        
        return $vendorCarts;
    }
    
    public function getGrandTotal(): float
    {
        $total = 0;
        $vendorCarts = $this->getAllVendorCarts();
        
        foreach ($vendorCarts as $vendorCart) {
            $total += $vendorCart['cart']['total'] + $vendorCart['shipping_cost'];
        }
        
        return $total;
    }
    
    public function mergeCarts(string $fromInstance, string $toInstance): bool
    {
        $fromCart = Cart::instance($fromInstance);
        $toCart = Cart::instance($toInstance);
        
        if ($fromCart->isEmpty()) {
            return false;
        }
        
        // Merge items
        foreach ($fromCart->getItems() as $item) {
            $toCart->add(
                $item->id,
                $item->name,
                $item->price,
                $item->quantity,
                $item->attributes->toArray()
            );
        }
        
        // Clear source cart
        $fromCart->clear();
        
        return true;
    }
    
    private function applyVendorConditions($cart, Vendor $vendor)
    {
        // Minimum order amount
        if ($vendor->minimum_order_amount && $cart->subtotal() < $vendor->minimum_order_amount) {
            $remaining = $vendor->minimum_order_amount - $cart->subtotal();
            $cart->addFee('minimum-order-fee', $remaining, [
                'description' => "Minimum order amount: $" . $vendor->minimum_order_amount,
                'temporary' => true
            ]);
        }
        
        // Free shipping threshold
        if ($vendor->free_shipping_threshold && $cart->subtotal() >= $vendor->free_shipping_threshold) {
            $cart->addDiscount('free-shipping', '100%', [
                'target' => 'shipping',
                'description' => 'Free shipping on orders over $' . $vendor->free_shipping_threshold
            ]);
        }
        
        // Vendor-specific discount
        if ($vendor->current_promotion) {
            $cart->addDiscount(
                'vendor-promotion',
                $vendor->current_promotion['value'],
                ['description' => $vendor->current_promotion['description']]
            );
        }
    }
    
    private function calculateVendorShipping($cart, Vendor $vendor): float
    {
        // Calculate shipping based on vendor location and items
        $baseShipping = $vendor->base_shipping_cost ?? 5.99;
        $itemCount = $cart->count();
        $additionalItemCost = max(0, $itemCount - 1) * 1.50;
        
        return $baseShipping + $additionalItemCost;
    }
    
    private function getUserVendorIds(): array
    {
        // Get all vendor IDs that the current user has items from
        // This could be stored in session or database
        return session()->get('user_vendor_ids', []);
    }
}
```

---

## 🔄 Subscription Service

### Subscription Cart with Billing Cycles

```php
<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Addon;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Conditions\CartCondition;

class SubscriptionCartService
{
    public function addPlan(Plan $plan, string $billingCycle = 'monthly', array $customization = [])
    {
        $subscriptionCart = Cart::instance('subscription');
        
        // Calculate price based on billing cycle
        $price = $this->calculatePlanPrice($plan, $billingCycle);
        
        $subscriptionCart->add(
            id: "plan_{$plan->id}",
            name: $plan->name,
            price: $price,
            quantity: 1,
            attributes: [
                'type' => 'plan',
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'features' => $plan->features,
                'customization' => $customization,
                'is_recurring' => true,
                'trial_days' => $plan->trial_days,
            ]
        );
        
        // Apply billing cycle discounts
        $this->applyBillingCycleDiscounts($subscriptionCart, $billingCycle);
        
        return $subscriptionCart->content();
    }
    
    public function addAddon(Addon $addon, int $quantity = 1)
    {
        $subscriptionCart = Cart::instance('subscription');
        
        $subscriptionCart->add(
            id: "addon_{$addon->id}",
            name: $addon->name,
            price: $addon->price,
            quantity: $quantity,
            attributes: [
                'type' => 'addon',
                'addon_id' => $addon->id,
                'billing_cycle' => 'monthly', // Addons are typically monthly
                'description' => $addon->description,
                'is_recurring' => true,
            ]
        );
        
        return $subscriptionCart->content();
    }
    
    public function calculateAnnualSavings(): float
    {
        $cart = Cart::instance('subscription');
        $monthlyTotal = 0;
        $annualTotal = $cart->total();
        
        foreach ($cart->getItems() as $item) {
            if ($item->getAttribute('billing_cycle') === 'annual') {
                // Calculate what this would cost monthly
                $monthlyPrice = $this->getPlanMonthlyPrice($item->getAttribute('plan_id'));
                $monthlyTotal += $monthlyPrice * 12;
            }
        }
        
        return max(0, $monthlyTotal - $annualTotal);
    }
    
    public function upgradeToAnnual(): array
    {
        $cart = Cart::instance('subscription');
        $upgradedItems = [];
        
        foreach ($cart->getItems() as $item) {
            if ($item->getAttribute('billing_cycle') === 'monthly' && $item->getAttribute('type') === 'plan') {
                $planId = $item->getAttribute('plan_id');
                $plan = Plan::find($planId);
                
                // Remove monthly plan
                $cart->remove($item->id);
                
                // Add annual plan
                $this->addPlan($plan, 'annual', $item->getAttribute('customization', []));
                
                $upgradedItems[] = $plan->name;
            }
        }
        
        return $upgradedItems;
    }
    
    public function applyPromoCode(string $promoCode): bool
    {
        $cart = Cart::instance('subscription');
        
        // Validate promo code (this would typically check database)
        $promo = $this->validatePromoCode($promoCode);
        
        if (!$promo) {
            return false;
        }
        
        // Apply promo discount
        $condition = new CartCondition(
            'promo-code',
            'discount',
            'subtotal',
            $promo['value'],
            [
                'description' => $promo['description'],
                'promo_code' => $promoCode,
                'expires_at' => $promo['expires_at'],
            ]
        );
        
        $cart->addCondition($condition);
        
        return true;
    }
    
    private function calculatePlanPrice(Plan $plan, string $billingCycle): float
    {
        return match($billingCycle) {
            'monthly' => $plan->monthly_price,
            'annual' => $plan->annual_price ?? ($plan->monthly_price * 12 * 0.8), // 20% annual discount
            'quarterly' => $plan->quarterly_price ?? ($plan->monthly_price * 3 * 0.95), // 5% quarterly discount
            default => $plan->monthly_price
        };
    }
    
    private function applyBillingCycleDiscounts($cart, string $billingCycle)
    {
        if ($billingCycle === 'annual') {
            $cart->addDiscount('annual-billing', '20%', [
                'description' => 'Annual billing discount - Save 20%!',
                'highlight' => true
            ]);
        } elseif ($billingCycle === 'quarterly') {
            $cart->addDiscount('quarterly-billing', '5%', [
                'description' => 'Quarterly billing discount - Save 5%!'
            ]);
        }
    }
    
    private function validatePromoCode(string $code): ?array
    {
        // This would typically check your database
        $promoCodes = [
            'SAVE20' => ['value' => '20%', 'description' => '20% off first month', 'expires_at' => '2024-12-31'],
            'ANNUAL50' => ['value' => '50.00', 'description' => '$50 off annual plans', 'expires_at' => '2024-12-31'],
        ];
        
        return $promoCodes[strtoupper($code)] ?? null;
    }
    
    private function getPlanMonthlyPrice(int $planId): float
    {
        return Plan::find($planId)?->monthly_price ?? 0;
    }
}
```

---

## 🏭 B2B Wholesale Platform

### Enterprise Cart with Complex Pricing

```php
<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Customer;
use App\Models\PricingTier;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Conditions\CartCondition;

class B2BCartService
{
    public function addProductWithTierPricing(Product $product, int $quantity, Customer $customer)
    {
        $quote = Cart::instance("quote_{$customer->id}");
        
        // Calculate tier-based pricing
        $unitPrice = $this->calculateTierPrice($product, $quantity, $customer);
        $totalPrice = $unitPrice * $quantity;
        
        $quote->add(
            id: $product->sku,
            name: $product->name,
            price: $unitPrice,
            quantity: $quantity,
            attributes: [
                'sku' => $product->sku,
                'product_id' => $product->id,
                'unit_price' => $unitPrice,
                'list_price' => $product->list_price,
                'discount_percentage' => $this->calculateDiscountPercentage($product->list_price, $unitPrice),
                'lead_time_days' => $product->lead_time_days,
                'minimum_order_quantity' => $product->minimum_order_quantity,
                'weight' => $product->weight,
                'category' => $product->category->name,
                'manufacturer' => $product->manufacturer,
                'specifications' => $product->specifications,
            ]
        );
        
        // Apply volume discounts
        $this->applyVolumeDiscounts($quote, $customer);
        
        // Apply payment terms
        $this->applyPaymentTerms($quote, $customer);
        
        return $quote->content();
    }
    
    public function createBulkQuote(array $items, Customer $customer): array
    {
        $quote = Cart::instance("quote_{$customer->id}");
        $quote->clear(); // Start fresh
        
        foreach ($items as $item) {
            $product = Product::where('sku', $item['sku'])->first();
            if ($product) {
                $this->addProductWithTierPricing($product, $item['quantity'], $customer);
            }
        }
        
        // Apply enterprise-level discounts
        $this->applyEnterpriseDiscounts($quote, $customer);
        
        return [
            'quote' => $quote->content(),
            'quote_number' => $this->generateQuoteNumber($customer),
            'validity_days' => $customer->quote_validity_days ?? 30,
            'payment_terms' => $customer->payment_terms,
            'shipping_terms' => $customer->shipping_terms,
        ];
    }
    
    public function convertQuoteToOrder(string $quoteInstance, Customer $customer): bool
    {
        $quoteCart = Cart::instance($quoteInstance);
        $orderCart = Cart::instance("order_{$customer->id}");
        
        if ($quoteCart->isEmpty()) {
            return false;
        }
        
        // Copy all items to order cart
        foreach ($quoteCart->getItems() as $item) {
            $orderCart->add(
                $item->id,
                $item->name,
                $item->price,
                $item->quantity,
                $item->attributes->toArray()
            );
        }
        
        // Copy conditions
        foreach ($quoteCart->getConditions() as $condition) {
            $orderCart->addCondition($condition);
        }
        
        // Add order-specific conditions
        $this->addOrderConditions($orderCart, $customer);
        
        return true;
    }
    
    private function calculateTierPrice(Product $product, int $quantity, Customer $customer): float
    {
        $basePrice = $product->base_price;
        
        // Customer tier discount
        $tierDiscount = match($customer->tier) {
            'bronze' => 0.05,    // 5%
            'silver' => 0.10,    // 10%
            'gold' => 0.15,      // 15%
            'platinum' => 0.20,  // 20%
            'diamond' => 0.25,   // 25%
            default => 0
        };
        
        // Volume-based pricing
        $volumeDiscount = $this->getVolumeDiscount($product, $quantity);
        
        // Apply both discounts
        $totalDiscount = min(0.40, $tierDiscount + $volumeDiscount); // Cap at 40%
        
        return $basePrice * (1 - $totalDiscount);
    }
    
    private function getVolumeDiscount(Product $product, int $quantity): float
    {
        $volumeTiers = [
            1000 => 0.15,    // 15% for 1000+
            500 => 0.12,     // 12% for 500+
            100 => 0.08,     // 8% for 100+
            50 => 0.05,      // 5% for 50+
            10 => 0.02,      // 2% for 10+
        ];
        
        foreach ($volumeTiers as $threshold => $discount) {
            if ($quantity >= $threshold) {
                return $discount;
            }
        }
        
        return 0;
    }
    
    private function applyVolumeDiscounts($cart, Customer $customer)
    {
        $totalQuantity = $cart->count();
        
        if ($totalQuantity >= 1000) {
            $cart->addDiscount('enterprise-volume', '10%', [
                'description' => 'Enterprise Volume Discount (1000+ units)',
                'type' => 'volume'
            ]);
        } elseif ($totalQuantity >= 500) {
            $cart->addDiscount('bulk-volume', '6%', [
                'description' => 'Bulk Volume Discount (500+ units)',
                'type' => 'volume'
            ]);
        } elseif ($totalQuantity >= 100) {
            $cart->addDiscount('volume-discount', '3%', [
                'description' => 'Volume Discount (100+ units)',
                'type' => 'volume'
            ]);
        }
    }
    
    private function applyPaymentTerms($cart, Customer $customer)
    {
        // Early payment discount
        if ($customer->payment_terms === 'net_10') {
            $cart->addDiscount('early-payment', '2%', [
                'description' => 'Early Payment Discount (Net 10)',
                'type' => 'payment'
            ]);
        }
        
        // Credit terms fee (for extended payment periods)
        if ($customer->payment_terms === 'net_60' || $customer->payment_terms === 'net_90') {
            $cart->addFee('extended-terms', '1.5%', [
                'description' => 'Extended Payment Terms Fee',
                'type' => 'payment'
            ]);
        }
    }
    
    private function applyEnterpriseDiscounts($cart, Customer $customer)
    {
        // Annual contract discount
        if ($customer->has_annual_contract) {
            $cart->addDiscount('annual-contract', '5%', [
                'description' => 'Annual Contract Discount',
                'type' => 'contract'
            ]);
        }
        
        // Loyalty discount
        if ($customer->years_as_customer >= 5) {
            $cart->addDiscount('loyalty-discount', '3%', [
                'description' => 'Long-term Customer Loyalty Discount',
                'type' => 'loyalty'
            ]);
        }
        
        // Strategic partner discount
        if ($customer->is_strategic_partner) {
            $cart->addDiscount('strategic-partner', '8%', [
                'description' => 'Strategic Partner Discount',
                'type' => 'partnership'
            ]);
        }
    }
    
    private function addOrderConditions($cart, Customer $customer)
    {
        // Shipping and handling
        $shippingCost = $this->calculateB2BShipping($cart, $customer);
        $cart->addFee('shipping-handling', $shippingCost, [
            'description' => 'Shipping & Handling',
            'type' => 'shipping'
        ]);
        
        // Processing fee for small orders
        if ($cart->subtotal() < $customer->minimum_order_amount) {
            $cart->addFee('small-order-fee', '25.00', [
                'description' => 'Small Order Processing Fee',
                'type' => 'processing'
            ]);
        }
    }
    
    private function calculateB2BShipping($cart, Customer $customer): float
    {
        $baseShipping = 50.00; // Base B2B shipping
        $weightBasedCost = $this->calculateWeightBasedShipping($cart);
        
        // Customer-specific shipping rates
        if ($customer->preferred_shipping_rate) {
            return $customer->preferred_shipping_rate;
        }
        
        return $baseShipping + $weightBasedCost;
    }
    
    private function calculateWeightBasedShipping($cart): float
    {
        $totalWeight = 0;
        
        foreach ($cart->getItems() as $item) {
            $weight = $item->getAttribute('weight', 1); // Default 1 lb
            $totalWeight += $weight * $item->quantity;
        }
        
        // $2 per pound over 50 lbs
        return max(0, ($totalWeight - 50) * 2.00);
    }
    
    private function calculateDiscountPercentage(float $listPrice, float $actualPrice): float
    {
        if ($listPrice <= 0) return 0;
        return round((($listPrice - $actualPrice) / $listPrice) * 100, 2);
    }
    
    private function generateQuoteNumber(Customer $customer): string
    {
        return 'Q-' . $customer->id . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
```

---

## 📱 Mobile API Integration

### RESTful API for Mobile Apps

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;
use MasyukAI\Cart\Facades\Cart;

class CartApiController extends Controller
{
    public function index(Request $request)
    {
        $instance = $request->get('instance', 'default');
        
        return new CartResource(
            Cart::instance($instance)->content()
        );
    }
    
    public function add(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'quantity' => 'required|integer|min:1|max:999',
            'attributes' => 'array',
            'instance' => 'string|max:255'
        ]);
        
        $instance = $validated['instance'] ?? 'default';
        
        Cart::instance($instance)->add(
            $validated['id'],
            $validated['name'],
            $validated['price'],
            $validated['quantity'],
            $validated['attributes'] ?? []
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'cart' => new CartResource(Cart::instance($instance)->content())
        ], 201);
    }
    
    public function update(Request $request, string $itemId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0|max:999',
            'attributes' => 'array',
            'instance' => 'string|max:255'
        ]);
        
        $instance = $validated['instance'] ?? 'default';
        $cart = Cart::instance($instance);
        
        if ($validated['quantity'] === 0) {
            $cart->remove($itemId);
            $message = 'Item removed from cart';
        } else {
            $updateData = ['quantity' => $validated['quantity']];
            if (isset($validated['attributes'])) {
                $updateData['attributes'] = $validated['attributes'];
            }
            
            $cart->update($itemId, $updateData);
            $message = 'Cart updated successfully';
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'cart' => new CartResource($cart->content())
        ]);
    }
    
    public function remove(Request $request, string $itemId)
    {
        $instance = $request->get('instance', 'default');
        
        Cart::instance($instance)->remove($itemId);
        
        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart' => new CartResource(Cart::instance($instance)->content())
        ]);
    }
    
    public function clear(Request $request)
    {
        $instance = $request->get('instance', 'default');
        
        Cart::instance($instance)->clear();
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'cart' => new CartResource(Cart::instance($instance)->content())
        ]);
    }
    
    public function applyCondition(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:discount,tax,fee',
            'target' => 'required|in:subtotal,item',
            'value' => 'required|string',
            'attributes' => 'array',
            'instance' => 'string|max:255'
        ]);
        
        $instance = $validated['instance'] ?? 'default';
        $cart = Cart::instance($instance);
        
        if ($validated['type'] === 'discount') {
            $cart->addDiscount($validated['name'], $validated['value'], $validated['attributes'] ?? []);
        } elseif ($validated['type'] === 'tax') {
            $cart->addTax($validated['name'], $validated['value'], $validated['attributes'] ?? []);
        } else {
            $cart->addFee($validated['name'], $validated['value'], $validated['attributes'] ?? []);
        }
        
        return response()->json([
            'success' => true,
            'message' => ucfirst($validated['type']) . ' applied successfully',
            'cart' => new CartResource($cart->content())
        ]);
    }
    
    public function removeCondition(Request $request, string $conditionName)
    {
        $instance = $request->get('instance', 'default');
        
        Cart::instance($instance)->removeCondition($conditionName);
        
        return response()->json([
            'success' => true,
            'message' => 'Condition removed successfully',
            'cart' => new CartResource(Cart::instance($instance)->content())
        ]);
    }
    
    public function merge(Request $request)
    {
        $validated = $request->validate([
            'from_instance' => 'required|string',
            'to_instance' => 'string',
            'strategy' => 'in:add_quantities,keep_highest,replace'
        ]);
        
        $toInstance = $validated['to_instance'] ?? 'default';
        $strategy = $validated['strategy'] ?? 'add_quantities';
        
        $success = Cart::instance($toInstance)->merge(
            $validated['from_instance'],
            $strategy
        );
        
        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Carts merged successfully',
                'cart' => new CartResource(Cart::instance($toInstance)->content())
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to merge carts'
            ], 400);
        }
    }
    
    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string',
            'field' => 'string|in:name,id,attribute',
            'instance' => 'string|max:255'
        ]);
        
        $instance = $validated['instance'] ?? 'default';
        $cart = Cart::instance($instance);
        $field = $validated['field'] ?? 'name';
        $query = strtolower($validated['query']);
        
        $results = $cart->search(function ($item) use ($query, $field) {
            return match($field) {
                'name' => str_contains(strtolower($item->name), $query),
                'id' => str_contains(strtolower($item->id), $query),
                'attribute' => collect($item->attributes)->contains(function ($value) use ($query) {
                    return str_contains(strtolower((string)$value), $query);
                }),
                default => str_contains(strtolower($item->name), $query)
            };
        });
        
        return response()->json([
            'success' => true,
            'results' => $results->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'attributes' => $item->attributes
            ])
        ]);
    }
}
```

### Mobile App Cart Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'instance' => $this->resource['instance'] ?? 'default',
            'summary' => [
                'item_count' => $this->resource['count'] ?? 0,
                'total_quantity' => $this->resource['quantity'] ?? 0,
                'subtotal' => number_format($this->resource['subtotal'] ?? 0, 2),
                'total' => number_format($this->resource['total'] ?? 0, 2),
                'is_empty' => $this->resource['is_empty'] ?? true,
                'currency' => 'USD',
                'formatted_subtotal' => '$' . number_format($this->resource['subtotal'] ?? 0, 2),
                'formatted_total' => '$' . number_format($this->resource['total'] ?? 0, 2),
            ],
            'items' => collect($this->resource['items'] ?? [])->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => number_format($item->price, 2),
                    'quantity' => $item->quantity,
                    'total' => number_format($item->getPriceSum(), 2),
                    'formatted_price' => '$' . number_format($item->price, 2),
                    'formatted_total' => '$' . number_format($item->getPriceSum(), 2),
                    'attributes' => $item->attributes,
                    'image_url' => $item->getAttribute('image'),
                    'category' => $item->getAttribute('category'),
                ];
            }),
            'conditions' => collect($this->resource['conditions'] ?? [])->map(function ($condition) {
                return [
                    'name' => $condition->getName(),
                    'type' => $condition->getType(),
                    'value' => $condition->getValue(),
                    'target' => $condition->getTarget(),
                    'description' => $condition->getAttribute('description'),
                    'calculated_value' => number_format($condition->getCalculatedValue($this->resource['subtotal'] ?? 0), 2),
                    'formatted_value' => '$' . number_format($condition->getCalculatedValue($this->resource['subtotal'] ?? 0), 2),
                ];
            }),
            'meta' => [
                'last_updated' => now()->toISOString(),
                'tax_inclusive' => false,
                'shipping_required' => $this->shippingRequired(),
                'available_payment_methods' => ['credit_card', 'paypal', 'apple_pay', 'google_pay'],
            ]
        ];
    }
    
    private function shippingRequired(): bool
    {
        if (!isset($this->resource['items'])) {
            return false;
        }
        
        return collect($this->resource['items'])->contains(function ($item) {
            return $item->getAttribute('requires_shipping', true);
        });
    }
}
```

These examples demonstrate how to implement MasyukAI Cart in various real-world scenarios, from simple e-commerce stores to complex B2B platforms and mobile applications. Each example shows best practices for handling different business requirements while leveraging the cart package's powerful features.
