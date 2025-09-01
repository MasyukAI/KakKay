<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Http\Middleware\AutoSwitchCartInstance;
use MasyukAI\Cart\Services\CartMigrationService;
use MasyukAI\Cart\Tests\TestCase;

beforeEach(function () {
    $this->cartMigration = new CartMigrationService();
    $this->middleware = new AutoSwitchCartInstance($this->cartMigration);
    
    // Create a test user
    $this->user = new class {
        public $id = 1;
        public function getAuthIdentifier() { return $this->id; }
    };
    
    // Clear all cart instances
    Cart::clear();
    Cart::setInstance('guest_123')->clear();
    Cart::setInstance('user_1')->clear();
    Cart::setInstance('default'); // Reset to default
});

it('preserves default instance when user is authenticated', function () {
    // Mock Auth facade
    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(1);
    
    // Create request with proper session setup
    $request = Request::create('/test', 'GET');
    $sessionStore = app('session.store');
    $request->setLaravelSession($sessionStore);
    
    $next = function ($request) {
        // Instance name should remain 'default' - only identifier changes internally
        expect(Cart::instance())->toBe('default');
        return response('test');
    };

    // Initially on default instance
    expect(Cart::instance())->toBe('default');

    $response = $this->middleware->handle($request, $next);

    // After middleware completes, should still be on default instance
    // The middleware manages identifiers (who owns cart), not instance names (which cart)
    expect(Cart::instance())->toBe('default');
    expect($response->getContent())->toBe('test');
});

it('preserves default instance when user is not authenticated', function () {
    // Mock Auth facade
    Auth::shouldReceive('check')->andReturn(false);
    
    // Create request with proper session setup
    $request = Request::create('/test', 'GET');
    $sessionStore = app('session.store');
    $request->setLaravelSession($sessionStore);
    
    $next = function ($request) {
        // Instance name should remain 'default' for guests too
        expect(Cart::instance())->toBe('default');
        return response('test');
    };

    // Initially on default instance
    expect(Cart::instance())->toBe('default');

    $response = $this->middleware->handle($request, $next);

    // After middleware completes, should still be on default instance
    expect(Cart::instance())->toBe('default');
    expect($response->getContent())->toBe('test');
});

it('preserves custom instance names when set by developer', function () {
    // Mock Auth facade
    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(1);
    
    // Set a custom instance before middleware - this should be preserved
    Cart::setInstance('custom_instance');
    $originalInstance = Cart::instance();
    expect($originalInstance)->toBe('custom_instance');

    // Create request with proper session setup
    $request = Request::create('/test', 'GET');
    $sessionStore = app('session.store');
    $request->setLaravelSession($sessionStore);
    
    $next = function ($request) {
        // Instance should remain as set by developer - middleware doesn't change it
        expect(Cart::instance())->toBe('custom_instance');
        return response('test');
    };

    $response = $this->middleware->handle($request, $next);

    // After middleware, should still be on custom instance
    expect(Cart::instance())->toBe('custom_instance');
    expect($response->getContent())->toBe('test');
});

it('handles exceptions gracefully without changing instances', function () {
    // Mock Auth facade
    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(1);
    
    // Set a custom instance before middleware
    Cart::setInstance('custom_instance');
    $originalInstance = Cart::instance();

    // Create request with proper session setup
    $request = Request::create('/test', 'GET');
    $sessionStore = app('session.store');
    $request->setLaravelSession($sessionStore);

    $next = function ($request) {
        throw new \Exception('Test exception');
    };

    try {
        $this->middleware->handle($request, $next);
    } catch (\Exception $e) {
        expect($e->getMessage())->toBe('Test exception');
    }

    // Should remain on original instance even after exception
    expect(Cart::instance())->toBe('custom_instance');
});

it('works with default cart instance', function () {
    // Mock Auth facade
    Auth::shouldReceive('check')->andReturn(false);
    
    // Start with default instance
    Cart::setInstance('default');
    
    // Create request with proper session setup
    $request = Request::create('/test', 'GET');
    $sessionStore = app('session.store');
    $sessionStore->put('test', 'value'); 
    $sessionStore->setId('session_456'); 
    $request->setLaravelSession($sessionStore);
    
    $next = function ($request) {
        // Should remain on default instance
        expect(Cart::instance())->toBe('default');
        return response('test');
    };

    $response = $this->middleware->handle($request, $next);

    // After middleware completes, should still be default
    expect(Cart::instance())->toBe('default');
    expect($response->getContent())->toBe('test');
});
