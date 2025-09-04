<?php

declare(strict_types=1);

namespace Masyukai\Chip\Services;

use Illuminate\Support\Facades\Log;
use Masyukai\Chip\Clients\ChipCollectClient;
use Masyukai\Chip\DataObjects\Client;
use Masyukai\Chip\DataObjects\ClientDetails;
use Masyukai\Chip\DataObjects\CurrencyConversion;
use Masyukai\Chip\DataObjects\IssuerDetails;
use Masyukai\Chip\DataObjects\Payment;
use Masyukai\Chip\DataObjects\Product;
use Masyukai\Chip\DataObjects\Purchase;
use Masyukai\Chip\DataObjects\PurchaseDetails;
use Masyukai\Chip\DataObjects\TransactionData;
use Masyukai\Chip\Exceptions\ChipValidationException;

class ChipCollectService
{
    public function __construct(
        protected ChipCollectClient $client,
        protected ?WebhookService $webhookService = null
    ) {
        //
    }

    /**
     * Create a purchase
     */
    public function createPurchase(array $data): Purchase
    {
        $this->validatePurchaseData($data);

        try {
            $responseData = $this->client->post('purchases/', $data);

            return $this->mapToPurchase($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to create CHIP purchase', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve a purchase by ID
     */
    public function getPurchase(string $purchaseId): Purchase
    {
        try {
            $responseData = $this->client->get("purchases/{$purchaseId}/");

            return $this->mapToPurchase($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve CHIP purchase', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel a purchase
     */
    public function cancelPurchase(string $purchaseId): Purchase
    {
        try {
            $responseData = $this->client->post("purchases/{$purchaseId}/cancel/");

            return $this->mapToPurchase($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to cancel CHIP purchase', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Refund a purchase
     */
    public function refundPurchase(string $purchaseId, ?int $amount = null): Purchase
    {
        $data = [];
        if ($amount !== null) {
            $data['amount'] = $amount;
        }

        try {
            $responseData = $this->client->post("purchases/{$purchaseId}/refund/", $data);

            return $this->mapToPurchase($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to refund CHIP purchase', [
                'purchase_id' => $purchaseId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get available payment methods
     */
    public function getPaymentMethods(array $filters = []): array
    {
        try {
            $queryString = http_build_query($filters);
            $endpoint = 'payment_methods/'.($queryString ? '?'.$queryString : '');

            return $this->client->get($endpoint);
        } catch (\Exception $e) {
            Log::error('Failed to get CHIP payment methods', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Charge a purchase using a saved token
     */
    public function chargePurchase(string $purchaseId, string $recurringToken): Purchase
    {
        try {
            $data = ['recurring_token' => $recurringToken];
            $responseData = $this->client->post("purchases/{$purchaseId}/charge/", $data);

            return $this->mapToPurchase($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to charge CHIP purchase', [
                'purchase_id' => $purchaseId,
                'recurring_token' => $recurringToken,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Capture a previously authorized payment
     */
    public function capturePurchase(string $purchaseId, ?int $amount = null): Purchase
    {
        try {
            $data = [];
            if ($amount !== null) {
                $data['amount'] = $amount;
            }

            $responseData = $this->client->post("purchases/{$purchaseId}/capture/", $data);

            return $this->mapToPurchase($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to capture CHIP purchase', [
                'purchase_id' => $purchaseId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Release funds on hold
     */
    public function releasePurchase(string $purchaseId): Purchase
    {
        try {
            $responseData = $this->client->post("purchases/{$purchaseId}/release/");

            return $this->mapToPurchase($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to release CHIP purchase', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark a purchase as paid
     */
    public function markPurchaseAsPaid(string $purchaseId, ?int $paidOn = null): Purchase
    {
        try {
            $data = [];
            if ($paidOn !== null) {
                $data['paid_on'] = $paidOn;
            }

            $responseData = $this->client->post("purchases/{$purchaseId}/mark_as_paid/", $data);

            return $this->mapToPurchase($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to mark CHIP purchase as paid', [
                'purchase_id' => $purchaseId,
                'paid_on' => $paidOn,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Resend invoice for a purchase
     */
    public function resendInvoice(string $purchaseId): Purchase
    {
        try {
            $responseData = $this->client->post("purchases/{$purchaseId}/resend_invoice/");

            return $this->mapToPurchase($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to resend CHIP purchase invoice', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a recurring token
     */
    public function deleteRecurringToken(string $purchaseId): void
    {
        try {
            $this->client->delete("purchases/{$purchaseId}/recurring_token/");
        } catch (\Exception $e) {
            Log::error('Failed to delete CHIP recurring token', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // Client Management Methods

    /**
     * Create a client
     */
    public function createClient(array $data): Client
    {
        try {
            $responseData = $this->client->post('clients/', $data);

            return Client::fromArray($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to create CHIP client', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve a client by ID
     */
    public function getClient(string $clientId): Client
    {
        try {
            $responseData = $this->client->get("clients/{$clientId}/");

            return Client::fromArray($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve CHIP client', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * List all clients
     */
    public function listClients(array $filters = []): array
    {
        try {
            $queryString = http_build_query($filters);
            $endpoint = 'clients/'.($queryString ? '?'.$queryString : '');

            return $this->client->get($endpoint);
        } catch (\Exception $e) {
            Log::error('Failed to list CHIP clients', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update a client
     */
    public function updateClient(string $clientId, array $data): Client
    {
        try {
            $responseData = $this->client->put("clients/{$clientId}/", $data);

            return Client::fromArray($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to update CHIP client', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Partially update a client
     */
    public function partialUpdateClient(string $clientId, array $data): Client
    {
        try {
            $responseData = $this->client->patch("clients/{$clientId}/", $data);

            return Client::fromArray($responseData);
        } catch (\Exception $e) {
            Log::error('Failed to partially update CHIP client', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Delete a client
     */
    public function deleteClient(string $clientId): void
    {
        try {
            $this->client->delete("clients/{$clientId}/");
        } catch (\Exception $e) {
            Log::error('Failed to delete CHIP client', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * List recurring tokens for a client
     */
    public function listClientRecurringTokens(string $clientId): array
    {
        try {
            return $this->client->get("clients/{$clientId}/recurring_tokens/");
        } catch (\Exception $e) {
            Log::error('Failed to list CHIP client recurring tokens', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve a specific recurring token for a client
     */
    public function getClientRecurringToken(string $clientId, string $tokenId): array
    {
        try {
            return $this->client->get("clients/{$clientId}/recurring_tokens/{$tokenId}/");
        } catch (\Exception $e) {
            Log::error('Failed to retrieve CHIP client recurring token', [
                'client_id' => $clientId,
                'token_id' => $tokenId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a recurring token for a client
     */
    public function deleteClientRecurringToken(string $clientId, string $tokenId): void
    {
        try {
            $this->client->delete("clients/{$clientId}/recurring_tokens/{$tokenId}/");
        } catch (\Exception $e) {
            Log::error('Failed to delete CHIP client recurring token', [
                'client_id' => $clientId,
                'token_id' => $tokenId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a purchase for checkout
     */
    public function createCheckoutPurchase(
        array $products,
        ClientDetails $client,
        array $options = []
    ): Purchase {
        $data = [
            'client' => $client->toArray(),
            'purchase' => [
                'products' => array_map(fn ($product) => $product instanceof Product
                    ? $product->toArray()
                    : $product, $products),
                'currency' => $options['currency'] ?? config('chip.defaults.currency', 'MYR'),
            ],
            'brand_id' => $this->client->getBrandId(),
            'send_receipt' => $options['send_receipt'] ?? true,
            'success_redirect' => $options['success_redirect'] ?? config('chip.defaults.success_redirect', '') ?: '',
            'failure_redirect' => $options['failure_redirect'] ?? config('chip.defaults.failure_redirect', '') ?: '',
            'cancel_redirect' => $options['cancel_redirect'] ?? config('chip.defaults.failure_redirect', '') ?: '',
            'success_callback' => $options['success_callback'] ?? '' ?: '',
            'reference' => $options['reference'] ?? null,
            'creator_agent' => config('chip.defaults.creator_agent', 'Laravel Package'),
            'platform' => config('chip.defaults.platform', 'api'),
        ];

        // Only add payment_method_whitelist if it has elements
        if (! empty($options['payment_method_whitelist'])) {
            $data['payment_method_whitelist'] = $options['payment_method_whitelist'];
        }

        return $this->createPurchase($data);
    }

    /**
     * Validate purchase data
     */
    protected function validatePurchaseData(array $data): void
    {
        $required = ['client', 'purchase', 'brand_id'];

        foreach ($required as $field) {
            if (! isset($data[$field])) {
                throw new ChipValidationException("Missing required field: {$field}");
            }
        }

        // Validate client data
        if (! isset($data['client']['email']) && ! isset($data['client_id'])) {
            throw new ChipValidationException('Either client.email or client_id is required');
        }

        // Validate purchase data
        if (! isset($data['purchase']['products']) || empty($data['purchase']['products'])) {
            throw new ChipValidationException('Purchase must have at least one product');
        }

        foreach ($data['purchase']['products'] as $product) {
            if (! isset($product['name']) || ! isset($product['price'])) {
                throw new ChipValidationException('Each product must have name and price');
            }
        }
    }

    /**
     * Map API response to Purchase object
     */
    protected function mapToPurchase(array $response): Purchase
    {
        // Map the response to our Purchase data object
        // This is a simplified mapping - you may need to adjust based on your needs
        return new Purchase(
            id: $response['id'],
            type: $response['type'],
            created_on: (int) $response['created_on'],
            updated_on: (int) $response['updated_on'],
            client: ClientDetails::fromArray($response['client']),
            purchase: PurchaseDetails::fromArray($response['purchase']),
            brand_id: $response['brand_id'],
            payment: $response['payment'] ? Payment::fromArray($response['payment']) : null,
            issuer_details: IssuerDetails::fromArray($response['issuer_details']),
            transaction_data: TransactionData::fromArray($response['transaction_data']),
            status: $response['status'],
            status_history: $response['status_history'],
            viewed_on: $response['viewed_on'] ?? null,
            company_id: $response['company_id'],
            is_test: $response['is_test'],
            user_id: $response['user_id'] ?? null,
            billing_template_id: $response['billing_template_id'] ?? null,
            client_id: $response['client_id'] ?? null,
            send_receipt: $response['send_receipt'],
            is_recurring_token: $response['is_recurring_token'],
            recurring_token: $response['recurring_token'] ?? null,
            skip_capture: $response['skip_capture'],
            force_recurring: $response['force_recurring'],
            reference_generated: $response['reference_generated'],
            reference: $response['reference'] ?? null,
            notes: $response['notes'] ?? null,
            issued: $response['issued'] ?? null,
            due: $response['due'] ?? null,
            refund_availability: $response['refund_availability'],
            refundable_amount: $response['refundable_amount'],
            currency_conversion: $response['currency_conversion'] ? CurrencyConversion::fromArray($response['currency_conversion']) : null,
            payment_method_whitelist: $response['payment_method_whitelist'] ?? [],
            success_redirect: $response['success_redirect'] ?? null,
            failure_redirect: $response['failure_redirect'] ?? null,
            cancel_redirect: $response['cancel_redirect'] ?? null,
            success_callback: $response['success_callback'] ?? null,
            creator_agent: $response['creator_agent'] ?? null,
            platform: $response['platform'],
            product: $response['product'],
            created_from_ip: $response['created_from_ip'] ?? null,
            invoice_url: $response['invoice_url'] ?? null,
            checkout_url: $response['checkout_url'] ?? null,
            direct_post_url: $response['direct_post_url'] ?? null,
            marked_as_paid: $response['marked_as_paid'] ?? false,
            order_id: $response['order_id'] ?? null,
        );
    }

    /**
     * Get the public key for webhook signature verification
     */
    public function getPublicKey(): string
    {
        // This would typically come from the CHIP API or configuration
        // For now, return a configured public key
        return config('chip.webhooks.public_key') ?: 'default_public_key_for_testing';
    }
}
