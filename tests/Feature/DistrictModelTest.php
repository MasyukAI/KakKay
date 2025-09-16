<?php

use App\Models\District;

test('district model can be instantiated', function () {
    $district = new District();
    expect($district)->toBeInstanceOf(District::class);
});

test('district model can retrieve all districts', function () {
    $districts = District::all();
    
    expect($districts)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    expect($districts->count())->toBeGreaterThan(0);
});

test('district model can filter by state', function () {
    // Test Johor districts (state_id = 1)
    $johorDistricts = District::forState('1')->get();
    
    expect($johorDistricts->count())->toEqual(10);
    expect($johorDistricts->pluck('name'))->toContain('JOHOR BAHRU');
    expect($johorDistricts->pluck('name'))->toContain('BATU PAHAT');
});

test('district model can get districts by state helper method', function () {
    // Test Kedah districts (state_id = 2)
    $kedahDistricts = District::getByState('2');
    
    expect($kedahDistricts->count())->toEqual(12);
    expect($kedahDistricts->first()->name)->toEqual('BALING'); // Should be ordered by name
});

test('district model has correct attributes', function () {
    $district = District::first();
    
    expect($district)->toHaveKey('id');
    expect($district)->toHaveKey('state_id');
    expect($district)->toHaveKey('name');
    expect($district)->toHaveKey('code_3');
});

test('district model can find specific district', function () {
    $district = District::find('0102'); // Johor Bahru
    
    expect($district)->not->toBeNull();
    expect($district->name)->toEqual('JOHOR BAHRU');
    expect($district->state_id)->toEqual('1');
    expect($district->code_3)->toEqual('JBA');
});

test('district model primary key is string', function () {
    $district = new District();
    
    expect($district->getKeyType())->toEqual('string');
    expect($district->getIncrementing())->toBeFalse();
});