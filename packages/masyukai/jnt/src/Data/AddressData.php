<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

class AddressData
{
    public function __construct(
        public readonly string $name,
        public readonly string $phone,
        public readonly string $address,
        public readonly string $postCode,
        public readonly string $countryCode = 'MYS',
        public readonly ?string $prov = null,
        public readonly ?string $city = null,
        public readonly ?string $area = null,
        public readonly ?string $email = null,
        public readonly ?string $idCard = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            phone: $data['phone'],
            address: $data['address'],
            postCode: $data['postCode'],
            countryCode: $data['countryCode'] ?? 'MYS',
            prov: $data['prov'] ?? null,
            city: $data['city'] ?? null,
            area: $data['area'] ?? null,
            email: $data['email'] ?? null,
            idCard: $data['idCard'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'postCode' => $this->postCode,
            'countryCode' => $this->countryCode,
            'prov' => $this->prov,
            'city' => $this->city,
            'area' => $this->area,
            'email' => $this->email,
            'idCard' => $this->idCard,
        ], fn ($value) => $value !== null);
    }
}
