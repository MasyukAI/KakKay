<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class PaymentStep extends Component
{
    /** @var array<string, mixed> */
    public array $checkoutData;

    #[Validate('required|string|min:2')]
    public string $name = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $country = 'Malaysia';

    #[Validate('required|string')]
    public string $city = '';

    #[Validate('required|string')]
    public string $phone = '';

    #[Validate('nullable|string')]
    public string $company = '';

    #[Validate('nullable|string')]
    public string $vatNumber = '';

    #[Validate('required|in:credit-card,pay-on-delivery,paypal')]
    public string $paymentMethod = 'credit-card';

    #[Validate('required|in:dhl,fedex,express')]
    public string $deliveryMethod = 'dhl';

    #[Validate('nullable|string')]
    public string $voucherCode = '';

    /**
     * @param  array<string, mixed>  $checkoutData
     */
    public function mount(array $checkoutData = []): void
    {
        $this->checkoutData = $checkoutData;

        // Pre-fill with user data if available
        if (Auth::check()) {
            $user = Auth::user();
            $this->name = $user->name;
            $this->email = $user->email;
        }
    }

    public function applyVoucher(): void
    {
        if (! empty($this->voucherCode)) {
            // Validate voucher code logic here
            session()->flash('success', "Voucher '{$this->voucherCode}' applied successfully!");
            $this->voucherCode = '';
        }
    }

    public function proceedToPayment(): void
    {
        $this->validate();

        $paymentData = [
            'delivery' => [
                'name' => $this->name,
                'email' => $this->email,
                'country' => $this->country,
                'city' => $this->city,
                'phone' => $this->phone,
                'company' => $this->company,
                'vat_number' => $this->vatNumber,
            ],
            'payment_method' => $this->paymentMethod,
            'delivery_method' => $this->deliveryMethod,
            'voucher_code' => $this->voucherCode,
        ];

        $this->dispatch('next-step', $paymentData);
    }

    public function goBack(): void
    {
        $this->dispatch('previous-step');
    }

    public function getDeliveryFee(): int
    {
        return match ($this->deliveryMethod) {
            'dhl' => 1500, // RM 15
            'fedex' => 0, // Free
            'express' => 4900, // RM 49
            default => 0
        };
    }

    public function getPaymentFee(): int
    {
        return match ($this->paymentMethod) {
            'pay-on-delivery' => 1500, // RM 15
            default => 0
        };
    }

    public function getSubtotal(): int
    {
        return $this->checkoutData['cart']['subtotal'] ?? 0;
    }

    public function getSavings(): int
    {
        return $this->checkoutData['cart']['savings'] ?? 0;
    }

    public function getTotal(): int
    {
        return $this->getSubtotal()
            - $this->getSavings()
            + $this->getDeliveryFee()
            + $this->getPaymentFee()
            + $this->getTax();
    }

    public function getTax(): int
    {
        return (int) ($this->getSubtotal() * 0.1); // 10% tax
    }

    public function formatPrice(int $cents): string
    {
        return 'RM '.number_format($cents / 100, 2);
    }

    public function render(): View
    {
        return view('livewire.checkout.payment-step');
    }
}
