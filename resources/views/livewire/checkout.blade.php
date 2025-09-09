<div class="checkout-container">
    <!-- Header Navigation -->
    <div class="cart-container">
        <header class="cart-header">
            <div class="flex items-center justify-between">
                <a href="/">
                    <div class="brand">
                    <div class="logo" aria-hidden="true"></div>
                        <div>
                        <h1>Kak Kay</h1>
                        <div class="tagline  text-xs sm:text-base">Counsellor • Therapist • KKDI Creator</div>
                        </div>
                    </div>
                </a>

                <div class="flex items-center gap-6">


                    <div class="relative">
                        <flux:button variant="primary" href="{{ route('cart') }}" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 shadow-lg">
                            <flux:icon.shopping-bag class="h-6 w-6" />
                            <span class="hidden sm:inline font-medium">Troli</span>
                            <div class="absolute top-0 right-0">
                            @livewire('cart-counter')
                            </div>
                        </flux:button>
                    </div>
                </div>
            </div>
        </header>
    </div>

    <!-- Checkout Content -->
    <section class="py-10">
        <div class="cart-container">
            <form wire:submit="processCheckout" class="max-w-screen-xl mx-auto">
                
                <!-- Progress Steps -->
                <div class="mb-12">
                    <ol class="flex items-center justify-center w-full max-w-2xl mx-auto text-center text-xs sm:text-sm font-medium text-gray-300 gap-1 sm:gap-0">
                        <a href="{{ route('cart') }}" class="flex items-centerfocus:outline-none" style="text-decoration: none;">
                            <li class="flex items-center text-pink-400">
                                <span class="flex items-center">
                                    <flux:icon.check-circle class="me-1 h-3 w-3 sm:h-4 sm:w-4" />
                                    Troli
                                </span>
                            </li>
                        </a>
                        <li class="flex items-center mx-1 sm:mx-2">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </li>
                        <li class="flex items-center text-pink-400">
                            <span class="flex items-center">
                                <flux:icon.check-circle class="me-1 h-3 w-3 sm:h-4 sm:w-4" />
                                Bayaran
                            </span>
                        </li>
                        <li class="flex items-center mx-1 sm:mx-2">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </li>
                        <li class="flex shrink-0 items-center text-gray-400">
                            <flux:icon.clock class="me-1 h-3 w-3 sm:h-4 sm:w-4" />
                            Pesanan
                        </li>
                    </ol>
                </div>

                @if(session('success'))
                    <flux:callout variant="success" class="mb-6">
                        {{ session('success') }}
                    </flux:callout>
                @endif

                @if(session('error'))
                    <flux:callout variant="danger" class="mb-6">
                        {{ session('error') }}
                    </flux:callout>
                @endif

                @if(session('message'))
                    <flux:callout variant="info" class="mb-6">
                        {{ session('message') }}
                    </flux:callout>
                @endif

                <div class="lg:flex lg:items-start lg:gap-12 xl:gap-16">
                    <div class="min-w-0 flex-1 space-y-8">
                        
                        <!-- Filament Form -->
                        <div class="relative">
                            <div class="filament-checkout-form">
                                {{ $this->form }}
                            </div>
                            
                            <!-- Country Code Display -->
                            <div class="mt-4 p-4 bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-500/20 rounded-lg">
                                <div class="flex items-center gap-2 text-sm text-blue-200">
                                    <flux:icon.information-circle class="h-4 w-4" />
                                    <span>Kod negara terpilih: <strong>{{ $selectedCountryCode }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods - Kept hidden for now -->
                        <div class="cart-card p-6 space-y-4 opacity-0 hidden">
                            {{-- <h3 class="text-xl font-semibold text-white" style="font-family: 'Caveat Brush', cursive;">
                                Cara <span class="cart-text-accent">Pembayaran</span>
                            </h3>

                            <!-- Payment Method Groups -->
                            <div class="space-y-4">
                                @if(!empty($this->getPaymentMethodsByGroup()))
                                    @foreach($this->getPaymentMethodsByGroup() as $groupName => $methods)
                                        <div class="space-y-3">
                                            <h4 class="text-lg font-medium text-white capitalize">
                                                {{ $this->getGroupDisplayName($groupName) }}
                                            </h4>
                                            
                                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                                @foreach($methods as $method)
                                                    <div class="p-4 border border-gray-600 rounded-lg bg-white/5 hover:bg-white/10 transition-colors cursor-pointer"
                                                         wire:click="selectPaymentMethod('{{ $method['id'] }}')">
                                                        <div class="flex items-center space-x-3">
                                                            <flux:icon name="{{ $method['icon'] }}" class="h-6 w-6 text-pink-400" />
                                                            <div class="flex-1">
                                                                <div class="font-medium text-white">{{ $method['name'] }}</div>
                                                                <div class="text-sm text-gray-400">{{ $method['description'] }}</div>
                                                            </div>
                                                            @if(in_array($method['id'], $form['payment_method_whitelist'] ?? []))
                                                                <flux:icon.check-circle class="h-5 w-5 text-green-400" />
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Fallback payment options -->
                                    {{-- <div class="space-y-3">
                                        <h4 class="text-lg font-medium text-white">Online Banking</h4>
                                        <div class="p-4 border border-gray-600 rounded-lg bg-white/5 hover:bg-white/10 transition-colors cursor-pointer"
                                             wire:click="selectPaymentMethod('fpx_b2c')">
                                            <div class="flex items-center space-x-3">
                                                <flux:icon name="building-office" class="h-6 w-6 text-pink-400" />
                                                <div class="flex-1">
                                                    <div class="font-medium text-white">FPX Online Banking</div>
                                                    <div class="text-sm text-gray-400">Bayar dengan Internet Banking Malaysia</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <h4 class="text-lg font-medium text-white">Kad Kredit/Debit</h4>
                                        <div class="p-4 border border-gray-600 rounded-lg bg-white/5 hover:bg-white/10 transition-colors cursor-pointer"
                                             wire:click="selectPaymentMethod('visa')">
                                            <div class="flex items-center space-x-3">
                                                <flux:icon name="credit-card" class="h-6 w-6 text-pink-400" />
                                                <div class="flex-1">
                                                    <div class="font-medium text-white">Kad Kredit/Debit</div>
                                                    <div class="text-sm text-gray-400">Visa, Mastercard</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <h4 class="text-lg font-medium text-white">E-Wallet</h4>
                                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                            <div class="p-4 border border-gray-600 rounded-lg bg-white/5 hover:bg-white/10 transition-colors cursor-pointer"
                                                 wire:click="selectPaymentMethod('tng_ewallet')">
                                                <div class="flex items-center space-x-3">
                                                    <flux:icon name="wallet" class="h-6 w-6 text-pink-400" />
                                                    <div class="flex-1">
                                                        <div class="font-medium text-white">Touch 'n Go eWallet</div>
                                                        <div class="text-sm text-gray-400">Bayar dengan TnG eWallet</div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="p-4 border border-gray-600 rounded-lg bg-white/5 hover:bg-white/10 transition-colors cursor-pointer"
                                                 wire:click="selectPaymentMethod('grabpay')">
                                                <div class="flex items-center space-x-3">
                                                    <flux:icon name="wallet" class="h-6 w-6 text-pink-400" />
                                                    <div class="flex-1">
                                                        <div class="font-medium text-white">GrabPay</div>
                                                        <div class="text-sm text-gray-400">Bayar dengan GrabPay</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                                @endif
                            </div>  --}}
                        </div>

                        <!-- Delivery Methods -->
                        {{-- <div class="cart-card p-6 space-y-4">
                            <h3 class="text-xl font-semibold text-white" style="font-family: 'Caveat Brush', cursive;">
                                Cara <span class="cart-text-accent">Penghantaran</span>
                            </h3>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="p-4 border border-gray-600 rounded-lg bg-white/5">
                                    <flux:radio wire:model="form.delivery_method" value="standard" checked>
                                        <div class="ml-3">
                                            <div class="font-medium text-white">RM5 - Penghantaran Standard</div>
                                            <div class="text-sm text-gray-400">3-5 hari bekerja</div>
                                        </div>
                                    </flux:radio>
                                </div>

                                <div class="p-4 border border-gray-600 rounded-lg bg-white/5">
                                    <flux:radio wire:model="form.delivery_method" value="fast">
                                        <div class="ml-3">
                                            <div class="font-medium text-white">RM15 - Penghantaran Pantas</div>
                                            <div class="text-sm text-gray-400">1-2 hari bekerja</div>
                                        </div>
                                    </flux:radio>
                                </div>

                                <div class="p-4 border border-gray-600 rounded-lg bg-white/5">
                                    <flux:radio wire:model="form.delivery_method" value="express">
                                        <div class="ml-3">
                                            <div class="font-medium text-white">RM49 - Penghantaran Ekspres</div>
                                            <div class="text-sm text-gray-400">Hari yang sama</div>
                                        </div>
                                    </flux:radio>
                                </div>
                            </div>
                        </div> --}}
{{-- 
                        <!-- Voucher Code -->
                        <div class="cart-card p-6">
                            <flux:field>
                                <flux:label>Kod Voucher atau Promosi</flux:label>
                                <div class="flex max-w-md items-center gap-4">
                                    <flux:input wire:model="form.voucher_code" placeholder="Masukkan kod voucher" class="flex-1" />
                                    <flux:button type="button" wire:click="applyVoucher" variant="primary">
                                        Guna
                                    </flux:button>
                                </div>
                            </flux:field>
                        </div> --}}
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div class="w-full lg:w-96 xl:w-[28rem] mt-0">
                        <div class="cart-summary-card p-6 sticky top-6">
                            <h3 class="text-2xl font-bold mb-6" style="font-family: 'Caveat Brush', cursive;">
                                Ringkasan <span class="cart-text-accent">Pesanan</span>
                            </h3>

                            <div class="space-y-4 mb-6">
                                <div class="flex justify-between">
                                    <span class="cart-text-muted">Subtotal</span>
                                    <span class="font-semibold">{{ $this->formatPrice($this->getSubtotal()) }}</span>
                                </div>

                                {{-- <div class="flex justify-between text-green-400">
                                    <span>Diskaun</span>
                                    <span class="font-semibold">-{{ $this->formatPrice($this->getSavings()) }}</span>
                                </div> --}}

                                <div class="flex justify-between">
                                    <span class="cart-text-muted">Penghantaran</span>
                                    <span class="font-semibold">{{ $this->formatPrice($this->getShipping()) }}</span>
                                </div>

                                {{-- <div class="flex justify-between">
                                    <span class="cart-text-muted">Cukai (10.5%)</span>
                                    <span class="font-semibold">{{ $this->formatPrice($this->getTax()) }}</span>
                                </div> --}}

                                <hr class="border-white/20">

                                <div class="flex justify-between text-xl font-bold">
                                    <span>Jumlah</span>
                                    <span class="cart-text-accent">{{ $this->formatPrice($this->getTotal()) }}</span>
                                </div>
                            </div>

                            <flux:button type="submit" variant="primary" class="w-full cart-button-primary mb-4 px-6 py-4 text-lg" wire:loading.attr="disabled">
                                <div wire:loading.remove class="flex items-center justify-center">
                                    <flux:icon.credit-card class="h-5 w-5 mr-2" />
                                    Bayar Sekarang
                                </div>
                                <div wire:loading class="flex items-center justify-center">
                                    <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Memproses...
                                </div>
                            </flux:button>
                        </div>

                        <div class="bg-white">
                            <img src="{{ asset('storage/images/fpx.png') }}" alt="payment-method" class="mx-auto mt-10">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <div class="container">
        <x-footer />
    </div>

</div>