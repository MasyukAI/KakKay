<?php

declare(strict_types=1);

namespace MasyukAI\Chip\DataObjects;

class TransactionData
{
    /**
     * @param array<string, mixed> $extra
     * @param array<array<string, mixed>> $attempts
     */
    public function __construct(
        public readonly ?string $payment_method,
        public readonly array $extra,
        public readonly ?string $country,
        public readonly array $attempts,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            payment_method: $data['payment_method'] ?? null,
            extra: $data['extra'] ?? [],
            country: $data['country'] ?? null,
            attempts: $data['attempts'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLastAttempt(): ?array
    {
        return ! empty($this->attempts) ? $this->attempts[0] : null;
    }

    public function hasFailedAttempts(): bool
    {
        return ! empty(array_filter($this->attempts, fn ($attempt) => ! ($attempt['successful'] ?? true)));
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getFailedAttempts(): array
    {
        return array_filter($this->attempts, fn ($attempt) => ! ($attempt['successful'] ?? true));
    }

    /**
     * @return array<string, mixed>
     */
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
