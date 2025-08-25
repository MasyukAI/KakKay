<div>
    <div class="mt-6 sm:mt-8 md:gap-6 lg:flex lg:items-start xl:gap-8">
        <div class="mx-auto w-full flex-none lg:max-w-2xl xl:max-w-4xl">
            @if(empty($cartItems))
                <div class="text-center py-12">
                    <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Your cart is empty</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Start shopping to add items to your cart.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($cartItems as $item)
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 md:p-6" wire:key="cart-item-{{ $item['id'] }}">
                            <div class="space-y-4 md:flex md:items-center md:justify-between md:gap-6 md:space-y-0">
                                <a href="#" class="shrink-0 md:order-1">
                                    <img class="h-20 w-20 dark:hidden" src="{{ $item['image'] }}" alt="{{ $item['name'] }}" />
                                    <img class="hidden h-20 w-20 dark:block" src="{{ $item['image_dark'] }}" alt="{{ $item['name'] }}" />
                                </a>

                                <div class="flex items-center justify-between md:order-3 md:justify-end">
                                    <div class="flex items-center" x-data="{ quantity: {{ $item['quantity'] }} }">
                                        <button 
                                            type="button" 
                                            @click="quantity = Math.max(1, quantity - 1); $wire.updateQuantity({{ $item['id'] }}, quantity)"
                                            class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                                            <svg class="h-2.5 w-2.5 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16" />
                                            </svg>
                                        </button>
                                        <input 
                                            type="text" 
                                            x-model="quantity"
                                            @change="$wire.updateQuantity({{ $item['id'] }}, parseInt($event.target.value) || 1)"
                                            class="w-10 shrink-0 border-0 bg-transparent text-center text-sm font-medium text-gray-900 focus:outline-none focus:ring-0 dark:text-white" 
                                            min="1" 
                                        />
                                        <button 
                                            type="button" 
                                            @click="quantity = quantity + 1; $wire.updateQuantity({{ $item['id'] }}, quantity)"
                                            class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                                            <svg class="h-2.5 w-2.5 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="text-end md:order-4 md:w-32">
                                        <p class="text-base font-bold text-gray-900 dark:text-white">{{ $this->formatPrice($item['price'] * $item['quantity']) }}</p>
                                    </div>
                                </div>

                                <div class="w-full min-w-0 flex-1 space-y-4 md:order-2 md:max-w-md">
                                    <a href="#" class="text-base font-medium text-gray-900 hover:underline dark:text-white">{{ $item['name'] }}</a>

                                    <div class="flex items-center gap-4">
                                        <button type="button" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 hover:underline dark:text-gray-400 dark:hover:text-white">
                                            <svg class="me-1.5 h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.01 6.001C6.5 1 1 8 5.782 13.001L12.011 20l6.23-7C23 8 17.5 1 12.01 6.002Z" />
                                            </svg>
                                            Add to Favorites
                                        </button>

                                        <button 
                                            type="button" 
                                            wire:click="removeItem({{ $item['id'] }})"
                                            wire:confirm="Are you sure you want to remove this item?"
                                            class="inline-flex items-center text-sm font-medium text-red-600 hover:underline dark:text-red-500">
                                            <svg class="me-1.5 h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                                            </svg>
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Order Summary Sidebar -->
        <div class="mx-auto mt-6 max-w-4xl flex-1 space-y-6 lg:mt-0 lg:w-full">
            <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                <p class="text-xl font-semibold text-gray-900 dark:text-white">Order summary</p>

                <div class="space-y-4">
                    <div class="space-y-2">
                        <dl class="flex items-center justify-between gap-4">
                            <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Original price</dt>
                            <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $this->formatPrice($this->getSubtotal()) }}</dd>
                        </dl>

                        @if($this->getSavings() > 0)
                            <dl class="flex items-center justify-between gap-4">
                                <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Savings</dt>
                                <dd class="text-base font-medium text-green-600">-{{ $this->formatPrice($this->getSavings()) }}</dd>
                            </dl>
                        @endif

                        <dl class="flex items-center justify-between gap-4">
                            <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Store Pickup</dt>
                            <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $this->formatPrice($this->getShipping()) }}</dd>
                        </dl>

                        <dl class="flex items-center justify-between gap-4">
                            <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Tax</dt>
                            <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $this->formatPrice($this->getTax()) }}</dd>
                        </dl>
                    </div>

                    <dl class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                        <dt class="text-base font-bold text-gray-900 dark:text-white">Total</dt>
                        <dd class="text-base font-bold text-gray-900 dark:text-white">{{ $this->formatPrice($this->getTotal()) }}</dd>
                    </dl>
                </div>

                @if(!empty($cartItems))
                    <button 
                        wire:click="proceedToCheckout" 
                        class="flex w-full items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        Proceed to Checkout
                    </button>
                @endif

                <div class="flex items-center justify-center gap-2">
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400"> or </span>
                    <a href="#" title="" class="inline-flex items-center gap-2 text-sm font-medium text-primary-700 underline hover:no-underline dark:text-primary-500">
                        Continue Shopping
                        <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Voucher Section -->
            <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                <form wire:submit="applyVoucher" class="space-y-4">
                    <div>
                        <label for="voucher" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Do you have a voucher or gift card?</label>
                        <input 
                            type="text" 
                            id="voucher" 
                            wire:model="voucherCode"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500" 
                            placeholder="Enter voucher code" 
                        />
                    </div>
                    <button 
                        type="submit" 
                        class="flex w-full items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        Apply Code
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mt-4 rounded-lg bg-green-50 p-4 text-green-800 dark:bg-green-800/20 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mt-4 rounded-lg bg-red-50 p-4 text-red-800 dark:bg-red-800/20 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif
</div>
