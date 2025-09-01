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

        Schema::connection($connection)->create($tablePrefix . 'clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_id')->unique();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('address')->nullable();
            $table->string('identity_type')->nullable();
            $table->string('identity_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('phone');
            $table->index(['identity_type', 'identity_number']);
        });
    }

    public function down(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        $connection = config('chip.database.connection');

        Schema::connection($connection)->dropIfExists($tablePrefix . 'clients');
    }
};
