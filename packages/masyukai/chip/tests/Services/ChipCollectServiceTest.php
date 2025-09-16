<?php

use MasyukAI\Chip\Clients\ChipCollectClient;
use MasyukAI\Chip\DataObjects\Client;
use MasyukAI\Chip\DataObjects\Purchase;
use MasyukAI\Chip\Services\ChipCollectService;
use MasyukAI\Chip\Services\WebhookService;

beforeEach(function () {
    $this->client = Mockery::mock(ChipCollectClient::class);
    $this->webhookService = Mockery::mock(WebhookService::class);
    $this->service = new ChipCollectService($this->client, $this->webhookService);
});

describe('ChipCollectService Public Key', function () {
    it('returns the public key as a PEM string', function () {
        $pemString = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7...\n-----END PUBLIC KEY-----\n";
        $this->client->shouldReceive('get')
            ->with('public_key/')
            ->andReturn($pemString);

        $publicKey = $this->service->getPublicKey();

        expect($publicKey)->toBeString();
        expect($publicKey)->toContain('BEGIN PUBLIC KEY');
        expect($publicKey)->toContain('END PUBLIC KEY');
    });
});

describe('ChipCollectService Purchase Management', function () {
    it('can create a purchase', function () {
        $purchaseData = [
            'id' => 'purchase_123',
            'type' => 'payment',
            'created_on' => '2024-01-01T12:00:00Z',
            'updated_on' => '2024-01-01T12:00:00Z',
            'client' => [
                'id' => 'client_123',
                'email' => 'test@example.com',
            ],
            'purchase' => [
                'currency' => 'MYR',
                'products' => [
                    [
                        'name' => 'Test Product',
                        'price' => 10000,
                        'quantity' => 1,
                    ],
                ],
            ],
            'brand_id' => 'test_brand_id',
            'payment' => null,
            'issuer_details' => [],
            'transaction_data' => [],
            'status' => 'created',
            'status_history' => [],
            'viewed_on' => null,
            'company_id' => 'company_123',
            'is_test' => true,
            'user_id' => null,
            'billing_template_id' => null,
            'client_id' => 'client_123',
            'send_receipt' => true,
            'is_recurring_token' => false,
            'recurring_token' => null,
            'skip_capture' => false,
            'force_recurring' => false,
            'reference_generated' => 'REF_123',
            'reference' => 'ORDER_001',
            'notes' => null,
            'issued' => null,
            'due' => null,
            'refund_availability' => 'full',
            'refundable_amount' => 10000,
            'currency_conversion' => null,
            'payment_method_whitelist' => [],
            'success_redirect' => null,
            'failure_redirect' => null,
            'cancel_redirect' => null,
            'success_callback' => null,
            'creator_agent' => 'Laravel Package',
            'platform' => 'api',
            'product' => 'CHIP',
            'created_from_ip' => null,
            'invoice_url' => null,
            'checkout_url' => 'https://gate.chip-in.asia/checkout/purchase_123',
            'direct_post_url' => null,
            'marked_as_paid' => false,
            'order_id' => null,
        ];

        $requestData = [
            'client' => ['email' => 'test@example.com'],
            'purchase' => [
                'currency' => 'MYR',
                'products' => [
                    ['name' => 'Test Product', 'price' => 10000, 'quantity' => 1],
                ],
            ],
            'brand_id' => 'test_brand_id',
            'reference' => 'ORDER_001',
        ];

        $this->client->shouldReceive('post')
            ->with('purchases/', $requestData)
            ->andReturn($purchaseData);

        $purchase = $this->service->createPurchase($requestData);

        expect($purchase)->toBeInstanceOf(Purchase::class);
        expect($purchase->id)->toBe('purchase_123');
        expect($purchase->status)->toBe('created');
        expect($purchase->reference)->toBe('ORDER_001');
    });

    it('can retrieve a purchase', function () {
        $purchaseData = [
            'id' => 'purchase_123',
            'type' => 'payment',
            'created_on' => '2024-01-01T12:00:00Z',
            'updated_on' => '2024-01-01T12:00:00Z',
            'client' => [
                'id' => 'client_123',
                'email' => 'test@example.com',
            ],
            'purchase' => [
                'currency' => 'MYR',
                'products' => [
                    [
                        'name' => 'Test Product',
                        'price' => 10000,
                        'quantity' => 1,
                    ],
                ],
            ],
            'brand_id' => 'test_brand_id',
            'payment' => null,
            'issuer_details' => [],
            'transaction_data' => [],
            'status' => 'paid',
            'status_history' => [],
            'viewed_on' => null,
            'company_id' => 'company_123',
            'is_test' => true,
            'user_id' => null,
            'billing_template_id' => null,
            'client_id' => 'client_123',
            'send_receipt' => true,
            'is_recurring_token' => false,
            'recurring_token' => null,
            'skip_capture' => false,
            'force_recurring' => false,
            'reference_generated' => 'REF_123',
            'reference' => 'ORDER_001',
            'notes' => null,
            'issued' => null,
            'due' => null,
            'refund_availability' => 'full',
            'refundable_amount' => 10000,
            'currency_conversion' => null,
            'payment_method_whitelist' => [],
            'success_redirect' => null,
            'failure_redirect' => null,
            'cancel_redirect' => null,
            'success_callback' => null,
            'creator_agent' => 'Laravel Package',
            'platform' => 'api',
            'product' => 'CHIP',
            'created_from_ip' => null,
            'invoice_url' => null,
            'checkout_url' => 'https://gate.chip-in.asia/checkout/purchase_123',
            'direct_post_url' => null,
            'marked_as_paid' => false,
            'order_id' => null,
        ];

        $this->client->shouldReceive('get')
            ->with('purchases/purchase_123/')
            ->andReturn($purchaseData);

        $purchase = $this->service->getPurchase('purchase_123');

        expect($purchase)->toBeInstanceOf(Purchase::class);
        expect($purchase->id)->toBe('purchase_123');
        expect($purchase->status)->toBe('paid');
    });

    it('can cancel a purchase', function () {
        $purchaseData = [
            'id' => 'purchase_123',
            'type' => 'payment',
            'created_on' => '2024-01-01T12:00:00Z',
            'updated_on' => '2024-01-01T12:00:00Z',
            'client' => [
                'id' => 'client_123',
                'email' => 'test@example.com',
            ],
            'purchase' => [
                'currency' => 'MYR',
                'products' => [
                    [
                        'name' => 'Test Product',
                        'price' => 10000,
                        'quantity' => 1,
                    ],
                ],
            ],
            'brand_id' => 'test_brand_id',
            'payment' => null,
            'issuer_details' => [],
            'transaction_data' => [],
            'status' => 'cancelled',
            'status_history' => [],
            'viewed_on' => null,
            'company_id' => 'company_123',
            'is_test' => true,
            'user_id' => null,
            'billing_template_id' => null,
            'client_id' => 'client_123',
            'send_receipt' => true,
            'is_recurring_token' => false,
            'recurring_token' => null,
            'skip_capture' => false,
            'force_recurring' => false,
            'reference_generated' => 'REF_123',
            'reference' => 'ORDER_001',
            'notes' => null,
            'issued' => null,
            'due' => null,
            'refund_availability' => 'none',
            'refundable_amount' => 0,
            'currency_conversion' => null,
            'payment_method_whitelist' => [],
            'success_redirect' => null,
            'failure_redirect' => null,
            'cancel_redirect' => null,
            'success_callback' => null,
            'creator_agent' => 'Laravel Package',
            'platform' => 'api',
            'product' => 'CHIP',
            'created_from_ip' => null,
            'invoice_url' => null,
            'checkout_url' => 'https://gate.chip-in.asia/checkout/purchase_123',
            'direct_post_url' => null,
            'marked_as_paid' => false,
            'order_id' => null,
        ];

        $this->client->shouldReceive('post')
            ->with('purchases/purchase_123/cancel/')
            ->andReturn($purchaseData);

        $purchase = $this->service->cancelPurchase('purchase_123');

        expect($purchase)->toBeInstanceOf(Purchase::class);
        expect($purchase->status)->toBe('cancelled');
    });

    it('can get payment methods', function () {
        $paymentMethods = [
            'available_payment_methods' => ['fpx', 'visa', 'mastercard'],
            'names' => [
                'fpx' => 'FPX Online Banking',
                'visa' => 'Visa',
                'mastercard' => 'Mastercard',
            ],
        ];

        $this->client->shouldReceive('get')
            ->with('payment_methods/?brand_id=test_brand&currency=MYR')
            ->andReturn($paymentMethods);

        $result = $this->service->getPaymentMethods(['brand_id' => 'test_brand', 'currency' => 'MYR']);

        expect($result)->toBe($paymentMethods);
        expect($result['available_payment_methods'])->toContain('fpx');
    });
});

describe('ChipCollectService Client Management', function () {
    it('can create a client', function () {
        $clientData = [
            'id' => 'client_123',
            'email' => 'john@example.com',
            'phone' => '+60123456789',
            'full_name' => 'John Doe',
            'personal_code' => null,
            'brand_id' => 'test_brand_id',
            'created_on' => '2024-01-01T12:00:00Z',
            'updated_on' => '2024-01-01T12:00:00Z',
        ];

        $requestData = [
            'email' => 'john@example.com',
            'phone' => '+60123456789',
            'full_name' => 'John Doe',
        ];

        $this->client->shouldReceive('post')
            ->with('clients/', $requestData)
            ->andReturn($clientData);

        $client = $this->service->createClient($requestData);

        expect($client)->toBeInstanceOf(Client::class);
        expect($client->id)->toBe('client_123');
        expect($client->email)->toBe('john@example.com');
        expect($client->full_name)->toBe('John Doe');
    });

    it('can retrieve a client', function () {
        $clientData = [
            'id' => 'client_123',
            'email' => 'john@example.com',
            'phone' => '+60123456789',
            'full_name' => 'John Doe',
            'personal_code' => null,
            'brand_id' => 'test_brand_id',
            'created_on' => '2024-01-01T12:00:00Z',
            'updated_on' => '2024-01-01T12:00:00Z',
        ];

        $this->client->shouldReceive('get')
            ->with('clients/client_123/')
            ->andReturn($clientData);

        $client = $this->service->getClient('client_123');

        expect($client)->toBeInstanceOf(Client::class);
        expect($client->id)->toBe('client_123');
        expect($client->full_name)->toBe('John Doe');
    });

    it('can list clients', function () {
        $clientsData = [
            [
                'id' => 'client_123',
                'email' => 'john@example.com',
                'full_name' => 'John Doe',
            ],
            [
                'id' => 'client_456',
                'email' => 'jane@example.com',
                'full_name' => 'Jane Smith',
            ],
        ];

        $this->client->shouldReceive('get')
            ->with('clients/')
            ->andReturn($clientsData);

        $clients = $this->service->listClients();

        expect($clients)->toBe($clientsData);
        expect($clients)->toHaveCount(2);
    });
});
