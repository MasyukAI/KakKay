<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        Schema::create($tablePrefix.'send_instructions', function (Blueprint $table) use ($tablePrefix) {
            // Core API fields - Send Instruction structure from CHIP Send API
            $table->integer('id')->primary(); // API uses integer IDs, not UUIDs
            $table->integer('bank_account_id'); // Reference to bank account (integer)

            // Amount and transaction details
            $table->string('amount'); // Floating point string with max 2 decimals
            $table->string('email'); // Email address
            $table->text('description'); // Description
            $table->string('reference'); // Any reference value

            // Status tracking - exact states from API
            $table->enum('state', [
                'received',   // Instruction received, processing not started
                'enquiring',  // Pending verification
                'executing',  // Pending execution
                'reviewing',  // Requires further attention
                'accepted',   // Accepted but not completed
                'completed',  // Execution completed
                'rejected',   // Instruction rejected
                'deleted',     // Instruction deleted
            ])->default('received');

            // Generated fields from API response
            $table->string('receipt_url')->nullable(); // Generated receipt URL
            $table->string('slug')->nullable(); // Receipt URL slug

            // API timestamps (DateTime strings)
            $table->timestamp('created_at'); // API: created_at
            $table->timestamp('updated_at'); // API: updated_at

            // Foreign key constraint
            $table->foreign('bank_account_id')
                ->references('id')
                ->on($tablePrefix.'bank_accounts')
                ->onDelete('cascade');

            // Indexes for optimal query performance
            $table->index(['state', 'created_at']);
            $table->index('bank_account_id');
            $table->index('reference');
            $table->index('email');
        });
    }

    public function down(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        Schema::dropIfExists($tablePrefix.'send_instructions');
    }
};
