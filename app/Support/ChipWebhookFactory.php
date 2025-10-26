<?php

declare(strict_types=1);

namespace App\Support;

use AIArmada\Chip\DataObjects\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ChipWebhookFactory
{
    public static function fromRequest(Request $request, ?string $webhookId, string $publicKey): Webhook
    {
        $payload = $request->all();
        $event = $payload['event_type'] ?? $payload['event'] ?? null;

        // CHIP can send data in two formats:
        // 1. Nested: {'event': 'purchase.paid', 'data': {'id': '123', ...}}
        // 2. Flat: {'event_type': 'purchase.paid', 'id': '123', ...}
        $data = $payload['data'] ?? $payload;

        $timestamp = $payload['timestamp'] ?? now()->toIso8601String();
        $epoch = is_numeric($timestamp)
            ? (int) $timestamp
            : ((strtotime((string) $timestamp)) ?: time());

        $identifier = $data['id']
            ?? $payload['id']
            ?? ($webhookId ? $webhookId.'-'.Str::uuid()->toString() : Str::uuid()->toString());

        $composedPayload = $payload + [
            'webhook_id' => $webhookId,
        ];

        Log::debug('Constructing CHIP webhook data object', [
            'event' => $event,
            'identifier' => $identifier,
            'has_nested_data' => isset($payload['data']),
        ]);

        return Webhook::fromArray([
            'id' => (string) $identifier,
            'type' => 'webhook_event',
            'created_on' => $epoch,
            'updated_on' => $epoch,
            'title' => is_string($event) ? $event : 'webhook-event',
            'all_events' => false,
            'public_key' => $publicKey,
            'events' => is_string($event) ? [$event] : [],
            'callback' => '',
            'event' => is_string($event) ? $event : null,
            'data' => is_array($data) ? $data : [],
            'timestamp' => is_numeric($timestamp) ? date(DATE_ATOM, (int) $timestamp) : (string) $timestamp,
            'payload' => $composedPayload,
            'headers' => $request->headers->all(),
            'signature' => $request->header('X-Signature'),
            'verified' => true,
            'processed' => false,
            'processing_attempts' => 0,
        ]);
    }
}
