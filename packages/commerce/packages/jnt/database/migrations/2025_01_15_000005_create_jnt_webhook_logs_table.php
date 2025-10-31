<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = config('jnt.database.tables', []);
        $prefix = config('jnt.database.table_prefix', 'jnt_');

        $ordersTable = $tables['orders'] ?? $prefix.'orders';
        $webhookLogsTable = $tables['webhook_logs'] ?? $prefix.'webhook_logs';

        Schema::create($webhookLogsTable, function (Blueprint $table) use ($ordersTable): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->nullable()->constrained($ordersTable)->nullOnDelete();
            $table->string('tracking_number', 30)->nullable()->index();
            $table->string('order_reference', 50)->nullable()->index();
            $table->string('digest', 255)->nullable();
            $table->jsonb('headers')->nullable();
            $table->jsonb('payload')->nullable();
            $table->string('processing_status', 32)->default('pending')->index();
            $table->text('processing_error')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::table($webhookLogsTable, function (Blueprint $table): void {
            $table->rawIndex('payload', 'jnt_webhook_logs_payload_gin_index', 'gin');
        });
    }

    public function down(): void
    {
        $tables = config('jnt.database.tables', []);
        $prefix = config('jnt.database.table_prefix', 'jnt_');

        $webhookLogsTable = $tables['webhook_logs'] ?? $prefix.'webhook_logs';

        Schema::dropIfExists($webhookLogsTable);
    }
};
