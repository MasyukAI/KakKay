<?php

declare(strict_types=1);

use App\Livewire\Checkout;
use App\Models\District;
use Livewire\Livewire;
use MasyukAI\Cart\Facades\Cart as CartFacade;

beforeEach(function () {
    // Add item to cart so checkout doesn't redirect
    CartFacade::add('test-product', 'Test Product', 1000, 1);
    
    // District uses Sushi, so data is already available from the model
});

test('shows validation errors for required fields when calling processCheckout', function () {
    $component = Livewire::test(Checkout::class)
        ->call('processCheckout');
    
    // Since getState() is used instead of validate(), no validation errors will be shown
    // This test demonstrates the problem
    $component->assertHasNoErrors(); // This will pass but shouldn't
    
    // Let's check what happens when we try to set invalid data
    $component->set('data', [])
        ->call('processCheckout')
        ->assertHasNoErrors(); // This shows the validation isn't triggered
});

test('demonstrates the validation issue - getState vs validate', function () {
    $component = Livewire::test(Checkout::class);
    
    // Try with empty data
    $component->set('data', [])
        ->call('processCheckout');
    
    // Since processCheckout uses getState() instead of validate(),
    // no validation errors are triggered even with missing required fields
    expect($component->instance()->getErrorBag()->isEmpty())->toBeTrue();
    
    // The ds() calls in processCheckout should show that the method continues
    // even with invalid data because validation isn't triggered
});

test('email confirmation validation with updatedData method commented out', function () {
    $component = Livewire::test(Checkout::class)
        ->set('data.email', 'test@example.com')
        ->set('data.email_confirmation', 'different@example.com');
    
    // Since updatedData is commented out, no real-time validation occurs
    $component->assertHasNoErrors();
    
    // Even calling processCheckout won't validate because getState() is used
    $component->call('processCheckout')
        ->assertHasNoErrors();
});

test('demonstrates what should happen with proper validation', function () {
    // This test shows what the validation should look like if implemented correctly
    $component = Livewire::test(Checkout::class);
    
    // Test with missing required fields
    $component->set('data', [
        'name' => '', // Required field empty
        'email' => 'invalid-email', // Invalid email format
        'email_confirmation' => 'different@email.com', // Doesn't match email
        'phone' => '', // Required field empty
        'state' => '', // Required field empty
        'district' => '', // Required field empty
        'postal_code' => '', // Required field empty
        'address' => '', // Required field empty
    ]);
    
    // If validation was working properly, these errors should appear:
    // - data.name (required)
    // - data.email (invalid format)
    // - data.email_confirmation (doesn't match email)
    // - data.phone (required)
    // - data.state (required)
    // - data.district (required)
    // - data.postal_code (required)
    // - data.address (required)
    
    // But they won't because getState() doesn't trigger validation
    $component->call('processCheckout')
        ->assertHasNoErrors(); // This passes but shouldn't
});

test('shows missing helper methods cause errors', function () {
    $component = Livewire::test(Checkout::class);
    
    // Set valid data to get past any potential validation
    $component->set('data', [
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
    ]);
    
    // This will fail because helper methods are missing
    expect(fn() => $component->call('processCheckout'))
        ->toThrow(\Error::class); // Method not found errors
});

test('form schema validation rules are properly defined', function () {
    $component = Livewire::test(Checkout::class);
    
    // Test that the form schema has the expected validation rules
    $schema = $component->instance()->form(app(\Filament\Schemas\Schema::class));
    
    expect($schema)->toBeInstanceOf(\Filament\Schemas\Schema::class);
    
    // The schema should have components with validation rules
    $components = $schema->getComponents();
    expect($components)->not->toBeEmpty();
});

test('demonstrates the fix needed', function () {
    // This test documents what needs to be fixed:
    
    // 1. Change $this->form->getState() to $this->form->validate() in processCheckout
    // 2. Uncomment and fix the updatedData method for real-time validation
    // 3. Add the missing helper methods: getStateName, getCityName, getDistrictName
    
    expect(true)->toBeTrue(); // Placeholder test to document the fixes needed
});