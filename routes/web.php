<?php

use App\Http\Controllers\PageController;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', Home::class)->name('home');

Volt::route('/cart', 'cart')->name('cart');

Volt::route('/checkout', 'checkout')->name('checkout');

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
