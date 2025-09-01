<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Masyukai\Chip\Http\Controllers\WebhookController;

Route::prefix('chip')->group(function () {
    Route::post('webhooks/{webhook_id}', [WebhookController::class, 'handle'])
        ->name('chip.webhooks.handle')
        ->middleware(config('chip.webhooks.middleware', ['api']));

    Route::post('webhooks/success', [WebhookController::class, 'handleSuccess'])
        ->name('chip.webhooks.success')
        ->middleware(config('chip.webhooks.middleware', ['api']));
});
