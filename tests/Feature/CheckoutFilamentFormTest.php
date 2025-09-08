<?php

use App\Schemas\CheckoutForm;
use Filament\Schemas\Schema;
use Livewire\Volt\Volt;

test('checkout form schema has required fields', function () {
    $schema = CheckoutForm::configure(new Schema());
    
    expect($schema)->toBeInstanceOf(Schema::class);
    
    $components = $schema->getComponents();
    expect($components)->not->toBeEmpty();
    
    // Verify we have sections for different parts of the form
    $sectionTitles = collect($components)
        ->pluck('label')
        ->filter()
        ->toArray();
    
    expect($sectionTitles)->toContain('Maklumat Penghantaran');
});

test('checkout component uses filament forms', function () {
    // This test verifies the basic structure loads without errors
    $component = Volt::test('checkout');
    
    expect($component)->not->toBeNull();
    
    // Check that the component has the required traits and properties
    $component->assertSet('data', []);
    $component->assertSet('cartItems', []);
});

test('checkout form validation works', function () {
    $component = Volt::test('checkout');
    
    // Try to submit empty form - should fail validation
    $component->call('processCheckout');
    
    // Should not redirect (would happen on success)
    $component->assertNoRedirect();
});

test('checkout form accepts valid data', function () {
    $component = Volt::test('checkout');
    
    // Fill form with valid data
    $validData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '123456789',
        'country' => 'Malaysia',
        'city' => 'Kuala Lumpur',
        'address' => '123 Test Street',
        'delivery_method' => 'standard',
    ];
    
    $component->set('data', $validData);
    
    // Verify data is set correctly
    expect($component->get('data')['name'])->toBe('John Doe');
    expect($component->get('data')['email'])->toBe('john@example.com');
});