<?php

declare(strict_types=1);

namespace App\Livewire;

use AIArmada\Cart\Facades\Cart as CartFacade;
use AIArmada\Products\Enums\ProductStatus;
use AIArmada\Vouchers\Facades\Voucher;
use App\Models\Product;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
final class Cart extends Component
{
    /** @var array<array<string, mixed>> */
    public array $cartItems = [];

    public string $voucherCode = '';

    public string $voucherError = '';

    public bool $isApplyingVoucher = false;

    /** @var array<string, mixed>|null */
    public ?array $appliedVoucher = null;

    public function mount(): void
    {
        $this->loadCartItems();
        $this->loadAppliedVoucher();
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        $this->loadCartItems();
        $this->loadAppliedVoucher();
    }

    /**
     * Get suggested products (lazy-loaded computed property).
     *
     * @return \Illuminate\Support\Collection<int, Product>
     */
    public function getSuggestedProductsProperty()
    {
        $cartProductIds = collect($this->cartItems)->pluck('id')->toArray();

        return Product::where('status', ProductStatus::Active)
            ->whereNotIn('id', $cartProductIds)
            ->inRandomOrder()
            ->limit(3)
            ->get();
    }

    public function loadCartItems(): void
    {
        try {
            $cartContents = CartFacade::getItems();

            $this->cartItems = $cartContents->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                    'price' => $item->getPrice()->format(),
                    'subtotal' => $item->getSubtotal()->format(),
                    'quantity' => (int) $item->quantity,
                    'slug' => $item->attributes->get('slug', 'cara-bercinta'),
                ];
            })->values()->toArray();

        } catch (Exception $e) {
            $this->cartItems = [];
            Log::error('Cart loading error: '.$e->getMessage());
        }
    }

    public function loadAppliedVoucher(): void
    {
        try {
            $cart = CartFacade::getCurrentCart();
            /** @var array<string> $voucherCodes */
            $voucherCodes = $cart->getMetadata('voucher_codes', []);

            if (! empty($voucherCodes)) {
                $code = $voucherCodes[0];
                $voucher = Voucher::find($code);

                if ($voucher) {
                    $this->appliedVoucher = [
                        'code' => $voucher->code,
                        'description' => $voucher->description,
                        'type' => $voucher->type->value,
                        'value' => $voucher->value,
                    ];
                }
            } else {
                $this->appliedVoucher = null;
            }
        } catch (Exception $e) {
            $this->appliedVoucher = null;
            Log::error('Failed to load applied voucher: '.$e->getMessage());
        }
    }

    public function updateQuantity(string $itemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($itemId);

            return;
        }

        // Use absolute quantity update by passing as array
        CartFacade::update($itemId, ['quantity' => ['value' => $quantity]]);

        // If cart is now empty, delete the cart from storage (this also removes conditions)
        if (CartFacade::isEmpty()) {
            CartFacade::clear();
        }

        $this->dispatch('cart-updated'); // Event handler will reload cart items
    }

    /**
     * Increment quantity of a cart item.
     */
    public function incrementQuantity(string $itemId): void
    {
        $item = CartFacade::get($itemId);
        if ($item) {
            $newQuantity = $item->quantity + 1;
            CartFacade::update($itemId, ['quantity' => ['value' => $newQuantity]]);
            $this->dispatch('cart-updated'); // Event handler will reload cart items
        }
    }

    public function decrementQuantity(string $itemId): void
    {
        $item = CartFacade::get($itemId);
        if ($item) {
            $newQuantity = $item->quantity - 1;

            // If quantity would become 0 or less, remove the item instead
            if ($newQuantity <= 0) {
                $this->removeItem($itemId);

                return;
            }

            CartFacade::update($itemId, ['quantity' => ['value' => $newQuantity]]);

            // If cart is now empty, delete the cart from storage (this also removes conditions)
            if (CartFacade::isEmpty()) {
                CartFacade::clear();
            }

            $this->dispatch('cart-updated'); // Event handler will reload cart items
        }
    }

    public function removeItem(string $itemId): void
    {
        $item = CartFacade::get($itemId);
        $itemName = $item ? $item->name : 'Item';
        CartFacade::remove($itemId);

        // If cart is now empty, delete the cart from storage (this also removes conditions)
        if (CartFacade::isEmpty()) {
            CartFacade::clear();
        }

        $this->dispatch('cart-updated'); // Event handler will reload cart items
        Notification::make()
            ->title('Buku Dikeluarkan!')
            ->body("'{$itemName}' telah dikeluarkan.")
            ->success()
            ->icon('heroicon-o-trash')
            ->iconColor('success')
            ->send();
    }

    public function applyVoucher(): void
    {
        $this->voucherError = '';

        if (empty($this->voucherCode)) {
            $this->voucherError = 'Sila masukkan kod voucher.';

            return;
        }

        try {
            $code = mb_strtoupper(mb_trim($this->voucherCode));
            $cart = CartFacade::getCurrentCart();

            // Check if already applied
            /** @var array<string> $existingCodes */
            $existingCodes = $cart->getMetadata('voucher_codes', []);
            if (in_array($code, $existingCodes)) {
                $this->voucherError = 'Voucher ini sudah digunakan.';

                return;
            }

            // Validate voucher
            $validationResult = Voucher::validate($code, $cart);

            if (! $validationResult->isValid) {
                $this->voucherError = $validationResult->message ?? 'Kod voucher tidak sah.';

                return;
            }

            // Store voucher code in cart metadata (auto-persists)
            $cart->setMetadata('voucher_codes', [$code]);

            $voucher = Voucher::find($code);

            $this->appliedVoucher = [
                'code' => $voucher->code ?? $code,
                'description' => $voucher->description ?? 'Diskaun',
                'type' => $voucher->type->value ?? 'fixed',
                'value' => $voucher->value ?? 0,
            ];

            $this->voucherCode = '';
            $this->dispatch('cart-updated'); // Event handler will reload cart items and applied voucher

            Notification::make()
                ->title('Voucher Berjaya!')
                ->body("Kod voucher '{$code}' telah digunakan.")
                ->success()
                ->icon('heroicon-o-ticket')
                ->iconColor('success')
                ->duration(4000)
                ->send();

        } catch (Exception $e) {
            Log::error('Voucher application error: '.$e->getMessage());
            $this->voucherError = 'Gagal memproses voucher. Sila cuba lagi.';
        }
    }

    public function removeVoucher(): void
    {
        try {
            $cart = CartFacade::getCurrentCart();
            $cart->setMetadata('voucher_codes', []);

            $removedCode = $this->appliedVoucher['code'] ?? 'Voucher';
            $this->appliedVoucher = null;
            $this->dispatch('cart-updated'); // Event handler will reload cart items and applied voucher

            Notification::make()
                ->title('Voucher Dikeluarkan')
                ->body("Kod voucher '{$removedCode}' telah dikeluarkan.")
                ->info()
                ->icon('heroicon-o-x-circle')
                ->iconColor('info')
                ->send();

        } catch (Exception $e) {
            Log::error('Voucher removal error: '.$e->getMessage());
            Notification::make()
                ->title('Ralat')
                ->body('Gagal mengeluarkan voucher.')
                ->danger()
                ->send();
        }
    }

    public function addToCart(string|int|Product $productId, int $quantity = 1): void
    {
        // Handle both Product object and product ID (string UUID or int)
        if ($productId instanceof Product) {
            $product = $productId;
        } else {
            $product = Product::findOrFail($productId);
        }

        // Add item to cart - price is already in cents (integer)
        CartFacade::add(
            id: (string) $product->id,
            name: $product->name,
            price: $product->price, // Keep as cents (integer)
            quantity: $quantity,
            attributes: [
                'slug' => $product->slug,
                'image' => $product->getFirstMediaUrl() ?: null,
                'weight' => $product->getShippingWeight(),
            ]
        );

        $this->loadCartItems();
        $this->loadAppliedVoucher();

        // Dispatch consistent event for UI feedback (event handler will reload cart items)
        $this->dispatch('cart-updated', [
            'product' => $product->name,
            'quantity' => $quantity,
        ]);

        // Show notification
        Notification::make()
            ->title('Buku Ditambah!')
            ->body("'{$product->name}' telah ditambah ke troli.")
            ->success()
            ->icon('heroicon-o-shopping-cart')
            ->iconColor('success')
            ->send();
    }

    public function getSubtotal(): \Akaunting\Money\Money
    {
        return CartFacade::subtotal(); // Return Money object directly for formatting
    }

    public function getSavings(): \Akaunting\Money\Money
    {
        return CartFacade::savings(); // Returns Money object with calculated savings from conditions
    }

    public function getShipping(): \Akaunting\Money\Money
    {
        // Check if there's a shipping condition applied to the cart
        $shippingCondition = CartFacade::getCondition('shipping');

        if ($shippingCondition) {
            $currency = config('cart.money.default_currency', 'MYR');

            return \Akaunting\Money\Money::{$currency}((int) $shippingCondition->getValue());
        }

        // Return zero if no shipping condition exists
        $currency = config('cart.money.default_currency', 'MYR');

        return \Akaunting\Money\Money::{$currency}(0);
    }

    public function getTotal(): \Akaunting\Money\Money
    {
        return CartFacade::total(); // Cart total already includes all conditions (including shipping)
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.cart');
    }
}
