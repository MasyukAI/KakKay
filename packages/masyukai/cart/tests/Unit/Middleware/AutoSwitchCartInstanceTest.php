<?php

declare(strict_types=1);

use MasyukAI\Cart\Http\Middleware\AutoSwitchCartInstance;
use MasyukAI\Cart\Services\CartMigrationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery as m;

beforeEach(function (): void {
    $this->migrationService = m::mock(CartMigrationService::class);
    $this->middleware = new AutoSwitchCartInstance($this->migrationService);
    $this->request = Request::create('/test', 'GET');
});

afterEach(function (): void {
    m::close();
});

it('can be instantiated with migration service', function (): void {
    expect($this->middleware)->toBeInstanceOf(AutoSwitchCartInstance::class);
});

it('calls auto switch cart instance method', function (): void {
    $this->migrationService->shouldReceive('autoSwitchCartInstance')
        ->once();
    
    $next = function ($request) {
        return new Response('OK');
    };
    
    $response = $this->middleware->handle($this->request, $next);
    
    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getContent())->toBe('OK');
});

it('passes request to next middleware', function (): void {
    $this->migrationService->shouldReceive('autoSwitchCartInstance')
        ->once();
    
    $nextCalled = false;
    $passedRequest = null;
    
    $next = function ($request) use (&$nextCalled, &$passedRequest) {
        $nextCalled = true;
        $passedRequest = $request;
        return new Response('Success');
    };
    
    $response = $this->middleware->handle($this->request, $next);
    
    expect($nextCalled)->toBeTrue()
        ->and($passedRequest)->toBe($this->request)
        ->and($response->getContent())->toBe('Success');
});

it('handles exceptions gracefully from migration service', function (): void {
    $this->migrationService->shouldReceive('autoSwitchCartInstance')
        ->once()
        ->andThrow(new \Exception('Migration service error'));
    
    $next = function ($request) {
        return new Response('Next called');
    };
    
    expect(function () use ($next) {
        $this->middleware->handle($this->request, $next);
    })->toThrow(\Exception::class, 'Migration service error');
});

it('maintains request integrity', function (): void {
    $this->migrationService->shouldReceive('autoSwitchCartInstance')
        ->once();
    
    // Add some data to the request
    $this->request->merge(['test_data' => 'test_value']);
    $this->request->headers->set('X-Test-Header', 'test-header-value');
    
    $next = function ($request) {
        return new Response('Request data: ' . $request->input('test_data'));
    };
    
    $response = $this->middleware->handle($this->request, $next);
    
    expect($response->getContent())->toBe('Request data: test_value');
});

it('works with different HTTP methods', function (): void {
    $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
    
    foreach ($methods as $method) {
        $request = Request::create('/test', $method);
        
        $this->migrationService->shouldReceive('autoSwitchCartInstance')
            ->once();
        
        $next = function ($req) use ($method) {
            return new Response("Method: {$method}");
        };
        
        $response = $this->middleware->handle($request, $next);
        
        expect($response->getContent())->toBe("Method: {$method}");
    }
});

it('preserves response from next middleware', function (): void {
    $this->migrationService->shouldReceive('autoSwitchCartInstance')
        ->once();
    
    $customResponse = new Response('Custom response', 201, ['X-Custom' => 'custom-value']);
    
    $next = function ($request) use ($customResponse) {
        return $customResponse;
    };
    
    $response = $this->middleware->handle($this->request, $next);
    
    expect($response)->toBe($customResponse)
        ->and($response->getStatusCode())->toBe(201)
        ->and($response->headers->get('X-Custom'))->toBe('custom-value');
});

it('works with JSON requests', function (): void {
    $jsonRequest = Request::create('/api/test', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], json_encode(['key' => 'value']));
    
    $this->migrationService->shouldReceive('autoSwitchCartInstance')
        ->once();
    
    $next = function ($request) {
        return new Response(json_encode(['status' => 'ok']), 200, [
            'Content-Type' => 'application/json'
        ]);
    };
    
    $response = $this->middleware->handle($jsonRequest, $next);
    
    expect($response->headers->get('Content-Type'))->toBe('application/json')
        ->and($response->getContent())->toBe('{"status":"ok"}');
});

it('executes in correct order with auto switch before next', function (): void {
    $executionOrder = [];
    
    $this->migrationService->shouldReceive('autoSwitchCartInstance')
        ->once()
        ->andReturnUsing(function () use (&$executionOrder) {
            $executionOrder[] = 'auto_switch';
        });
    
    $next = function ($request) use (&$executionOrder) {
        $executionOrder[] = 'next_middleware';
        return new Response('OK');
    };
    
    $this->middleware->handle($this->request, $next);
    
    expect($executionOrder)->toBe(['auto_switch', 'next_middleware']);
});
