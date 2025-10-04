<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MasyukAI\Chip\Http\Controllers\WebhookController;

Route::prefix('chip')->group(function (): void {
    Route::post('webhook', [WebhookController::class, 'handle'])
        ->name('chip.webhook.handle')
        ->middleware(config('chip.webhooks.middleware', ['api']));

    Route::post('webhooks/success', [WebhookController::class, 'handleSuccess'])
        ->name('chip.webhooks.success')
        ->middleware(config('chip.webhooks.middleware', ['api']));

    Route::post('webhooks/{webhook_id}', [WebhookController::class, 'handle'])
        ->name('chip.webhooks.handle')
        ->middleware(config('chip.webhooks.middleware', ['api']));
});
