<?php

declare(strict_types=1);

namespace MasyukAI\Chip\DataObjects;

use Carbon\Carbon;

final class Purchase
{
    public function __construct(
        public readonly string $id, // UUID as string
        public readonly string $type,
        public readonly int $created_on, // Unix timestamp from API
        public readonly int $updated_on, // Unix timestamp from API
        public readonly ClientDetails $client,
        public readonly PurchaseDetails $purchase,
        public readonly string $brand_id, // UUID as string
        public readonly ?Payment $payment,
        public readonly IssuerDetails $issuer_details,
        public readonly TransactionData $transaction_data,
        public readonly string $status,
        /** @var array<string, mixed> */
        public readonly array $status_history,
        public readonly ?int $viewed_on, // Unix timestamp or null
        public readonly string $company_id, // UUID as string
        public readonly bool $is_test,
        public readonly ?string $user_id, // UUID as string or null
        public readonly ?string $billing_template_id, // UUID as string or null
        public readonly ?string $client_id, // UUID as string or null
        public readonly bool $send_receipt,
        public readonly bool $is_recurring_token,
        public readonly ?string $recurring_token, // UUID as string or null
        public readonly bool $skip_capture,
        public readonly bool $force_recurring,
        public readonly string $reference_generated,
        public readonly ?string $reference,
        public readonly ?string $notes,
        public readonly ?string $issued, // ISO 8601 date string
        public readonly ?int $due, // Unix timestamp or null
        public readonly string $refund_availability,
        public readonly int $refundable_amount,
        public readonly ?CurrencyConversion $currency_conversion,
        /** @var array<string, mixed> */
        public readonly array $payment_method_whitelist,
        public readonly ?string $success_redirect,
        public readonly ?string $failure_redirect,
        public readonly ?string $cancel_redirect,
        public readonly ?string $success_callback,
        public readonly ?string $creator_agent,
        public readonly string $platform,
        public readonly string $product,
        public readonly ?string $created_from_ip,
        public readonly ?string $invoice_url,
        public readonly ?string $checkout_url,
        public readonly ?string $direct_post_url,
        public readonly bool $marked_as_paid,
        public readonly ?string $order_id,
    ) {}

    /**
     * Magic property accessor for convenient access to nested properties
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        return match ($name) {
            'amountInCents' => $this->purchase->total,
            'currency' => $this->purchase->currency,
            'checkoutUrl' => $this->checkout_url,
            'metadata' => ($this->purchase->metadata !== null && count($this->purchase->metadata) > 0) ? $this->purchase->metadata : null,
            'clientId' => $this->client_id,
            'isRecurring' => $this->is_recurring_token,
            default => null,
        };
    }

    /**
     * @param  string  $name
     */
    public function __isset($name): bool
    {
        return in_array($name, ['amountInCents', 'currency', 'checkoutUrl', 'isRecurring', 'metadata', 'clientId']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        // Handle both API response structure and simplified test data

        // For timestamps, convert string dates to timestamps if needed
        $created_on = $data['created_on'] ?? null;
        if (is_string($created_on)) {
            $created_on = strtotime($created_on) ?: null;
        }
        if ($created_on === null && isset($data['created_at'])) {
            $created_at = $data['created_at'];
            $created_on = is_string($created_at) ? strtotime($created_at) : $created_at;
        }
        $created_on = $created_on ?? time();

        $updated_on = $data['updated_on'] ?? null;
        if (is_string($updated_on)) {
            $updated_on = strtotime($updated_on) ?: null;
        }
        if ($updated_on === null && isset($data['updated_at'])) {
            $updated_at = $data['updated_at'];
            $updated_on = is_string($updated_at) ? strtotime($updated_at) : $updated_at;
        }
        $updated_on = $updated_on ?? time();

        // Handle client data
        $client = null;
        if (isset($data['client'])) {
            $client = ClientDetails::fromArray($data['client']);
        } else {
            // Create minimal client for test data
            $client = ClientDetails::fromArray([]);
        }

        // Handle purchase data
        $purchase = null;
        if (isset($data['purchase'])) {
            $purchase = PurchaseDetails::fromArray($data['purchase']);
        } else {
            // Create purchase from flat test data structure
            $purchase = PurchaseDetails::fromArray([
                'total' => $data['amount_in_cents'] ?? 0,
                'currency' => $data['currency'] ?? 'MYR',
                'products' => [],
                'metadata' => $data['metadata'] ?? null,
            ]);
        }

        return new self(
            id: $data['id'],
            type: $data['type'] ?? 'purchase',
            created_on: $created_on,
            updated_on: $updated_on,
            client: $client,
            purchase: $purchase,
            brand_id: $data['brand_id'] ?? '',
            payment: isset($data['payment']) ? Payment::fromArray($data['payment']) : null,
            issuer_details: isset($data['issuer_details']) ? IssuerDetails::fromArray($data['issuer_details']) : IssuerDetails::fromArray(['legal_name' => '']),
            transaction_data: isset($data['transaction_data']) ? TransactionData::fromArray($data['transaction_data']) : TransactionData::fromArray(['payment_method' => '', 'attempts' => []]),
            status: $data['status'] ?? 'created',
            status_history: $data['status_history'] ?? [],
            viewed_on: $data['viewed_on'] ?? null,
            company_id: $data['company_id'] ?? '',
            is_test: $data['is_test'] ?? true,
            user_id: $data['user_id'] ?? null,
            billing_template_id: $data['billing_template_id'] ?? null,
            client_id: $data['client_id'] ?? null,
            send_receipt: $data['send_receipt'] ?? false,
            is_recurring_token: $data['is_recurring_token'] ?? ($data['is_recurring'] ?? false),
            recurring_token: $data['recurring_token'] ?? null,
            skip_capture: $data['skip_capture'] ?? false,
            force_recurring: $data['force_recurring'] ?? false,
            reference_generated: $data['reference_generated'] ?? ($data['reference'] ?? ''),
            reference: $data['reference'] ?? null,
            notes: $data['notes'] ?? null,
            issued: $data['issued'] ?? null,
            due: $data['due'] ?? null,
            refund_availability: $data['refund_availability'] ?? 'all',
            refundable_amount: $data['refundable_amount'] ?? 0,
            currency_conversion: isset($data['currency_conversion']) ? CurrencyConversion::fromArray($data['currency_conversion']) : null,
            payment_method_whitelist: $data['payment_method_whitelist'] ?? [],
            success_redirect: $data['success_redirect'] ?? null,
            failure_redirect: $data['failure_redirect'] ?? null,
            cancel_redirect: $data['cancel_redirect'] ?? null,
            success_callback: $data['success_callback'] ?? null,
            creator_agent: $data['creator_agent'] ?? null,
            platform: $data['platform'] ?? 'api',
            product: $data['product'] ?? 'purchases',
            created_from_ip: $data['created_from_ip'] ?? null,
            invoice_url: $data['invoice_url'] ?? null,
            checkout_url: $data['checkout_url'] ?? null,
            direct_post_url: $data['direct_post_url'] ?? null,
            marked_as_paid: $data['marked_as_paid'] ?? false,
            order_id: $data['order_id'] ?? null,
        );
    }

    public function getAmountInMajorUnits(): float
    {
        return $this->purchase->total / 100;
    }

    public function getCreatedAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->created_on);
    }

    public function getUpdatedAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->updated_on);
    }

    public function getViewedAt(): ?Carbon
    {
        return $this->viewed_on ? Carbon::createFromTimestamp($this->viewed_on) : null;
    }

    public function getDueDate(): ?Carbon
    {
        return $this->due ? Carbon::createFromTimestamp($this->due) : null;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isOnHold(): bool
    {
        return $this->status === 'hold';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending_execute', 'pending_capture', 'pending_charge', 'pending_refund', 'pending_release']);
    }

    public function hasError(): bool
    {
        return in_array($this->status, ['error', 'blocked']);
    }

    public function canBeRefunded(): bool
    {
        return in_array($this->refund_availability, ['all', 'full_only', 'partial_only']);
    }

    public function canBePartiallyRefunded(): bool
    {
        return in_array($this->refund_availability, ['all', 'partial_only']);
    }

    public function getAmountInCurrency(): float
    {
        return $this->purchase->total / 100;
    }

    public function getRefundableAmountInCurrency(): float
    {
        return $this->refundable_amount / 100;
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
            'client' => $this->client->toArray(),
            'purchase' => $this->purchase->toArray(),
            'brand_id' => $this->brand_id,
            'payment' => $this->payment?->toArray(),
            'issuer_details' => $this->issuer_details->toArray(),
            'transaction_data' => $this->transaction_data->toArray(),
            'status' => $this->status,
            'status_history' => $this->status_history,
            'viewed_on' => $this->viewed_on,
            'company_id' => $this->company_id,
            'is_test' => $this->is_test,
            'user_id' => $this->user_id,
            'billing_template_id' => $this->billing_template_id,
            'client_id' => $this->client_id,
            'send_receipt' => $this->send_receipt,
            'is_recurring_token' => $this->is_recurring_token,
            'recurring_token' => $this->recurring_token,
            'skip_capture' => $this->skip_capture,
            'force_recurring' => $this->force_recurring,
            'reference_generated' => $this->reference_generated,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'issued' => $this->issued,
            'due' => $this->due,
            'refund_availability' => $this->refund_availability,
            'refundable_amount' => $this->refundable_amount,
            'currency_conversion' => $this->currency_conversion?->toArray(),
            'payment_method_whitelist' => $this->payment_method_whitelist,
            'success_redirect' => $this->success_redirect,
            'failure_redirect' => $this->failure_redirect,
            'cancel_redirect' => $this->cancel_redirect,
            'success_callback' => $this->success_callback,
            'creator_agent' => $this->creator_agent,
            'platform' => $this->platform,
            'product' => $this->product,
            'created_from_ip' => $this->created_from_ip,
            'invoice_url' => $this->invoice_url,
            'checkout_url' => $this->checkout_url,
            'direct_post_url' => $this->direct_post_url,
            'marked_as_paid' => $this->marked_as_paid,
            'order_id' => $this->order_id,
        ];
    }
}
