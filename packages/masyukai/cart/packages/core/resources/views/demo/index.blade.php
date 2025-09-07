@extends('cart::demo.layout')

@section('title', 'Shop - MasyukAI Cart Demo')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="cartDemo()">
    <!-- Products Section -->
    <div class="lg:col-span-2">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Products</h2>
            <p class="text-gray-600">Add products to your cart and see the cart functionality in action.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($products as $product)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200" data-product-id="{{ $product['id'] }}">
                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-full h-48 object-cover">
                
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $product['name'] }}</h3>
                    <p class="text-gray-600 text-sm mb-4">{{ $product['description'] }}</p>
                    <div class="text-2xl font-bold text-blue-600 mb-4">${{ number_format($product['price'], 2) }}</div>
                    
                    <!-- Product attributes -->
                    @if(count($product['attributes']) > 0)
                    <div class="space-y-3 mb-4">
                        @foreach($product['attributes'] as $attributeName => $attributeValues)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ ucfirst($attributeName) }}</label>
                            <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" 
                                    data-attribute="{{ $attributeName }}">
                                <option value="">Select {{ $attributeName }}</option>
                                @foreach($attributeValues as $value)
                                <option value="{{ $value }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    
                    <!-- Quantity selector -->
                    <div class="flex items-center space-x-4 mb-4">
                        <label class="text-sm font-medium text-gray-700">Quantity:</label>
                        <div class="flex items-center space-x-2">
                            <button @click="decrementQuantity($event)" 
                                    class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <input type="number" min="1" value="1" 
                                   class="w-16 text-center border border-gray-300 rounded-md py-1 text-sm quantity-input">
                            <button @click="incrementQuantity($event)" 
                                    class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Add to cart button -->
                    <button @click="addToCart($event)" 
                            data-id="{{ $product['id'] }}" 
                            data-name="{{ $product['name'] }}" 
                            data-price="{{ $product['price'] }}"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                        Add to Cart
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Cart Sidebar -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Shopping Cart</h3>
                @if($cartCount > 0)
                <button @click="clearCart()" class="text-red-600 hover:text-red-700 text-sm font-medium">
                    Clear Cart
                </button>
                @endif
            </div>
            
            @if($cartCount > 0)
                <!-- Cart items -->
                <div class="space-y-4 mb-6" id="cart-items">
                    @foreach($cartItems as $item)
                    <div class="border-b border-gray-200 pb-4" data-item-id="{{ $item->id }}">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-medium text-gray-900 text-sm">{{ $item->name }}</h4>
                            <button @click="removeItem('{{ $item->id }}')" 
                                    class="text-red-600 hover:text-red-700 ml-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        
                        @if($item->attributes->isNotEmpty())
                        <div class="text-xs text-gray-500 mb-2">
                            @foreach($item->attributes as $key => $value)
                                {{ ucfirst($key) }}: {{ $value }}@if(!$loop->last), @endif
                            @endforeach
                        </div>
                        @endif
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <button @click="updateQuantity('{{ $item->id }}', {{ $item->quantity - 1 }})" 
                                        class="w-6 h-6 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 text-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg>
                                </button>
                                <span class="text-sm font-medium">{{ $item->quantity }}</span>
                                <button @click="updateQuantity('{{ $item->id }}', {{ $item->quantity + 1 }})" 
                                        class="w-6 h-6 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 text-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </button>
                            </div>
                            <span class="text-sm font-medium text-gray-900">${{ number_format($item->price * $item->quantity, 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Cart conditions -->
                @if($cartConditions->isNotEmpty())
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Applied Conditions</h4>
                    <div class="space-y-2">
                        @foreach($cartConditions as $condition)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">{{ $condition->getName() }}</span>
                            <div class="flex items-center space-x-2">
                                <span class="text-{{ $condition->getType() === 'discount' ? 'green' : 'red' }}-600">
                                    {{ $condition->getType() === 'discount' ? '-' : '+' }}{{ $condition->getValue() }}
                                </span>
                                <button @click="removeCondition('{{ $condition->getName() }}')" 
                                        class="text-red-600 hover:text-red-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Cart totals -->
                <div class="border-t border-gray-200 pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-medium">${{ number_format($cartSubtotal, 2) }}</span>
                    </div>
                    @if($cartTotal != $cartSubtotal)
                    <div class="flex justify-between text-lg font-semibold">
                        <span class="text-gray-900">Total:</span>
                        <span class="text-gray-900">${{ number_format($cartTotal, 2) }}</span>
                    </div>
                    @endif
                </div>
                
                <!-- Checkout button -->
                <button class="w-full mt-6 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-md transition-colors duration-200">
                    Checkout
                </button>
            @else
                <!-- Empty cart -->
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Your cart is empty</h3>
                    <p class="mt-1 text-sm text-gray-500">Start adding some products to your cart.</p>
                </div>
            @endif
        </div>
        
        <!-- Cart conditions panel -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cart Conditions</h3>
            
            <div class="space-y-4">
                <!-- Quick condition buttons -->
                <div class="grid grid-cols-2 gap-2">
                    <button @click="applyCondition('10% Discount', 'discount', '-10%')" 
                            class="bg-green-100 hover:bg-green-200 text-green-800 text-xs font-medium py-2 px-3 rounded">
                        10% OFF
                    </button>
                    <button @click="applyCondition('$5 Discount', 'discount', '-5')" 
                            class="bg-green-100 hover:bg-green-200 text-green-800 text-xs font-medium py-2 px-3 rounded">
                        $5 OFF
                    </button>
                    <button @click="applyCondition('Tax 8.5%', 'charge', '+8.5%')" 
                            class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-medium py-2 px-3 rounded">
                        Tax 8.5%
                    </button>
                    <button @click="applyCondition('Shipping', 'charge', '+9.99')" 
                            class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-medium py-2 px-3 rounded">
                        Shipping $9.99
                    </button>
                </div>
                
                <!-- Custom condition form -->
                <div class="border-t border-gray-200 pt-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Custom Condition</h4>
                    <form @submit.prevent="applyCustomCondition($event)" class="space-y-3">
                        <input type="text" placeholder="Condition name" name="name" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <select name="type" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">Select type</option>
                            <option value="discount">Discount</option>
                            <option value="charge">Charge</option>
                        </select>
                        <input type="text" placeholder="Value (e.g., -10%, +5)" name="value" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-md">
                            Apply Condition
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function cartDemo() {
    return {
        addToCart(event) {
            const button = event.target;
            const productCard = button.closest('[data-product-id]');
            const id = button.dataset.id;
            const name = button.dataset.name;
            const price = parseFloat(button.dataset.price);
            const quantity = parseInt(productCard.querySelector('.quantity-input').value);
            
            // Get selected attributes
            const attributes = {};
            const attributeSelects = productCard.querySelectorAll('[data-attribute]');
            attributeSelects.forEach(select => {
                if (select.value) {
                    attributes[select.dataset.attribute] = select.value;
                }
            });
            
            CartDemo.makeRequest('/cart-demo/add', {
                method: 'POST',
                body: JSON.stringify({
                    id, name, price, quantity, attributes
                })
            }).then((data) => {
                // Reset form
                productCard.querySelector('.quantity-input').value = 1;
                attributeSelects.forEach(select => select.value = '');
                
                // Instead of reloading, just update cart count and redirect after delay
                if (data.success) {
                    setTimeout(() => window.location.reload(), 500);
                }
            });
        },
        
        updateQuantity(itemId, newQuantity) {
            if (newQuantity < 0) return;
            
            CartDemo.makeRequest('/cart-demo/update', {
                method: 'PATCH',
                body: JSON.stringify({
                    id: itemId,
                    quantity: newQuantity
                })
            }).then((data) => {
                if (data.success) {
                    setTimeout(() => window.location.reload(), 500);
                }
            });
        },
        
        removeItem(itemId) {
            CartDemo.makeRequest('/cart-demo/remove', {
                method: 'DELETE',
                body: JSON.stringify({ id: itemId })
            }).then((data) => {
                if (data.success) {
                    setTimeout(() => window.location.reload(), 500);
                }
            });
        },
        
        clearCart() {
            if (confirm('Are you sure you want to clear the cart?')) {
                CartDemo.makeRequest('/cart-demo/clear', {
                    method: 'DELETE'
                }).then((data) => {
                    if (data.success) {
                        setTimeout(() => window.location.reload(), 500);
                    }
                });
            }
        },
        
        applyCondition(name, type, value) {
            CartDemo.makeRequest('/cart-demo/condition/apply', {
                method: 'POST',
                body: JSON.stringify({ name, type, value })
            }).then((data) => {
                if (data.success) {
                    setTimeout(() => window.location.reload(), 500);
                }
            });
        },
        
        applyCustomCondition(event) {
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);
            
            CartDemo.makeRequest('/cart-demo/condition/apply', {
                method: 'POST',
                body: JSON.stringify(data)
            }).then((response) => {
                if (response.success) {
                    event.target.reset();
                    setTimeout(() => window.location.reload(), 500);
                }
            });
        },
        
        removeCondition(name) {
            CartDemo.makeRequest('/cart-demo/condition/remove', {
                method: 'DELETE',
                body: JSON.stringify({ name })
            }).then((data) => {
                if (data.success) {
                    setTimeout(() => window.location.reload(), 500);
                }
            });
        },
        
        incrementQuantity(event) {
            const input = event.target.closest('div').querySelector('.quantity-input');
            input.value = parseInt(input.value) + 1;
        },
        
        decrementQuantity(event) {
            const input = event.target.closest('div').querySelector('.quantity-input');
            const current = parseInt(input.value);
            if (current > 1) {
                input.value = current - 1;
            }
        }
    }
}
</script>
@endsection
