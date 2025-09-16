<?php

declare(strict_types=1);

use App\Models\District;
use App\Livewire\Checkout;
use Livewire\Volt\Volt;

test('hardcoded states match district state_ids', function () {
    // Get all unique state_ids from District model
    $districtStateIds = District::all()
        ->pluck('state_id')
        ->unique()
        ->sort()
        ->values()
        ->toArray();

    // The hardcoded states from Checkout component
    $hardcodedStates = [
        '1', '2', '3', '4', '5', '6', '7', '8', 
        '9', '10', '11', '12', '13', '14', '15', '16'
    ];

    // Convert to integers for comparison
    $hardcodedStateIds = array_map('intval', $hardcodedStates);
    sort($hardcodedStateIds);

    // All district state_ids should exist in hardcoded states
    foreach ($districtStateIds as $stateId) {
        expect($hardcodedStateIds)->toContain((int)$stateId);
    }
    
    // Should have districts for most states (at least 10)
    expect(count($districtStateIds))->toBeGreaterThan(10);
});

test('districts can be retrieved by hardcoded state ids', function () {
    // Test some specific state IDs
    $testStates = [
        1 => 'Johor',
        10 => 'Selangor',
        12 => 'Sabah',
        13 => 'Sarawak'
    ];

    foreach ($testStates as $stateId => $stateName) {
        $districts = District::forState($stateId)->get();
        
        expect($districts)->not->toBeEmpty()
            ->and($districts->first()->state_id)->toBe((string)$stateId);
    }
});

test('checkout form state options use correct format', function () {
    // Simulate the checkout component data
    $checkoutComponent = Livewire\Livewire::test(Checkout::class);
    
    // The form should be able to handle state selection
    $checkoutComponent
        ->set('data.state', '1') // Johor
        ->assertSet('data.state', '1');
        
    // Should be able to set district based on state
    $checkoutComponent
        ->set('data.district', '102') // Johor Bahru district
        ->assertSet('data.district', '102');
});

test('district filtering by state works with hardcoded data', function () {
    // Test filtering districts by different states
    $johorDistricts = District::forState(1)->get();
    $selangorDistricts = District::forState(10)->get();
    
    expect($johorDistricts)->not->toBeEmpty()
        ->and($selangorDistricts)->not->toBeEmpty()
        ->and($johorDistricts->count())->not->toBe($selangorDistricts->count());
    
    // Verify all districts belong to correct state
    $johorDistricts->each(function ($district) {
        expect($district->state_id)->toBe('1');
    });
    
    $selangorDistricts->each(function ($district) {
        expect($district->state_id)->toBe('10');
    });
});