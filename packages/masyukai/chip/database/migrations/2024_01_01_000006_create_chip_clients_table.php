<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        Schema::create($tablePrefix . 'clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_id')->unique();
            $table->string('type')->default('client');
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('personal_code')->nullable();
            $table->string('street_address')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('state')->nullable();
            $table->string('shipping_street_address')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_zip_code')->nullable();
            $table->string('shipping_state')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_code')->nullable();
            $table->json('address')->nullable();
            $table->string('identity_type')->nullable();
            $table->string('identity_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->integer('chip_created_on');
            $table->integer('chip_updated_on');
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

        Schema::dropIfExists($tablePrefix . 'clients');
    }
};
