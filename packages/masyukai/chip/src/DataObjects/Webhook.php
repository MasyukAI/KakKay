<?php

declare(strict_types=1);

namespace MasyukAI\Chip\DataObjects;

use Carbon\Carbon;

class Webhook
{
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
        // Additional properties for webhook events
        public readonly ?string $event = null,
        public readonly ?array $data = null,
        public readonly ?string $timestamp = null,
    ) {}

    public static function fromArray(array $data): self
    {
        // Handle webhook event data (from webhook payload)
        if (isset($data['event'])) {
            return new self(
                id: $data['id'] ?? 'webhook_event_'.uniqid(),
                type: 'webhook_event',
                created_on: isset($data['timestamp']) ? strtotime($data['timestamp']) : time(),
                updated_on: isset($data['timestamp']) ? strtotime($data['timestamp']) : time(),
                title: 'Webhook Event',
                all_events: false,
                public_key: '',
                events: [$data['event']],
                callback: '',
                event: $data['event'],
                data: $data['data'] ?? null,
                timestamp: $data['timestamp'] ?? null,
            );
        }

        // Handle webhook configuration data (for webhook endpoints)
        return new self(
            id: $data['id'] ?? 'webhook_'.uniqid(),
            type: $data['type'] ?? 'webhook',
            created_on: $data['created_on'] ?? strtotime($data['created_at'] ?? 'now'),
            updated_on: $data['updated_on'] ?? strtotime($data['updated_at'] ?? 'now'),
            title: $data['title'] ?? '',
            all_events: $data['all_events'] ?? false,
            public_key: $data['public_key'] ?? '',
            events: $data['events'] ?? [],
            callback: $data['callback'] ?? '',
            event: null,
            data: null,
            timestamp: null,
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

    public function handlesEvent(string $eventType): bool
    {
        return $this->all_events || in_array($eventType, $this->events);
    }

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
        ];
    }
}
