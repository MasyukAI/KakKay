<?php

declare(strict_types=1);

namespace Masyukai\Chip\DataObjects;

class TransactionData
{
    public function __construct(
        public readonly ?string $payment_method,
        public readonly array $extra,
        public readonly ?string $country,
        public readonly array $attempts,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            payment_method: $data['payment_method'] ?? null,
            extra: $data['extra'] ?? [],
            country: $data['country'] ?? null,
            attempts: $data['attempts'] ?? [],
        );
    }

    public function getLastAttempt(): ?array
    {
        return !empty($this->attempts) ? $this->attempts[0] : null;
    }

    public function hasFailedAttempts(): bool
    {
        return !empty(array_filter($this->attempts, fn($attempt) => !($attempt['successful'] ?? true)));
    }

    public function getFailedAttempts(): array
    {
        return array_filter($this->attempts, fn($attempt) => !($attempt['successful'] ?? true));
    }

    public function toArray(): array
    {
        return [
            'payment_method' => $this->payment_method,
            'extra' => $this->extra,
            'country' => $this->country,
            'attempts' => $this->attempts,
        ];
    }
}
