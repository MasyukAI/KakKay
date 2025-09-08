<div class="checkout-container">
    <!-- Header Navigation -->
    <div class="cart-container">
        <header class="cart-header">
            <div class="flex items-center justify-between">
                <div class="brand">
                    <div class="logo" aria-hidden="true"></div>
                    <div>
                        <h1>Kak Kay</h1>
                        <div class="tagline">Counsellor • Therapist • KKDI Creator</div>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <a href="{{ route('cart') }}" class="cart-nav-link">
                        ← Balik ke Troli
                    </a>

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
    <section class="py-8 md:py-16">
        <div class="cart-container">
            <form wire:submit="processCheckout" class="max-w-screen-xl mx-auto">
                
                <!-- Progress Steps -->
                <div class="mb-8">
                    <ol class="flex items-center justify-center w-full max-w-2xl mx-auto text-center text-sm font-medium text-gray-300 sm:text-base">
                        <li class="flex items-center text-pink-400 after:mx-6 after:hidden after:h-1 after:w-full after:border-b after:border-gray-600 sm:after:inline-block sm:after:content-[''] md:w-full xl:after:mx-10">
                            <span class="flex items-center after:mx-2 after:text-gray-500 after:content-['/'] sm:after:hidden">
                                <flux:icon.check-circle class="me-2 h-4 w-4 sm:h-5 sm:w-5" />
                                Troli
                            </span>
                        </li>

                        <li class="flex items-center text-pink-400 after:mx-6 after:hidden after:h-1 after:w-full after:border-b after:border-gray-600 sm:after:inline-block sm:after:content-[''] md:w-full xl:after:mx-10">
                            <span class="flex items-center after:mx-2 after:text-gray-500 after:content-['/'] sm:after:hidden">
                                <flux:icon.check-circle class="me-2 h-4 w-4 sm:h-5 sm:w-5" />
                                Bayaran
                            </span>
                        </li>

                        <li class="flex shrink-0 items-center text-gray-400">
                            <flux:icon.clock class="me-2 h-4 w-4 sm:h-5 sm:w-5" />
                            Ringkasan Pesanan
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
                    <div class="mt-6 w-full space-y-6 sm:mt-8 lg:mt-0 lg:max-w-xs xl:max-w-md">
                        <div class="cart-card p-6">
                            <h3 class="text-lg font-semibold text-white mb-4" style="font-family: 'Caveat Brush', cursive;">
                                Ringkasan <span class="cart-text-accent">Pesanan</span>
                            </h3>

                            <div class="space-y-3 divide-y divide-gray-600">
                                <dl class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-base font-normal text-gray-400">Jumlah Kecil</dt>
                                    <dd class="text-base font-medium text-white">{{ $this->formatPrice($this->getSubtotal()) }}</dd>
                                </dl>

                                @if($this->getSavings() > 0)
                                <dl class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-base font-normal text-gray-400">Jimat</dt>
                                    <dd class="text-base font-medium text-green-400">-{{ $this->formatPrice($this->getSavings()) }}</dd>
                                </dl>
                                @endif

                                <dl class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-base font-normal text-gray-400">Penghantaran</dt>
                                    <dd class="text-base font-medium text-white flex items-center gap-2">
                                        {{ $this->formatPrice($this->getShipping()) }}
                                        @if(($this->data['delivery_method'] ?? 'standard') !== 'standard')
                                            <span class="text-xs text-pink-400 bg-pink-400/10 px-2 py-1 rounded-full">
                                                {{ match($this->data['delivery_method'] ?? 'standard') {
                                                    'fast' => 'Pantas',
                                                    'express' => 'Ekspres',
                                                    default => 'Standard'
                                                } }}
                                            </span>
                                        @endif
                                    </dd>
                                </dl>

                                <!-- Tax removed -->

                                <dl class="flex items-center justify-between gap-4 py-3 border-gray-600 pt-3">
                                    <dt class="text-base font-bold text-white">Jumlah Keseluruhan</dt>
                                    <dd class="text-base font-bold cart-text-accent">{{ $this->formatPrice($this->getTotal()) }}</dd>
                                </dl>
                            </div>

                            <div class="space-y-3 mt-6">
                                <flux:button type="submit" variant="primary" class="cursor-pointer w-full cart-button-primary py-3" wire:loading.attr="disabled">
                                    <div wire:loading.remove>
                                        <flux:icon.credit-card class="h-5 w-5 mr-2" />
                                        Teruskan ke Pembayaran
                                    </div>
                                    <div wire:loading class="flex items-center justify-center">
                                        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Memproses...
                                    </div>
                                </flux:button>

                                <p class="text-sm text-gray-400 text-center">
                                    Maklumat peribadi anda selamat dan tidak akan dikongsi dengan pihak ketiga.
                                </p>
                            </div>
                        </div>

                        <div>
                            <img src="{{ asset('storage/images/payment-method.jpg') }}" alt="payment-method" class="mx-auto mt-10">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Debug Authentication Status -->
    <div style="background: rgba(0,0,0,0.3); border-top: 1px solid rgba(255,255,255,0.1); padding: 0.5rem 1rem; font-size: 0.8rem; color: rgba(255,255,255,0.7); text-align: center;">
      <strong style="color: #ff69b4;">DEBUG:</strong>
      @auth
        <span style="color: #4ade80;">✓ Authenticated User</span> - 
        <span>{{ auth()->user()->name ?? 'No Name' }}</span> 
        (<span>{{ auth()->user()->email ?? 'No Email' }}</span>)
      @else
        <span style="color: #f87171;">✗ Guest User</span> - Not logged in
      @endauth
      | Role: {{ auth()->user()->role ?? 'guest' }}
    </div>
</div>