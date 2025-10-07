<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MasyukAI\Chip\DataObjects\Webhook;

final class ChipWebhookFactory
{
    public static function fromRequest(Request $request, ?string $webhookId, string $publicKey): Webhook
    {
        $payload = $request->all();
        $event = $payload['event_type'] ?? $payload['event'] ?? null;
        $data = $payload; // CHIP puts all purchase data at root level

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
