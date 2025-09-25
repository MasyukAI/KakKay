<?php

namespace App\Livewire;

use Akaunting\Money\Money;
use App\Data\DistrictData;
use App\Data\StateData;
use App\Models\District;
use App\Services\CheckoutService;
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
use MasyukAI\Cart\Facades\Cart as CartFacade;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class Checkout extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public array $cartItems = [];

    public string $selectedCountryCode = '+60';

    public array $availablePaymentMethods = [];

    public string $selectedPaymentGroup = 'card';

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

            $currentCartHash = md5(serialize($cartItems->toArray()));
            $sessionCartHash = session('cart_hash');

            if ($sessionCartHash && $sessionCartHash !== $currentCartHash) {
                // Cart has changed, clear old purchase session
                session()->forget(['chip_purchase_id', 'checkout_data', 'cart_hash']);
            }

            // Store current cart hash
            session(['cart_hash' => $currentCartHash]);

            $this->loadCartItems();
            $this->loadPaymentMethods();

            // Initialize form with default values
            $this->form->fill();

        } catch (\Exception $e) {
            // Log error but don't crash - might be in testing environment
            Log::warning('Checkout mount error: '.$e->getMessage());

            // Initialize with minimal data
            $this->cartItems = [];
            $this->availablePaymentMethods = [];
        }
    }

    public function form(Schema $schema): Schema
    {
        $states = StateData::getStatesOptions();

        return $schema
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
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('district', null);
                                        $set('city', null);
                                    })
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                Select::make('district')
                                    ->label('Daerah')
                                    ->searchPrompt('Taip untuk mencari daerah')
                                    ->options(function (callable $get) {
                                        $state = $get('state');
                                        if (! $state) {
                                            return [];
                                        }

                                        return DistrictData::getByStateOptions($state);
                                    })
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Pilih daerah')
                                    ->disabled(fn (callable $get) => ! $get('state'))
                                    ->live()
                                    ->afterStateUpdated(function ($district, callable $set) {
                                        $set('city', null);
                                    })
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                Select::make('city')
                                    ->label('Mukim, Bandar, Pekan')
                                    ->searchPrompt('Taip untuk mencari mukim, bandar, pekan')
                                    ->options(function (callable $get) {
                                        $district = $get('district');
                                        if (! $district) {
                                            return [];
                                        }

                                        // Support single or multiple districts
                                        $districts = is_array($district) ? $district : [$district];
                                        return \App\Data\SubdistrictData::getByDistrictsOptions($districts);
                                    })
                                    ->required(fn (callable $get) => ! empty($get('district')))
                                    ->searchable()
                                    ->placeholder('Pilih mukim, bandar, pekan')
                                    ->disabled(fn (callable $get) => ! $get('district'))
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                TextInput::make('postcode')
                                    ->required()
                                    ->integer()
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

    public function loadCartItems()
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
        } catch (\Exception $e) {
            Log::error('Checkout loading error: '.$e->getMessage());
            $this->cartItems = [];
            $this->redirect(route('cart'));
        }
    }

    public function loadPaymentMethods(): void
    {
        try {
            $paymentService = app(\App\Services\PaymentService::class);
            $this->availablePaymentMethods = $paymentService->getAvailablePaymentMethods();
            // No need to set default payment methods - let CHIP gateway handle selection
        } catch (\Exception $e) {
            Log::error('Failed to load payment methods: '.$e->getMessage());
            // Use fallback payment methods for reference (not used in checkout)
            $this->availablePaymentMethods = [
                [
                    'id' => 'fpx_b2c',
                    'name' => 'FPX Online Banking',
                    'description' => 'Bayar dengan Internet Banking Malaysia',
                    'icon' => 'building-office',
                    'group' => 'banking',
                ],
                [
                    'id' => 'visa',
                    'name' => 'Kad Kredit/Debit',
                    'description' => 'Visa, Mastercard',
                    'icon' => 'credit-card',
                    'group' => 'card',
                ],
            ];
        }
    }

    public function selectPaymentGroup(?string $group): void
    {
        $group = $group ?: $this->determineDefaultGroup();
        $this->selectedPaymentGroup = $group ?? '';
        $this->data['payment_group'] = $group;

        if (! $group) {
            $this->data['payment_method_whitelist'] = [];

            return;
        }

        $groupMethods = collect($this->availablePaymentMethods)
            ->where('group', $group)
            ->pluck('id')
            ->toArray();

        $this->data['payment_method_whitelist'] = $groupMethods;
    }

    public function selectPaymentMethod(?string $methodId): void
    {
        if (empty($methodId)) {
            $this->data['payment_method_whitelist'] = [];

            return;
        }

        $this->data['payment_method'] = $methodId;
        $this->data['payment_method_whitelist'] = [$methodId];
    }

    #[Computed]
    public function getSubtotal(): \Akaunting\Money\Money
    {
        return CartFacade::subtotal(); // Return Money object directly for formatting
    }

    #[Computed]
    public function getSavings(): int
    {
        return 0; // No savings for now
    }

    #[Computed]
    public function getTotal(): \Akaunting\Money\Money
    {
        $cartTotal = CartFacade::total();
        $shipping = $this->getShippingMoney();

        return $cartTotal->add($shipping);
    }

    public function getShippingMoney(): \Akaunting\Money\Money
    {
        $deliveryMethod = $this->data['delivery_method'] ?? 'standard';
        $currency = config('cart.money.default_currency', 'MYR');

        $shippingAmount = match ($deliveryMethod) {
            'express' => 4900, // RM49 in cents
            'fast' => 1500,    // RM15 in cents
            default => 500,    // RM5 in cents
        };

        return \Akaunting\Money\Money::{$currency}($shippingAmount);
    }

    public function applyVoucher(): void
    {
        $voucherCode = $this->data['voucher_code'] ?? '';
        if (! empty($voucherCode)) {
            // Voucher logic here
            session()->flash('message', 'Kod voucher akan disemak...');
        }
    }

    #[Computed]
    public function getPaymentMethodsByGroup(): array
    {
        $grouped = [];
        foreach ($this->availablePaymentMethods as $method) {
            $grouped[$method['group']][] = $method;
        }

        return $grouped;
    }

    protected function getPaymentGroupOptions(): array
    {
        return collect($this->getPaymentMethodsByGroup())
            ->mapWithKeys(fn ($methods, $group) => [
                $group => $this->getGroupDisplayName($group),
            ])
            ->toArray();
    }

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

    public function submitCheckout()
    {
        $formData = $this->form->getState();

        try {
            $checkoutService = app(CheckoutService::class);

            // Check for duplicate orders using session-based tracking
            $existingPurchaseForSession = $checkoutService->getPurchaseStatus(session()->getId());
            if ($existingPurchaseForSession) {
                $this->addError('data.email', 'Pesanan telah wujud untuk keranjang ini. Sila lengkapkan pembayaran atau muat semula halaman.');

                return;
            }

            // Check if there's already a pending order for this cart
            $existingPurchaseId = session('chip_purchase_id');
            if ($existingPurchaseId) {
                // Check if the existing purchase is still valid and pending
                $existingPurchase = $checkoutService->getPurchaseStatus($existingPurchaseId);

                if ($existingPurchase && in_array($existingPurchase['status'], ['pending', 'created'])) {
                    // Redirect to existing payment instead of creating new one
                    return $this->redirect($existingPurchase['checkout_url']);
                }

                // If purchase expired or failed, clear session and continue with new order
                session()->forget(['chip_purchase_id', 'checkout_data']);
            }

            $checkoutService = app(CheckoutService::class);

            // Prepare customer data aligned with addresses table columns
            $customerData = [
                // Core address fields (matching addresses table)
                'name' => $formData['name'],
                'company' => $formData['company'] ?? '',
                'street1' => $formData['street1'],
                'street2' => $formData['street2'] ?? '',
                'city' => $formData['district'] ?? '', // Using district as city (now district is the name directly)
                'state' => $formData['state'],
                'country' => $formData['country'],
                'postcode' => $formData['postcode'],
                'phone' => $formData['phone'], // PhoneInput component already includes country code

                // Additional fields for checkout (not in addresses table)
                'email' => $formData['email'],

                // CHIP API specific fields
                'district' => $formData['district'] ?? '', // District name directly
                'address' => $formData['street1'].($formData['street2'] ? ', '.$formData['street2'] : ''), // Combined address for CHIP
                'zip' => $formData['postcode'],

                // Required CHIP fields - use defaults if not provided by user
                'personal_code' => $formData['vat_number'] ?? 'PERSONAL',
                'brand_name' => $formData['company'] ?? $formData['name'],
                'legal_name' => $formData['company'] ?? $formData['name'],
                'registration_number' => $formData['vat_number'] ?? '',
                'tax_number' => $formData['vat_number'] ?? '',
                // Add bank account information (required by CHIP API)
                'bank_account' => 'default',
                'bank_code' => 'default',
                // Use empty array to let CHIP gateway handle payment method selection
                'payment_method_whitelist' => [],
            ];

            // Create payment using the configured gateway
            $result = $checkoutService->processCheckout($customerData, $this->cartItems);

            if ($result['success']) {
                // Store purchase info in session
                session([
                    'chip_purchase_id' => $result['purchase_id'],
                    'checkout_data' => $formData,
                ]);

                // Redirect to CHIP checkout
                return $this->redirect($result['checkout_url']);
            } else {
                session()->flash('error', 'Gagal memproses pembayaran: '.$result['error']);
            }
        } catch (\Exception $e) {
            Log::error('Checkout processing failed', [
                'error' => $e->getMessage(),
                'form_data' => $formData ?? [],
                'cart_items' => $this->cartItems,
            ]);

            session()->flash('error', 'Terjadi ralat semasa memproses pesanan. Sila cuba lagi.');
        }
    }

    public function render()
    {
        return view('livewire.checkout', [
            'cartQuantity' => CartFacade::getTotalQuantity(),
        ])->layout('components.layouts.app');
    }
}
