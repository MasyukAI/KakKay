<div class="min-h-screen">

    <!-- Header Navigation -->
    <div class="cart-container">
        <header class="cart-header">
            <div class="flex items-center justify-between">
                <a href="/">
                    <div class="brand">
                    <div class="logo" aria-hidden="true"></div>
                        <div>
                        <h1>Kak Kay</h1>
                        <div class="tagline text-xs sm:text-base">Counsellor • Therapist • KKDI Creator</div>
                        </div>
                    </div>
                </a>

                {{-- <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="cart-nav-link">
                        ← Kembali Beli-belah
                    </a>
                </div> --}}
            </div>
        </header>
    </div>

    <!-- Cart Content -->
    <section class="py-10">
        <div class="cart-container">

             <!-- Progress Steps -->
                <div class="mb-12">
                    <ol class="flex items-center justify-center w-full max-w-2xl mx-auto text-center text-xs font-medium text-gray-300 gap-1 sm:gap-0">
                        <li class="flex items-center text-pink-400">
                            <span class="flex items-center">
                                <flux:icon.check-circle class="me-1 h-3 w-3 sm:h-4 sm:w-4" />
                                Troli
                            </span>
                        </li>
                        <li class="flex items-center mx-1 sm:mx-2">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </li>
                        <li class="flex items-center text-gray-400">
                            <span class="flex items-center">
                                <flux:icon.clock class="me-1 h-3 w-3 sm:h-4 sm:w-4" />
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

            <div class="mb-8">
                <h2 class="text-3xl font-bold mb-2" style="font-family: 'Caveat Brush', cursive;">
                    Troli <span class="cart-text-accent">Belanja</span>
                </h2>
                <p class="cart-text-muted text-lg">Buku-buku pilihan yang akan mengubah hidupmu</p>
            </div>

            @if(empty($cartItems))
                <div class="cart-empty-state">
                    <div class="max-w-md mx-auto">
                        <div class="text-6xl mb-4">📚</div>
                        <h3 class="text-2xl font-bold mb-4" style="font-family: 'Caveat Brush', cursive;">
                            Troli Masih Kosong
                        </h3>
                        <p class="cart-text-muted mb-8 text-lg">
                            Jom pilih buku-buku yang boleh ubah hidup kamu! Setiap halaman ada cerita, setiap cerita ada pelajaran.
                        </p>
                        <flux:button variant="primary" href="{{ route('home') }}" class="cart-button-primary px-8 py-4 text-lg">
                            <flux:icon.sparkles class="h-5 w-5 mr-2" />
                            Pilih buku jom!
                        </flux:button>
                    </div>
                </div>
            @else
                <div class="lg:flex lg:gap-8 xl:gap-12">
                    <!-- Cart Items -->
                    <div class="flex-1 space-y-6 mb-8 lg:mb-0">
                        @foreach($cartItems as $index => $item)
                            <div class="cart-item-card">
                                <div class="flex flex-col sm:flex-row gap-6">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0">
                                        <a href="/{{ $item['slug'] }}" style="display:block;" target="_blank" rel="noopener">
                                            <div class="w-24 h-32 sm:w-32 sm:h-40 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg overflow-hidden shadow-lg">
                                                <img src="{{ asset('storage/images/cover/' . $item['slug'] . '.png') }}"
                                                     alt="{{ $item['slug'] }}"
                                                     class="w-full h-full object-cover">
                                            </div>
                                        </a>
                                    </div>

                                    <!-- Product Details -->
                                    <div class="flex-1 space-y-4">
                                        <div>
                                            <h3 class="text-xl font-bold mb-2" style="font-family: 'Montserrat', sans-serif;">
                                                <a href="/{{ $item['slug'] }}" target="_blank" rel="noopener">
                                                    {{ $item['name'] }}
                                                </a>
                                            </h3>
                                            {{-- <div class="flex flex-wrap gap-2 mb-3">
                                                <flux:badge variant="solid" color="pink" size="sm">
                                                    <flux:icon.heart class="h-3 w-3 mr-1" />
                                                    Bestseller
                                                </flux:badge>
                                                <flux:badge variant="subtle" color="purple" size="sm">
                                                    Digital
                                                </flux:badge>
                                            </div> --}}
                                            <p class="text-2xl font-bold cart-text-accent">
                                                {{ $item['price'] }}
                                            </p>
                                        </div>

                                        <!-- Quantity and Actions -->
                                        <div class="flex items-center justify-between mt-4">
                                            <div class="flex items-center gap-3">
                                                <flux:button variant="subtle" size="sm"
                                                           wire:click="decrementQuantity('{{ $item['id'] }}')"
                                                           class="cursor-pointer w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-white/20 rounded-lg">
                                                    <flux:icon.minus class="h-4 w-4" />
                                                </flux:button>

                                                <span class="text-lg font-semibold px-4 py-2 bg-white/15 rounded-lg min-w-[3rem] text-center">
                                                    {{ $item['quantity'] }}
                                                </span>

                                                <flux:button variant="subtle" size="sm"
                                                           wire:click="incrementQuantity('{{ $item['id'] }}')"
                                                           class="cursor-pointer w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-white/20 rounded-lg">
                                                    <flux:icon.plus class="h-4 w-4" />
                                                </flux:button>
                                            </div>

                                            <flux:button variant="subtle" color="red"
                                                       wire:click="removeItem('{{ $item['id'] }}')"
                                                       class="hover:bg-red-500/30 border border-red-400/60 text-red-300 hover:text-white px-5 py-2.5 font-semibold rounded-lg transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer">
                                                <flux:icon.trash class="h-5 w-5" />
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Order Summary -->
                    <div class="w-full lg:w-96 xl:w-[28rem] mt-0">
                        <div class="cart-summary-card p-6 sticky top-6">
                            <h3 class="text-2xl font-bold mb-6" style="font-family: 'Caveat Brush', cursive;">
                                Ringkasan <span class="cart-text-accent">Pesanan</span>
                            </h3>

                            <div class="space-y-4 mb-6">
                                <div class="flex justify-between">
                                    <span class="cart-text-muted">Subtotal</span>
                                    <span class="font-semibold">{{ $this->getSubtotal() }}</span>
                                </div>

                                {{-- <div class="flex justify-between text-green-400">
                                    <span>Diskaun</span>
                                    <span class="font-semibold">-{{ $this->formatPrice($this->getSavings()) }}</span>
                                </div> --}}

                                <div class="flex justify-between">
                                    <span class="cart-text-muted">Penghantaran</span>
                                    <span class="font-semibold">{{ $this->getShipping() }}</span>
                                </div>

                                {{-- <div class="flex justify-between">
                                    <span class="cart-text-muted">Cukai (10.5%)</span>
                                    <span class="font-semibold">{{ $this->formatPrice($this->getTax()) }}</span>
                                </div> --}}

                                <hr class="border-white/20">

                                <div class="flex justify-between text-xl font-bold">
                                    <span>Jumlah</span>
                                    <span class="cart-text-accent">{{ $this->getTotal() }}</span>
                                </div>
                            </div>

                            <flux:button variant="primary" href="{{ route('checkout') }}"
                                       class="w-full cart-button-primary mb-4 px-6 py-4 text-lg">
                                <flux:icon.credit-card class="h-5 w-5 mr-2" />
                                Teruskan Pembelian
                            </flux:button>

                            {{-- <div class="text-center">
                                <span class="cart-text-muted text-sm">atau</span>
                                <flux:button variant="subtle" href="{{ route('home') }}" class="cart-button-ghost ml-2">
                                    <flux:icon.arrow-left class="h-4 w-4 mr-2" />
                                    Teruskan Beli-belah
                                </flux:button>
                            </div> --}}
                        </div>

                        <!-- Voucher Section -->
                        {{-- <div class="cart-summary-card p-6 mt-6">
                            <h4 class="text-lg font-bold mb-4" style="font-family: 'Montserrat', sans-serif;">
                                Kod Voucher
                            </h4>
                            <form wire:submit="applyVoucher" class="space-y-4">
                                <flux:input
                                    wire:model="voucherCode"
                                    placeholder="Masukkan kod voucher"
                                    class="w-full"
                                />
                                <flux:button type="submit" variant="outline" size="sm" class="w-full">
                                    Guna Voucher
                                </flux:button>
                            </form>
                        </div> --}}
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- People Also Bought Section -->
    @if(!empty($cartItems) && $suggestedProducts && $suggestedProducts->count() > 0)
        <section class="py-8">
            <div class="cart-container">
                <h3 class="text-3xl font-bold mb-8 text-center" style="font-family: 'Caveat Brush', cursive;">
                    Orang Lain <span class="cart-text-accent">Turut Beli</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($suggestedProducts as $product)
                        <a href="/{{ $product->slug }}" style="text-decoration: none; color: inherit;">
                            <div class="cart-item-card overflow-hidden">
                                <div class="aspect-[3/4] bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg mb-4 overflow-hidden">
                                    <img src="{{ asset('storage/images/cover/' . $product->slug . '.png') }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover">
                                </div>

                                <div class="space-y-4">
                                    {{-- <h4 class="text-lg font-bold leading-tight" style="font-family: 'Montserrat', sans-serif;">
                                        {{ $product->name }}
                                    </h4> --}}
                                    <p class="cart-text-muted text-sm line-clamp-2">
                                        {{ $product->description }}
                                    </p>

                                    <div class="flex items-center justify-between">
                                        <span class="text-xl font-bold cart-text-accent">
                                            {{ \Akaunting\Money\Money::MYR($product->price) }}
                                        </span>
                                        {{-- <flux:badge variant="solid" color="pink" size="sm">
                                            <flux:icon.sparkles class="h-3 w-3 mr-1" />
                                            Hot
                                        </flux:badge> --}}
                                    </div>

                                    <div class="flex gap-3 pt-2">
                                        {{-- <flux:button variant="subtle" size="sm"
                                                   class="flex-1 hover:bg-white/10">
                                            <flux:icon.heart class="h-4 w-4 mr-1" />
                                            Suka
                                        </flux:button> --}}
                                        <flux:button variant="primary" size="sm"
                                                   wire:click.prevent="addToCart({{ $product->id }})"
                                                   class="cursor-pointer flex-1 cart-button-primary">
                                            <flux:icon.shopping-cart class="h-4 w-4 mr-1 block" />
                                            Tambah
                                        </flux:button>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div class="container">
        <x-footer />
    </div>

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
