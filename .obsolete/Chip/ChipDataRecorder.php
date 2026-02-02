<?php

declare(strict_types=1);

namespace App\Services\Chip;

use AIArmada\Chip\Data\PaymentData as ChipPayment;
use AIArmada\Chip\Data\PurchaseData as Purchase;
use AIArmada\Chip\Data\PurchaseDetailsData as PurchaseDetails;
use AIArmada\Chip\Data\WebhookData as Webhook;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ChipDataRecorder
{
    private readonly string $connection;

    private readonly string $prefix;

    /** @var array<string, bool> */
    private array $tableAvailability = [];

    /** @var array<string, array<int, string>> */
    private array $columnCache = [];

    public function __construct()
    {
        $this->connection = (string) config('chip.database.connection', config('database.default'));
        $this->prefix = (string) config('chip.database.table_prefix', 'chip_');
    }

    public function recordWebhook(Webhook $webhook): void
    {
        $table = $this->prefixed('webhooks');

        if (! $this->tableExists($table)) {
            Log::debug('Skipping CHIP webhook persistence because table is missing', [
                'table' => $table,
            ]);

            return;
        }

        $now = CarbonImmutable::now();
        $eventType = $webhook->event ?? $webhook->event_type;

        $columns = $this->getTableColumns($table);
        $keyColumn = $this->resolveWebhookKeyColumn($columns);

        if (! $keyColumn) {
            Log::debug('Unable to determine CHIP webhook key column', [
                'table' => $table,
                'columns' => $columns,
            ]);

            return;
        }

        $record = [];
        $this->setColumn($record, $columns, 'event_type', $eventType);
        $this->setColumn($record, $columns, 'payload', $this->jsonOrNull($webhook->payload ?? []));
        $this->setColumn($record, $columns, 'headers', $this->jsonOrNull($webhook->headers ?? []));
        $this->setColumn($record, $columns, 'signature', $webhook->signature);
        $this->setColumn($record, $columns, 'verified', $webhook->verified ? 1 : 0);
        $this->setColumn($record, $columns, 'processed', $webhook->processed ? 1 : 0);
        $this->setColumn($record, $columns, 'processed_at', $this->datetimeOrNull($webhook->processed_at));
        $this->setColumn($record, $columns, 'processing_error', $webhook->processing_error);
        $this->setColumn($record, $columns, 'processing_attempts', $webhook->processing_attempts ?? 0);
        $this->setColumn($record, $columns, 'title', $webhook->title ?? ($eventType ?? 'webhook'));
        $this->setColumn($record, $columns, 'events', $this->jsonOrNull($webhook->events ?? ($eventType ? [$eventType] : [])));
        $this->setColumn($record, $columns, 'callback', $webhook->callback ?? '');
        $this->setColumn($record, $columns, 'all_events', $webhook->all_events ? 1 : 0);
        $this->setColumn($record, $columns, 'public_key', $webhook->public_key);
        $this->setColumn($record, $columns, 'type', $webhook->type ?? 'webhook');
        $this->setColumn($record, $columns, 'created_on', $webhook->created_on);
        $this->setColumn($record, $columns, 'updated_on', $webhook->updated_on);
        $this->setColumn($record, $columns, 'updated_at', $now->toDateTimeString());

        Log::debug('Recording CHIP webhook payload', [
            'webhook_id' => $webhook->id,
            'event_type' => $eventType,
        ]);

        $connection = DB::connection($this->connection);
        $query = $connection->table($table);

        $exists = $query->where($keyColumn, $webhook->id)->exists();

        $insertPayload = $record;
        $this->setColumn($insertPayload, $columns, $keyColumn, $webhook->id);
        $this->setColumn($insertPayload, $columns, 'created_at', $now);

        if ($exists) {
            $query->where($keyColumn, $webhook->id)->update($record);

            return;
        }

        $connection->table($table)->insert($insertPayload);
    }

    public function markWebhookProcessed(string $webhookId, bool $success, ?string $error = null): void
    {
        $table = $this->prefixed('webhooks');

        if (! $this->tableExists($table)) {
            return;
        }

        $columns = $this->getTableColumns($table);
        $keyColumn = $this->resolveWebhookKeyColumn($columns);

        if (! $keyColumn) {
            return;
        }

        $now = CarbonImmutable::now();
        $updates = [];
        $this->setColumn($updates, $columns, 'processed', $success ? 1 : 0);
        $this->setColumn($updates, $columns, 'processed_at', $now->toDateTimeString());
        $this->setColumn($updates, $columns, 'processing_error', $error);

        if (in_array('processing_attempts', $columns, true)) {
            $updates['processing_attempts'] = DB::raw('processing_attempts + 1');
        }

        $this->setColumn($updates, $columns, 'updated_at', $now->toDateTimeString());

        if ($updates === []) {
            return;
        }

        DB::connection($this->connection)
            ->table($table)
            ->where($keyColumn, $webhookId)
            ->update($updates);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function upsertPurchase(array $payload): Purchase
    {
        $purchase = Purchase::from($payload);
        $table = $this->prefixed('purchases');

        if (! $this->tableExists($table)) {
            Log::debug('Skipping CHIP purchase persistence because table is missing', [
                'table' => $table,
                'purchase_id' => $purchase->id,
            ]);

            return $purchase;
        }

        $columns = $this->getTableColumns($table);
        $keyColumn = $this->resolvePurchaseKeyColumn($columns);

        if (! $keyColumn) {
            Log::debug('Unable to determine CHIP purchase key column', [
                'table' => $table,
                'columns' => $columns,
            ]);

            return $purchase;
        }

        $connection = DB::connection($this->connection);
        $query = $connection->table($table);
        $exists = $query->where($keyColumn, $purchase->id)->exists();
        $now = CarbonImmutable::now();

        $record = [];
        $this->setColumn($record, $columns, 'type', $purchase->type ?? 'purchase');
        $this->setColumn($record, $columns, 'status', $purchase->status);
        $this->setColumn($record, $columns, 'created_on', $purchase->created_on);
        $this->setColumn($record, $columns, 'updated_on', $purchase->updated_on);
        $this->setColumn($record, $columns, 'client', $this->jsonOrNull($purchase->client->toArray()));
        $this->setColumn($record, $columns, 'client_details', $this->jsonOrNull($purchase->client->toArray()));
        $this->setColumn($record, $columns, 'purchase', $this->jsonOrNull($this->purchaseDetailsArray($purchase->purchase)));
        $this->setColumn($record, $columns, 'purchase_details', $this->jsonOrNull($this->purchaseDetailsArray($purchase->purchase)));
        $this->setColumn($record, $columns, 'brand_id', $purchase->brand_id);
        $this->setColumn($record, $columns, 'company_id', $purchase->company_id);
        $this->setColumn($record, $columns, 'user_id', $purchase->user_id);
        $this->setColumn($record, $columns, 'billing_template_id', $purchase->billing_template_id);
        $this->setColumn($record, $columns, 'client_id', $purchase->client_id);

        $paymentJson = $purchase->payment ? $this->jsonOrNull($this->paymentArray($purchase->payment)) : null;
        $this->setColumn($record, $columns, 'payment', $paymentJson);
        $this->setColumn($record, $columns, 'payment_details', $paymentJson);

        $this->setColumn($record, $columns, 'issuer_details', $this->jsonOrNull($purchase->issuer_details->toArray()));
        $this->setColumn($record, $columns, 'transaction_data', $this->jsonOrNull($purchase->transaction_data->toArray()));
        $this->setColumn($record, $columns, 'status_history', $this->jsonOrNull($payload['status_history'] ?? []));
        $this->setColumn($record, $columns, 'viewed_on', $purchase->viewed_on);

        $this->setColumn($record, $columns, 'send_receipt', $purchase->send_receipt ? 1 : 0);
        $this->setColumn($record, $columns, 'is_test', $purchase->is_test ? 1 : 0);
        $this->setColumn($record, $columns, 'is_recurring_token', $purchase->is_recurring_token ? 1 : 0);
        $this->setColumn($record, $columns, 'recurring_token', $purchase->recurring_token);
        $this->setColumn($record, $columns, 'skip_capture', $purchase->skip_capture ? 1 : 0);
        $this->setColumn($record, $columns, 'force_recurring', $purchase->force_recurring ? 1 : 0);

        $this->setColumn($record, $columns, 'reference', $purchase->reference);
        $this->setColumn($record, $columns, 'reference_generated', $purchase->reference_generated ? '1' : ($purchase->reference_generated ?? null));
        $this->setColumn($record, $columns, 'notes', $purchase->notes);
        $this->setColumn($record, $columns, 'issued', $purchase->issued);
        $this->setColumn($record, $columns, 'due', $purchase->due);

        $this->setColumn($record, $columns, 'refund_availability', $purchase->refund_availability);
        $this->setColumn($record, $columns, 'refundable_amount', $purchase->refundable_amount);
        $this->setColumn($record, $columns, 'currency_conversion', $purchase->currency_conversion ? $this->jsonOrNull($purchase->currency_conversion->toArray()) : null);
        $this->setColumn($record, $columns, 'payment_method_whitelist', $this->jsonOrNull($payload['payment_method_whitelist'] ?? []));

        $this->setColumn($record, $columns, 'success_redirect', $purchase->success_redirect);
        $this->setColumn($record, $columns, 'failure_redirect', $purchase->failure_redirect);
        $this->setColumn($record, $columns, 'cancel_redirect', $purchase->cancel_redirect);
        $this->setColumn($record, $columns, 'success_callback', $purchase->success_callback);
        $this->setColumn($record, $columns, 'invoice_url', $purchase->invoice_url);
        $this->setColumn($record, $columns, 'checkout_url', $purchase->checkout_url);
        $this->setColumn($record, $columns, 'direct_post_url', $purchase->direct_post_url);

        $this->setColumn($record, $columns, 'creator_agent', $purchase->creator_agent);
        $this->setColumn($record, $columns, 'platform', $purchase->platform);
        $this->setColumn($record, $columns, 'product', $purchase->product);
        $this->setColumn($record, $columns, 'created_from_ip', $purchase->created_from_ip);

        $this->setColumn($record, $columns, 'marked_as_paid', $purchase->marked_as_paid ? 1 : 0);
        $this->setColumn($record, $columns, 'order_id', $purchase->order_id);

        $this->setColumn($record, $columns, 'amount_cents', $this->extractAmountCents($purchase, $payload));
        $this->setColumn($record, $columns, 'currency', $this->extractCurrency($purchase, $payload));
        $this->setColumn($record, $columns, 'metadata', $this->jsonOrNull($payload));
        $this->setColumn($record, $columns, 'chip_created_at', $this->datetimeOrNull($purchase->created_on));
        $this->setColumn($record, $columns, 'chip_updated_at', $this->datetimeOrNull($purchase->updated_on));
        $this->setColumn($record, $columns, 'updated_at', $now->toDateTimeString());

        if ($exists) {
            $query->where($keyColumn, $purchase->id)->update($record);
        } else {
            $insert = $record;
            $this->setColumn($insert, $columns, $keyColumn, $purchase->id);
            $this->setColumn($insert, $columns, 'created_at', $now->toDateTimeString());

            $query->insert($insert);
        }

        Log::debug('CHIP purchase snapshot stored', [
            'purchase_id' => $purchase->id,
            'status' => $purchase->status,
        ]);

        return $purchase;
    }

    private function prefixed(string $table): string
    {
        return $this->prefix.$table;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  mixed  $value
     */
    private function setColumn(array &$record, array $columns, string $column, $value): void
    {
        if (! in_array($column, $columns, true)) {
            return;
        }

        $record[$column] = $value;
    }

    private function jsonOrNull($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value);
    }

    private function datetimeOrNull($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return CarbonImmutable::createFromTimestamp((int) $value)->toDateTimeString();
        }

        try {
            return CarbonImmutable::parse((string) $value)->toDateTimeString();
        } catch (Throwable $throwable) {
            Log::debug('Unable to convert value to datetime', [
                'value' => $value,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    private function extractAmountCents(Purchase $purchase, array $payload): ?int
    {
        $amount = $payload['amount']
            ?? $purchase->purchase->total
            ?? $purchase->payment?->amount
            ?? null;

        if ($amount === null) {
            return null;
        }

        return is_numeric($amount) ? (int) $amount : null;
    }

    private function extractCurrency(Purchase $purchase, array $payload): ?string
    {
        $currency = $payload['currency']
            ?? $purchase->purchase->currency
            ?? $purchase->payment?->currency
            ?? null;

        return is_string($currency) ? $currency : null;
    }

    private function purchaseDetailsArray(PurchaseDetails $details): array
    {
        return $details->toArray();
    }

    private function paymentArray(?ChipPayment $payment): array
    {
        return $payment?->toArray() ?? [];
    }

    private function tableExists(string $table): bool
    {
        if (! array_key_exists($table, $this->tableAvailability)) {
            try {
                $this->tableAvailability[$table] = Schema::connection($this->connection)->hasTable($table);
            } catch (Throwable $throwable) {
                Log::debug('Unable to inspect CHIP table availability', [
                    'table' => $table,
                    'connection' => $this->connection,
                    'error' => $throwable->getMessage(),
                ]);

                $this->tableAvailability[$table] = false;
            }
        }

        return $this->tableAvailability[$table];
    }

    /**
     * @return array<int, string>
     */
    private function getTableColumns(string $table): array
    {
        if (array_key_exists($table, $this->columnCache)) {
            return $this->columnCache[$table];
        }

        try {
            $columns = Schema::connection($this->connection)->getColumnListing($table);
        } catch (Throwable $throwable) {
            Log::debug('Unable to list CHIP table columns', [
                'table' => $table,
                'connection' => $this->connection,
                'error' => $throwable->getMessage(),
            ]);

            $columns = [];
        }

        return $this->columnCache[$table] = $columns;
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function resolveWebhookKeyColumn(array $columns): ?string
    {
        if (in_array('webhook_id', $columns, true)) {
            return 'webhook_id';
        }

        if (in_array('id', $columns, true)) {
            return 'id';
        }

        return null;
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function resolvePurchaseKeyColumn(array $columns): ?string
    {
        if (in_array('chip_id', $columns, true)) {
            return 'chip_id';
        }

        if (in_array('id', $columns, true)) {
            return 'id';
        }

        return null;
    }
}
