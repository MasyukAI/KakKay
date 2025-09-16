<?php

use App\Models\District;
use App\Livewire\Checkout;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checkout component includes district selection', function () {
    // Install world data for the test
    $this->artisan('world:install')->assertSuccessful();
    
    $component = Livewire::test(Checkout::class);
    
    // Check that the component renders without errors
    $component->assertOk();
    
    // Check that we can set form data for state and district
    $component->set('data.state', '1'); // Johor
    
    // The component should render without errors after setting state
    $component->assertOk();
    
    // Check if district field is available after setting state
    $component->set('data.district', '0102'); // Johor Bahru district
    $component->assertOk();
    
    // Verify the form data was set correctly
    expect($component->get('data.state'))->toEqual('1');
    expect($component->get('data.district'))->toEqual('0102');
});

test('district options are filtered by state', function () {
    // Test that Johor districts are returned when Johor state is selected
    $johorDistricts = District::getByState('1');
    
    expect($johorDistricts->count())->toEqual(10);
    expect($johorDistricts->pluck('name'))->toContain('JOHOR BAHRU');
    expect($johorDistricts->pluck('name'))->toContain('BATU PAHAT');
    
    // Test that Kedah districts are returned when Kedah state is selected  
    $kedahDistricts = District::getByState('2');
    
    expect($kedahDistricts->count())->toEqual(12);
    expect($kedahDistricts->pluck('name'))->toContain('KOTA SETAR');
    expect($kedahDistricts->pluck('name'))->toContain('LANGKAWI');
});

test('checkout helper methods work for district', function () {
    $checkout = new Checkout();
    
    // Test getDistrictName method using reflection since it's private
    $reflection = new ReflectionClass($checkout);
    $method = $reflection->getMethod('getDistrictName');
    $method->setAccessible(true);
    
    // Test with valid district ID
    $districtName = $method->invoke($checkout, '0102'); // Johor Bahru
    expect($districtName)->toEqual('JOHOR BAHRU');
    
    // Test with invalid/null district ID
    $emptyName = $method->invoke($checkout, null);
    expect($emptyName)->toEqual('');
    
    // Test with non-existent district ID (should use fallback)
    $fallbackName = $method->invoke($checkout, 'invalid');
    expect($fallbackName)->toEqual('');
});