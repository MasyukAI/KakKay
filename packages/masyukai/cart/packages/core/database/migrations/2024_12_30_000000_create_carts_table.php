<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('cart.database.table', 'carts'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('identifier')->index();
            $table->string('instance')->default('default')->index();
            $table->jsonb('items')->nullable();
            $table->jsonb('conditions')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->integer('version')->default(1)->index();
            $table->timestamps();

            $table->unique(['identifier', 'instance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('cart.database.table', 'carts'));
    }
};
