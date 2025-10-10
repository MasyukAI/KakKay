<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MasyukAI\Jnt\Http\Controllers\WebhookController;
use MasyukAI\Jnt\Http\Middleware\VerifyWebhookSignature;

/*
|--------------------------------------------------------------------------
| J&T Express Webhook Routes
|--------------------------------------------------------------------------
|
| These routes handle incoming webhook notifications from J&T Express
| servers. All requests must pass signature verification via the
| VerifyWebhookSignature middleware before being processed.
|
*/

Route::post(
    config('jnt.webhooks.route', 'webhooks/jnt/status'),
    [WebhookController::class, 'handle']
)
    ->middleware(config('jnt.webhooks.middleware', ['api', VerifyWebhookSignature::class]))
    ->name('jnt.webhooks.status');
