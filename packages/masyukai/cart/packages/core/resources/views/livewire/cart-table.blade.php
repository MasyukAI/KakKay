<div class="cart-table">
    @if($this->isEmpty)
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6H19M7 13l-1.5 6m4.5-6h6"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-700 mb-2">Your cart is empty</h3>
            <p class="text-gray-500">Start shopping to add items to your cart</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Shopping Cart</h2>
                <div class="flex items-center space-x-4">
                    <button 
                        wire:click="toggleConditions"
                        class="text-sm px-3 py-1 rounded-md {{ $showConditions ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }} transition-colors"
                    >
                        {{ $showConditions ? 'Hide Discounts' : 'Show Discounts' }}
                    </button>
                    <button 
                        wire:click="clearCart"
                        wire:confirm="Are you sure you want to clear the cart?"
                        class="text-sm px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors"
                    >
                        Clear Cart
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($this->items as $item)
                            <tr wire:key="cart-item-{{ $item->id }}">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                        @if($item->attributes->isNotEmpty())
                                            <div class="text-xs text-gray-500 mt-1">
                                                @foreach($item->attributes as $key => $value)
                                                    <span class="inline-block bg-gray-100 rounded-full px-2 py-1 mr-1 mb-1">
                                                        {{ ucfirst($key) }}: {{ $value }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($item->hasConditions() && $showConditions)
                                            <div class="text-xs text-green-600 mt-1">
                                                @foreach($item->getConditions() as $condition)
                                                    <span class="inline-block bg-green-50 border border-green-200 rounded px-2 py-1 mr-1">
                                                        {{ $condition->getName() }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        @if($showConditions && $this->getItemPrice($item) !== $item->price)
                                            <div class="text-gray-500 line-through">${{ number_format($item->price, 2) }}</div>
                                            <div class="text-green-600 font-medium">${{ number_format($this->getItemPrice($item), 2) }}</div>
                                        @else
                                            <div class="text-gray-900">${{ number_format($this->getItemPrice($item), 2) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button 
                                            wire:click="decreaseQuantity('{{ $item->id }}')"
                                            class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </button>
                                        
                                        <input 
                                            type="number" 
                                            value="{{ $item->quantity }}"
                                            wire:change="updateQuantity('{{ $item->id }}', $event.target.value)"
                                            class="w-16 text-center border border-gray-300 rounded-md py-1 text-sm"
                                            min="0"
                                        >
                                        
                                        <button 
                                            wire:click="increaseQuantity('{{ $item->id }}')"
                                            class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        ${{ number_format($this->getItemTotal($item), 2) }}
                                    </div>
                                    @if($item->getDiscountAmount() > 0 && $showConditions)
                                        <div class="text-xs text-green-600">
                                            Saved: ${{ number_format($item->getDiscountAmount(), 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <button 
                                        wire:click="removeItem('{{ $item->id }}')"
                                        wire:confirm="Remove {{ $item->name }} from cart?"
                                        class="text-red-600 hover:text-red-700 transition-colors"
                                        title="Remove item"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(session()->has('cart.message'))
        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-md">
            <p class="text-sm text-green-700">{{ session('cart.message') }}</p>
        </div>
    @endif

    @if(session()->has('cart.error'))
        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-700">{{ session('cart.error') }}</p>
        </div>
    @endif
</div>
