<?php

declare(strict_types=1);

namespace App\Livewire;

use AIArmada\Cart\Facades\Cart as CartFacade;
use AIArmada\Checkout\Facades\Checkout as CheckoutFacade;
use AIArmada\Checkout\Models\CheckoutSession;
use AIArmada\Checkout\States\Completed;
use Akaunting\Money\Money;
use App\Data\StateData;
use Exception;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

final class Checkout extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var array<array<string, mixed>> */
    public array $cartItems = [];

    public string $selectedCountryCode = '+60';

    public string $selectedPaymentGroup = 'card';

    public ?string $checkoutSessionId = null;

    public bool $hasActiveSession = false;

    public function mount(): void
    {
        try {
            $cartItems = CartFacade::getItems();

            if ($cartItems->isEmpty()) {
                $this->redirect(route('cart'));

                return;
            }

            $this->checkoutSessionId = session('checkout_session_id');
            $this->hasActiveSession = $this->checkoutSessionId !== null
                && $this->getActiveCheckoutSession() !== null;

            $this->loadCartItems();

            $this->data = [
                'name' => '',
                'company' => '',
                'email' => '',
                'email_confirmation' => '',
                'phone' => '',
                'country' => 'Malaysia',
                'state' => '',
                'city' => '',
                'postcode' => '',
                'line1' => '',
                'line2' => '',
            ];

            if ($this->hasActiveSession) {
                $session = $this->getActiveCheckoutSession();
                if ($session !== null) {
                    $billingData = $session->billing_data ?? [];
                    $shippingData = $session->shipping_data ?? [];
                    $addressData = array_merge($billingData, $shippingData);

                    $this->data = array_merge($this->data, array_filter($addressData));
                }
            }

            $this->form->fill($this->data); /** @phpstan-ignore-line property.notFound */
        } catch (Exception $e) {
            Log::warning('Checkout mount error: '.$e->getMessage());

            $this->cartItems = [];
            $this->data = [
                'name' => '',
                'company' => '',
                'email' => '',
                'email_confirmation' => '',
                'phone' => '',
                'country' => 'Malaysia',
                'state' => '',
                'city' => '',
                'postcode' => '',
                'line1' => '',
                'line2' => '',
            ];
        }
    }

    public function form(Schema $form): Schema
    {
        $states = StateData::getStatesOptions();

        return $form
            ->components([/** @phpstan-ignore-next-line class.notFound */
                Section::make('Maklumat Penghantaran')
                    // ->icon('heroicon-o-truck')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Penuh')
                                    ->required()
                                    ->placeholder('Nama penuh')
                                    ->maxLength(255)
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                TextInput::make('company')
                                    ->label('Nama Syarikat')
                                    ->placeholder('Nama syarikat (jika berkenaan)')
                                    ->maxLength(255)
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->live(onBlur: true)
                                    ->required()
                                    ->placeholder('nama@email.com')
                                    ->maxLength(255)
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                TextInput::make('email_confirmation')
                                    ->label('Sahkan Alamat Email')
                                    ->email()
                                    ->live(onBlur: true)
                                    ->required()
                                    ->same('email')
                                    ->placeholder('Sila masukkan semula alamat email')
                                    ->maxLength(255)
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                PhoneInput::make('phone')
                                    ->defaultCountry('MY')
                                    ->initialCountry('MY')
                                    ->label('Nombor Telefon')
                                    ->required()
                                    ->placeholder('Nombor telefon')
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                TextInput::make('country')
                                    ->label('Negara')
                                    ->default('Malaysia')
                                    ->columnStart(1)
                                    ->disabled()
                                    ->dehydrated()
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'MY', 'Malaysia', null => 'Malaysia',
                                        default => $state,
                                    })
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                Select::make('state')
                                    ->label('Negeri')
                                    ->options($states)
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Pilih negeri')
                                    ->searchPrompt('Taip untuk mencari negeri')
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                TextInput::make('city')
                                    ->label('Bandar')
                                    ->required()
                                    ->placeholder('Contoh: Kuala Lumpur, Subang Jaya, Johor Bahru')
                                    ->maxLength(255)
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                TextInput::make('postcode')
                                    ->required()
                                    ->inputMode('integer')
                                    ->label('Poskod')
                                    ->placeholder('Contoh: 40000')
                                    ->length(5)
                                    ->mask('99999')
                                    ->extraAttributes(['class' => 'checkout-sm']),

                            ]),

                        TextInput::make('line1')
                            ->label('Alamat Baris 1')
                            ->required()
                            ->placeholder('Nombor rumah, nama jalan')
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'checkout-sm']),

                        TextInput::make('line2')
                            ->label('Alamat Baris 2')
                            ->placeholder('Taman, kawasan, dll')
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'checkout-sm']),
                    ]),

                // Section::make('Kaedah Pembayaran')
                //     ->schema([
                //         Grid::make(2)
                //             ->schema([
                //                 Select::make('payment_group')
                //                     ->label('Kategori Pembayaran')
                //                     ->options(fn () => $this->getPaymentGroupOptions())
                //                     ->required()
                //                     ->live()
                //                     ->extraAttributes(['class' => 'checkout-select'])
                //                     ->afterStateHydrated(function (?string $state, callable $set): void {
                //                         $defaultGroup = $state ?? $this->selectedPaymentGroup ?? $this->determineDefaultGroup();
                //                         if ($defaultGroup) {
                //                             $set('payment_group', $defaultGroup);
                //                             $this->selectPaymentGroup($defaultGroup);
                //                         }
                //                     })
                //                     ->afterStateUpdated(function (?string $state, callable $set): void {
                //                         $this->selectPaymentGroup($state);

                //                         $defaultMethod = $this->determineDefaultMethod($state);
                //                         $set('payment_method', $defaultMethod);

                //                         if ($defaultMethod) {
                //                             $this->selectPaymentMethod($defaultMethod);
                //                         }
                //                     }),
                //                 Select::make('payment_method')
                //                     ->label('Kaedah Pembayaran')
                //                     ->options(fn (callable $get) => $this->getPaymentMethodOptions($get('payment_group')))
                //                     ->required()
                //                     ->searchable()
                //                     ->live()
                //                     ->extraAttributes(['class' => 'checkout-select'])
                //                     ->afterStateHydrated(function (?string $state): void {
                //                         if ($state) {
                //                             $this->selectPaymentMethod($state);
                //                         }
                //                     })
                //                     ->afterStateUpdated(function (?string $state): void {
                //                         $this->selectPaymentMethod($state);
                //                     }),
                //             ]),
                //     ]),
            ])
            ->statePath('data');
    }

    public function loadCartItems(): void
    {
        try {
            // Middleware handles cart instance switching
            $cartContents = CartFacade::getItems();

            if ($cartContents->isEmpty()) {
                $this->cartItems = [];
                $this->redirect(route('cart'));

                return;
            }

            $this->cartItems = $cartContents->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                    'price' => (int) $item->getRawPrice(), // Already in cents
                    'quantity' => (int) $item->quantity,
                    'attributes' => $item->attributes->toArray(),
                ];
            })->values()->toArray();
        } catch (Exception $e) {
            Log::error('Checkout loading error: '.$e->getMessage());
            $this->cartItems = [];
            $this->redirect(route('cart'));
        }
    }

    // public function loadPaymentMethods(): void
    // {
    //     try {
    //         $paymentService = app(\App\Services\PaymentService::class);
    //         $this->availablePaymentMethods = $paymentService->getAvailablePaymentMethods();
    //         // No need to set default payment methods - let CHIP gateway handle selection
    //     } catch (Exception $e) {
    //         Log::error('Failed to load payment methods: '.$e->getMessage());
    //         // Use fallback payment methods for reference (not used in checkout)
    //         $this->availablePaymentMethods = [
    //             [
    //                 'id' => 'fpx_b2c',
    //                 'name' => 'FPX Online Banking',
    //                 'description' => 'Bayar dengan Internet Banking Malaysia',
    //                 'icon' => 'building-office',
    //                 'group' => 'banking',
    //             ],
    //             [
    //                 'id' => 'visa',
    //                 'name' => 'Kad Kredit/Debit',
    //                 'description' => 'Visa, Mastercard',
    //                 'icon' => 'credit-card',
    //                 'group' => 'card',
    //             ],
    //         ];
    //     }
    // }

    // public function selectPaymentGroup(?string $group): void
    // {
    //     $group = $group ?: $this->determineDefaultGroup();
    //     $this->selectedPaymentGroup = $group ?? '';
    //     $this->data['payment_group'] = $group;

    //     if (! $group) {
    //         $this->data['payment_method_whitelist'] = [];

    //         return;
    //     }

    //     $groupMethods = collect($this->availablePaymentMethods)
    //         ->where('group', $group)
    //         ->pluck('id')
    //         ->toArray();

    //     $this->data['payment_method_whitelist'] = $groupMethods;
    // }

    // public function selectPaymentMethod(?string $methodId): void
    // {
    //     if (empty($methodId)) {
    //         $this->data['payment_method_whitelist'] = [];

    //         return;
    //     }

    //     $this->data['payment_method'] = $methodId;
    //     $this->data['payment_method_whitelist'] = [$methodId];
    // }

    #[Computed]
    public function getSubtotal(): Money
    {
        return CartFacade::subtotal(); // Return Money object directly for formatting
    }

    #[Computed]
    public function getSavings(): Money
    {
        return CartFacade::savings(); // Returns Money object with calculated savings from conditions
    }

    #[Computed]
    public function getTotal(): Money
    {
        return CartFacade::total(); // Cart total already includes all conditions (including shipping)
    }

    #[Computed]
    public function getShipping(): Money
    {
        // Check if there's a shipping condition applied to the cart
        $shippingCondition = CartFacade::getCondition('shipping');

        if ($shippingCondition) {
            $currency = config('cart.money.default_currency', 'MYR');

            return Money::{$currency}((int) $shippingCondition->getValue());
        }

        // Return zero if no shipping condition exists
        $currency = config('cart.money.default_currency', 'MYR');

        return Money::{$currency}(0);
    }

    public function applyVoucher(): void
    {
        $voucherCode = $this->data['voucher_code'] ?? '';
        if (! empty($voucherCode)) {
            // Voucher logic here
            session()->flash('message', 'Kod voucher akan disemak...');
        }
    }

    public function getGroupDisplayName(string $group): string
    {
        $groupNames = [
            'banking' => 'Online Banking',
            'card' => 'Kad Kredit/Debit',
            'ewallet' => 'E-Wallet',
            'qr' => 'QR Payment',
            'bnpl' => 'Beli Sekarang, Bayar Kemudian',
            'other' => 'Lain-lain',
        ];

        return $groupNames[$group] ?? ucfirst($group);
    }

    public function submitCheckout(): void
    {
        $this->form->validate(); /** @phpstan-ignore-line property.notFound */
        $formData = $this->form->getState(); /** @phpstan-ignore-line property.notFound */
        try {
            $cart = CartFacade::getCurrentCart();
            $cartId = (string) $cart->getId();

            $billingData = [
                'email' => $formData['email'],
                'name' => $formData['name'],
                'phone' => $formData['phone'],
                'company' => $formData['company'] ?? null,
            ];

            // Convert country name to ISO code for database storage
            $countryCode = match ($formData['country']) {
                'Malaysia' => 'MY',
                default => 'MY',
            };

            $shippingData = [
                'name' => $formData['name'],
                'phone' => $formData['phone'],
                'line1' => $formData['line1'],
                'line2' => $formData['line2'] ?? null,
                'city' => $formData['city'] ?? null,
                'state' => $formData['state'],
                'country' => $countryCode,
                'postcode' => (string) $formData['postcode'],
                'company' => $formData['company'] ?? null,
            ];

            $session = CheckoutFacade::startCheckout($cartId);

            // Transfer voucher codes from cart to checkout session
            /** @var array<string> $voucherCodes */
            $voucherCodes = $cart->getMetadata('voucher_codes', []);

            // Calculate totals with proper discount extraction
            $originalSubtotal = (int) $cart->subtotalWithoutConditions()->getAmount();
            $subtotalAfterDiscount = (int) $cart->subtotal()->getAmount();
            $discountTotal = max(0, $originalSubtotal - $subtotalAfterDiscount);
            $shippingTotal = 0; // Free shipping for now
            $grandTotal = $originalSubtotal - $discountTotal + $shippingTotal;

            $session->update([
                'billing_data' => $billingData,
                'shipping_data' => $shippingData,
                'subtotal' => $originalSubtotal,
                'discount_total' => $discountTotal,
                'shipping_total' => $shippingTotal,
                'grand_total' => $grandTotal,
                'discount_data' => [
                    'voucher_codes' => $voucherCodes,
                ],
            ]);

            session(['checkout_session_id' => $session->id]);

            $result = CheckoutFacade::processCheckout($session);

            if ($result->success) {
                $this->redirect(route('checkout.success', ['session' => $session->id]));

                return;
            }

            if ($result->requiresRedirect()) {
                $this->redirect($result->redirectUrl);

                return;
            }

            $errorMessage = $result->message ?? 'Gagal memproses pembayaran';
            session()->flash('error', $errorMessage);

            Log::warning('Checkout failed', [
                'session_id' => $session->id,
                'errors' => $result->errors,
                'message' => $result->message,
            ]);

        } catch (Exception $e) {
            Log::error('Checkout processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $formData ?? [],
            ]);

            session()->flash('error', 'Terjadi ralat semasa memproses pesanan. Sila cuba lagi.');
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.checkout', [
            'cartQuantity' => CartFacade::getTotalQuantity(),
            'hasActiveSession' => $this->hasActiveSession,
        ])->layout('components.layouts.app');
    }

    // #[Computed]
    // public function getPaymentMethodsByGroup(): array
    // {
    //     $grouped = [];
    //     foreach ($this->availablePaymentMethods as $method) {
    //         $grouped[$method['group']][] = $method;
    //     }

    //     return $grouped;
    // }

    /**
     * @return array<string, array<array<string, mixed>>>
     */
    protected function getPaymentMethodsByGroup(): array
    {
        // Payment methods functionality is currently disabled
        return [];
    }

    /**
     * @return array<string, string>
     */
    protected function getPaymentGroupOptions(): array
    {
        return collect($this->getPaymentMethodsByGroup())
            ->mapWithKeys(fn ($methods, $group) => [
                $group => $this->getGroupDisplayName($group),
            ])
            ->toArray();
    }

    /**
     * @return array<string, string>
     */
    protected function getPaymentMethodOptions(?string $group): array
    {
        $group = $group ?: $this->determineDefaultGroup();

        if (! $group) {
            return [];
        }

        return collect($this->getPaymentMethodsByGroup()[$group] ?? [])
            ->mapWithKeys(fn ($method) => [
                $method['id'] => $method['name'],
            ])->toArray();
    }

    protected function determineDefaultGroup(): ?string
    {
        return array_key_first($this->getPaymentMethodsByGroup()) ?: null;
    }

    protected function determineDefaultMethod(?string $group): ?string
    {
        $group = $group ?: $this->determineDefaultGroup();

        if (! $group) {
            return null;
        }

        $methods = $this->getPaymentMethodsByGroup()[$group] ?? [];

        return $methods[0]['id'] ?? null;
    }

    private function getActiveCheckoutSession(): ?CheckoutSession
    {
        if ($this->checkoutSessionId === null) {
            return null;
        }

        try {
            $session = CheckoutFacade::resumeCheckout($this->checkoutSessionId);

            if ($session->status instanceof Completed || $session->isExpired()) {
                session()->forget('checkout_session_id');

                return null;
            }

            return $session;
        } catch (Exception) {
            session()->forget('checkout_session_id');

            return null;
        }
    }
}
