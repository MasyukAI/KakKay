<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <!-- Header -->
    <div class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border-b border-gray-200/50 dark:border-gray-700/50 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="/" class="flex items-center text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Shop
                    </a>
                    <div class="h-6 w-px bg-gray-300 dark:bg-gray-600"></div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                        Secure Checkout
                    </h1>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    256-bit SSL Encrypted
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Progress Steps -->
        <div class="mb-12">
            <div class="flex items-center justify-center">
                <div class="flex items-center space-x-4">
                    @php
                        $steps = [
                            1 => ['name' => 'Cart Review', 'icon' => 'shopping-cart'],
                            2 => ['name' => 'Payment & Delivery', 'icon' => 'credit-card'],
                            3 => ['name' => 'Order Confirmation', 'icon' => 'check-circle']
                        ];
                    @endphp
                    
                    @foreach($steps as $stepNumber => $step)
                        <div class="flex items-center">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 ease-in-out
                                    {{ $currentStep == $stepNumber ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/30 scale-110' : 
                                       ($currentStep > $stepNumber ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-lg shadow-green-500/30' : 
                                        'bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 border-2 border-gray-200 dark:border-gray-600') }}">
                                    
                                    @if($currentStep > $stepNumber)
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    @elseif($step['icon'] == 'shopping-cart')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 2.5M7 13l2.5 2.5"></path>
                                        </svg>
                                    @elseif($step['icon'] == 'credit-card')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                </div>
                                
                                @if($currentStep == $stepNumber)
                                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-500 rounded-full animate-ping"></div>
                                @endif
                            </div>
                            
                            <div class="ml-4 {{ $stepNumber == 3 ? '' : 'mr-8' }}">
                                <div class="text-sm font-medium {{ $currentStep >= $stepNumber ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                                    Step {{ $stepNumber }}
                                </div>
                                <div class="text-xs {{ $currentStep >= $stepNumber ? 'text-gray-600 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500' }}">
                                    {{ $step['name'] }}
                                </div>
                            </div>
                            
                            @if($stepNumber < 3)
                                <div class="w-16 h-0.5 {{ $currentStep > $stepNumber ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gray-200 dark:bg-gray-600' }} transition-all duration-300"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Area -->
            <div class="lg:col-span-2">
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/20 p-8 transition-all duration-300 hover:shadow-2xl">
                    <!-- Step Content -->
                    @if($currentStep === 1)
                        <div class="space-y-6" x-data="{ removing: null }">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-7 h-7 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 2.5M7 13l2.5 2.5"></path>
                                    </svg>
                                    Review Your Cart
                                </h2>
                            </div>
                            
                            @php
                                $cartItems = \Cart::getItems();
                            @endphp
                            
                            @if($cartItems->count() > 0)
                                <div class="space-y-4">
                                    @foreach($cartItems as $item)
                                        <div class="group bg-gradient-to-r from-white to-gray-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 border border-gray-200/50 dark:border-gray-600/50 hover:shadow-lg hover:border-blue-200 dark:hover:border-blue-500/50 transition-all duration-300"
                                             x-show="removing !== '{{ $item->id }}'"
                                             x-transition:leave="transition ease-in duration-300"
                                             x-transition:leave-start="opacity-100 transform scale-100"
                                             x-transition:leave-end="opacity-0 transform scale-95">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-start space-x-4">
                                                        <div class="w-16 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/50 dark:to-indigo-900/50 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                                                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1">
                                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                                {{ $item->name }}
                                                            </h3>
                                                            <p class="text-gray-600 dark:text-gray-300 mt-1">
                                                                {{ $item->attributes->category ?? 'Books' }}
                                                            </p>
                                                            <div class="flex items-center mt-3 space-x-4">
                                                                <div class="flex items-center space-x-2">
                                                                    <span class="text-sm text-gray-500 dark:text-gray-400">Qty:</span>
                                                                    <div class="flex items-center space-x-2">
                                                                        <button class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                                                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                                            </svg>
                                                                        </button>
                                                                        <span class="w-8 text-center font-medium text-gray-900 dark:text-white">{{ $item->quantity }}</span>
                                                                        <button class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                                                                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                                        {{ $item->attributes->formatted_price ?? 'RM ' . number_format($item->price / 100, 2) }}
                                                    </div>
                                                    <button 
                                                        @click="removing = '{{ $item->id }}'; setTimeout(() => removing = null, 300)"
                                                        class="mt-2 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium hover:underline transition-colors">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="flex justify-end mt-8">
                                    <button 
                                        wire:click="nextStep"
                                        class="group relative inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                                        <span class="mr-2">Continue to Payment</span>
                                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <div class="text-center py-16">
                                    <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-full flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 2.5M7 13l2.5 2.5"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Your cart is empty</h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-8">Discover our amazing collection of books</p>
                                    <a href="/" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-300 shadow-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                        Continue Shopping
                                    </a>
                                </div>
                            @endif
                        </div>
                    @elseif($currentStep === 2)
                        <div class="space-y-8">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-7 h-7 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    Payment & Delivery
                                </h2>
                            </div>

                            <!-- Payment Methods -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payment Method</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <label class="relative flex items-center p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-blue-300 dark:hover:border-blue-500 transition-colors group">
                                        <input type="radio" name="payment" value="card" class="sr-only" checked>
                                        <div class="w-5 h-5 border-2 border-blue-600 rounded-full mr-3 flex items-center justify-center">
                                            <div class="w-2.5 h-2.5 bg-blue-600 rounded-full"></div>
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm0 4v8h16V8H4z"/>
                                            </svg>
                                            <span class="font-medium text-gray-900 dark:text-white">Credit Card</span>
                                        </div>
                                    </label>
                                    
                                    <label class="relative flex items-center p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-blue-300 dark:hover:border-blue-500 transition-colors group">
                                        <input type="radio" name="payment" value="paypal" class="sr-only">
                                        <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-500 rounded-full mr-3"></div>
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M7.076 21.337H2.47a.641.641 0 01-.633-.74L4.944 2.9C5.026 2.382 5.474 2 5.998 2h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-.598 3.784-.024.152c-.01.128-.1.223-.221.223z"/>
                                            </svg>
                                            <span class="font-medium text-gray-900 dark:text-white">PayPal</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Credit Card Form -->
                            <div class="space-y-6 p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700 rounded-xl border border-gray-200/50 dark:border-gray-600/50">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Card Number</label>
                                        <input type="text" placeholder="1234 5678 9012 3456" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white transition-all duration-200">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expiry Date</label>
                                        <input type="text" placeholder="MM/YY" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white transition-all duration-200">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">CVV</label>
                                        <input type="text" placeholder="123" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white transition-all duration-200">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cardholder Name</label>
                                        <input type="text" placeholder="John Doe" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white transition-all duration-200">
                                    </div>
                                </div>
                            </div>

                            <!-- Delivery Options -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delivery Options</h3>
                                <div class="space-y-3">
                                    <label class="flex items-center justify-between p-4 border-2 border-blue-200 dark:border-blue-500 bg-blue-50 dark:bg-blue-900/20 rounded-xl cursor-pointer">
                                        <div class="flex items-center">
                                            <input type="radio" name="delivery" value="standard" class="sr-only" checked>
                                            <div class="w-5 h-5 border-2 border-blue-600 rounded-full mr-3 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-blue-600 rounded-full"></div>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">Standard Delivery</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-300">5-7 business days</div>
                                            </div>
                                        </div>
                                        <span class="font-semibold text-green-600 dark:text-green-400">FREE</span>
                                    </label>
                                    
                                    <label class="flex items-center justify-between p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-blue-300 dark:hover:border-blue-500 transition-colors">
                                        <div class="flex items-center">
                                            <input type="radio" name="delivery" value="express" class="sr-only">
                                            <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-500 rounded-full mr-3"></div>
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">Express Delivery</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-300">1-2 business days</div>
                                            </div>
                                        </div>
                                        <span class="font-semibold text-gray-900 dark:text-white">RM 15.00</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="flex justify-between pt-6">
                                <button 
                                    wire:click="previousStep"
                                    class="group inline-flex items-center px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300">
                                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                    Back to Cart
                                </button>
                                <button 
                                    wire:click="nextStep"
                                    class="group inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                                    <span class="mr-2">Review Order</span>
                                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @elseif($currentStep === 3)
                        <div class="space-y-8">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                    <svg class="w-7 h-7 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Order Confirmation
                                </h2>
                            </div>

                            <!-- Success Animation -->
                            <div class="text-center py-8">
                                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/50 dark:to-emerald-900/50 rounded-full flex items-center justify-center animate-pulse">
                                    <svg class="w-12 h-12 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Almost There!</h3>
                                <p class="text-gray-600 dark:text-gray-400 mb-8">Review your order details before placing your order</p>
                            </div>

                            <!-- Order Review -->
                            @php
                                $cartItems = \Cart::getItems();
                            @endphp
                            
                            @if($cartItems->count() > 0)
                                <div class="space-y-4">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Order Items</h4>
                                    @foreach($cartItems as $item)
                                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-lg border border-gray-200/50 dark:border-gray-600/50">
                                            <div class="flex items-center space-x-4">
                                                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/50 dark:to-indigo-900/50 rounded-lg flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h5 class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</h5>
                                                    <p class="text-sm text-gray-600 dark:text-gray-300">Qty: {{ $item->quantity }}</p>
                                                </div>
                                            </div>
                                            <span class="font-semibold text-gray-900 dark:text-white">
                                                {{ $item->attributes->formatted_price ?? 'RM ' . number_format($item->price / 100, 2) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Order Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700 rounded-xl border border-gray-200/50 dark:border-gray-600/50">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Method</h4>
                                    <div class="flex items-center">
                                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm0 4v8h16V8H4z"/>
                                        </svg>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">Credit Card</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-300">**** **** **** 3456</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700 rounded-xl border border-gray-200/50 dark:border-gray-600/50">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Delivery</h4>
                                    <div class="flex items-center">
                                        <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">Standard Delivery</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-300">5-7 business days - FREE</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between pt-6">
                                <button 
                                    wire:click="previousStep"
                                    class="group inline-flex items-center px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300">
                                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                    Back to Payment
                                </button>
                                <button 
                                    wire:click="placeOrder"
                                    class="group inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-xl hover:from-green-700 hover:to-emerald-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Place Order</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="space-y-6">
                <!-- Order Summary Card -->
                <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/20 p-6 sticky top-24">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Order Summary</h3>
                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300 text-sm font-medium rounded-full">
                            {{ \Cart::getItems()->count() }} items
                        </span>
                    </div>

                    @php
                        $cartItems = \Cart::getItems();
                        $subtotal = \Cart::getSubTotal();
                        $total = \Cart::getTotal();
                    @endphp

                    <!-- Cart Items Preview -->
                    <div class="space-y-3 mb-6 max-h-64 overflow-y-auto">
                        @foreach($cartItems as $item)
                            <div class="flex items-center space-x-3 p-3 bg-gray-50/50 dark:bg-gray-700/50 rounded-lg">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/50 dark:to-indigo-900/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item->name }}</h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Qty: {{ $item->quantity }}</p>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $item->attributes->formatted_price ?? 'RM ' . number_format($item->price / 100, 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Promo Code -->
                    <div class="mb-6 p-4 bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 rounded-lg border border-yellow-200/50 dark:border-yellow-700/50">
                        <div class="flex items-center space-x-2 mb-3">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Have a promo code?</span>
                        </div>
                        <div class="flex space-x-2">
                            <input type="text" placeholder="Enter code" class="flex-1 px-3 py-2 text-sm border border-yellow-300 dark:border-yellow-600 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-800 dark:text-white">
                            <button class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Apply
                            </button>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Subtotal</span>
                            <span>RM {{ number_format($subtotal / 100, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Shipping</span>
                            <span class="text-green-600 dark:text-green-400 font-medium">FREE</span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <span>Tax</span>
                            <span>RM 0.00</span>
                        </div>
                        <div class="h-px bg-gray-200 dark:bg-gray-600"></div>
                        <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white">
                            <span>Total</span>
                            <span class="text-blue-600 dark:text-blue-400">RM {{ number_format($total / 100, 2) }}</span>
                        </div>
                    </div>

                    <!-- Security Features -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="space-y-3">
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                </svg>
                                SSL Secured Payment
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                30-day Money Back Guarantee
                            </div>
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                </svg>
                                Free Delivery on Orders Over RM 50
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-200/50 dark:border-blue-700/50">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Need Help?</h4>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                        Our customer support team is here to assist you 24/7.
                    </p>
                    <button class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Contact Support
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>