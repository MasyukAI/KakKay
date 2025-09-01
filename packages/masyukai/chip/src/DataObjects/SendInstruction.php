<?php

declare(strict_types=1);

namespace Masyukai\Chip\DataObjects;

use Carbon\Carbon;

class SendInstruction
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly int $created_on,
        public readonly int $updated_on,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $status,
        public readonly array $recipient,
        public readonly ?string $reference,
        public readonly ?string $description,
        public readonly ?int $executed_on,
        public readonly ?array $error,
        // Additional properties for test compatibility
        public readonly ?string $recipient_bank_account_id,
        public readonly ?array $recipient_details,
        public readonly ?string $failure_reason,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            type: $data['type'] ?? 'send_instruction',
            created_on: $data['created_on'] ?? strtotime($data['created_at'] ?? 'now'),
            updated_on: $data['updated_on'] ?? strtotime($data['updated_at'] ?? 'now'),
            amount: $data['amount'] ?? $data['amount_in_cents'] ?? 0,
            currency: $data['currency'] ?? 'MYR',
            status: $data['status'] ?? 'pending',
            recipient: is_array($data['recipient'] ?? []) ? ($data['recipient'] ?? []) : ['account_holder_name' => $data['recipient'] ?? ''],
            reference: $data['reference'] ?? null,
            description: $data['description'] ?? null,
            executed_on: $data['executed_on'] ?? null,
            error: $data['error'] ?? null,
            recipient_bank_account_id: $data['recipient_bank_account_id'] ?? null,
            recipient_details: $data['recipient_details'] ?? null,
            failure_reason: $data['failure_reason'] ?? null,
        );
    }

    // Compatibility properties for tests
    public function __get($name)
    {
        return match($name) {
            'amountInCents' => $this->amount,
            'recipientName' => $this->recipient['account_holder_name'] ?? null,
            'recipientAccount' => $this->recipient['account_number'] ?? null,
            'recipientBankAccountId' => $this->recipient_bank_account_id,
            'recipientDetails' => $this->recipient_details,
            'failureReason' => $this->failure_reason ?? $this->error['message'] ?? null,
            'completedAt' => $this->executed_on ? Carbon::createFromTimestamp($this->executed_on) : null,
            default => null,
        };
    }

    public function __isset($name): bool
    {
        return in_array($name, ['amountInCents', 'recipientName', 'recipientAccount', 'recipientBankAccountId', 'recipientDetails', 'failureReason', 'completedAt']);
    }

    public function getCreatedAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->created_on);
    }

    public function getUpdatedAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->updated_on);
    }

    public function getExecutedAt(): ?Carbon
    {
        return $this->executed_on ? Carbon::createFromTimestamp($this->executed_on) : null;
    }

    public function getAmountInCurrency(): float
    {
        return $this->amount / 100;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
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
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'recipient' => $this->recipient,
            'reference' => $this->reference,
            'description' => $this->description,
            'executed_on' => $this->executed_on,
            'error' => $this->error,
        ];
    }
}
