<?php

declare(strict_types=1);

namespace Masyukai\Chip\DataObjects;

use Carbon\Carbon;

class SendInstruction
{
    public function __construct(
        public readonly int $id,
        public readonly int $bank_account_id,
        public readonly string $amount,
        public readonly string $state,
        public readonly ?string $email,
        public readonly ?string $description,
        public readonly ?string $reference,
        public readonly string $created_at,
        public readonly string $updated_at,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            bank_account_id: (int) $data['bank_account_id'],
            amount: (string) $data['amount'],
            state: $data['state'] ?? 'pending',
            email: $data['email'] ?? null,
            description: $data['description'] ?? null,
            reference: $data['reference'] ?? null,
            created_at: $data['created_at'],
            updated_at: $data['updated_at'],
        );
    }

    public function getCreatedAt(): Carbon
    {
        return Carbon::parse($this->created_at);
    }

    public function getUpdatedAt(): Carbon
    {
        return Carbon::parse($this->updated_at);
    }

    public function getAmountInMajorUnits(): float
    {
        return (float) $this->amount;
    }

    public function getAmountInMinorUnits(): int
    {
        return (int) (((float) $this->amount) * 100);
    }

    public function isCompleted(): bool
    {
        return $this->state === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->state === 'cancelled';
    }

    public function isPending(): bool
    {
        return $this->state === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->state === 'failed';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'bank_account_id' => $this->bank_account_id,
            'amount' => $this->amount,
            'state' => $this->state,
            'email' => $this->email,
            'description' => $this->description,
            'reference' => $this->reference,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
