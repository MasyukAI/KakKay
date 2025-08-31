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
        Schema::dropIfExists('cart_storage');
        
        Schema::create('cart_storage', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->index();
            $table->string('instance')->default('default')->index();
            $table->longText('content');
            $table->timestamps();

            $table->unique(['identifier', 'instance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_storage');
    }
};
