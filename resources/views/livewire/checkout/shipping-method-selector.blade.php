<div class="mt-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Shipping Method</h3>
    
    @if (!$shippingRequired)
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            No shipping required for this order (digital products only).
        </p>
    @else
        <div class="mt-4 space-y-4">
            @foreach($methods as $method)
                <div class="flex items-center">
                    <input
                        id="shipping-{{ $method['id'] }}"
                        name="shipping-method"
                        type="radio"
                        wire:model.live="selectedMethod"
                        value="{{ $method['id'] }}"
                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    <label for="shipping-{{ $method['id'] }}" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        <span class="font-medium">{{ $method['name'] }}</span>
                        <span class="ml-1 text-gray-500 dark:text-gray-400">
                            ({{ $method['description'] }})
                        </span>
                        @if($method['price'] > 0)
                            <span class="ml-1 text-gray-900 dark:text-gray-100">
                                RM{{ number_format($method['price'] / 100, 2) }}
                            </span>
                        @else
                            <span class="ml-1 text-green-600 dark:text-green-400">Free</span>
                        @endif
                    </label>
                </div>
            @endforeach
        </div>
    @endif
</div>
