<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add database index for efficient cart lookup by payment_intent purchase_id.
     * This is critical for webhook processing where we need to find carts by CHIP purchase ID.
     *
     * PostgreSQL: Uses JSONB expression index for optimal performance
     * SQLite: Skipped (tests use in-memory database, production uses PostgreSQL)
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Add expression index on metadata->payment_intent->purchase_id
            DB::statement(
                'CREATE INDEX IF NOT EXISTS carts_payment_intent_purchase_id_idx '.
                "ON carts USING btree (((metadata::jsonb->'payment_intent'->>'purchase_id')))"
            );
        }

        // SQLite: No index needed for testing (in-memory database)
        // MySQL: Not supported (use PostgreSQL for production)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS carts_payment_intent_purchase_id_idx');
        }
    }
};
