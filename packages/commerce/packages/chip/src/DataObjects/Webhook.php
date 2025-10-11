<?php

declare(strict_types=1);

namespace AIArmada\Chip\DataObjects;

use Carbon\Carbon;

/**
 * Value object representing both stored webhook configurations and individual webhook event deliveries.
 * Incoming deliveries populate the event/payload/headers fields, while stored endpoint definitions
 * expose configuration metadata alongside processing status flags.
 */
final class Webhook
{
    /**
     * @param  array<string, mixed>  $events
     * @param  array<string, mixed>|null  $data
     * @param  array<string, mixed>|null  $payload
     * @param  array<string, string>|null  $headers
     * @param  array<string>  $unique_brands
     */
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly int $created_on,
        public readonly int $updated_on,
        public readonly string $title,
        public readonly bool $all_events,
        public readonly string $public_key,
        public readonly array $events,
        public readonly string $callback,
        public readonly bool $is_test,
        public readonly string $version,
        public readonly array $unique_brands,
        public readonly ?string $event = null,
        public readonly ?array $data = null,
        public readonly ?string $timestamp = null,
        public readonly ?string $event_type = null,
        public readonly ?array $payload = null,
        public readonly ?array $headers = null,
        public readonly ?string $signature = null,
        public readonly bool $verified = false,
        public readonly bool $processed = false,
        public readonly ?string $processed_at = null,
        public readonly ?string $processing_error = null,
        public readonly int $processing_attempts = 0,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        // Handle webhook event data (live delivery payload)
        if (isset($data['event'])) {
            /** @var array<string, mixed> $events */
            $events = is_array($data['event']) ? $data['event'] : [$data['event']];
            $events = array_combine(array_map('strval', array_keys($events)), array_values($events));

            /** @var array<string> $uniqueBrands */
            $uniqueBrands = $data['unique_brands'] ?? [];

            return new self(
                id: $data['id'] ?? 'webhook_event_'.uniqid(),
                type: 'webhook_event',
                created_on: isset($data['timestamp']) ? strtotime((string) $data['timestamp']) : time(),
                updated_on: isset($data['timestamp']) ? strtotime((string) $data['timestamp']) : time(),
                title: 'Webhook Event',
                all_events: false,
                public_key: '',
                events: $events,
                callback: '',
                is_test: (bool) ($data['is_test'] ?? false),
                version: $data['version'] ?? 'v1',
                unique_brands: $uniqueBrands,
                event: is_string($data['event']) ? $data['event'] : null,
                data: $data['data'] ?? null,
                timestamp: isset($data['timestamp']) ? (string) $data['timestamp'] : null,
                event_type: $data['event_type'] ?? (is_string($data['event']) ? $data['event'] : null),
                payload: $data['payload'] ?? null,
                headers: $data['headers'] ?? null,
                signature: $data['signature'] ?? null,
                verified: (bool) ($data['verified'] ?? false),
                processed: (bool) ($data['processed'] ?? false),
                processed_at: isset($data['processed_at']) ? (string) $data['processed_at'] : null,
                processing_error: $data['processing_error'] ?? null,
                processing_attempts: (int) ($data['processing_attempts'] ?? 0),
            );
        }

        // Handle webhook configuration data (persisted endpoint definition)
        /** @var array<string, mixed> $events */
        $events = $data['events'] ?? [];
        $events = array_combine(array_map('strval', array_keys($events)), array_values($events));

        /** @var array<string> $uniqueBrands */
        $uniqueBrands = $data['unique_brands'] ?? [];

        return new self(
            id: $data['id'] ?? 'webhook_'.uniqid(),
            type: $data['type'] ?? 'webhook',
            created_on: $data['created_on'] ?? strtotime((string) ($data['created_at'] ?? 'now')),
            updated_on: $data['updated_on'] ?? strtotime((string) ($data['updated_at'] ?? 'now')),
            title: $data['title'] ?? '',
            all_events: $data['all_events'] ?? false,
            public_key: $data['public_key'] ?? '',
            events: $events,
            callback: $data['callback'] ?? '',
            is_test: (bool) ($data['is_test'] ?? false),
            version: $data['version'] ?? 'v1',
            unique_brands: $uniqueBrands,
            event: null,
            data: null,
            timestamp: null,
            event_type: $data['event_type'] ?? null,
            payload: $data['payload'] ?? null,
            headers: $data['headers'] ?? null,
            signature: $data['signature'] ?? null,
            verified: (bool) ($data['verified'] ?? false),
            processed: (bool) ($data['processed'] ?? false),
            processed_at: isset($data['processed_at']) ? (string) $data['processed_at'] : null,
            processing_error: $data['processing_error'] ?? null,
            processing_attempts: (int) ($data['processing_attempts'] ?? 0),
        );
    }

    public function getPurchase(): ?Purchase
    {
        // Only return purchase for purchase-related events
        if ($this->event && str_starts_with($this->event, 'purchase.') && $this->data) {
            return Purchase::fromArray($this->data);
        }

        return null;
    }

    public function getCreatedAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->created_on);
    }

    public function getUpdatedAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->updated_on);
    }

    public function getProcessedAt(): ?Carbon
    {
        return $this->processed_at ? Carbon::parse($this->processed_at) : null;
    }

    public function handlesEvent(string $eventType): bool
    {
        return $this->all_events || in_array($eventType, $this->events);
    }

    public function isTestWebhook(): bool
    {
        return $this->is_test;
    }

    public function isVersion1(): bool
    {
        return $this->version === 'v1';
    }

    public function hasBrands(): bool
    {
        return count($this->unique_brands) > 0;
    }

    /**
     * @return array<string>
     */
    public function getBrandIds(): array
    {
        return $this->unique_brands;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'created_on' => $this->created_on,
            'updated_on' => $this->updated_on,
            'title' => $this->title,
            'all_events' => $this->all_events,
            'public_key' => $this->public_key,
            'events' => $this->events,
            'callback' => $this->callback,
            'is_test' => $this->is_test,
            'version' => $this->version,
            'unique_brands' => $this->unique_brands,
            'event' => $this->event,
            'data' => $this->data,
            'timestamp' => $this->timestamp,
            'event_type' => $this->event_type,
            'payload' => $this->payload,
            'headers' => $this->headers,
            'signature' => $this->signature,
            'verified' => $this->verified,
            'processed' => $this->processed,
            'processed_at' => $this->processed_at,
            'processing_error' => $this->processing_error,
            'processing_attempts' => $this->processing_attempts,
        ];
    }
}
