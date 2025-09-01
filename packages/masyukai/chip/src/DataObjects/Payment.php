<?php

declare(strict_types=1);

namespace Masyukai\Chip\DataObjects;

use Carbon\Carbon;

class Payment
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly int $created_on,
        public readonly int $updated_on,
        public readonly ClientDetails $client,
        public readonly bool $is_outgoing,
        public readonly ?string $payment_type,
        public readonly int $amount,
        public readonly string $currency,
        public readonly int $net_amount,
        public readonly int $fee_amount,
        public readonly int $pending_amount,
        public readonly ?int $pending_unfreeze_on,
        public readonly ?string $description,
        public readonly ?int $paid_on,
        public readonly ?int $remote_paid_on,
        public readonly array $transaction_data,
        public readonly ?object $related_to,
        public readonly string $reference_generated,
        public readonly ?string $reference,
        public readonly string $account_id,
        public readonly string $company_id,
        public readonly bool $is_test,
        public readonly ?string $user_id,
        public readonly string $brand_id,
    ) {}

    public static function fromArray(array $data): self
    {
        // Handle test data structure vs API response structure
        $relatedTo = null;
        if (isset($data['purchase_id'])) {
            $relatedTo = (object) ['purchase_id' => $data['purchase_id']];
        } elseif (isset($data['related_to'])) {
            $relatedTo = (object) $data['related_to'];
        }

        $amount = $data['payment']['amount'] ?? $data['amount'] ?? $data['amount_in_cents'] ?? 0;
        $feeAmount = $data['payment']['fee_amount'] ?? $data['fee_amount'] ?? $data['transaction_fee_in_cents'] ?? 0;
        $netAmount = $data['payment']['net_amount'] ?? $data['net_amount'] ?? ($amount - $feeAmount);

        return new self(
            id: $data['id'],
            type: $data['type'] ?? 'payment',
            created_on: $data['created_on'] ?? strtotime($data['created_at'] ?? 'now'),
            updated_on: $data['updated_on'] ?? strtotime($data['updated_at'] ?? 'now'),
            client: isset($data['client']) ? ClientDetails::fromArray($data['client']) : ClientDetails::fromArray([]),
            is_outgoing: $data['payment']['is_outgoing'] ?? $data['is_outgoing'] ?? false,
            payment_type: $data['payment']['payment_type'] ?? $data['payment_type'] ?? $data['method'] ?? null,
            amount: $amount,
            currency: $data['payment']['currency'] ?? $data['currency'] ?? 'MYR',
            net_amount: $netAmount,
            fee_amount: $feeAmount,
            pending_amount: $data['payment']['pending_amount'] ?? $data['pending_amount'] ?? 0,
            pending_unfreeze_on: $data['payment']['pending_unfreeze_on'] ?? $data['pending_unfreeze_on'] ?? null,
            description: $data['payment']['description'] ?? $data['description'] ?? null,
            paid_on: $data['payment']['paid_on'] ?? $data['paid_on'] ?? (isset($data['paid_at']) ? strtotime($data['paid_at']) : null),
            remote_paid_on: $data['payment']['remote_paid_on'] ?? $data['remote_paid_on'] ?? null,
            transaction_data: $data['transaction_data'] ?? $data['metadata'] ?? [],
            related_to: $relatedTo,
            reference_generated: $data['reference_generated'] ?? $data['reference'] ?? '',
            reference: $data['reference'] ?? null,
            account_id: $data['account_id'] ?? '',
            company_id: $data['company_id'] ?? '',
            is_test: $data['is_test'] ?? true,
            user_id: $data['user_id'] ?? null,
            brand_id: $data['brand_id'] ?? '',
        );
    }

    public function getCreatedAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->created_on);
    }

    public function getUpdatedAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->updated_on);
    }

    public function getPaidAt(): ?Carbon
    {
        return $this->paid_on ? Carbon::createFromTimestamp($this->paid_on) : null;
    }

    public function getRemotePaidAt(): ?Carbon
    {
        return $this->remote_paid_on ? Carbon::createFromTimestamp($this->remote_paid_on) : null;
    }

    public function getAmountInCurrency(): float
    {
        return $this->amount / 100;
    }

    public function getNetAmountInCurrency(): float
    {
        return $this->net_amount / 100;
    }

    public function getFeeAmountInCurrency(): float
    {
        return $this->fee_amount / 100;
    }

    // Compatibility properties for tests
    public function __get($name)
    {
        return match($name) {
            'purchaseId' => $this->related_to?->purchase_id ?? null,
            'amountInCents' => $this->amount,
            'method' => $this->payment_type,
            'transactionFeeInCents' => $this->fee_amount,
            'status' => $this->getStatus(),
            'paidAt' => $this->paid_on ? $this->getPaidAt() : null,
            default => throw new \InvalidArgumentException("Property {$name} does not exist")
        };
    }

    public function __isset($name): bool
    {
        return in_array($name, ['purchaseId', 'amountInCents', 'method', 'transactionFeeInCents', 'status', 'paidAt']);
    }

    private function getStatus(): string
    {
        if ($this->paid_on) {
            return 'successful';
        }
        return 'pending';
    }

    public function getNetAmountInCents(): int
    {
        return $this->net_amount;
    }

    public function getNetAmountInMajorUnits(): float
    {
        return $this->net_amount / 100;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'client' => $this->client->toArray(),
            'payment' => [
                'is_outgoing' => $this->is_outgoing,
                'payment_type' => $this->payment_type,
                'amount' => $this->amount,
                'currency' => $this->currency,
                'net_amount' => $this->net_amount,
                'fee_amount' => $this->fee_amount,
                'pending_amount' => $this->pending_amount,
                'pending_unfreeze_on' => $this->pending_unfreeze_on,
                'description' => $this->description,
                'paid_on' => $this->paid_on,
                'remote_paid_on' => $this->remote_paid_on,
            ],
            'transaction_data' => $this->transaction_data,
            'related_to' => $this->related_to,
            'reference_generated' => $this->reference_generated,
            'reference' => $this->reference,
            'account_id' => $this->account_id,
            'company_id' => $this->company_id,
            'is_test' => $this->is_test,
            'user_id' => $this->user_id,
            'brand_id' => $this->brand_id,
        ];
    }
}
