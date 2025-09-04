<div>
    @if($orderProcessed && $order)
        <!-- Success State -->
        <div class="text-center py-12">
            <div class="mx-auto mb-6 w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Order Confirmed!</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Thank you for your purchase. Your order has been successfully placed.</p>
            
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-6 inline-block">
                <p class="text-sm text-gray-500 dark:text-gray-400">Order Number</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $orderNumber }}</p>
            </div>
        </div>

        <!-- Order Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Delivery Information -->
            <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delivery Information</h3>
                
                @if(isset($checkoutData['payment']['delivery']))
                    @php $delivery = $checkoutData['payment']['delivery']; @endphp
                    <div class="space-y-2">
                        <p class="text-sm">
                            <span class="font-medium text-gray-900 dark:text-white">Name:</span>
                            <span class="text-gray-600 dark:text-gray-400">{{ $delivery['name'] ?? 'N/A' }}</span>
                        </p>
                        <p class="text-sm">
                            <span class="font-medium text-gray-900 dark:text-white">Email:</span>
                            <span class="text-gray-600 dark:text-gray-400">{{ $delivery['email'] ?? 'N/A' }}</span>
                        </p>
                        <p class="text-sm">
                            <span class="font-medium text-gray-900 dark:text-white">Phone:</span>
                            <span class="text-gray-600 dark:text-gray-400">{{ $delivery['phone'] ?? 'N/A' }}</span>
                        </p>
                        <p class="text-sm">
                            <span class="font-medium text-gray-900 dark:text-white">Location:</span>
                            <span class="text-gray-600 dark:text-gray-400">{{ $delivery['city'] ?? 'N/A' }}, {{ $delivery['country'] ?? 'N/A' }}</span>
                        </p>
                        @if(!empty($delivery['company']))
                            <p class="text-sm">
                                <span class="font-medium text-gray-900 dark:text-white">Company:</span>
                                <span class="text-gray-600 dark:text-gray-400">{{ $delivery['company'] }}</span>
                            </p>
                        @endif
                    </div>
                @endif
                
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                    <p class="text-sm">
                        <span class="font-medium text-gray-900 dark:text-white">Delivery Method:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $this->getDeliveryMethodName() }}</span>
                    </p>
                    <p class="text-sm">
                        <span class="font-medium text-gray-900 dark:text-white">Payment Method:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $this->getPaymentMethodName() }}</span>
                    </p>
                </div>
            </div>

            <!-- Order Items -->
            <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Order Items</h3>
                
                @if(isset($checkoutData['cart']['items']))
                    <div class="space-y-3">
                        @foreach($checkoutData['cart']['items'] as $item)
                            <div class="flex items-center space-x-3 py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                <img class="h-12 w-12 rounded-lg object-cover" src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item['name'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Qty: {{ $item['quantity'] }}</p>
                                </div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $this->formatPrice($item['price'] * $item['quantity']) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Order Summary -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 max-w-md mx-auto">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Summary</h3>
            
            <div class="flow-root">
                <div class="-my-3 divide-y divide-gray-200 dark:divide-gray-800">
                    <dl class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Subtotal</dt>
                        <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $this->formatPrice($this->getSubtotal()) }}</dd>
                    </dl>

                    @if($this->getSavings() > 0)
                        <dl class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Savings</dt>
                            <dd class="text-base font-medium text-green-500">-{{ $this->formatPrice($this->getSavings()) }}</dd>
                        </dl>
                    @endif

                    <dl class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Delivery</dt>
                        <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $this->formatPrice($this->getDeliveryFee()) }}</dd>
                    </dl>

                    @if($this->getPaymentFee() > 0)
                        <dl class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Payment Fee</dt>
                            <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $this->formatPrice($this->getPaymentFee()) }}</dd>
                        </dl>
                    @endif

                    @if($this->getTax() > 0)
                    <dl class="flex items-center justify-between gap-4 py-3">
                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Tax</dt>
                        <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $this->formatPrice($this->getTax()) }}</dd>
                    </dl>
                    @endif

                    <dl class="flex items-center justify-between gap-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        <dt class="text-base font-bold text-gray-900 dark:text-white">Total</dt>
                        <dd class="text-base font-bold text-gray-900 dark:text-white">{{ $this->formatPrice($this->getTotal()) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
            <button 
                wire:click="startNewOrder"
                class="flex items-center justify-center rounded-lg bg-primary-700 px-6 py-3 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                Continue Shopping
            </button>
            
            <a href="#" class="flex items-center justify-center rounded-lg border border-gray-200 bg-white px-6 py-3 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                View Order Details
            </a>
        </div>

        <!-- Additional Information -->
        <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">What happens next?</h4>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <ul class="list-disc list-inside space-y-1">
                            <li>You'll receive an order confirmation email shortly</li>
                            <li>We'll send tracking information when your order ships</li>
                            <li>Estimated delivery: 1-3 business days</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Error State -->
        <div class="text-center py-12">
            <div class="mx-auto mb-6 w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Something went wrong</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">We couldn't process your order. Please try again.</p>
            
            <button 
                wire:click="goBack"
                class="flex items-center justify-center rounded-lg bg-primary-700 px-6 py-3 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 mx-auto">
                ← Back to Payment
            </button>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session('error'))
        <div class="mt-4 rounded-lg bg-red-50 p-4 text-red-800 dark:bg-red-800/20 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif
</div>
