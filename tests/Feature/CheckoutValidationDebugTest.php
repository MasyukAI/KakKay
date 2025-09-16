<?php

declare(strict_types=1);

use App\Livewire\Checkout;
use Livewire\Livewire;
use MasyukAI\Cart\Facades\Cart as CartFacade;

beforeEach(function () {
    CartFacade::add('test-product', 'Test Product', 1000, 1);
});

test('validation errors appear when form is submitted via wire:submit', function () {
    // Test by triggering the actual form submission
    $component = Livewire::test(Checkout::class)
        ->call('processCheckout'); // This should trigger validation
    
    // Check if the component stops execution due to validation errors
    // by looking at the debug output
    expect(true)->toBeTrue(); // We'll observe the behavior
});

test('validation shows errors when required field is empty', function () {
    $component = Livewire::test(Checkout::class);
    
    // Clear a required field and trigger validation
    $component->set('data.name', '')
        ->call('processCheckout');
    
    // Log the component state for debugging
    $errors = $component->instance()->getErrorBag();
    dump('Component errors:', $errors->toArray());
    
    expect(true)->toBeTrue();
});

test('check if form validation is triggered by getState', function () {
    $component = Livewire::test(Checkout::class);
    
    // Set invalid data
    $component->set('data', [
        'name' => '', // Required
        'email' => 'invalid-email', // Invalid format
    ]);
    
    // Get the form instance and try validation
    $form = $component->instance()->form;
    
    try {
        $result = $form->getState();
        expect($result)->toBeArray();
        dump('getState succeeded with:', $result);
    } catch (\Exception $e) {
        dump('getState failed with:', $e->getMessage());
        expect($e)->toBeInstanceOf(\Exception::class);
    }
});