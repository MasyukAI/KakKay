<?php

use Illuminate\Support\Facades\Http;
use Masyukai\Chip\DataObjects\Purchase;
use Masyukai\Chip\DataObjects\Payment;
use Masyukai\Chip\DataObjects\Client;
use Masyukai\Chip\Services\ChipCollectService;
use Masyukai\Chip\Services\WebhookService;
use Masyukai\Chip\Clients\ChipCollectClient;

beforeEach(function () {
    $this->client = Mockery::mock(ChipCollectClient::class);
    $this->webhookService = Mockery::mock(WebhookService::class);
    $this->service = new ChipCollectService($this->client, $this->webhookService);
});

describe('ChipCollectService Purchase Management', function () {
    it('can create a purchase', function () {
        $purchaseData = [
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'reference' => 'ORDER_001',
            'checkout_url' => 'https://gate-sandbox.chip-in.asia/checkout/purchase_123',
            'status' => 'created',
            'metadata' => ['order_id' => '123']
        ];

        $this->client->shouldReceive('post')
            ->with('/purchases/', [
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'reference' => 'ORDER_001',
                'metadata' => ['order_id' => '123']
            ])
            ->andReturn(['data' => $purchaseData]);

        $purchase = $this->service->createPurchase(
            amountInCents: 10000,
            currency: 'MYR',
            reference: 'ORDER_001',
            metadata: ['order_id' => '123']
        );

        expect($purchase)->toBeInstanceOf(Purchase::class);
        expect($purchase->id)->toBe('purchase_123');
        expect($purchase->amountInCents)->toBe(10000);
        expect($purchase->currency)->toBe('MYR');
        expect($purchase->reference)->toBe('ORDER_001');
        expect($purchase->status)->toBe('created');
    });

    it('can retrieve a purchase', function () {
        $purchaseData = [
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'reference' => 'ORDER_001',
            'checkout_url' => 'https://gate-sandbox.chip-in.asia/checkout/purchase_123',
            'status' => 'paid'
        ];

        $this->client->shouldReceive('get')
            ->with('/purchases/purchase_123/')
            ->andReturn(['data' => $purchaseData]);

        $purchase = $this->service->getPurchase('purchase_123');

        expect($purchase)->toBeInstanceOf(Purchase::class);
        expect($purchase->id)->toBe('purchase_123');
        expect($purchase->status)->toBe('paid');
    });

    it('can list purchases', function () {
        $purchasesData = [
            [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'paid'
            ],
            [
                'id' => 'purchase_456',
                'amount_in_cents' => 15000,
                'currency' => 'MYR',
                'status' => 'created'
            ]
        ];

        $this->client->shouldReceive('get')
            ->with('/purchases/', ['limit' => 20, 'offset' => 0])
            ->andReturn(['data' => $purchasesData]);

        $purchases = $this->service->listPurchases();

        expect($purchases)->toHaveCount(2);
        expect($purchases[0])->toBeInstanceOf(Purchase::class);
        expect($purchases[0]->id)->toBe('purchase_123');
        expect($purchases[1]->id)->toBe('purchase_456');
    });

    it('can update a purchase', function () {
        $updatedData = [
            'id' => 'purchase_123',
            'amount_in_cents' => 12000,
            'currency' => 'MYR',
            'reference' => 'ORDER_001_UPDATED',
            'status' => 'created'
        ];

        $this->client->shouldReceive('put')
            ->with('/purchases/purchase_123/', [
                'amount_in_cents' => 12000,
                'reference' => 'ORDER_001_UPDATED'
            ])
            ->andReturn(['data' => $updatedData]);

        $purchase = $this->service->updatePurchase(
            'purchase_123',
            amountInCents: 12000,
            reference: 'ORDER_001_UPDATED'
        );

        expect($purchase)->toBeInstanceOf(Purchase::class);
        expect($purchase->amountInCents)->toBe(12000);
        expect($purchase->reference)->toBe('ORDER_001_UPDATED');
    });

    it('can delete a purchase', function () {
        $this->client->shouldReceive('delete')
            ->with('/purchases/purchase_123/')
            ->andReturn([]);

        $result = $this->service->deletePurchase('purchase_123');

        expect($result)->toBeTrue();
    });
});

describe('ChipCollectService Payment Management', function () {
    it('can list payments for a purchase', function () {
        $paymentsData = [
            [
                'id' => 'payment_123',
                'purchase_id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'successful',
                'method' => 'fpx'
            ]
        ];

        $this->client->shouldReceive('get')
            ->with('/purchases/purchase_123/payments/')
            ->andReturn(['data' => $paymentsData]);

        $payments = $this->service->getPayments('purchase_123');

        expect($payments)->toHaveCount(1);
        expect($payments[0])->toBeInstanceOf(Payment::class);
        expect($payments[0]->id)->toBe('payment_123');
        expect($payments[0]->purchaseId)->toBe('purchase_123');
        expect($payments[0]->status)->toBe('successful');
    });

    it('can retrieve a specific payment', function () {
        $paymentData = [
            'id' => 'payment_123',
            'purchase_id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'successful',
            'method' => 'fpx',
            'paid_at' => '2024-01-01T12:00:00Z'
        ];

        $this->client->shouldReceive('get')
            ->with('/purchases/purchase_123/payments/payment_123/')
            ->andReturn(['data' => $paymentData]);

        $payment = $this->service->getPayment('purchase_123', 'payment_123');

        expect($payment)->toBeInstanceOf(Payment::class);
        expect($payment->id)->toBe('payment_123');
        expect($payment->status)->toBe('successful');
        expect($payment->method)->toBe('fpx');
    });
});

describe('ChipCollectService Client Management', function () {
    it('can create a client', function () {
        $clientData = [
            'id' => 'client_123',
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+60123456789'
        ];

        $this->client->shouldReceive('post')
            ->with('/clients/', [
                'full_name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+60123456789'
            ])
            ->andReturn(['data' => $clientData]);

        $client = $this->service->createClient(
            fullName: 'John Doe',
            email: 'john@example.com',
            phone: '+60123456789'
        );

        expect($client)->toBeInstanceOf(Client::class);
        expect($client->id)->toBe('client_123');
        expect($client->fullName)->toBe('John Doe');
        expect($client->email)->toBe('john@example.com');
    });

    it('can retrieve a client', function () {
        $clientData = [
            'id' => 'client_123',
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+60123456789'
        ];

        $this->client->shouldReceive('get')
            ->with('/clients/client_123/')
            ->andReturn(['data' => $clientData]);

        $client = $this->service->getClient('client_123');

        expect($client)->toBeInstanceOf(Client::class);
        expect($client->id)->toBe('client_123');
        expect($client->fullName)->toBe('John Doe');
    });

    it('can list clients', function () {
        $clientsData = [
            [
                'id' => 'client_123',
                'full_name' => 'John Doe',
                'email' => 'john@example.com'
            ],
            [
                'id' => 'client_456',
                'full_name' => 'Jane Smith',
                'email' => 'jane@example.com'
            ]
        ];

        $this->client->shouldReceive('get')
            ->with('/clients/', ['limit' => 20, 'offset' => 0])
            ->andReturn(['data' => $clientsData]);

        $clients = $this->service->listClients();

        expect($clients)->toHaveCount(2);
        expect($clients[0])->toBeInstanceOf(Client::class);
        expect($clients[0]->fullName)->toBe('John Doe');
        expect($clients[1]->fullName)->toBe('Jane Smith');
    });
});
