<?php

declare(strict_types=1);

namespace Masyukai\Chip\Services;

use Masyukai\Chip\DataObjects\Purchase;

class SubscriptionService
{
    public function __construct(
        protected ChipCollectService $chipService
    ) {}

    public function createWithFreeTrial(array $data): Purchase
    {
        $trialData = [
            'client' => $data['client'],
            'purchase' => [
                'products' => [
                    [
                        'name' => $data['trial_product_name'] ?? 'Free Trial',
                        'price' => 0,
                    ],
                ],
            ],
            'skip_capture' => true,
            'brand_id' => $data['brand_id'] ?? null,
            'payment_method_whitelist' => $data['payment_method_whitelist'] ?? ['visa', 'mastercard', 'maestro'],
        ];

        return $this->chipService->createPurchase($trialData);
    }

    public function createWithRegistrationFee(array $data): Purchase
    {
        $registrationData = [
            'client' => $data['client'],
            'purchase' => [
                'products' => [
                    [
                        'name' => $data['registration_product_name'] ?? 'Registration Fee',
                        'price' => $data['registration_fee'],
                    ],
                ],
            ],
            'payment_method_whitelist' => $data['payment_method_whitelist'] ?? ['visa', 'mastercard', 'maestro'],
            'force_recurring' => true,
            'brand_id' => $data['brand_id'] ?? null,
        ];

        return $this->chipService->createPurchase($registrationData);
    }

    public function createSubscriptionPayment(array $data): Purchase
    {
        $subscriptionData = [
            'client' => $data['client'],
            'purchase' => [
                'products' => [
                    [
                        'name' => $data['product_name'] ?? 'Subscription Fee',
                        'price' => $data['amount'],
                    ],
                ],
            ],
            'brand_id' => $data['brand_id'] ?? null,
        ];

        return $this->chipService->createPurchase($subscriptionData);
    }

    public function chargeSubscription(string $subscriptionPurchaseId, string $recurringToken): Purchase
    {
        return $this->chipService->chargePurchase($subscriptionPurchaseId, $recurringToken);
    }

    public function createMonthlySubscription(array $data): array
    {
        $hasRegistrationFee = isset($data['registration_fee']) && $data['registration_fee'] > 0;
        $hasFreeTrial = isset($data['trial_days']) && $data['trial_days'] > 0;

        // Step 1: Create initial purchase (trial or registration)
        if ($hasFreeTrial) {
            $initialPurchase = $this->createWithFreeTrial($data);
        } elseif ($hasRegistrationFee) {
            $initialPurchase = $this->createWithRegistrationFee($data);
        } else {
            throw new \InvalidArgumentException('Either registration_fee or trial_days must be provided');
        }

        // Step 2: Create the recurring subscription purchase
        $subscriptionPurchase = $this->createSubscriptionPayment([
            'client' => $data['client'],
            'amount' => $data['amount'],
            'product_name' => $data['product_name'] ?? 'Monthly Subscription',
            'brand_id' => $data['brand_id'] ?? null,
        ]);

        return [
            'initial_purchase' => $initialPurchase,
            'subscription_purchase' => $subscriptionPurchase,
        ];
    }
}
