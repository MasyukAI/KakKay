<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Facades\Cart;

new class extends Component {

    public array $form = [
        'name' => '',
        'email' => '',
        'phone' => '',
        'country' => 'Malaysia',
        'city' => 'Kuala Lumpur',
        'company_name' => '',
        'vat_number' => '',
        'payment_method' => 'credit-card',
        'delivery_method' => 'standard',
        'voucher_code' => '',
    ];

    public array $cartItems = [];
    public string $selectedCountryCode = '+60';

    public function mount(): void
    {
        $this->loadCartItems();
    }

    public function loadCartItems(): void
    {
        try {
            // Middleware handles cart instance switching
            $cartContents = Cart::getItems();

            if ($cartContents->isEmpty()) {
                $this->redirect(route('cart'));
                return;
            }

            $this->cartItems = $cartContents->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                    'price' => (int) ($item->price * 100), // Convert to cents
                    'quantity' => (int) $item->quantity,
                    'attributes' => $item->attributes->toArray(),
                ];
            })->values()->toArray();
        } catch (\Exception $e) {
            Log::error('Checkout loading error: ' . $e->getMessage());
            $this->redirect(route('cart'));
        }
    }

    #[Computed]
    public function getSubtotal(): int
    {
        return collect($this->cartItems)->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    #[Computed]
    public function getSavings(): int
    {
        return 0; // No savings for now
    }

    #[Computed]
    public function getShipping(): int
    {
        return match($this->form['delivery_method']) {
            'express' => 4900, // RM49
            'fast' => 1500,    // RM15
            default => 0,      // Free shipping
        };
    }

    #[Computed]
    public function getTax(): int
    {
        return (int) ($this->getSubtotal() * 0.10); // 10% SST
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

    public function applyVoucher(): void
    {
        // Voucher logic here
        session()->flash('message', 'Kod voucher akan disemak...');
    }

    public function processCheckout(): void
    {
        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.email' => 'required|email|max:255',
            'form.phone' => 'required|string|max:20',
            'form.country' => 'required|string',
            'form.city' => 'required|string',
        ]);

        // Middleware handles cart instance switching
        
        // Process the checkout
        session()->flash('success', 'Pesanan berjaya dihantar! Kami akan hubungi anda tidak lama lagi.');
        
        // Here you would typically:
        // 1. Create order record
        // 2. Process payment
        // 3. Clear cart
        // 4. Send confirmation email
        // 5. Redirect to success page
        
        // Clear the cart after successful checkout
        $cartManager->clear();
        
        // For now, just redirect back with success message
        $this->redirect(route('home'));
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
                    <a href="{{ route('cart') }}" class="cart-nav-link">
                        ← Kembali ke Keranjang
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
                                Keranjang
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

                @if(session('message'))
                    <flux:callout variant="info" class="mb-6">
                        {{ session('message') }}
                    </flux:callout>
                @endif

                <div class="lg:flex lg:items-start lg:gap-12 xl:gap-16">
                    <div class="min-w-0 flex-1 space-y-8">
                        
                        <!-- Delivery Details -->
                        <div class="cart-card p-6 space-y-4">
                            <h2 class="text-xl font-semibold text-white" style="font-family: 'Caveat Brush', cursive;">
                                Maklumat <span class="cart-text-accent">Penghantaran</span>
                            </h2>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <flux:field>
                                        <flux:label>Nama Penuh *</flux:label>
                                        <flux:input wire:model="form.name" placeholder="Nama penuh anda" required />
                                        <flux:error name="form.name" />
                                    </flux:field>
                                </div>

                                <div>
                                    <flux:field>
                                        <flux:label>Alamat Email *</flux:label>
                                        <flux:input type="email" wire:model="form.email" placeholder="nama@email.com" required />
                                        <flux:error name="form.email" />
                                    </flux:field>
                                </div>

                                <div>
                                    <flux:field>
                                        <flux:label>Negara *</flux:label>
                                        <flux:select wire:model="form.country" required>
                                            <option value="Malaysia">Malaysia</option>
                                            <option value="Singapore">Singapura</option>
                                            <option value="Indonesia">Indonesia</option>
                                            <option value="Thailand">Thailand</option>
                                            <option value="Brunei">Brunei</option>
                                        </flux:select>
                                        <flux:error name="form.country" />
                                    </flux:field>
                                </div>

                                <div>
                                    <flux:field>
                                        <flux:label>Bandar *</flux:label>
                                        <flux:select wire:model="form.city" required>
                                            <option value="Kuala Lumpur">Kuala Lumpur</option>
                                            <option value="Johor Bahru">Johor Bahru</option>
                                            <option value="Penang">Pulau Pinang</option>
                                            <option value="Kota Kinabalu">Kota Kinabalu</option>
                                            <option value="Kuching">Kuching</option>
                                        </flux:select>
                                        <flux:error name="form.city" />
                                    </flux:field>
                                </div>

                                <div>
                                    <flux:field>
                                        <flux:label>Nombor Telefon *</flux:label>
                                        <div class="flex">
                                            <flux:select wire:model="selectedCountryCode" class="w-24 rounded-r-none">
                                                <option value="+60">🇲🇾 +60</option>
                                                <option value="+65">🇸🇬 +65</option>
                                                <option value="+62">🇮🇩 +62</option>
                                                <option value="+66">🇹🇭 +66</option>
                                                <option value="+673">🇧🇳 +673</option>
                                            </flux:select>
                                            <flux:input wire:model="form.phone" placeholder="123456789" class="flex-1 rounded-l-none" required />
                                        </div>
                                        <flux:error name="form.phone" />
                                    </flux:field>
                                </div>

                                <div>
                                    <flux:field>
                                        <flux:label>Nama Syarikat (Opsional)</flux:label>
                                        <flux:input wire:model="form.company_name" placeholder="Nama syarikat" />
                                    </flux:field>
                                </div>

                                <div class="sm:col-span-2">
                                    <flux:button variant="subtle" type="button" class="w-full flex items-center justify-center gap-2">
                                        <flux:icon.plus class="h-5 w-5" />
                                        Tambah Alamat Baru
                                    </flux:button>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods -->
                        <div class="cart-card p-6 space-y-4">
                            <h3 class="text-xl font-semibold text-white" style="font-family: 'Caveat Brush', cursive;">
                                Cara <span class="cart-text-accent">Pembayaran</span>
                            </h3>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="p-4 border border-gray-600 rounded-lg bg-white/5">
                                    <flux:radio wire:model="form.payment_method" value="credit-card" checked>
                                        <div class="ml-3">
                                            <div class="font-medium text-white">Kad Kredit</div>
                                            <div class="text-sm text-gray-400">Bayar dengan kad kredit anda</div>
                                        </div>
                                    </flux:radio>
                                </div>

                                <div class="p-4 border border-gray-600 rounded-lg bg-white/5">
                                    <flux:radio wire:model="form.payment_method" value="online-banking">
                                        <div class="ml-3">
                                            <div class="font-medium text-white">Online Banking</div>
                                            <div class="text-sm text-gray-400">FPX / Internet Banking</div>
                                        </div>
                                    </flux:radio>
                                </div>

                                <div class="p-4 border border-gray-600 rounded-lg bg-white/5">
                                    <flux:radio wire:model="form.payment_method" value="ewallet">
                                        <div class="ml-3">
                                            <div class="font-medium text-white">E-Wallet</div>
                                            <div class="text-sm text-gray-400">GrabPay, Touch 'n Go</div>
                                        </div>
                                    </flux:radio>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Methods -->
                        <div class="cart-card p-6 space-y-4">
                            <h3 class="text-xl font-semibold text-white" style="font-family: 'Caveat Brush', cursive;">
                                Cara <span class="cart-text-accent">Penghantaran</span>
                            </h3>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="p-4 border border-gray-600 rounded-lg bg-white/5">
                                    <flux:radio wire:model="form.delivery_method" value="standard" checked>
                                        <div class="ml-3">
                                            <div class="font-medium text-white">Penghantaran Percuma</div>
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
                        </div>
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
                                    <dd class="text-base font-medium text-white">
                                        @if($this->getShipping() > 0)
                                            {{ $this->formatPrice($this->getShipping()) }}
                                        @else
                                            <span class="text-green-400">PERCUMA</span>
                                        @endif
                                    </dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-base font-normal text-gray-400">SST (10%)</dt>
                                    <dd class="text-base font-medium text-white">{{ $this->formatPrice($this->getTax()) }}</dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4 py-3 border-t border-gray-600 pt-3">
                                    <dt class="text-base font-bold text-white">Jumlah Keseluruhan</dt>
                                    <dd class="text-base font-bold cart-text-accent">{{ $this->formatPrice($this->getTotal()) }}</dd>
                                </dl>
                            </div>

                            <div class="space-y-3 mt-6">
                                <flux:button type="submit" variant="primary" class="w-full cart-button-primary py-3">
                                    <flux:icon.credit-card class="h-5 w-5 mr-2" />
                                    Teruskan ke Pembayaran
                                </flux:button>

                                <p class="text-sm text-gray-400 text-center">
                                    Maklumat peribadi anda selamat dan tidak akan dikongsi dengan pihak ketiga.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
