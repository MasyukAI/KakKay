<?php

declare(strict_types=1);

use MasyukAI\Cart\Http\Controllers\DemoController;
use MasyukAI\Cart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

beforeEach(function (): void {
    $this->controller = new DemoController();
    $this->viewFactory = \Mockery::mock(\Illuminate\Contracts\View\Factory::class);
    $this->mockView = \Mockery::mock(\Illuminate\Contracts\View\View::class);
    app()->instance(\Illuminate\Contracts\View\Factory::class, $this->viewFactory);
    Cart::clear();
});

it('can display instances page', function (): void {
    Cart::add('default-product', 'Default Product', 100, 1);
    
    Cart::setInstance('wishlist');
    Cart::add('wish-product', 'Wish Product', 50, 2);
    
    Cart::setInstance('default'); // Reset
    
    $this->viewFactory
        ->shouldReceive('make')
        ->with('cart::demo.instances', Mockery::on(function ($data) {
            return is_array($data) && array_key_exists('instanceData', $data);
        }), [])
        ->andReturn($this->mockView);
    
    $response = $this->controller->instances();
    
    expect($response)->toBe($this->mockView);
});

afterEach(function (): void {
    Cart::clear();
});

it('can display cart demo page', function (): void {
    // Set up the view mock expectation
    $this->viewFactory->shouldReceive('make')
        ->with('cart::demo.index', \Mockery::on(function ($data) {
            return is_array($data) && 
                   array_key_exists('products', $data) &&
                   array_key_exists('cartItems', $data) &&
                   array_key_exists('cartCount', $data) &&
                   array_key_exists('cartSubtotal', $data) &&
                   array_key_exists('cartTotal', $data) &&
                   array_key_exists('cartConditions', $data);
        }), [])
        ->once()
        ->andReturn($this->mockView);
    
    $response = $this->controller->index();
    
    expect($response)->toBe($this->mockView);
});

it('can add item to cart via API', function (): void {
    $request = Request::create('/add-to-cart', 'POST', [
        'id' => 'test-product',
        'name' => 'Test Product',
        'price' => 99.99,
        'quantity' => 2,
        'attributes' => ['color' => 'red', 'size' => 'medium']
    ]);
    
    $response = $this->controller->addToCart($request);
    $data = json_decode($response->getContent(), true);
    
    expect($data['success'])->toBeTrue()
        ->and($data['message'])->toContain('added to cart')
        ->and($data['cart_count'])->toBe(2)
        ->and($data['cart_total'])->toBe(199.98);
        
    // Verify item was actually added
    expect(Cart::get('test-product'))->not->toBeNull()
        ->and(Cart::get('test-product')->name)->toBe('Test Product')
        ->and(Cart::get('test-product')->attributes['color'])->toBe('red');
});

it('validates required fields when adding to cart', function (): void {
    $request = Request::create('/add-to-cart', 'POST', [
        'id' => 'test-product',
        // Missing required fields
    ]);
    
    expect(function () use ($request) {
        $this->controller->addToCart($request);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('can update item quantity', function (): void {
    // Ensure cart is completely clear
    Cart::clear();
    
    // First add an item
    Cart::add('test-product', 'Test Product', 50, 2);
    
    // Verify cart state before update
    expect(Cart::count())->toBe(2);
    
    $request = Request::create('/update-quantity', 'POST', [
        'id' => 'test-product',
        'quantity' => 5
    ]);
    
    $response = $this->controller->updateQuantity($request);
    $data = json_decode($response->getContent(), true);
    
    expect($data['success'])->toBeTrue()
        ->and($data['cart_count'])->toBe(5)
        ->and($data['cart_total'])->toBe(250);
        
    expect(Cart::get('test-product')->quantity)->toBe(5);
});

it('removes item when quantity is updated to zero', function (): void {
    Cart::add('test-product', 'Test Product', 50, 2);
    
    $request = Request::create('/update-quantity', 'POST', [
        'id' => 'test-product',
        'quantity' => 0
    ]);
    
    $response = $this->controller->updateQuantity($request);
    $data = json_decode($response->getContent(), true);
    
    expect($data['success'])->toBeTrue()
        ->and($data['cart_count'])->toBe(0);
        
    expect(Cart::get('test-product'))->toBeNull();
});

it('can remove specific item from cart', function (): void {
    Cart::add('product-1', 'Product 1', 100, 1);
    Cart::add('product-2', 'Product 2', 200, 1);
    
    $request = Request::create('/remove-item', 'POST', [
        'id' => 'product-1'
    ]);
    
    $response = $this->controller->removeItem($request);
    $data = json_decode($response->getContent(), true);
    
    expect($data['success'])->toBeTrue()
        ->and($data['cart_count'])->toBe(1)
        ->and($data['cart_total'])->toBe(200);
        
    expect(Cart::get('product-1'))->toBeNull()
        ->and(Cart::get('product-2'))->not->toBeNull();
});

it('can apply discount condition', function (): void {
    Cart::add('test-product', 'Test Product', 100, 2);
    
    $request = Request::create('/apply-condition', 'POST', [
        'type' => 'discount',
        'name' => 'holiday-sale',
        'value' => '-10%',
        'target' => 'subtotal'
    ]);
    
    $response = $this->controller->applyCondition($request);
    $data = json_decode($response->getContent(), true);
    
    expect($data['success'])->toBeTrue()
        ->and($data['message'])->toContain('applied successfully');
});

it('can apply charge condition', function (): void {
    Cart::add('test-product', 'Test Product', 100, 2);
    
    $request = Request::create('/apply-condition', 'POST', [
        'type' => 'charge',
        'name' => 'shipping',
        'value' => '+15',
        'target' => 'subtotal'
    ]);
    
    $response = $this->controller->applyCondition($request);
    $data = json_decode($response->getContent(), true);
    
    expect($data['success'])->toBeTrue();
});

it('validates condition type and required fields', function (): void {
    $request = Request::create('/apply-condition', 'POST', [
        'type' => 'invalid-type',
        'name' => 'test-condition'
        // Missing value
    ]);
    
    expect(function () use ($request) {
        $this->controller->applyCondition($request);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('can remove condition', function (): void {
    Cart::add('test-product', 'Test Product', 100, 2);
    
    // First apply a condition
    $condition = new \MasyukAI\Cart\Conditions\CartCondition(
        'test-discount',
        'discount',
        'subtotal',
        '-10%'
    );
    Cart::condition($condition);
    
    $request = Request::create('/remove-condition', 'POST', [
        'name' => 'test-discount'
    ]);
    
    $response = $this->controller->removeCondition($request);
    $data = json_decode($response->getContent(), true);
    
    expect($data['success'])->toBeTrue()
        ->and($data['message'])->toContain('removed successfully');
});

it('can clear entire cart', function (): void {
    Cart::add('product-1', 'Product 1', 100, 1);
    Cart::add('product-2', 'Product 2', 200, 1);
    
    $response = $this->controller->clearCart();
    $data = json_decode($response->getContent(), true);
    
    expect($data['success'])->toBeTrue()
        ->and($data['cart_count'])->toBe(0)
        ->and($data['cart_total'])->toBe(0);
        
    expect(Cart::count())->toBe(0);
});

it('can switch cart instance', function (): void {
    $request = Request::create('/switch-instance', 'POST', [
        'instance' => 'wishlist'
    ]);
    
    $response = $this->controller->switchInstance($request);
    $data = json_decode($response->getContent(), true);
    
    expect($data['success'])->toBeTrue()
        ->and($data['message'])->toContain('wishlist cart');
});

it('validates instance name when switching', function (): void {
    $request = Request::create('/switch-instance', 'POST', [
        'instance' => 'invalid-instance'
    ]);
    
    expect(function () use ($request) {
        $this->controller->switchInstance($request);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('can display migration demo page', function (): void {
    $this->viewFactory
        ->shouldReceive('make')
        ->with('cart::demo.migration', Mockery::on(function ($data) {
            return is_array($data) && 
                   array_key_exists('guestCartItems', $data) && 
                   array_key_exists('userCartItems', $data);
        }), [])
        ->andReturn($this->mockView);
    
    $response = $this->controller->migrationDemo();
    
    expect($response)->toBe($this->mockView);
});

it('can setup guest cart for migration demo', function (): void {
    $request = Request::create('/setup-migration', 'POST', [
        'type' => 'guest'
    ]);
    
    $response = $this->controller->setupMigrationDemo($request);
    $data = json_decode($response->getContent(), true);
    
    expect($data['success'])->toBeTrue()
        ->and($data['message'])->toContain('Guest cart setup');
});

it('can setup user cart for migration demo', function (): void {
    $request = Request::create('/setup-migration', 'POST', [
        'type' => 'user'
    ]);
    
    $response = $this->controller->setupMigrationDemo($request);
    $data = json_decode($response->getContent(), true);
    
    expect($data['success'])->toBeTrue()
        ->and($data['message'])->toContain('User cart setup');
});

it('can perform cart migration with different strategies', function (): void {
    $strategies = ['add_quantities', 'keep_highest_quantity', 'keep_user_cart', 'replace_with_guest'];
    
    foreach ($strategies as $strategy) {
        $request = Request::create('/perform-migration', 'POST', [
            'strategy' => $strategy
        ]);
        
        $response = $this->controller->performMigration($request);
        $data = json_decode($response->getContent(), true);
        
        expect($data['strategy_used'])->toBe($strategy);
    }
});

it('validates migration strategy', function (): void {
    $request = Request::create('/perform-migration', 'POST', [
        'strategy' => 'invalid-strategy'
    ]);
    
    expect(function () use ($request) {
        $this->controller->performMigration($request);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('provides sample products with correct structure', function (): void {
    $reflection = new \ReflectionClass($this->controller);
    $method = $reflection->getMethod('getSampleProducts');
    $method->setAccessible(true);
    
    $products = $method->invoke($this->controller);
    
    expect($products)->toBeArray()
        ->and(count($products))->toBeGreaterThan(0);
        
    foreach ($products as $product) {
        expect($product)->toHaveKeys(['id', 'name', 'price', 'description', 'image', 'attributes'])
            ->and($product['price'])->toBeFloat()
            ->and($product['attributes'])->toBeArray();
    }
});