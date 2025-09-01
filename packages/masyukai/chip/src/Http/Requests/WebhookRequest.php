<?php

declare(strict_types=1);

namespace Masyukai\Chip\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Masyukai\Chip\Services\WebhookService;

class WebhookRequest extends FormRequest
{
    protected ?object $payload = null;

    public function authorize(): bool
    {
        if (!config('chip.webhooks.verify_signature')) {
            return true;
        }

        try {
            $signature = $this->header('X-Signature');
            $payload = $this->getContent();
            $webhookId = $this->route('webhook_id');

            if (!$signature || !$payload) {
                return false;
            }

            $webhookService = app(WebhookService::class);
            $publicKey = $webhookService->getPublicKey($webhookId);

            return $webhookService->verifySignature($payload, $signature, $publicKey);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validation rules for webhook request
     */
    public function rules(): array
    {
        return [
            'event' => 'required|string',
            'data' => 'required|array',
            'timestamp' => 'nullable|date',
        ];
    }

    public function getEvent(): ?string
    {
        return $this->input('event');
    }

    public function getData(): array
    {
        return $this->input('data', []);
    }

    public function getSignature(): ?string
    {
        return $this->header('X-Signature');
    }

    public function getWebhookPayload(): object
    {
        if ($this->payload === null) {
            $webhookService = app(WebhookService::class);
            $this->payload = $webhookService->parsePayload($this->getContent());
        }

        return $this->payload;
    }

    public function isEvent(string $eventType): bool
    {
        return $this->getEvent() === $eventType;
    }

    public function isPurchaseEvent(): bool
    {
        $event = $this->getEvent();
        return $event && str_starts_with($event, 'purchase.');
    }

    public function getPurchaseId(): ?string
    {
        if (!$this->isPurchaseEvent()) {
            return null;
        }

        $data = $this->getData();
        return $data['id'] ?? null;
    }

    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    public function getEventType(): string
    {
        return $this->getWebhookPayload()->event_type ?? 'unknown';
    }
}
