<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        Schema::create($tablePrefix.'send_instructions', function (Blueprint $table) {
            $table->id();
            $table->string('instruction_id')->unique();
            $table->integer('bank_account_id');
            $table->string('amount'); // Keep as string to match API
            $table->string('state')->default('received'); // Default should be 'received' per API
            $table->string('email');
            $table->text('description');
            $table->string('reference');
            $table->string('receipt_url')->nullable();
            $table->string('slug')->nullable();
            $table->json('metadata')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['state', 'created_at']);
            $table->index('bank_account_id');
            $table->index('reference');
        });
    }

    public function down(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        Schema::dropIfExists($tablePrefix.'send_instructions');
    }
};
