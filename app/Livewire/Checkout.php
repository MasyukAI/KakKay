<?php

namespace App\Livewire;

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
use MasyukAI\Cart\Facades\Cart;
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
        $this->loadCartItems();
        $this->loadPaymentMethods();
        
        // Initialize form with default values
        $this->form->fill([
            'country' => 'Malaysia',
            'city' => 'Kuala Lumpur',
            'delivery_method' => 'standard',
        ]);
    }    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Penghantaran')
                    ->description('Masukkan maklumat untuk penghantaran')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Penuh')
                                    ->required()
                                    ->placeholder('Nama penuh anda')
                                    ->maxLength(255)
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->placeholder('nama@email.com')
                                    ->maxLength(255)
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                Select::make('country')
                                    ->label('Negara')
                                    ->required()
                                    ->default('Malaysia')
                                    ->options([
                                        'Malaysia' => 'Malaysia',
                                        'Singapore' => 'Singapura',
                                        'Indonesia' => 'Indonesia',
                                        'Thailand' => 'Thailand',
                                        'Brunei' => 'Brunei',
                                    ])
                                    ->live(),

                                Select::make('city')
                                    ->label('Bandar')
                                    ->required()
                                    ->default('Kuala Lumpur')
                                    ->options([
                                        'Kuala Lumpur' => 'Kuala Lumpur',
                                        'Johor Bahru' => 'Johor Bahru',
                                        'Penang' => 'Pulau Pinang',
                                        'Kota Kinabalu' => 'Kota Kinabalu',
                                        'Kuching' => 'Kuching',
                                    ]),

                                \Ysfkaya\FilamentPhoneInput\Forms\PhoneInput::make('phone')
                                    ->extraAttributes(['class' => 'checkout-sm']),
                            ]),

                        TextInput::make('address')
                            ->label('Alamat Baris 1')
                            ->required()
                            ->placeholder('Nombor rumah, nama jalan')
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'checkout-sm']),

                        TextInput::make('address2')
                            ->label('Alamat Baris 2 (Opsional)')
                            ->placeholder('Taman, kawasan, dll')
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'checkout-sm']),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('state')
                                    ->label('Negeri')
                                    ->placeholder('Contoh: Selangor')
                                    ->maxLength(100)
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                TextInput::make('postal_code')
                                    ->label('Poskod')
                                    ->placeholder('Contoh: 40000')
                                    ->maxLength(10)
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                TextInput::make('company_name')
                                    ->label('Nama Syarikat (Opsional)')
                                    ->placeholder('Nama syarikat')
                                    ->maxLength(255)
                                    ->extraAttributes(['class' => 'checkout-sm']),

                                TextInput::make('vat_number')
                                    ->label('VAT/SST Number (Opsional)')
                                    ->placeholder('Nombor VAT/SST')
                                    ->maxLength(50)
                                    ->extraAttributes(['class' => 'checkout-sm']),
                            ]),
                    ]),

                Section::make('Cara Penghantaran')
                    ->description('Pilih cara penghantaran yang sesuai')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        Select::make('delivery_method')
                            ->label('Kaedah Penghantaran')
                            ->required()
                            ->default('standard')
                            ->options([
                                'standard' => 'RM5 - Penghantaran Standard (3-5 hari bekerja)',
                                'fast' => 'RM15 - Penghantaran Pantas (1-2 hari bekerja)',
                                'express' => 'RM49 - Penghantaran Ekspres (Hari yang sama)',
                            ])
                            ->live()
                            ->native(false)
                            ->helperText('Kos penghantaran akan dikira secara automatik'),
                    ]),

                Section::make('Kod Promosi')
                    ->description('Masukkan kod voucher atau promosi (jika ada)')
                    ->icon('heroicon-o-ticket')
                    ->schema([
                        TextInput::make('voucher_code')
                            ->label('Kod Voucher')
                            ->placeholder('Masukkan kod voucher')
                            ->maxLength(50)
                            ->extraAttributes(['class' => 'checkout-sm']),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ])
            ->statePath('data');
    }

    public function updatedData($value, $key): void
    {
        // Update country code when country changes
        if ($key === 'country') {
            $this->selectedCountryCode = match ($value) {
                'Malaysia' => '+60',
                'Singapore' => '+65',
                'Indonesia' => '+62',
                'Thailand' => '+66',
                'Brunei' => '+673',
                default => '+60',
            };
        }
    }

    public function loadCartItems()
    {
        try {
            // Middleware handles cart instance switching
            $cartContents = Cart::getItems();

            if ($cartContents->isEmpty()) {
                return $this->redirect(route('cart'));
            }

            $this->cartItems = $cartContents->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                    'price' => (int) ($item->price),
                    'quantity' => (int) $item->quantity,
                    'attributes' => $item->attributes->toArray(),
                ];
            })->values()->toArray();
        } catch (\Exception $e) {
            Log::error('Checkout loading error: '.$e->getMessage());

            return $this->redirect(route('cart'));
        }
    }

    public function loadPaymentMethods(): void
    {
        try {
            $checkoutService = app(CheckoutService::class);
            $this->availablePaymentMethods = $checkoutService->getAvailablePaymentMethods();
        } catch (\Exception $e) {
            Log::error('Failed to load payment methods: '.$e->getMessage());
            // Use fallback payment methods
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

    public function selectPaymentGroup(string $group): void
    {
        $this->selectedPaymentGroup = $group;

        // Set payment method whitelist based on selected group
        $groupMethods = collect($this->availablePaymentMethods)
            ->where('group', $group)
            ->pluck('id')
            ->toArray();

        $this->data['payment_method_whitelist'] = $groupMethods;
    }

    public function selectPaymentMethod(string $methodId): void
    {
        $this->data['payment_method_whitelist'] = [$methodId];
    }

    #[Computed]
    public function getSubtotal(): int
    {
        return (int) Cart::subtotal(); // Use Cart facade for consistency
    }

    #[Computed]
    public function getSavings(): int
    {
        return 0; // No savings for now
    }

    #[Computed]
    public function getShipping(): int
    {
        $deliveryMethod = $this->data['delivery_method'] ?? 'standard';

        return match ($deliveryMethod) {
            'express' => 4900, // RM49
            'fast' => 1500,    // RM15
            default => 500,    // RM5 Standard shipping
        };
    }

    #[Computed]
    public function getTax(): int
    {
        return 0; // No tax applied
    }

    #[Computed]
    public function getTotal(): int
    {
        // is there a native function from Cart package
        $cartTotal = Cart::total();

        return $cartTotal - $this->getSavings() + $this->getShipping() + $this->getTax();
    }

    public function formatPrice(int $cents): string
    {
        return 'RM'.number_format($cents / 100, 2);
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

    public function processCheckout()
    {
        // Validate form using Filament's validation
        try {
            $formData = $this->form->getState();
        } catch (\Filament\Forms\ValidationException $e) {
            return;
        }

        try {
            $checkoutService = app(CheckoutService::class);

            // Prepare customer data with all required CHIP fields
            $customerData = [
                'name' => $formData['name'],
                'email' => $formData['email'],
                'phone' => $this->selectedCountryCode.$formData['phone'],
                'country' => $formData['country'],
                'city' => $formData['city'],
                'address' => $formData['address'],
                'state' => $formData['state'] ?? '',
                'zip' => $formData['postal_code'] ?? '',
                'company_name' => $formData['company_name'] ?? '',
                'vat_number' => $formData['vat_number'] ?? '',
                // Required CHIP fields - use defaults if not provided by user
                'personal_code' => $formData['vat_number'] ?? 'PERSONAL',
                'brand_name' => $formData['company_name'] ?? $formData['name'],
                'legal_name' => $formData['company_name'] ?? $formData['name'],
                'registration_number' => $formData['vat_number'] ?? '',
                'tax_number' => $formData['vat_number'] ?? '',
                // Add bank account information (required by CHIP API)
                'bank_account' => 'default',
                'bank_code' => 'default',
                // Use empty array instead of null for payment_method_whitelist
                'payment_method_whitelist' => $this->data['payment_method_whitelist'] ?? [],
            ];

            // Create payment using the configured gateway
            $result = $checkoutService->createPayment($customerData, $this->cartItems);

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
        return view('livewire.checkout')
            ->layout('components.layouts.app');
    }
}
