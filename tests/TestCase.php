<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create carts table for testing if it doesn't exist
        if (! Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();
                $table->string('identifier')->index();
                $table->string('instance')->default('default')->index();
                $table->json('items')->nullable();
                $table->json('conditions')->nullable();
                $table->json('metadata')->nullable();
                $table->bigInteger('version')->default(1)->index();
                $table->timestamps();

                $table->unique(['identifier', 'instance']);
            });
        }
    }
}
