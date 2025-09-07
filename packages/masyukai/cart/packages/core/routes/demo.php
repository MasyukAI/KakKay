<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MasyukAI\Cart\Http\Controllers\DemoController;

/*
|--------------------------------------------------------------------------
| Cart Demo Routes
|--------------------------------------------------------------------------
|
| These routes provide a comprehensive demo of the MasyukAI Cart package
| functionality. They are designed for testing and demonstration purposes.
|
*/

Route::prefix('cart-demo')->name('cart.demo.')->group(function () {
    // Main demo page
    Route::get('/', [DemoController::class, 'index'])->name('index');

    // Cart operations
    Route::post('/add', [DemoController::class, 'addToCart'])->name('add');
    Route::patch('/update', [DemoController::class, 'updateQuantity'])->name('update');
    Route::delete('/remove', [DemoController::class, 'removeItem'])->name('remove');
    Route::delete('/clear', [DemoController::class, 'clearCart'])->name('clear');

    // Conditions
    Route::post('/condition/apply', [DemoController::class, 'applyCondition'])->name('condition.apply');
    Route::delete('/condition/remove', [DemoController::class, 'removeCondition'])->name('condition.remove');

    // Cart instances
    Route::get('/instances', [DemoController::class, 'instances'])->name('instances');
    Route::post('/switch-instance', [DemoController::class, 'switchInstance'])->name('switch-instance');

    // Migration demo
    Route::get('/migration', [DemoController::class, 'migrationDemo'])->name('migration');
    Route::post('/migration/setup', [DemoController::class, 'setupMigrationDemo'])->name('migration.setup');
    Route::post('/migration/perform', [DemoController::class, 'performMigration'])->name('migration.perform');
});
