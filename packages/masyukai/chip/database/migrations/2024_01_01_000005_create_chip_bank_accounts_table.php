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

        Schema::connection($connection)->create($tablePrefix . 'bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_id')->unique();
            $table->string('bank_code');
            $table->string('account_number');
            $table->string('account_holder_name');
            $table->string('account_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->json('verification_details')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['bank_code', 'account_number']);
            $table->index(['is_active', 'is_verified']);
            $table->index('verified_at');
        });
    }

    public function down(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        $connection = config('chip.database.connection');

        Schema::connection($connection)->dropIfExists($tablePrefix . 'bank_accounts');
    }
};
