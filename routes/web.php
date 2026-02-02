<?php

declare(strict_types=1);

use Akaunting\Money\Money;
use App\Http\Controllers\PageController;
use App\Livewire\Cart;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::get('/cart', Cart::class)->name('cart');

Route::get('/checkout', App\Livewire\Checkout::class)->name('checkout');

// These routes display the final result pages AFTER package processes payment callbacks
// Payment flow: CHIP → /checkout/payment/success (package) → /checkout/success (app view)
Route::get('/checkout/success/{session}', [App\Http\Controllers\CheckoutController::class, 'success'])
    ->name('checkout.success');
Route::get('/checkout/failure/{session}', [App\Http\Controllers\CheckoutController::class, 'failure'])
    ->name('checkout.failure');
Route::get('/checkout/cancel/{session}', [App\Http\Controllers\CheckoutController::class, 'cancel'])
    ->name('checkout.cancel');

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

    Route::livewire('settings/profile', 'pages::settings.profile')->name('settings.profile');
    Route::livewire('settings/password', 'pages::settings.password')->name('settings.password');
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

// Product pages
Route::livewire('/cara-bercinta', 'pages::cara-bercinta')->name('pages.cara-bercinta');

// Policy pages - must be before catch-all route
Route::livewire('/privacy-policy', 'pages::privacy-policy');
Route::livewire('/refund-policy', 'pages::refund-policy');
Route::livewire('/shipping-policy', 'pages::shipping-policy');
Route::livewire('/terms-of-service', 'pages::terms-of-service');

// Catch-all route for dynamic pages - must be last
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('page.show');

Route::get('saiffil', function () {
    echo Money::USD(7777)->getAmount();
})->name('saiffil');
