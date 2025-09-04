<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Create a purchase through the payment gateway
     *
     * @param array $customerData Customer details
     * @param array $items Cart items to be purchased
     * @return array Result containing success status, purchase ID, checkout URL, and other details
     */
    public function createPurchase(array $customerData, array $items): array;
    
    /**
     * Get available payment methods for this gateway
     *
     * @return array List of available payment methods
     */
    public function getAvailablePaymentMethods(): array;
}
