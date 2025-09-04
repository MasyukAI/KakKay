<?php

declare(strict_types=1);

namespace Masyukai\Chip\Services;

use Illuminate\Support\Facades\Log;
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
    private string $apiKey;

    private string $brandId;

    private string $baseUrl;

    private $client;

    public function __construct()
    {
        $this->apiKey = env('CHIP_COLLECT_API_KEY');
        $this->brandId = env('CHIP_COLLECT_BRAND_ID');
        $this->baseUrl = env('CHIP_BASE_URL', 'https://gate.chip-in.asia/api/v1/');

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Create a purchase
     */
    public function createPurchase(array $data): Purchase
    {
        $this->validatePurchaseData($data);

        try {
            $response = $this->client->post('purchases/', [
                'json' => $data,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

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
            $response = $this->client->get("purchases/{$purchaseId}/");

            $responseData = json_decode($response->getBody()->getContents(), true);

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
            $response = $this->client->post("purchases/{$purchaseId}/cancel/");

            $responseData = json_decode($response->getBody()->getContents(), true);

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
            $response = $this->client->post("purchases/{$purchaseId}/refund/", [
                'json' => $data,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

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

            $response = $this->client->get($endpoint);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Failed to get CHIP payment methods', [
                'filters' => $filters,
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
            'brand_id' => $this->brandId,
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
            created_on: $response['created_on'],
            updated_on: $response['updated_on'],
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
}
