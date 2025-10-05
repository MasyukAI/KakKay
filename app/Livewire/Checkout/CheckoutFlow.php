<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use Livewire\Attributes\On;
use Livewire\Component;

final class CheckoutFlow extends Component
{
    public int $currentStep = 1;

    public array $checkoutData = [];

    public function mount(): void
    {
        $this->checkoutData = [
            'cart' => [],
            'shipping' => [],
            'payment' => [],
            'order' => null,
        ];
    }

    #[On('next-step')]
    public function nextStep(array $stepData = []): void
    {
        // Store step data
        $this->updateStepData($stepData);

        if ($this->currentStep < 3) {
            $this->currentStep++;
        }
    }

    #[On('previous-step')]
    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    #[On('go-to-step')]
    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= 3) {
            $this->currentStep = $step;
        }
    }

    #[On('update-step-data')]
    public function updateStepData(array $data): void
    {
        $stepKey = match ($this->currentStep) {
            1 => 'cart',
            2 => 'payment',
            3 => 'order',
            default => 'cart'
        };

        $this->checkoutData[$stepKey] = array_merge(
            $this->checkoutData[$stepKey] ?? [],
            $data
        );
    }

    public function getStepTitle(): string
    {
        return match ($this->currentStep) {
            1 => 'Shopping Cart',
            2 => 'Payment & Delivery',
            3 => 'Order Summary',
            default => 'Checkout'
        };
    }

    public function placeOrder(): void
    {
        // TODO: Implement order placement logic
        session()->flash('message', 'Order placed successfully!');
        $this->redirect('/');
    }

    public function render()
    {
        return view('livewire.checkout.checkout-flow', [
            'stepTitle' => $this->getStepTitle(),
        ]);
    }
}
