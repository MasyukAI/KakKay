<?php

declare(strict_types=1);

use MasyukAI\Chip\DataObjects\Client;

describe('Client data object', function () {
    it('creates a client from array data', function () {
        $data = [
            'id' => 'client_123',
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+60123456789',
            'address' => [
                'street1' => '123 Main Street',
                'city' => 'Kuala Lumpur',
                'state' => 'Selangor',
                'country' => 'MY',
            ],
            'identity_type' => 'nric',
            'identity_number' => '123456-78-9012',
            'date_of_birth' => '1990-01-01',
            'nationality' => 'MY',
        ];

        $client = Client::fromArray($data);

        expect($client->id)->toBe('client_123');
        expect($client->fullName)->toBe('John Doe');
        expect($client->email)->toBe('john@example.com');
        expect($client->phone)->toBe('+60123456789');
        expect($client->address)->toBe([
            'street1' => '123 Main Street',
            'city' => 'Kuala Lumpur',
            'state' => 'Selangor',
            'country' => 'MY',
        ]);
        expect($client->identityType)->toBe('nric');
        expect($client->identityNumber)->toBe('123456-78-9012');
    });

    it('handles minimal client data', function () {
        $client = Client::fromArray([
            'id' => 'client_123',
            'full_name' => 'John Doe',
        ]);

        expect($client->fullName)->toBe('John Doe');
        expect($client->email)->toBeNull();
        expect($client->phone)->toBeNull();
        expect($client->address)->toBeNull();
    });
});
