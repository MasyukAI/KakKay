<?php

declare(strict_types=1);

use App\Livewire\Checkout;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checkout component class exists', function () {
    expect(class_exists(Checkout::class))->toBeTrue();
});

test('checkout component implements required interfaces', function () {
    $reflection = new ReflectionClass(Checkout::class);

    expect($reflection->implementsInterface(Filament\Schemas\Contracts\HasSchemas::class))->toBeTrue();
});

test('checkout component has required methods', function () {
    $checkout = new Checkout;

    expect(method_exists($checkout, 'form'))->toBeTrue();
    expect(method_exists($checkout, 'submitCheckout'))->toBeTrue();
    expect(method_exists($checkout, 'getSubtotal'))->toBeTrue();
    expect(method_exists($checkout, 'getTotal'))->toBeTrue();
});

test('checkout route works', function () {
    // Install world data for the test
    $this->artisan('world:install')->assertSuccessful();

    $response = $this->get('/checkout');

    // Should either show checkout page or redirect to cart (when no items)
    expect($response->status())->toBeIn([200, 302]);
});

test('checkout form has state field with malaysian states', function () {
    // Install world data
    $this->artisan('world:install')->assertSuccessful();

    // Get the checkout component
    $checkout = new Checkout;
    $checkout->mount();

    // Get the form schema
    $form = $checkout->form(Filament\Schemas\Schema::make());

    // Find the state select component
    $components = $form->getComponents();
    $stateField = null;

    foreach ($components as $component) {
        if ($component instanceof Filament\Schemas\Components\Section) {
            foreach ($component->getChildComponents() as $child) {
                if ($child instanceof Filament\Schemas\Components\Grid) {
                    foreach ($child->getChildComponents() as $gridChild) {
                        if ($gridChild instanceof Filament\Forms\Components\Select &&
                            $gridChild->getName() === 'state') {
                            $stateField = $gridChild;
                            break 3;
                        }
                    }
                }
            }
        }
    }

    expect($stateField)->not->toBeNull();

    // Check that it has Malaysian states as options
    $options = $stateField->getOptions();
    expect($options)->toBeArray();
    expect($options)->toHaveKeys(['Johor', 'Kedah', 'Kelantan', 'Selangor', 'Kuala Lumpur']);
    expect($stateField->isSearchable())->toBeTrue();
    expect($stateField->isRequired())->toBeTrue();
});
