<div class="cart-summary bg-white rounded-lg shadow-sm p-4 border">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Cart Summary</h3>
        
        @if(!$this->isEmpty)
            <button 
                wire:click="toggleDetails"
                class="text-sm text-blue-600 hover:text-blue-700 transition-colors"
            >
                {{ $showDetails ? 'Hide Details' : 'Show Details' }}
            </button>
        @endif
    </div>

    @if($this->isEmpty)
        <div class="mt-4 text-center py-8">
            <div class="text-gray-400 mb-2">
                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6H19M7 13l-1.5 6m4.5-6h6"></path>
                </svg>
            </div>
            <p class="text-gray-500">Your cart is empty</p>
        </div>
    @else
        <div class="mt-4 space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Items</span>
                <span class="font-medium">{{ $this->itemCount }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Quantity</span>
                <span class="font-medium">{{ $this->totalQuantity }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Subtotal</span>
                <span class="font-medium">${{ number_format($this->subtotal, 2) }}</span>
            </div>
            
            @if($this->shippingValue !== null)
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Shipping</span>
                <span class="font-medium">
                    @if($this->shippingValue > 0)
                        ${{ number_format($this->shippingValue, 2) }}
                    @else
                        <span class="text-green-600">Free</span>
                    @endif
                </span>
            </div>
            @endif
            
            <div class="border-t pt-3">
                <div class="flex justify-between items-center">
                    <span class="text-base font-semibold text-gray-800">Total</span>
                    <span class="text-lg font-bold text-gray-900">${{ number_format($this->total, 2) }}</span>
                </div>
            </div>

            @if($showDetails)
                <div class="mt-4 pt-4 border-t">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Cart Details</h4>
                    <div class="space-y-2 text-xs text-gray-600">
                        @foreach($this->cart['items'] as $item)
                            <div class="flex justify-between">
                                <span class="truncate">{{ $item['name'] }} (x{{ $item['quantity'] }})</span>
                                <span>${{ number_format($item['price_sum_with_conditions'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if(session()->has('cart.message'))
        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
            <p class="text-sm text-green-700">{{ session('cart.message') }}</p>
        </div>
    @endif

    @if(session()->has('cart.error'))
        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-700">{{ session('cart.error') }}</p>
        </div>
    @endif
</div>
