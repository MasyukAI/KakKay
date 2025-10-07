<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Livewire\Livewire;
use MasyukAI\FilamentChip\Models\ChipClient;
use MasyukAI\FilamentChip\Resources\ClientResource\Pages\ListClients;
use MasyukAI\FilamentChip\Resources\ClientResource\Pages\ViewClient;

it('renders clients in the list table', function () {
    $client = createChipClient();

    Livewire::test(ListClients::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$client]);
});

it('renders client detail infolist', function () {
    $client = createChipClient();

    Livewire::test(ViewClient::class, [
        'record' => $client->getKey(),
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'email' => $client->email,
            'full_name' => $client->full_name,
            'bank_account' => $client->bank_account,
        ]);
});

function createChipClient(array $overrides = []): ChipClient
{
    ChipClient::setConnectionResolver(app('db'));
    ChipClient::setEventDispatcher(app('events'));

    $now = now();

    return ChipClient::query()->create(array_merge([
        'id' => Str::uuid()->toString(),
        'type' => 'client',
        'email' => 'client-'.Str::uuid()->toString().'@example.com',
        'phone' => '+60 12-345 6789',
        'full_name' => 'Jane Client',
        'personal_code' => 'PC-123',
        'street_address' => '123 Example Street',
        'city' => 'Kuala Lumpur',
        'state' => 'Wilayah Persekutuan',
        'zip_code' => '50000',
        'country' => 'MY',
        'shipping_street_address' => '123 Example Street',
        'shipping_city' => 'Kuala Lumpur',
        'shipping_state' => 'Wilayah Persekutuan',
        'shipping_zip_code' => '50000',
        'shipping_country' => 'MY',
        'cc' => ['finance@example.com'],
        'bcc' => ['audit@example.com'],
        'legal_name' => 'Client Sdn Bhd',
        'brand_name' => 'Client Co',
        'registration_number' => 'REG-123',
        'tax_number' => 'TAX-123',
        'bank_account' => 'MY00 1234 5678 9012',
        'bank_code' => 'CIMBMYKL',
        'created_on' => $now->copy()->subDay()->timestamp,
        'updated_on' => $now->timestamp,
        'created_at' => $now,
        'updated_at' => $now,
    ], $overrides));
}
