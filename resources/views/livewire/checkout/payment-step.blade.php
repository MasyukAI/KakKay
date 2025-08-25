<div>
    <form wire:submit="proceedToPayment">
        <div class="mt-6 sm:mt-8 lg:flex lg:items-start lg:gap-12 xl:gap-16">
            <div class="min-w-0 flex-1 space-y-8">
                <!-- Delivery Details -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Delivery Details</h2>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Your name</label>
                            <input 
                                type="text" 
                                id="name" 
                                wire:model="name"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500 @error('name') border-red-500 @enderror" 
                                placeholder="John Doe" 
                                required 
                            />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Your email*</label>
                            <input 
                                type="email" 
                                id="email" 
                                wire:model="email"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500 @error('email') border-red-500 @enderror" 
                                placeholder="name@example.com" 
                                required 
                            />
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="country" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Country*</label>
                            <select 
                                id="country" 
                                wire:model="country"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500 @error('country') border-red-500 @enderror">
                                <option value="Malaysia">Malaysia</option>
                                <option value="Singapore">Singapore</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Philippines">Philippines</option>
                            </select>
                            @error('country') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="city" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">City*</label>
                            <input 
                                type="text" 
                                id="city" 
                                wire:model="city"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500 @error('city') border-red-500 @enderror" 
                                placeholder="Kuala Lumpur" 
                                required 
                            />
                            @error('city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="phone" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Phone Number*</label>
                            <input 
                                type="tel" 
                                id="phone" 
                                wire:model="phone"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500 @error('phone') border-red-500 @enderror" 
                                placeholder="012-345-6789" 
                                required 
                            />
                            @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="company" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Company name</label>
                            <input 
                                type="text" 
                                id="company" 
                                wire:model="company"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500" 
                                placeholder="Company LLC" 
                            />
                        </div>

                        <div>
                            <label for="vatNumber" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">VAT number</label>
                            <input 
                                type="text" 
                                id="vatNumber" 
                                wire:model="vatNumber"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500" 
                                placeholder="MY12345678901" 
                            />
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="space-y-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Payment</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 p-4 ps-4 dark:border-gray-700 {{ $paymentMethod === 'credit-card' ? 'bg-primary-50 border-primary-500 dark:bg-primary-900/20' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <div class="flex items-start">
                                <div class="flex h-5 items-center">
                                    <input 
                                        id="credit-card" 
                                        type="radio" 
                                        name="payment-method" 
                                        value="credit-card"
                                        wire:model.live="paymentMethod"
                                        class="h-4 w-4 border-gray-300 bg-white text-primary-600 focus:ring-2 focus:ring-primary-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-primary-600"
                                    />
                                </div>
                                <div class="ms-4 text-sm">
                                    <label for="credit-card" class="font-medium leading-none text-gray-900 dark:text-white">Credit Card</label>
                                    <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">Pay with your credit card</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4 ps-4 dark:border-gray-700 {{ $paymentMethod === 'pay-on-delivery' ? 'bg-primary-50 border-primary-500 dark:bg-primary-900/20' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <div class="flex items-start">
                                <div class="flex h-5 items-center">
                                    <input 
                                        id="pay-on-delivery" 
                                        type="radio" 
                                        name="payment-method" 
                                        value="pay-on-delivery"
                                        wire:model.live="paymentMethod"
                                        class="h-4 w-4 border-gray-300 bg-white text-primary-600 focus:ring-2 focus:ring-primary-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-primary-600"
                                    />
                                </div>
                                <div class="ms-4 text-sm">
                                    <label for="pay-on-delivery" class="font-medium leading-none text-gray-900 dark:text-white">Payment on delivery</label>
                                    <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">+{{ $this->formatPrice($this->getPaymentFee()) }} processing fee</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4 ps-4 dark:border-gray-700 {{ $paymentMethod === 'paypal' ? 'bg-primary-50 border-primary-500 dark:bg-primary-900/20' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <div class="flex items-start">
                                <div class="flex h-5 items-center">
                                    <input 
                                        id="paypal" 
                                        type="radio" 
                                        name="payment-method" 
                                        value="paypal"
                                        wire:model.live="paymentMethod"
                                        class="h-4 w-4 border-gray-300 bg-white text-primary-600 focus:ring-2 focus:ring-primary-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-primary-600"
                                    />
                                </div>
                                <div class="ms-4 text-sm">
                                    <label for="paypal" class="font-medium leading-none text-gray-900 dark:text-white">PayPal account</label>
                                    <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">Connect to your account</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Methods -->
                <div class="space-y-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Delivery Methods</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 p-4 ps-4 dark:border-gray-700 {{ $deliveryMethod === 'dhl' ? 'bg-primary-50 border-primary-500 dark:bg-primary-900/20' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <div class="flex items-start">
                                <div class="flex h-5 items-center">
                                    <input 
                                        id="dhl" 
                                        type="radio" 
                                        name="delivery-method" 
                                        value="dhl"
                                        wire:model.live="deliveryMethod"
                                        class="h-4 w-4 border-gray-300 bg-white text-primary-600 focus:ring-2 focus:ring-primary-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-primary-600"
                                    />
                                </div>
                                <div class="ms-4 text-sm">
                                    <label for="dhl" class="font-medium leading-none text-gray-900 dark:text-white">{{ $this->formatPrice($this->getDeliveryFee()) }} - DHL Fast Delivery</label>
                                    <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">Get it by Tomorrow</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4 ps-4 dark:border-gray-700 {{ $deliveryMethod === 'fedex' ? 'bg-primary-50 border-primary-500 dark:bg-primary-900/20' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <div class="flex items-start">
                                <div class="flex h-5 items-center">
                                    <input 
                                        id="fedex" 
                                        type="radio" 
                                        name="delivery-method" 
                                        value="fedex"
                                        wire:model.live="deliveryMethod"
                                        class="h-4 w-4 border-gray-300 bg-white text-primary-600 focus:ring-2 focus:ring-primary-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-primary-600"
                                    />
                                </div>
                                <div class="ms-4 text-sm">
                                    <label for="fedex" class="font-medium leading-none text-gray-900 dark:text-white">Free Delivery - FedEx</label>
                                    <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">Get it by Friday, 13 Dec 2024</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4 ps-4 dark:border-gray-700 {{ $deliveryMethod === 'express' ? 'bg-primary-50 border-primary-500 dark:bg-primary-900/20' : 'bg-gray-50 dark:bg-gray-800' }}">
                            <div class="flex items-start">
                                <div class="flex h-5 items-center">
                                    <input 
                                        id="express" 
                                        type="radio" 
                                        name="delivery-method" 
                                        value="express"
                                        wire:model.live="deliveryMethod"
                                        class="h-4 w-4 border-gray-300 bg-white text-primary-600 focus:ring-2 focus:ring-primary-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-primary-600"
                                    />
                                </div>
                                <div class="ms-4 text-sm">
                                    <label for="express" class="font-medium leading-none text-gray-900 dark:text-white">{{ $this->formatPrice($this->getDeliveryFee()) }} - Express Delivery</label>
                                    <p class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">Get it today</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Voucher Code -->
                <div>
                    <label for="voucherCode" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Enter a gift card, voucher or promotional code</label>
                    <div class="flex max-w-md items-center gap-4">
                        <input 
                            type="text" 
                            id="voucherCode" 
                            wire:model="voucherCode"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500" 
                            placeholder="Enter code" 
                        />
                        <button 
                            type="button" 
                            wire:click="applyVoucher"
                            class="flex items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                            Apply
                        </button>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="mt-6 w-full space-y-6 sm:mt-8 lg:mt-0 lg:max-w-xs xl:max-w-md">
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

                        <dl class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Tax</dt>
                            <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $this->formatPrice($this->getTax()) }}</dd>
                        </dl>

                        <dl class="flex items-center justify-between gap-4 py-3">
                            <dt class="text-base font-bold text-gray-900 dark:text-white">Total</dt>
                            <dd class="text-base font-bold text-gray-900 dark:text-white">{{ $this->formatPrice($this->getTotal()) }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="space-y-3">
                    <button 
                        type="submit" 
                        class="flex w-full items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        Proceed to Payment
                    </button>

                    <button 
                        type="button" 
                        wire:click="goBack"
                        class="flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700">
                        ← Back to Cart
                    </button>
                </div>
            </div>
        </div>
    </form>

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
