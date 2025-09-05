<?php

declare(strict_types=1);

use MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer;

it('debug integer price transformer', function () {
    $transformer = new IntegerPriceTransformer('USD', 'en_US', 2);
    
    // Test the conversion flow
    $inputPrice = 19.99;
    
    // Step 1: normalize for storage (should convert to 1999 cents)
    $stored = $transformer->toStorage($inputPrice);
    expect($stored)->toBe(1999);
    
    // Step 2: convert to display (should show "19.99")
    $displayed = $transformer->toDisplay($stored);
    expect($displayed)->toBe('19.99');
    
    // Step 3: convert to numeric for calculations (should be 19.99)
    $numeric = $transformer->toNumeric($stored);
    expect($numeric)->toBe(19.99);
});
