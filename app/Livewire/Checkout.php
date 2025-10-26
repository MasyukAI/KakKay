<?php

declare(strict_types=1);

namespace App\Livewire;

use AIArmada\Cart\Facades\Cart as CartFacade;
use Akaunting\Money\Money;
use App\Data\StateData;
use App\Services\CheckoutService;
use Exception;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

final class Checkout extends Component implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var array<array<string, mixed>> */
    public array $cartItems = [];

    public string $selectedCountryCode = '+60';

    // public array $availablePaymentMethods = [];

    public string $selectedPaymentGroup = 'card';

    // Cart-Intent validation properties
    public bool $cartChangedSinceIntent = false;

    /** @var array<string, mixed>|null */
    public ?array $activePaymentIntent = null;

    public bool $showCartChangeWarning = false;

    public function mount(): void
    {
        try {
            // Clear any old session data if cart has changed
            $cartItems = CartFacade::getItems();

            // Check if cart is empty and redirect
            if ($cartItems->isEmpty()) {
                $this->redirect(route('cart'));

                return;
            }

            // Get current cart version from database for change detection
            $currentCartVersion = CartFacade::getCurrentCart()->getVersion();
            $sessionCartVersion = session('cart_version');

            // Check for cart changes and active payment intents
            $checkoutService = app(CheckoutService::class);
            $cartStatus = $checkoutService->getCartChangeStatus();

            $this->activePaymentIntent = $cartStatus['intent'];
            $this->cartChangedSinceIntent = $cartStatus['cart_changed'];
            $this->showCartChangeWarning = $cartStatus['cart_changed'] && $cartStatus['has_active_intent'];

            // Store current cart version in session
            session(['cart_version' => $currentCartVersion]);

            if ($sessionCartVersion && $sessionCartVersion !== $currentCartVersion) {
                // Cart has changed, clear old cart version from session
                session()->forget('cart_version');
            }

            $this->loadCartItems();
            // $this->loadPaymentMethods();

            // Initialize form data with default values to prevent Livewire entangle errors
            // This ensures nested properties like data.phone and data.state exist before Alpine tries to bind
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
                'street1' => '',
                'street2' => '',
            ];

            // Initialize form with the pre-populated data
            $this->form->fill($this->data); /** @phpstan-ignore-line property.notFound */
        } catch (Exception $e) {
            // Log error but don't crash - might be in testing environment
            Log::warning('Checkout mount error: '.$e->getMessage());

            // Initialize with minimal data
            $this->cartItems = [];
            // $this->availablePaymentMethods = [];
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
                'street1' => '',
                'street2' => '',
            ];
        }
    }

    public function form(Form $form): Form
    {
        $states = StateData::getStatesOptions();

        return $form
            ->components([
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

                                Select::make('country')
                                    ->label('Negara')
                                    ->options(['Malaysia' => 'Malaysia'])
                                    ->default('Malaysia')
                                    ->columnStart(1)
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(), // Include disabled field in form data

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

                        TextInput::make('street1')
                            ->label('Alamat Baris 1')
                            ->required()
                            ->placeholder('Nombor rumah, nama jalan')
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'checkout-sm']),

                        TextInput::make('street2')
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
        $formData = $this->form->getState(); /** @phpstan-ignore-line property.notFound */
        try {
            $checkoutService = app(CheckoutService::class);

            // Prepare customer data - send only required email to CHIP
            $customerData = [
                // Required for CHIP API
                'email' => $formData['email'],

                // Required for User creation/lookup
                'name' => $formData['name'],
                'phone' => $formData['phone'],

                // Required for Address creation (following database schema)
                'street1' => $formData['street1'],
                'street2' => $formData['street2'] ?? null,
                'city' => $formData['city'] ?? null,
                'state' => $formData['state'],
                'country' => $formData['country'],
                'postcode' => (string) $formData['postcode'], // Ensure string for leading zeros
                'company' => $formData['company'] ?? null,

                // Optional fields - only include if provided
                'type' => 'shipping', // Address type for database
            ];

            // Process checkout using cart metadata-based payment intents
            $result = $checkoutService->processCheckout($customerData);

            if ($result['success']) {
                // Redirect to CHIP checkout
                $this->redirect($result['checkout_url']);

                return;
            }
            session()->flash('error', 'Gagal memproses pembayaran: '.$result['error']);

        } catch (Exception $e) {
            Log::error('Checkout processing failed', [
                'error' => $e->getMessage(),
                'form_data' => $formData ?? [],
                'cart_items' => $this->cartItems,
            ]);

            session()->flash('error', 'Terjadi ralat semasa memproses pesanan. Sila cuba lagi.');
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.checkout', [
            'cartQuantity' => CartFacade::getTotalQuantity(),
            'showCartChangeWarning' => $this->showCartChangeWarning,
            'activePaymentIntent' => $this->activePaymentIntent,
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
}
