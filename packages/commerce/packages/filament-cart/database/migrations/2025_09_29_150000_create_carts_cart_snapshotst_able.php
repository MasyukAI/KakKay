<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('identifier');
            $table->string('instance')->default('default');
            $table->unsignedInteger('items_count')->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('savings')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->jsonb('items')->nullable();
            $table->jsonb('conditions')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->unique(['identifier', 'instance']);
            $table->index('identifier');
            $table->index('instance');
            $table->index('items_count');
            $table->index('quantity');
            $table->index('subtotal');
            $table->index('total');
            $table->index('savings');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_snapshots');
    }
};
