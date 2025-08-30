<?php

use App\Traits\ManagesCart;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Log;
// use Cart;

new class extends Component {
    // use ManagesCart;

    public array $cartItems = [];
    public string $voucherCode = '';
    public $suggestedProducts;

    public function mount(): void
    {
        // $this->setCartSession();
        $this->loadCartItems();
        $this->loadSuggestedProducts();
    }

    public function loadCartItems(): void
    {
        try {

            // Cart::setSessionKey(Auth::check() ? (string) Auth::user()->id : Session::getId());


            $cartContents = Cart::getContent();

            // if ($cartContents->isEmpty()) {
            //     $this->cartItems = [];
            //     return;
            // }

            $this->cartItems = $cartContents->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'name' => (string) $item->name,
                    'price' => (int) $item->price,
                    'quantity' => (int) $item->quantity,
                    'attributes' => $item->attributes,
                    // 'conditions' => $item->conditions,
                    'imageUrl' => $item->attributes['imageUrl'],
                ];
            })->values()->toArray();
        } catch (\Exception $e) {
            // $this->cartItems = [];
            // Log::error('Cart loading error: ' . $e->getMessage());
        }
    }

    public function loadSuggestedProducts(): void
    {
        // Get cart item IDs to exclude them from suggestions
        $cartProductIds = collect($this->cartItems)->pluck('id')->toArray();
        
        // Get 3 random products that are not in cart
        $this->suggestedProducts = \App\Models\Product::where('is_active', true)
            ->whereNotIn('id', $cartProductIds)
            ->inRandomOrder()
            ->limit(3)
            ->get();
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $this->setCartSession();

        if ($quantity <= 0) {
            $this->removeItem($itemId);
            return;
        }

        Cart::update($itemId, [
            'quantity' => [
                'relative' => false,
                'value' => $quantity
            ]
        ]);

        $this->loadCartItems();
        $this->dispatch('product-added-to-cart');
    }

    public function removeItem(int $itemId): void
    {
        $this->setCartSession();
        Cart::remove($itemId);
        $this->loadCartItems();
        $this->dispatch('product-added-to-cart');

        session()->flash('success', 'Item removed from cart.');
    }

    public function applyVoucher(): void
    {
        if (!empty($this->voucherCode)) {
            session()->flash('success', "Voucher '{$this->voucherCode}' applied successfully!");
            $this->voucherCode = '';
        }
    }

    // public function addToCart(int $productId): void
    // {
    //     $product = \App\Models\Product::find($productId);
        
    //     if (!$product) {
    //         return;
    //     }

    //     // $this->setCartSession();

    //     // Check if product already in cart
    //     $cartItem = Cart::get($productId);
        
    //     if ($cartItem) {
    //         // Update quantity if already exists
    //         Cart::update($productId, [
    //             'quantity' => [
    //                 'relative' => true,
    //                 'value' => 1
    //             ]
    //         ]);
    //     } else {
    //         // Add new item to cart
    //         Cart::add([
    //             'id' => $product->id,
    //             'name' => $product->name,
    //             'price' => $product->price,
    //             'quantity' => 1,
    //             'attributes' => [
    //                 'image' => $product->primaryImage() ? '/storage/' . $product->primaryImage()->image_path : 'https://flowbite.s3.amazonaws.com/blocks/e-commerce/book-placeholder.svg',
    //                 'description' => $product->description,
    //             ]
    //         ]);
    //     }

    //     $this->loadCartItems();
    //     $this->loadSuggestedProducts(); // Refresh suggestions to exclude newly added item
    //     $this->dispatch('product-added-to-cart');
        
    //     session()->flash('success', "'{$product->name}' telah ditambah ke keranjang!");
    // }

    #[Computed]
    public function getSubtotal(): int
    {
        return $this->getCartSubtotal();
    }

    #[Computed]
    public function getSavings(): int
    {
        return 29900; // $299.00 in cents
    }

    #[Computed]
    public function getShipping(): int
    {
        return 9900; // $99.00 in cents
    }

    #[Computed]
    public function getTax(): int
    {
        return (int) ($this->getSubtotal() * 0.105); // 10.5% tax
    }

    #[Computed]
    public function getTotal(): int
    {
        return $this->getSubtotal() - $this->getSavings() + $this->getShipping() + $this->getTax();
    }

    public function formatPrice(int $cents): string
    {
        return 'RM' . number_format($cents / 100, 2);
    }
}; ?>

<div class="min-h-screen">
    <!-- Header Navigation -->
    <div class="cart-container">
        <header class="cart-header">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="cart-brand">
                        <flux:icon.home class="h-8 w-8" />
                        <span>Kak Kay</span>
                    </a>
                </div>

                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="cart-nav-link">
                        ← Kembali Beli-belah
                    </a>

                    <div class="relative">
                        <flux:button variant="primary" size="sm" href="{{ route('cart') }}" class="flex items-center gap-2">
                            <flux:icon.shopping-bag class="h-5 w-5" />
                        </flux:button>
                        @livewire('cart-counter')
                    </div>
                </div>
            </div>
        </header>
    </div>

    <!-- Cart Content -->
    <section class="py-8 md:py-16">
        <div class="cart-container">
            <div class="mb-8">
                <h2 class="text-3xl font-bold mb-2" style="font-family: 'Caveat Brush', cursive;">
                    Keranjang <span class="cart-text-accent">Belanja</span> Kamu
                </h2>
                <p class="cart-text-muted text-lg">Buku-buku pilihan yang akan mengubah hidupmu</p>
            </div>

            @if(session('success'))
                <flux:callout variant="success" class="mb-6">
                    {{ session('success') }}
                </flux:callout>
            @endif

            @if(empty($cartItems))
                <div class="cart-empty-state">
                    <div class="max-w-md mx-auto">
                        <div class="text-6xl mb-4">📚</div>
                        <h3 class="text-2xl font-bold mb-4" style="font-family: 'Caveat Brush', cursive;">
                            Keranjang Masih Kosong
                        </h3>
                        <p class="cart-text-muted mb-8 text-lg">
                            Jom pilih buku-buku yang boleh ubah hidup kamu! Setiap halaman ada cerita, setiap cerita ada pelajaran.
                        </p>
                        <flux:button variant="primary" href="{{ route('home') }}" class="cart-button-primary px-8 py-4 text-lg">
                            <flux:icon.sparkles class="h-5 w-5 mr-2" />
                            Mula Beli-belah
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
                                        <div class="w-24 h-32 sm:w-32 sm:h-40 bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg overflow-hidden shadow-lg">
                                            <img src="{{ $item['imageUrl'] }}" 
                                                 alt="{{ $item['name'] }}" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                    </div>

                                    <!-- Product Details -->
                                    <div class="flex-1 space-y-4">
                                        <div>
                                            <h3 class="text-xl font-bold mb-2" style="font-family: 'Montserrat', sans-serif;">
                                                {{ $item['name'] }}
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
                                                {{ $this->formatPrice($item['price']) }}
                                            </p>
                                        </div>

                                        <!-- Quantity and Actions -->
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <flux:button variant="subtle" size="sm" 
                                                           wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                                           class="w-8 h-8 flex items-center justify-center">
                                                    <flux:icon.minus class="h-4 w-4" />
                                                </flux:button>
                                                
                                                <span class="text-lg font-semibold px-4 py-2 bg-white/10 rounded-lg min-w-[3rem] text-center">
                                                    {{ $item['quantity'] }}
                                                </span>
                                                
                                                <flux:button variant="subtle" size="sm" 
                                                           wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                                           class="w-8 h-8 flex items-center justify-center">
                                                    <flux:icon.plus class="h-4 w-4" />
                                                </flux:button>
                                            </div>

                                            <flux:button variant="subtle" color="red" size="sm" 
                                                       wire:click="removeItem({{ $item['id'] }})"
                                                       class="hover:bg-red-500/20">
                                                <flux:icon.trash class="h-4 w-4 mr-1" />
                                                Buang
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Order Summary -->
                    <div class="w-full lg:w-96 xl:w-[28rem]">
                        <div class="cart-summary-card p-6 sticky top-6">
                            <h3 class="text-2xl font-bold mb-6" style="font-family: 'Caveat Brush', cursive;">
                                Ringkasan <span class="cart-text-accent">Pesanan</span>
                            </h3>

                            <div class="space-y-4 mb-6">
                                <div class="flex justify-between">
                                    <span class="cart-text-muted">Subtotal</span>
                                    <span class="font-semibold">{{ $this->formatPrice($this->getSubtotal()) }}</span>
                                </div>
                                
                                <div class="flex justify-between text-green-400">
                                    <span>Diskaun</span>
                                    <span class="font-semibold">-{{ $this->formatPrice($this->getSavings()) }}</span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="cart-text-muted">Penghantaran</span>
                                    <span class="font-semibold">{{ $this->formatPrice($this->getShipping()) }}</span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="cart-text-muted">Cukai (10.5%)</span>
                                    <span class="font-semibold">{{ $this->formatPrice($this->getTax()) }}</span>
                                </div>
                                
                                <hr class="border-white/20">
                                
                                <div class="flex justify-between text-xl font-bold">
                                    <span>Jumlah</span>
                                    <span class="cart-text-accent">{{ $this->formatPrice($this->getTotal()) }}</span>
                                </div>
                            </div>

                            <flux:button variant="primary" href="{{ route('checkout') }}" 
                                       class="w-full cart-button-primary mb-4 px-6 py-4 text-lg">
                                <flux:icon.credit-card class="h-5 w-5 mr-2" />
                                Bayar Sekarang
                            </flux:button>

                            <div class="text-center">
                                <span class="cart-text-muted text-sm">atau</span>
                                <flux:button variant="subtle" href="{{ route('home') }}" class="cart-button-ghost ml-2">
                                    <flux:icon.arrow-left class="h-4 w-4 mr-2" />
                                    Teruskan Beli-belah
                                </flux:button>
                            </div>
                        </div>

                        <!-- Voucher Section -->
                        <div class="cart-summary-card p-6 mt-6">
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
                        </div>
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
                        <div class="cart-item-card overflow-hidden">
                            <div class="aspect-[3/4] bg-gradient-to-br from-purple-600 to-pink-600 rounded-lg mb-4 overflow-hidden">
                                @php
                                    $primaryImage = $product->primaryImage();
                                    $imageUrl = $primaryImage ? '/storage/' . $primaryImage->image_path : 'https://flowbite.s3.amazonaws.com/blocks/e-commerce/book-placeholder.svg';
                                @endphp
                                <img src="{{ $imageUrl }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover">
                            </div>
                            
                            <div class="space-y-4">
                                <h4 class="text-lg font-bold leading-tight" style="font-family: 'Montserrat', sans-serif;">
                                    {{ $product->name }}
                                </h4>
                                <p class="cart-text-muted text-sm line-clamp-2">
                                    {{ $product->description }}
                                </p>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-xl font-bold cart-text-accent">
                                        {{ $this->formatPrice($product->price) }}
                                    </span>
                                    <flux:badge variant="solid" color="pink" size="sm">
                                        <flux:icon.sparkles class="h-3 w-3 mr-1" />
                                        Hot
                                    </flux:badge>
                                </div>
                                
                                <div class="flex gap-3 pt-2">
                                    <flux:button variant="subtle" size="sm" 
                                               class="flex-1 hover:bg-white/10">
                                        <flux:icon.heart class="h-4 w-4 mr-1" />
                                        Suka
                                    </flux:button>
                                    <flux:button variant="primary" size="sm" 
                                               wire:click="addToCart({{ $product->id }})"
                                               class="flex-1 cart-button-primary">
                                        <flux:icon.shopping-cart class="h-4 w-4 mr-1" />
                                        Tambah
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
