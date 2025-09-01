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

        Schema::connection($connection)->create($tablePrefix . 'send_instructions', function (Blueprint $table) {
            $table->id();
            $table->string('instruction_id')->unique();
            $table->string('reference')->nullable();
            $table->integer('amount_in_cents');
            $table->string('currency', 3);
            $table->string('recipient_bank_account_id');
            $table->json('recipient_details')->nullable();
            $table->text('description')->nullable();
            $table->string('status');
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('recipient_bank_account_id');
            $table->index('sent_at');
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        $connection = config('chip.database.connection');

        Schema::connection($connection)->dropIfExists($tablePrefix . 'send_instructions');
    }
};
