<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Livewire\Livewire;
use MasyukAI\FilamentChip\Models\ChipPayment;
use MasyukAI\FilamentChip\Models\ChipPurchase;
use MasyukAI\FilamentChip\Resources\PaymentResource\Pages\ListPayments;
use MasyukAI\FilamentChip\Resources\PaymentResource\Pages\ViewPayment;

it('renders payments in the list table', function () {
    $purchase = createChipPurchase();
    $payment = createChipPayment($purchase->getKey());

    Livewire::test(ListPayments::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$payment]);
});

it('renders payment detail infolist', function () {
    $purchase = createChipPurchase();
    $payment = createChipPayment($purchase->getKey());

    Livewire::test(ViewPayment::class, [
        'record' => $payment->getKey(),
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'payment_type' => $payment->payment_type,
            'currency' => $payment->currency,
            'formatted_amount' => $payment->formatted_amount,
            'formatted_net_amount' => $payment->formatted_net_amount,
        ]);
});

function createChipPurchase(array $overrides = []): ChipPurchase
{
    ChipPurchase::setConnectionResolver(app('db'));
    ChipPurchase::setEventDispatcher(app('events'));

    $now = now();

    return ChipPurchase::query()->create(array_merge([
        'id' => Str::uuid()->toString(),
        'type' => 'purchase',
        'created_on' => $now->copy()->subMinutes(10)->timestamp,
        'updated_on' => $now->timestamp,
        'client' => [
            'email' => 'client@example.com',
            'full_name' => 'Test Client',
        ],
        'purchase' => [
            'amount' => 10000,
            'currency' => 'MYR',
            'total' => [
                'amount' => 10000,
                'currency' => 'MYR',
            ],
        ],
        'brand_id' => Str::uuid()->toString(),
        'company_id' => Str::uuid()->toString(),
        'user_id' => Str::uuid()->toString(),
        'billing_template_id' => Str::uuid()->toString(),
        'client_id' => Str::uuid()->toString(),
        'payment' => [],
        'issuer_details' => [],
        'transaction_data' => [],
        'status_history' => [],
        'status' => 'paid',
        'viewed_on' => $now->timestamp,
        'send_receipt' => true,
        'is_test' => false,
        'is_recurring_token' => false,
        'recurring_token' => null,
        'skip_capture' => false,
        'force_recurring' => false,
        'reference' => 'INV-1001',
        'reference_generated' => 'INV-1001',
        'notes' => 'Test purchase',
        'issued' => $now->toDateString(),
        'due' => $now->copy()->addDay()->timestamp,
        'refund_availability' => 'all',
        'refundable_amount' => 0,
        'currency_conversion' => null,
        'payment_method_whitelist' => [],
        'success_redirect' => null,
        'failure_redirect' => null,
        'cancel_redirect' => null,
        'success_callback' => null,
        'invoice_url' => null,
        'checkout_url' => null,
        'direct_post_url' => null,
        'creator_agent' => 'testing',
        'platform' => 'api',
        'product' => 'purchases',
        'created_from_ip' => '127.0.0.1',
        'marked_as_paid' => true,
        'order_id' => 'ORDER-1',
        'created_at' => $now,
        'updated_at' => $now,
    ], $overrides));
}

function createChipPayment(string $purchaseId, array $overrides = []): ChipPayment
{
    ChipPayment::setConnectionResolver(app('db'));
    ChipPayment::setEventDispatcher(app('events'));

    $now = now();

    return ChipPayment::query()->create(array_merge([
        'id' => Str::uuid()->toString(),
        'purchase_id' => $purchaseId,
        'payment_type' => 'purchase',
        'is_outgoing' => false,
        'amount' => 10000,
        'currency' => 'MYR',
        'net_amount' => 9500,
        'fee_amount' => 500,
        'pending_amount' => 0,
        'pending_unfreeze_on' => null,
        'description' => 'Gateway settlement',
        'paid_on' => $now->timestamp,
        'remote_paid_on' => $now->timestamp,
        'created_on' => $now->copy()->subMinutes(5)->timestamp,
        'updated_on' => $now->timestamp,
        'created_at' => $now,
        'updated_at' => $now,
    ], $overrides));
}
