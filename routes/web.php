<?php

use App\Livewire\Home;
use Livewire\Volt\Volt;
use MasyukAI\Cart\Facades\Cart;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ChipWebhookController;

Route::get('/', Home::class)->name('home');

Volt::route('/cart', 'cart')->name('cart');

Route::get('/checkout', \App\Livewire\Checkout::class)->name('checkout');

// Checkout success/failure routes
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/failure', [CheckoutController::class, 'failure'])->name('checkout.failure');

// CHIP webhook route
Route::post('/webhooks/chip', [ChipWebhookController::class, 'handle'])->name('webhooks.chip');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
//
// Route::get('/{slug}', [PageController::class, 'show'])
//    ->where('slug', '[a-z0-9\-]+')
//    ->name('page.show');

Volt::route('cara-bercinta', 'cara-bercinta');

// Policy pages
Volt::route('privacy-policy', 'privacy-policy');
Volt::route('refund-policy', 'refund-policy');
Volt::route('shipping-policy', 'shipping-policy');
Volt::route('terms-of-service', 'terms-of-service');

Route::get('saiffil', function () {
    Cart::add(id: '293ad', name: 'Saiffil', price: 100000, quantity: 1, attributes: [
        'image' => 'https://saiffil.com/wp-content/uploads/2023/10/cropped-favicon-32x32.png',
        'url' => 'https://saiffil.com',
    ]);
})->name('saiffil');
