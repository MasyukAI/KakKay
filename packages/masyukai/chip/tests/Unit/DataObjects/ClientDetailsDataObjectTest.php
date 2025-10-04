<?php

declare(strict_types=1);

use MasyukAI\Chip\DataObjects\ClientDetails;

describe('ClientDetails data object', function (): void {
    it('creates client details from array data', function (): void {
        $data = [
            'bank_account' => '1234567890',
            'bank_code' => 'MBBEMYKL',
            'email' => 'customer@example.com',
            'phone' => '+60123456789',
            'full_name' => 'Jane Customer',
            'personal_code' => '123456-78-9012',
            'street_address' => '123, Jalan Test',
            'country' => 'MY',
            'city' => 'Kuala Lumpur',
            'zip_code' => '50450',
            'state' => 'KL',
            'shipping_street_address' => 'Warehouse 1',
            'shipping_country' => 'MY',
            'shipping_city' => 'Kuala Lumpur',
            'shipping_zip_code' => '50450',
            'shipping_state' => 'KL',
            'cc' => ['finance@example.com'],
            'bcc' => ['audit@example.com'],
            'legal_name' => 'Example Sdn Bhd',
            'brand_name' => 'Example',
            'registration_number' => '202201234567',
            'tax_number' => 'TX1234567',
        ];

        $details = ClientDetails::fromArray($data);

        expect($details->email)->toBe('customer@example.com');
        expect($details->shipping_city)->toBe('Kuala Lumpur');
        expect($details->toArray())->toMatchArray($data);
    });

    it('uses defaults for missing optional fields', function (): void {
        $details = ClientDetails::fromArray([]);

        expect($details->email)->toBeNull();
        expect($details->cc)->toBe([]);
        expect($details->toArray())->toMatchArray([
            'bank_account' => null,
            'bank_code' => null,
            'email' => null,
            'phone' => null,
            'full_name' => null,
            'personal_code' => null,
            'street_address' => null,
            'country' => null,
            'city' => null,
            'zip_code' => null,
            'state' => null,
            'shipping_street_address' => null,
            'shipping_country' => null,
            'shipping_city' => null,
            'shipping_zip_code' => null,
            'shipping_state' => null,
            'cc' => [],
            'bcc' => [],
            'legal_name' => null,
            'brand_name' => null,
            'registration_number' => null,
            'tax_number' => null,
        ]);
    });
});
