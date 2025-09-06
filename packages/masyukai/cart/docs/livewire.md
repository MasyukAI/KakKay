# Livewire Integration

The MasyukAI Cart package is designed to work seamlessly with Livewire 3 and Livewire Volt, providing reactive cart functionality for modern Laravel applications.

## Basic Livewire Component

### Creating a Cart Component

```bash
php artisan make:livewire CartComponent
```

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Conditions\CartCondition;

class CartComponent extends Component
{
    public $cartItems = [];
    public $cartTotal = 0;
    public $cartCount = 0;
    public $couponCode = '';
    public $showCart = false;

    protected $listeners = ['cartUpdated' => 'refreshCart'];

    public function mount()
    {
        $this->refreshCart();
    }

    public function addToCart($productId, $name, $price, $quantity = 1, $attributes = [])
    {
        Cart::add($productId, $name, $price, $quantity, $attributes);
        
        $this->refreshCart();
        $this->dispatch('cartUpdated');
        
        session()->flash('message', 'Item added to cart!');
    }

    public function updateQuantity($itemId, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeItem($itemId);
            return;
        }

        Cart::update($itemId, ['quantity' => $quantity]);
        $this->refreshCart();
        $this->dispatch('cartUpdated');
    }

    public function removeItem($itemId)
    {
        Cart::remove($itemId);
        $this->refreshCart();
        $this->dispatch('cartUpdated');
        
        session()->flash('message', 'Item removed from cart!');
    }

    public function applyCoupon()
    {
        if (empty($this->couponCode)) {
            session()->flash('error', 'Please enter a coupon code.');
            return;
        }

        // Validate coupon (implement your logic)
        $discount = $this->validateCoupon($this->couponCode);
        
        if ($discount) {
            $condition = new CartCondition(
                "coupon-{$this->couponCode}",
                'discount',
                'subtotal',
                "-{$discount}%"
            );
            
            Cart::addCondition($condition);
            $this->refreshCart();
            $this->couponCode = '';
            
            session()->flash('message', 'Coupon applied successfully!');
        } else {
            session()->flash('error', 'Invalid coupon code.');
        }
    }

    public function clearCart()
    {
        Cart::clear();
        $this->refreshCart();
        $this->dispatch('cartUpdated');
        
        session()->flash('message', 'Cart cleared!');
    }

    public function refreshCart()
    {
        $this->cartItems = Cart::getItems()->toArray();
        $this->cartTotal = Cart::getTotal();
        $this->cartCount = Cart::count();
    }

    private function validateCoupon($code)
    {
        // Implement your coupon validation logic
        $validCoupons = [
            'SAVE10' => 10,
            'SAVE20' => 20,
        ];

        return $validCoupons[$code] ?? false;
    }

    public function render()
    {
        return view('livewire.cart-component');
    }
}
```

### Cart Component Blade Template

```blade
{{-- resources/views/livewire/cart-component.blade.php --}}
<div>
    {{-- Cart Toggle Button --}}
    <button 
        wire:click="$toggle('showCart')" 
        class="relative p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h10" />
        </svg>
        @if($cartCount > 0)
            <span class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs px-1">
                {{ $cartCount }}
            </span>
        @endif
    </button>

    {{-- Cart Dropdown --}}
    @if($showCart)
        <div class="absolute right-0 mt-2 w-80 bg-white border rounded-lg shadow-lg z-50">
            <div class="p-4">
                <h3 class="text-lg font-semibold mb-4">Shopping Cart</h3>
                
                {{-- Flash Messages --}}
                @if(session()->has('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('message') }}
                    </div>
                @endif

                @if(session()->has('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Cart Items --}}
                @if(count($cartItems) > 0)
                    <div class="space-y-4 mb-4">
                        @foreach($cartItems as $item)
                            <div class="flex items-center justify-between border-b pb-2">
                                <div class="flex-1">
                                    <h4 class="font-medium">{{ $item['name'] }}</h4>
                                    <p class="text-sm text-gray-600">${{ number_format($item['price'], 2) }}</p>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <input 
                                        type="number" 
                                        wire:change="updateQuantity('{{ $item['id'] }}', $event.target.value)"
                                        value="{{ $item['quantity'] }}" 
                                        min="0"
                                        class="w-16 px-2 py-1 border rounded text-center"
                                    >
                                    
                                    <button 
                                        wire:click="removeItem('{{ $item['id'] }}')"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Coupon Code --}}
                    <div class="mb-4">
                        <div class="flex space-x-2">
                            <input 
                                type="text" 
                                wire:model="couponCode" 
                                placeholder="Coupon code"
                                class="flex-1 px-3 py-2 border rounded-lg"
                            >
                            <button 
                                wire:click="applyCoupon"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                            >
                                Apply
                            </button>
                        </div>
                    </div>

                    {{-- Cart Total --}}
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center text-lg font-semibold">
                            <span>Total:</span>
                            <span>${{ number_format($cartTotal, 2) }}</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-4 space-y-2">
                        <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                            Checkout
                        </button>
                        <button 
                            wire:click="clearCart"
                            class="w-full bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400"
                        >
                            Clear Cart
                        </button>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Your cart is empty</p>
                @endif
            </div>
        </div>
    @endif
</div>
```

## Livewire Volt Integration

Using Livewire Volt for more compact components:

### Product Add to Cart (Volt)

```php
<?php
// resources/views/livewire/add-to-cart-button.blade.php

use MasyukAI\Cart\Facades\Cart;
use Livewire\Volt\Component;

new class extends Component {
    public $productId;
    public $productName;
    public $productPrice;
    public $quantity = 1;
    public $adding = false;

    public function addToCart()
    {
        $this->adding = true;
        
        Cart::add(
            $this->productId,
            $this->productName,
            $this->productPrice,
            $this->quantity
        );
        
        $this->dispatch('cartUpdated');
        $this->dispatch('item-added', $this->productName);
        
        $this->adding = false;
    }
    
    public function increment()
    {
        $this->quantity++;
    }
    
    public function decrement()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }
} ?>

<div class="flex items-center space-x-4">
    {{-- Quantity Controls --}}
    <div class="flex items-center space-x-2">
        <button 
            wire:click="decrement"
            class="w-8 h-8 flex items-center justify-center border rounded-lg hover:bg-gray-100"
        >
            -
        </button>
        
        <span class="w-12 text-center">{{ $quantity }}</span>
        
        <button 
            wire:click="increment"
            class="w-8 h-8 flex items-center justify-center border rounded-lg hover:bg-gray-100"
        >
            +
        </button>
    </div>

    {{-- Add to Cart Button --}}
    <button 
        wire:click="addToCart"
        wire:loading.attr="disabled"
        wire:target="addToCart"
        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
    >
        <span wire:loading.remove wire:target="addToCart">
            Add to Cart
        </span>
        <span wire:loading wire:target="addToCart">
            Adding...
        </span>
    </button>
</div>
```

### Cart Mini Widget (Volt)

```php
<?php
// resources/views/livewire/cart-mini-widget.blade.php

use MasyukAI\Cart\Facades\Cart;
use Livewire\Volt\Component;

new class extends Component {
    public $cartCount = 0;
    public $cartTotal = 0;

    protected $listeners = ['cartUpdated' => 'refreshCart'];

    public function mount()
    {
        $this->refreshCart();
    }

    public function refreshCart()
    {
        $this->cartCount = Cart::count();
        $this->cartTotal = Cart::getTotal();
    }
} ?>

<div class="flex items-center space-x-4">
    <div class="relative">
        <a href="/cart" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h10" />
            </svg>
            
            @if($cartCount > 0)
                <span class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs px-2 py-1 min-w-6 text-center">
                    {{ $cartCount }}
                </span>
            @endif
        </a>
    </div>
    
    @if($cartTotal > 0)
        <span class="text-sm font-medium text-gray-900">
            ${{ number_format($cartTotal, 2) }}
        </span>
    @endif
</div>
```

## Real-time Cart Updates

### Global Cart Event Listener

```javascript
// resources/js/cart.js

document.addEventListener('livewire:init', () => {
    Livewire.on('cartUpdated', () => {
        // Update any non-Livewire cart displays
        updateCartBadge();
        
        // Show notification
        showNotification('Cart updated!');
    });
    
    Livewire.on('item-added', (itemName) => {
        showNotification(`${itemName} added to cart!`);
    });
});

function updateCartBadge() {
    // Update cart count in navigation, etc.
    fetch('/api/cart/count')
        .then(response => response.json())
        .then(data => {
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = data.count;
            });
        });
}

function showNotification(message) {
    // Show toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
```

## Advanced Cart Features

### Cart with Bulk Actions

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use MasyukAI\Cart\Facades\Cart;

class CartBulkActions extends Component
{
    public $selectedItems = [];
    public $selectAll = false;

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedItems = Cart::getItems()->pluck('id')->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    public function removeSelected()
    {
        foreach ($this->selectedItems as $itemId) {
            Cart::remove($itemId);
        }
        
        $this->selectedItems = [];
        $this->selectAll = false;
        $this->dispatch('cartUpdated');
        
        session()->flash('message', 'Selected items removed!');
    }

    public function moveToWishlist()
    {
        $wishlist = Cart::instance('wishlist');
        
        foreach ($this->selectedItems as $itemId) {
            $item = Cart::get($itemId);
            if ($item) {
                $wishlist->add(
                    $item->id,
                    $item->name,
                    $item->price,
                    $item->quantity,
                    $item->attributes->toArray()
                );
                Cart::remove($itemId);
            }
        }
        
        $this->selectedItems = [];
        $this->selectAll = false;
        $this->dispatch('cartUpdated');
        
        session()->flash('message', 'Items moved to wishlist!');
    }

    public function render()
    {
        return view('livewire.cart-bulk-actions', [
            'cartItems' => Cart::getItems()
        ]);
    }
}
```

### Product Recommendations

```php
<?php
// resources/views/livewire/cart-recommendations.blade.php

use App\Models\Product;
use MasyukAI\Cart\Facades\Cart;
use Livewire\Volt\Component;

new class extends Component {
    public $recommendations = [];

    protected $listeners = ['cartUpdated' => 'loadRecommendations'];

    public function mount()
    {
        $this->loadRecommendations();
    }

    public function loadRecommendations()
    {
        $cartItems = Cart::getItems();
        $productIds = $cartItems->pluck('id')->toArray();
        
        // Load related products (implement your logic)
        $this->recommendations = Product::whereNotIn('id', $productIds)
            ->inRandomOrder()
            ->limit(4)
            ->get()
            ->toArray();
    }

    public function addRecommendation($productId, $name, $price)
    {
        Cart::add($productId, $name, $price, 1);
        $this->dispatch('cartUpdated');
        $this->loadRecommendations();
    }
} ?>

<div class="mt-8">
    <h3 class="text-lg font-semibold mb-4">You might also like</h3>
    
    @if(count($recommendations) > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($recommendations as $product)
                <div class="border rounded-lg p-4">
                    <img src="{{ $product['image'] ?? '/placeholder.jpg' }}" 
                         alt="{{ $product['name'] }}" 
                         class="w-full h-32 object-cover mb-2">
                    
                    <h4 class="font-medium text-sm mb-1">{{ $product['name'] }}</h4>
                    <p class="text-lg font-bold text-blue-600 mb-2">
                        ${{ number_format($product['price'], 2) }}
                    </p>
                    
                    <button 
                        wire:click="addRecommendation('{{ $product['id'] }}', '{{ $product['name'] }}', {{ $product['price'] }})"
                        class="w-full bg-blue-600 text-white py-1 px-2 rounded text-sm hover:bg-blue-700"
                    >
                        Add to Cart
                    </button>
                </div>
            @endforeach
        </div>
    @endif
</div>
```

## Cart Persistence with User Authentication

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use MasyukAI\Cart\Facades\Cart;
use Illuminate\Support\Facades\Auth;

class AuthenticatedCart extends Component
{
    protected $listeners = ['auth-login' => 'handleLogin', 'auth-logout' => 'handleLogout'];

    public function handleLogin()
    {
        // Merge guest cart with user cart
        $guestCart = Cart::instance('guest');
        $userCart = Cart::instance('user_' . Auth::id());
        
        if (!$guestCart->isEmpty()) {
            foreach ($guestCart->getItems() as $item) {
                $userCart->add(
                    $item->id,
                    $item->name,
                    $item->price,
                    $item->quantity,
                    $item->attributes->toArray(),
                    $item->conditions->toArray()
                );
            }
            
            $guestCart->clear();
        }
        
        $this->dispatch('cartUpdated');
    }

    public function handleLogout()
    {
        // Clear user-specific cart
        $userCart = Cart::instance('user_' . Auth::id());
        $userCart->clear();
        
        // Switch to guest cart
        Cart::instance('guest');
        
        $this->dispatch('cartUpdated');
    }

    public function render()
    {
        return view('livewire.authenticated-cart');
    }
}
```

## Testing Livewire Cart Components

```php
use Livewire\Livewire;
use App\Livewire\CartComponent;
use MasyukAI\Cart\Facades\Cart;

it('can add items via livewire component', function () {
    Livewire::test(CartComponent::class)
        ->call('addToCart', 'product-1', 'Test Product', 99.99, 2)
        ->assertSet('cartCount', 1)
        ->assertSee('Item added to cart!');
    
    expect(Cart::get('product-1'))->not->toBeNull()
        ->and(Cart::get('product-1')->quantity)->toBe(2);
});

it('can update quantities via livewire component', function () {
    Cart::add('product-1', 'Test Product', 99.99, 1);
    
    Livewire::test(CartComponent::class)
        ->call('updateQuantity', 'product-1', 3)
        ->assertSet('cartTotal', 299.97);
    
    expect(Cart::get('product-1')->quantity)->toBe(3);
});

it('dispatches cart updated events', function () {
    Livewire::test(CartComponent::class)
        ->call('addToCart', 'product-1', 'Test Product', 99.99)
        ->assertDispatched('cartUpdated');
});
```

## Best Practices

1. **Use Events**: Dispatch events for cart updates to keep components in sync
2. **Loading States**: Show loading indicators during cart operations
3. **Optimistic Updates**: Update UI immediately, revert on failure
4. **Real-time Sync**: Keep multiple cart components synchronized
5. **Performance**: Minimize database queries in cart operations
6. **Error Handling**: Gracefully handle cart operation failures
7. **User Experience**: Provide clear feedback for all cart actions

## Next Steps

- Explore [Events](events.md) for advanced cart synchronization
- Learn about [Storage](storage.md) for cart persistence strategies
- Check out [Testing](testing.md) for Livewire component testing
