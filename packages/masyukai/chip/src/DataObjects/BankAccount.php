<?php

declare(strict_types=1);

namespace Masyukai\Chip\DataObjects;

use Carbon\Carbon;

class BankAccount
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly int $created_on,
        public readonly int $updated_on,
        public readonly string $account_number,
        public readonly string $bank_code,
        public readonly string $account_name,
        public readonly string $status,
        public readonly ?array $validation_data,
        // Additional properties for test compatibility
        public readonly ?string $account_holder_name,
        public readonly ?string $account_type,
        public readonly ?bool $is_active,
        public readonly ?bool $is_verified,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            type: $data['type'] ?? 'bank_account',
            created_on: $data['created_on'] ?? strtotime($data['created_at'] ?? 'now'),
            updated_on: $data['updated_on'] ?? strtotime($data['updated_at'] ?? 'now'),
            account_number: $data['account_number'] ?? '',
            bank_code: $data['bank_code'] ?? '',
            account_name: $data['account_name'] ?? '',
            status: $data['status'] ?? 'active',
            validation_data: $data['validation_data'] ?? null,
            account_holder_name: $data['account_holder_name'] ?? null,
            account_type: $data['account_type'] ?? null,
            is_active: $data['is_active'] ?? null,
            is_verified: $data['is_verified'] ?? null,
        );
    }

    // Compatibility properties for tests
    public function __get($name)
    {
        return match($name) {
            'bankCode' => $this->bank_code,
            'accountNumber' => $this->account_number,
            'accountName' => $this->account_name,
            'accountHolderName' => $this->account_holder_name,
            'accountType' => $this->account_type,
            'isActive' => $this->is_active ?? ($this->status === 'active'),
            'isVerified' => $this->is_verified ?? ($this->status === 'verified'),
            default => null,
        };
    }

    public function __isset($name): bool
    {
        return in_array($name, ['bankCode', 'accountNumber', 'accountName', 'accountHolderName', 'accountType', 'isActive', 'isVerified']);
    }

    public function getCreatedAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->created_on);
    }

    public function getUpdatedAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->updated_on);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'account_number' => $this->account_number,
            'bank_code' => $this->bank_code,
            'account_name' => $this->account_name,
            'status' => $this->status,
            'validation_data' => $this->validation_data,
        ];
    }
}
