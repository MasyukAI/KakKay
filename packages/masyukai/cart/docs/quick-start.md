# 🚀 Quick Start Guide

Get up and running with MasyukAI Cart in under 5 minutes. This guide covers the essential features you need to start building amazing cart experiences.

## 📦 Installation (30 seconds)

```bash
composer require masyukai/cart
```

**That's it!** Laravel's auto-discovery handles the rest.

---

## 🛒 Your First Cart (1 minute)

### Basic Operations

```php
use MasyukAI\Cart\Facades\Cart;

// Add your first item
Cart::add('iphone-15', 'iPhone 15 Pro', 999.99, 1);

// Check what's in the cart
$items = Cart::getItems();           // Get cart items collection
$cartData = Cart::content();         // Complete cart data (items + conditions + totals)
$total = Cart::total();              // Final total: 999.99
$count = Cart::count();              // Total quantity: 1

echo "Cart has {$count} items worth ${$total}";
// Output: Cart has 1 items worth $999.99
```

### Add Multiple Items

```php
// Add items with attributes
Cart::add('macbook-pro', 'MacBook Pro 16"', 2499.99, 1, [
    'color' => 'Space Gray',
    'storage' => '512GB',
    'memory' => '16GB',
    'warranty' => 'AppleCare+'
]);

Cart::add('airpods-pro', 'AirPods Pro', 249.99, 2, [
    'color' => 'White',
    'features' => ['Active Noise Cancellation', 'Spatial Audio']
]);

// Check updated totals
echo "Items: " . Cart::count() . "\n";        // Items: 4
echo "Total: $" . Cart::total() . "\n";       // Total: $3,999.97
```

---

## 🏷️ Apply Discounts & Fees (2 minutes)

### Simple Conditions

```php
// Apply a 20% discount
Cart::addDiscount('black-friday', '20%');

// Add sales tax
Cart::addTax('sales-tax', '8.25%');

// Add shipping fee
Cart::addFee('express-shipping', '15.99');

// Check updated total
echo "Subtotal: $" . Cart::subtotal() . "\n";     // Subtotal: $3,999.97
echo "Final Total: $" . Cart::total() . "\n";     // Final Total: $3,255.96
```

### Advanced Conditions

```php
use MasyukAI\Cart\Conditions\CartCondition;

// Conditional discount (only for orders over $100)
$volumeDiscount = new CartCondition(
    'volume-discount',
    'discount', 
    'subtotal',
    '-5%',
    ['minimum_amount' => 100.00]
);

Cart::addCondition($volumeDiscount);

// Item-specific condition
Cart::addItemCondition('macbook-pro', 
    new CartCondition('apple-care', 'fee', 'price', '199.00')
);
```

---

## 🔄 Multiple Cart Instances (2 minutes)

Perfect for different cart types in your application:

```php
// Main shopping cart
$mainCart = Cart::instance('main');
$mainCart->add('laptop', 'Gaming Laptop', 1499.99);

// Customer wishlist
$wishlist = Cart::instance('wishlist');
$wishlist->add('dream-phone', 'Latest iPhone', 1199.99);
$wishlist->add('smartwatch', 'Apple Watch', 399.99);

// Product comparison cart
$comparison = Cart::instance('comparison');
$comparison->add('phone-a', 'Samsung Galaxy', 899.99);
$comparison->add('phone-b', 'Google Pixel', 799.99);

// Check different instances
echo "Main cart: " . $mainCart->count() . " items\n";       // 1 item
echo "Wishlist: " . $wishlist->count() . " items\n";        // 2 items  
echo "Comparison: " . $comparison->count() . " items\n";    // 2 items

// Switch between instances
Cart::instance('wishlist');
echo "Current instance total: $" . Cart::total();           // Wishlist total
```

---

## 🔍 Search & Filter Cart Content

```php
// Search cart items
$expensiveItems = Cart::search(fn($item) => $item->price > 500);
$appleProducts = Cart::search(fn($item) => str_contains($item->name, 'Apple'));
$redItems = Cart::search(fn($item) => $item->getAttribute('color') === 'red');

// Advanced collection operations
$items = Cart::content();

$electronics = $items->whereAttribute('category', 'electronics');
$bulkItems = $items->whereQuantityAbove(5);
$itemsByBrand = $items->groupByAttribute('brand');
$topItems = $items->sortByPrice('desc')->take(3);

// Get cart statistics
$stats = $items->getStatistics();
// Returns: total_items, total_quantity, average_price, etc.
```

---

## 🛠️ Cart Management Operations

### Update & Remove Items

```php
// Update item quantity
Cart::update('iphone-15', ['quantity' => 3]);

// Update item attributes
Cart::update('macbook-pro', [
    'attributes' => [
        'color' => 'Silver',
        'storage' => '1TB'  // Upgraded storage
    ]
]);

// Remove specific item
Cart::remove('airpods-pro');

// Clear entire cart
Cart::clear();
```

### Bulk Operations

```php
// Add multiple items at once
Cart::addMany([
    ['id' => 'item-1', 'name' => 'Product 1', 'price' => 29.99, 'quantity' => 2],
    ['id' => 'item-2', 'name' => 'Product 2', 'price' => 39.99, 'quantity' => 1],
    ['id' => 'item-3', 'name' => 'Product 3', 'price' => 49.99, 'quantity' => 3],
]);

// Update multiple items
Cart::updateMany([
    'item-1' => ['quantity' => 5],
    'item-2' => ['quantity' => 2],
]);

// Remove multiple items
Cart::removeMany(['item-1', 'item-3']);
```

---

## 📊 Getting Cart Information

### Basic Information

```php
// Cart state
$isEmpty = Cart::isEmpty();          // boolean
$itemCount = Cart::count();          // total quantity
$uniqueItems = Cart::countItems();   // unique items count

// Financial totals
$subtotal = Cart::subtotal();        // before conditions
$total = Cart::total();             // final total
$taxAmount = Cart::getTaxAmount();  // calculated tax
$discountAmount = Cart::getDiscountAmount(); // total discounts
```

### Detailed Cart Data

```php
// Get complete cart information
$cartData = Cart::content();

// Array structure:
[
    'instance' => 'main',
    'items' => CartCollection,     // All cart items
    'conditions' => ConditionCollection, // Applied conditions
    'subtotal' => 1999.98,
    'total' => 1847.98,
    'quantity' => 3,
    'count' => 2,                 // unique items
    'is_empty' => false,
    'subtotal_with_conditions' => 1999.98,
    'total_with_conditions' => 1847.98,
    'discount_amount' => 152.00
]

// Access specific item details
$item = Cart::get('iphone-15');
echo $item->name;                    // "iPhone 15 Pro"
echo $item->price;                   // 999.99
echo $item->quantity;                // 1
echo $item->getPriceSum();           // 999.99 (price × quantity)
echo $item->getAttribute('color');    // "Natural Titanium"
```

---

## 🚀 Real-World Example (Complete Workflow)

Here's a complete e-commerce cart workflow:

```php
use MasyukAI\Cart\Facades\Cart;

class CartController extends Controller 
{
    public function addToCart(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:10',
            'variants' => 'array'
        ]);
        
        $product = Product::find($validated['product_id']);
        
        // Add to cart with product data
        Cart::add(
            id: $product->id,
            name: $product->name,
            price: $product->price,
            quantity: $validated['quantity'],
            attributes: [
                'sku' => $product->sku,
                'image' => $product->image_url,
                'category' => $product->category->name,
                ...$validated['variants'] ?? []
            ]
        );
        
        // Apply user-specific discounts
        if (auth()->user()?->isVip()) {
            Cart::addDiscount('vip-discount', '15%');
        }
        
        // Apply automatic tax
        $taxRate = $this->getTaxRateForUser(auth()->user());
        Cart::addTax('sales-tax', $taxRate . '%');
        
        return response()->json([
            'success' => true,
            'cart' => Cart::content(),
            'message' => 'Product added successfully!'
        ]);
    }
    
    public function getCart()
    {
        return Cart::content();
    }
    
    public function updateQuantity(Request $request, string $itemId)
    {
        $quantity = $request->validate(['quantity' => 'required|integer|min:0'])['quantity'];
        
        if ($quantity === 0) {
            Cart::remove($itemId);
        } else {
            Cart::update($itemId, ['quantity' => $quantity]);
        }
        
        return Cart::content();
    }
    
    public function applyCoupon(Request $request)
    {
        $code = $request->validate(['code' => 'required|string'])['code'];
        
        $coupon = Coupon::where('code', $code)->active()->first();
        
        if (!$coupon) {
            return response()->json(['error' => 'Invalid coupon code'], 422);
        }
        
        Cart::addDiscount($coupon->code, $coupon->value . ($coupon->is_percentage ? '%' : ''));
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'cart' => Cart::content()
        ]);
    }
}
```

---

## 📱 Frontend Integration Examples

### Vue.js/Inertia

```vue
<template>
    <div>
        <!-- Product Grid -->
        <div class="grid grid-cols-3 gap-4">
            <div v-for="product in products" :key="product.id" class="border p-4">
                <h3>{{ product.name }}</h3>
                <p>${{ product.price }}</p>
                <button @click="addToCart(product)" :disabled="loading">
                    Add to Cart
                </button>
            </div>
        </div>
        
        <!-- Cart Summary -->
        <div class="mt-8 border p-4">
            <h3>Cart Summary</h3>
            <p>Items: {{ cart.count }}</p>
            <p>Total: ${{ cart.total }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const cart = ref({count: 0, total: 0});
const loading = ref(false);

const addToCart = (product) => {
    loading.value = true;
    
    router.post('/cart/add', {
        product_id: product.id,
        quantity: 1
    }, {
        onSuccess: (page) => {
            cart.value = page.props.cart;
        },
        onFinish: () => loading.value = false
    });
};
</script>
```

### Alpine.js

```html
<div x-data="cartManager()" x-init="loadCart()">
    <!-- Add to Cart Button -->
    <button @click="addToCart('product-123', 'iPhone 15', 999.99)" 
            :disabled="loading"
            class="bg-blue-500 text-white px-4 py-2 rounded">
        <span x-show="!loading">Add to Cart</span>
        <span x-show="loading">Adding...</span>
    </button>
    
    <!-- Cart Badge -->
    <div class="relative">
        🛒 <span x-text="cart.count" class="badge"></span>
    </div>
    
    <!-- Cart Summary -->
    <div x-show="cart.count > 0" class="mt-4">
        <h3>Cart Summary</h3>
        <div x-html="cartSummary"></div>
    </div>
</div>

<script>
function cartManager() {
    return {
        cart: {count: 0, total: 0, items: []},
        loading: false,
        
        async addToCart(id, name, price) {
            this.loading = true;
            
            try {
                const response = await fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({id, name, price, quantity: 1})
                });
                
                const data = await response.json();
                this.cart = data.cart;
            } finally {
                this.loading = false;
            }
        },
        
        async loadCart() {
            const response = await fetch('/cart');
            this.cart = await response.json();
        },
        
        get cartSummary() {
            return `
                <p>Items: ${this.cart.count}</p>
                <p>Total: $${this.cart.total}</p>
            `;
        }
    }
}
</script>
```

---

## 📚 Next Steps

Now that you have the basics down, explore these advanced features:

### **🏗️ Core Features**
- **[🛒 Cart Operations](cart-operations.md)** - Advanced item management
- **[🏷️ Conditions System](conditions.md)** - Complex discounts and fees
- **[🗄️ Storage Drivers](storage.md)** - Database and cache storage
- **[🔄 Multiple Instances](instances.md)** - Wishlist, comparison carts

### **🎨 Frontend Integration**
- **[💫 Livewire Components](livewire.md)** - Reactive cart UI
- **[🌐 API Endpoints](api-endpoints.md)** - REST API for SPAs
- **[📱 Mobile Integration](mobile-integration.md)** - React Native, Flutter

### **⚙️ Advanced Topics**
- **[⚡ Events & Hooks](events.md)** - Cart lifecycle events
- **[🔒 Security](security.md)** - Validation and sanitization
- **[📈 Performance](performance.md)** - Optimization strategies
- **[🔄 Migration](migration.md)** - Guest to user cart migration

### **📋 Reference**
- **[📖 Complete API Reference](api-reference.md)** - Every method documented
- **[🔧 Configuration](configuration.md)** - All config options
- **[🧪 Testing Guide](testing.md)** - Test your implementation

---

## 🤝 Need Help?

- 📖 **[Full Documentation](../README.md#documentation)**
- 🐛 **[Report Issues](../../issues)**
- 💬 **[Community Discussions](../../discussions)**
- 📧 **Email Support:** support@masyukai.com

**🎉 You're all set!** Start building amazing cart experiences with MasyukAI Cart.

// Add shipping fee
Cart::addFee('shipping', '9.99');

// Get final total
$finalTotal = Cart::total(); // Includes all conditions
```

### Update and Remove Items

```php
// Update quantity
Cart::update('product-1', ['quantity' => 3]);

// Update attributes
Cart::update('laptop-1', [
    'attributes' => ['color' => 'Silver']
]);

// Remove item
Cart::remove('product-1');

// Clear entire cart
Cart::clear();
```

## 4. Multiple Cart Types

```php
// Main shopping cart
$mainCart = Cart::instance('default');
$mainCart->add('product-1', 'Main Product', 99.99);

// Customer wishlist
$wishlist = Cart::instance('wishlist');
$wishlist->add('product-2', 'Dream Item', 199.99);

// Product comparison
$comparison = Cart::instance('comparison');
$comparison->add('product-3', 'Alternative', 89.99);
```

## 5. Using Livewire Components

Add reactive cart components to your Blade templates:

```blade
<!-- Add to cart button -->
<livewire:add-to-cart 
    product-id="123" 
    product-name="iPhone 15" 
    product-price="999.99"
    :product-attributes="['color' => 'blue']"
/>

<!-- Cart summary in header -->
<livewire:cart-summary />

<!-- Full cart page -->
<livewire:cart-table />
```

## 6. Advanced Usage

### Search and Filter

```php
// Find expensive items
$luxuryItems = Cart::search(function($item) {
    return $item->price > 1000;
});

// Find items by attribute
$blueItems = Cart::search(function($item) {
    return $item->getAttribute('color') === 'blue';
});
```

### Complex Conditions

```php
use MasyukAI\Cart\Conditions\CartCondition;

// Bulk discount for large quantities
$bulkDiscount = new CartCondition(
    'bulk-discount',
    'discount', 
    'price',
    '-15%',
    ['min_quantity' => 10]
);

Cart::addItemCondition('product-1', $bulkDiscount);
```

### Event Handling

```php
// Listen to cart events
Event::listen(ItemAdded::class, function ($event) {
    Log::info('Item added to cart', [
        'item_id' => $event->item->id,
        'cart_instance' => $event->cart->instance()
    ]);
});
```

## 7. Testing Your Implementation

```php
// In your tests
use MasyukAI\Cart\Facades\Cart;

public function test_can_add_item_to_cart()
{
    Cart::add('test-product', 'Test Product', 99.99);
    
    $this->assertEquals(1, Cart::count());
    $this->assertEquals(99.99, Cart::total());
    $this->assertTrue(Cart::has('test-product'));
}
```

## Next Steps

Now that you have the basics working:

1. **[Read the Basic Usage Guide](basic-usage.md)** - Learn all cart operations
2. **[Explore the Conditions System](conditions.md)** - Master discounts and taxes  
3. **[Configure Storage](storage.md)** - Choose the right storage driver
4. **[Set Up Events](events.md)** - Hook into cart lifecycle
5. **[Customize Livewire Components](livewire.md)** - Build your perfect UI

## Need Help?

- 📖 **[Complete Documentation](./)**
- 🐛 **[Report Issues](../../issues)**
- 💬 **[Join Discussions](../../discussions)**
- 📧 **Email**: support@masyukai.com

You're all set! Start building amazing cart experiences. 🚀
