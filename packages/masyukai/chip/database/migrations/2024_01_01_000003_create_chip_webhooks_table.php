<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        $connection = config('chip.database.connection');

        Schema::connection($connection)->create($tablePrefix . 'webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('webhook_id')->nullable();
            $table->string('event_type');
            $table->json('payload');
            $table->json('headers');
            $table->string('signature');
            $table->boolean('verified')->default(false);
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_error')->nullable();
            $table->integer('processing_attempts')->default(0);
            $table->timestamps();

            $table->index(['event_type', 'processed']);
            $table->index(['verified', 'processed']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        $connection = config('chip.database.connection');

        Schema::connection($connection)->dropIfExists($tablePrefix . 'webhooks');
    }
};
