<?php

namespace App\Services;

class PaymentMethodService
{
    /**
     * Get available payment methods for Malaysia
     * Since we removed the payment gateway infrastructure, we'll use the default methods
     */
    public function getAvailablePaymentMethods(): array
    {
        return $this->getDefaultPaymentMethods();
    }

    /**
     * Format payment methods for frontend display
     */
    public function formatPaymentMethods(array $methods): array
    {
        $formatted = [];

        foreach ($methods as $method) {
            $formatted[] = [
                'id' => $method['name'],
                'name' => $this->getPaymentMethodDisplayName($method['name']),
                'description' => $this->getPaymentMethodDescription($method['name']),
                'icon' => $this->getPaymentMethodIcon($method['name']),
                'group' => $this->getPaymentMethodGroup($method['name']),
            ];
        }

        return $formatted;
    }

    /**
     * Get default payment methods (fallback)
     */
    public function getDefaultPaymentMethods(): array
    {
        return [
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
            [
                'id' => 'tng_ewallet',
                'name' => 'Touch \'n Go eWallet',
                'description' => 'Bayar dengan Touch \'n Go eWallet',
                'icon' => 'wallet',
                'group' => 'ewallet',
            ],
            [
                'id' => 'grabpay',
                'name' => 'GrabPay',
                'description' => 'Bayar dengan GrabPay',
                'icon' => 'wallet',
                'group' => 'ewallet',
            ],
            [
                'id' => 'shopeepay',
                'name' => 'ShopeePay',
                'description' => 'Bayar dengan ShopeePay',
                'icon' => 'wallet',
                'group' => 'ewallet',
            ],
            [
                'id' => 'duitnow_qr',
                'name' => 'DuitNow QR',
                'description' => 'Imbas kod QR untuk bayar',
                'icon' => 'qr-code',
                'group' => 'qr',
            ],
            [
                'id' => 'atome',
                'name' => 'Atome',
                'description' => 'Beli sekarang, bayar kemudian',
                'icon' => 'clock',
                'group' => 'bnpl',
            ],
        ];
    }

    /**
     * Get display name for payment method
     */
    public function getPaymentMethodDisplayName(string $method): string
    {
        $names = [
            'fpx_b2c' => 'FPX Online Banking',
            'fpx_b2b1' => 'FPX Corporate Banking',
            'visa' => 'Kad Kredit/Debit Visa',
            'mastercard' => 'Kad Kredit/Debit Mastercard',
            'tng_ewallet' => 'Touch \'n Go eWallet',
            'grabpay' => 'GrabPay',
            'shopeepay' => 'ShopeePay',
            'maybank_qrpay' => 'Maybank QRPay',
            'duitnow_qr' => 'DuitNow QR',
            'atome' => 'Atome - Beli Sekarang, Bayar Kemudian',
        ];

        return $names[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }

    /**
     * Get description for payment method
     */
    public function getPaymentMethodDescription(string $method): string
    {
        $descriptions = [
            'fpx_b2c' => 'Bayar dengan Internet Banking Malaysia',
            'fpx_b2b1' => 'Bayar dengan Internet Banking Korporat',
            'visa' => 'Bayar dengan kad kredit atau debit Visa',
            'mastercard' => 'Bayar dengan kad kredit atau debit Mastercard',
            'tng_ewallet' => 'Bayar dengan Touch \'n Go eWallet',
            'grabpay' => 'Bayar dengan GrabPay',
            'shopeepay' => 'Bayar dengan ShopeePay',
            'maybank_qrpay' => 'Bayar dengan Maybank QRPay',
            'duitnow_qr' => 'Imbas kod QR untuk bayar',
            'atome' => 'Beli sekarang, bayar dalam 3 ansuran tanpa faedah',
        ];

        return $descriptions[$method] ?? 'Bayar dengan '.$this->getPaymentMethodDisplayName($method);
    }

    /**
     * Get icon for payment method
     */
    public function getPaymentMethodIcon(string $method): string
    {
        $icons = [
            'fpx_b2c' => 'building-office',
            'fpx_b2b1' => 'building-office',
            'visa' => 'credit-card',
            'mastercard' => 'credit-card',
            'tng_ewallet' => 'wallet',
            'grabpay' => 'wallet',
            'shopeepay' => 'wallet',
            'maybank_qrpay' => 'qr-code',
            'duitnow_qr' => 'qr-code',
            'atome' => 'clock',
        ];

        return $icons[$method] ?? 'credit-card';
    }

    /**
     * Get group for payment method
     */
    public function getPaymentMethodGroup(string $method): string
    {
        $groups = [
            'fpx_b2c' => 'banking',
            'fpx_b2b1' => 'banking',
            'visa' => 'card',
            'mastercard' => 'card',
            'tng_ewallet' => 'ewallet',
            'grabpay' => 'ewallet',
            'shopeepay' => 'ewallet',
            'maybank_qrpay' => 'qr',
            'duitnow_qr' => 'qr',
            'atome' => 'bnpl',
        ];

        return $groups[$method] ?? 'other';
    }

    /**
     * Get payment methods grouped by type
     */
    public function getGroupedPaymentMethods(): array
    {
        $methods = $this->getAvailablePaymentMethods();
        $grouped = [];

        foreach ($methods as $method) {
            $group = $this->getPaymentMethodGroup($method['id']);
            $grouped[$group][] = $method;
        }

        return $grouped;
    }

    /**
     * Get payment method by ID
     */
    public function getPaymentMethodById(string $id): ?array
    {
        $methods = $this->getAvailablePaymentMethods();

        foreach ($methods as $method) {
            if ($method['id'] === $id) {
                return $method;
            }
        }

        return null;
    }

    /**
     * Check if payment method is available
     */
    public function isPaymentMethodAvailable(string $id): bool
    {
        $method = $this->getPaymentMethodById($id);

        return $method && ($method['is_available'] ?? false);
    }
}
