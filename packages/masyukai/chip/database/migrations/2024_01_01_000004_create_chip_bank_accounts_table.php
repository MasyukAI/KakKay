<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        Schema::create($tablePrefix.'bank_accounts', function (Blueprint $table) {
            // Core API fields - Bank Account structure from CHIP Send API
            $table->integer('id')->primary(); // API uses integer IDs, not UUIDs

            // Account identification
            $table->string('account_number'); // Account number (length varies by bank)
            $table->enum('bank_code', [
                'PHBMMYKL', 'AIBBMYKL', 'BPMBMYKL', 'MFBBMYKL', 'ALSRMYK1',
                'RJHIMYKL', 'ARBKMYKL', 'AISLMYKL', 'BIMBMYKL', 'BKRMMYKL',
                'BMMBMYKL', 'BSNAMYK1', 'CIBBMYKL', 'CTBBMYKL', 'HLBBMYKL',
                'HLIBMYKL', 'HBMBMYKL', 'HMABMYKL', 'KFHOMYKL', 'MBBEMYKL',
                'MBISMYKL', 'OCBCMYKL', 'OABBMYKL', 'PBBEMYKL', 'PIBEMYK1',
                'RHBBMYKL', 'RHBAMYKL', 'SCBLMYKX', 'UOVBMYKL', 'ACDBMYK2',
                'AGOBMYKL', 'BOFAMY2X', 'BKCHMYKL', 'BOTKMYKX', 'BNPAMYKL',
                'PCBCMYKL', 'DEUTMYKL', 'FNXSMYNB', 'GXSPMYKL', 'ICBKMYKL',
                'CHASMYKX', 'AFBQMYKL', 'MHCBMYKA', 'SMBCMYKL', 'TNGDMYNB',
            ]); // Exact bank codes from CHIP API
            $table->string('name'); // Account holder name

            // Status and verification - exact states from API
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->integer('group_id')->nullable(); // Account grouping
            $table->string('reference')->nullable(); // Unique submission reference

            // Account capabilities
            $table->boolean('is_debiting_account')->default(false);
            $table->boolean('is_crediting_account')->default(false);

            // API timestamps (DateTime strings)
            $table->timestamp('created_at'); // Object creation time in UTC
            $table->timestamp('updated_at'); // Object update time
            $table->timestamp('deleted_at')->nullable(); // Soft deletion

            // Rejection details
            $table->text('rejection_reason')->nullable(); // Why account was rejected

            // Indexes for optimal query performance
            $table->index(['bank_code', 'account_number']);
            $table->index(['status', 'created_at']);
            $table->index('group_id');
            $table->index('reference');
        });
    }

    public function down(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        Schema::dropIfExists($tablePrefix.'bank_accounts');
    }
};
