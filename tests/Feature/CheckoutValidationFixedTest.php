<?php

declare(strict_types=1);

use App\Livewire\Checkout;
use Livewire\Livewire;
use MasyukAI\Cart\Facades\Cart as CartFacade;

beforeEach(function () {
    // Add item to cart so checkout doesn't redirect
    CartFacade::add('test-product', 'Test Product', 1000, 1);
});

test('validation errors now appear for required fields when submitting form', function () {
    $component = Livewire::test(Checkout::class)
        ->set('data', []) // Empty data to trigger validation errors
        ->call('processCheckout');
    
    // Now validation should work and show errors for required fields
    $component->assertHasErrors([
        'data.name',
        'data.email', 
        'data.phone',
        'data.state',
        'data.district',
        'data.postal_code',
        'data.address',
    ]);
});

test('email confirmation validation works in real time', function () {
    $component = Livewire::test(Checkout::class)
        ->set('data.email', 'test@example.com')
        ->set('data.email_confirmation', 'different@example.com');
    
    // Should show validation error for mismatched emails
    $component->assertHasErrors(['data.email_confirmation']);
    
    // When emails match, error should disappear
    $component->set('data.email_confirmation', 'test@example.com')
        ->assertHasNoErrors(['data.email_confirmation']);
});

test('form validates email format correctly', function () {
    Livewire::test(Checkout::class)
        ->set('data.email', 'invalid-email')
        ->call('processCheckout')
        ->assertHasErrors(['data.email']);
});

test('form passes validation with complete valid data', function () {
    Livewire::test(Checkout::class)
        ->set('data', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'email_confirmation' => 'john@example.com',
            'phone' => '123456789',
            'country' => 'Malaysia',
            'state' => '1',
            'district' => '0101',
            'postal_code' => '40000',
            'address' => '123 Test Street',
            'delivery_method' => 'standard',
        ])
        ->call('processCheckout')
        // Since we don't have a complete checkout service setup in tests,
        // we expect it to fail at the payment processing stage, not validation
        ->assertHasNoErrors();
});

test('helper methods work correctly', function () {
    $component = Livewire::test(Checkout::class);
    $checkout = $component->instance();
    
    expect($checkout->getStateName('1'))->toBe('Johor');
    expect($checkout->getStateName('999'))->toBe('Unknown State');
    expect($checkout->getCityName('some-city'))->toBe('some-city');
    expect($checkout->getDistrictName(null))->toBe('Unknown District');
});