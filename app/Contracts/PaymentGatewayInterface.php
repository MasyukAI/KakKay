<?php

declare(strict_types=1);

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Create a purchase through the payment gateway
     *
     * @param  array<string, mixed>  $customerData  Customer details
     * @param  array<array<string, mixed>>  $items  Cart items to be purchased
     * @return array<string, mixed> Result containing success status, purchase ID, checkout URL, and other details
     */
    public function createPurchase(array $customerData, array $items): array;

    /**
     * Get purchase status.
     *
     * @return array{status: string, amount: float, currency: string}|null
     */
    public function getPurchaseStatus(string $purchaseId): ?array;

    /**
     * Get available payment methods.
     *
     * @return array<int, array{id: string, name: string, group?: string}>
     */
    public function getAvailablePaymentMethods(): array;
}
