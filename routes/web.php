<?php

declare(strict_types=1);

use Akaunting\Money\Money;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ChipController;
use App\Http\Controllers\PageController;
use App\Livewire\Cart;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', Home::class)->name('home');

Route::get('/cart', Cart::class)->name('cart');

Route::get('/checkout', App\Livewire\Checkout::class)->name('checkout');

// Checkout success/failure/cancel routes with cart reference
Route::get('/checkout/success/{reference}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/failure/{reference}', [CheckoutController::class, 'failure'])->name('checkout.failure');
Route::get('/checkout/cancel/{reference}', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// CHIP callbacks (success + webhooks)
Route::post('/webhooks/chip/{webhook?}', [ChipController::class, 'handle'])->name('webhooks.chip');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Order routes
/** @phpstan-ignore-next-line argument.type */
Route::get('/orders/{order}', function () {
    return view('orders.show');
})->name('orders.show');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    /** @phpstan-ignore-next-line */
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    /** @phpstan-ignore-next-line */
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    /** @phpstan-ignore-next-line */
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

// Product pages - Volt routes
/** @phpstan-ignore-next-line */
Volt::route('/cara-bercinta', 'cara-bercinta')->name('pages.cara-bercinta');

// Policy pages - must be before catch-all route
/** @phpstan-ignore-next-line */
Volt::route('/privacy-policy', 'privacy-policy');
/** @phpstan-ignore-next-line */
Volt::route('/refund-policy', 'refund-policy');
/** @phpstan-ignore-next-line */
Volt::route('/shipping-policy', 'shipping-policy');
/** @phpstan-ignore-next-line */
Volt::route('/terms-of-service', 'terms-of-service');

// Catch-all route for dynamic pages - must be last
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('page.show');

Route::get('saiffil', function () {
    echo Money::USD(7777)->getAmount();
})->name('saiffil');
