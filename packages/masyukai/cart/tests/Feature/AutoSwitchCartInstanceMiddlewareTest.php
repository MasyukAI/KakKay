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

it('switches to user cart instance when user is authenticated', function () {
    // Mock Auth facade
    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(1);
    
    // Create request with proper session setup
    $request = Request::create('/test', 'GET');
    $sessionStore = app('session.store');
    $request->setLaravelSession($sessionStore);
    
    $next = function ($request) {
        // During request processing, should be on user instance
        expect(Cart::instance())->toBe('user_1');
        return response('test');
    };

    // Initially on default instance
    expect(Cart::instance())->toBe('default');

    $response = $this->middleware->handle($request, $next);

    // After middleware completes, should restore to original instance
    expect(Cart::instance())->toBe('default');
    expect($response->getContent())->toBe('test');
});

it('switches to guest cart instance when user is not authenticated', function () {
    // Mock Auth facade
    Auth::shouldReceive('check')->andReturn(false);
    
    // Create request with proper session setup
    $request = Request::create('/test', 'GET');
    $sessionStore = app('session.store');
    $sessionStore->put('test', 'value'); // Initialize session with some data
    $sessionStore->setId('session_123'); // Set specific session ID
    $request->setLaravelSession($sessionStore);
    
    $next = function ($request) {
        // During request processing, should be on guest instance based on actual session ID
        $sessionId = $request->session()->getId();
        $expectedInstance = "guest_{$sessionId}";
        expect(Cart::instance())->toBe($expectedInstance);
        return response('test');
    };

    // Initially on default instance
    expect(Cart::instance())->toBe('default');

    $response = $this->middleware->handle($request, $next);

    // After middleware completes, should restore to original instance
    expect(Cart::instance())->toBe('default');
    expect($response->getContent())->toBe('test');
});

it('restores original cart instance after request', function () {
    // Mock Auth facade
    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(1);
    
    // Set a custom instance before middleware
    Cart::setInstance('custom_instance');
    $originalInstance = Cart::instance();
    expect($originalInstance)->toBe('custom_instance');

    // Create request with proper session setup
    $request = Request::create('/test', 'GET');
    $sessionStore = app('session.store');
    $request->setLaravelSession($sessionStore);
    
    $next = function ($request) {
        // During request processing, should be on user instance
        expect(Cart::instance())->toBe('user_1');
        return response('test');
    };

    $response = $this->middleware->handle($request, $next);

    // After middleware, should restore to original instance
    expect(Cart::instance())->toBe('custom_instance');
    expect($response->getContent())->toBe('test');
});

it('handles exceptions gracefully and restores instance', function () {
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

    // Should still restore to original instance even after exception
    expect(Cart::instance())->toBe('custom_instance');
});

it('works with no active cart instance initially', function () {
    // Mock Auth facade
    Auth::shouldReceive('check')->andReturn(false);
    
    // Clear any active instance
    Cart::setInstance('default'); // Reset to default
    
    // Create request with proper session setup
    $request = Request::create('/test', 'GET');
    $sessionStore = app('session.store');
    $sessionStore->put('test', 'value'); // Initialize session with some data
    $sessionStore->setId('session_456'); // Set specific session ID
    $request->setLaravelSession($sessionStore);
    
    $next = function ($request) {
        // Should switch to guest instance based on session ID
        $sessionId = $request->session()->getId();
        $expectedInstance = "guest_{$sessionId}";
        expect(Cart::instance())->toBe($expectedInstance);
        return response('test');
    };

    $response = $this->middleware->handle($request, $next);

    // After middleware completes, should restore to original instance
    expect(Cart::instance())->toBe('default');
    expect($response->getContent())->toBe('test');
});
