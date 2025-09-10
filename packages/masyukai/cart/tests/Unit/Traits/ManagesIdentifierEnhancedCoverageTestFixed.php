<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use MasyukAI\Cart\Traits\ManagesIdentifier;

describe('ManagesIdentifier Enhanced Coverage', function () {
    beforeEach(function () {
        $this->app = new Application;
        $this->class = new class
        {
            use ManagesIdentifier;

            public function testGetAuthenticatedUserId()
            {
                return $this->getAuthenticatedUserId(app());
            }

            public function testGetSessionId()
            {
                return $this->getSessionId(app());
            }

            public function testIsTestingEnvironment()
            {
                return $this->isTestingEnvironment(app());
            }

            public function testGetIdentifier()
            {
                return $this->getIdentifier(app());
            }
        };
    });

    it('returns authenticated user ID when user is authenticated', function () {
        // Mock Auth facade to return authenticated user
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn(123);

        // Mock the auth guard to check if user is actually authenticated
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('check')->andReturn(true);
        $guard->shouldReceive('id')->andReturn(123);

        $this->app->instance('auth', $guard);

        expect($this->class->testGetAuthenticatedUserId())->toBe(123);
    });

    it('returns null when user is not authenticated', function () {
        Auth::shouldReceive('check')->andReturn(false);

        expect($this->class->testGetAuthenticatedUserId())->toBeNull();
    });

    it('handles auth exception and returns null', function () {
        Auth::shouldReceive('check')->andThrow(new \Exception('Auth error'));

        expect($this->class->testGetAuthenticatedUserId())->toBeNull();
    });

    it('returns session ID when available', function () {
        Session::shouldReceive('getId')->andReturn('test_session_123');

        expect($this->class->testGetSessionId())->toBe('test_session_123');
    });

    it('handles session exception and returns null', function () {
        Session::shouldReceive('getId')->andThrow(new \Exception('Session error'));

        expect($this->class->testGetSessionId())->toBeNull();
    });

    it('detects testing environment correctly', function () {
        $this->app->instance('env', 'testing');

        expect($this->class->testIsTestingEnvironment())->toBeTrue();
    });

    it('detects non-testing environment correctly', function () {
        $this->app->instance('env', 'production');

        expect($this->class->testIsTestingEnvironment())->toBeFalse();
    });

    it('returns authenticated user ID when available', function () {
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn(456);

        expect($this->class->testGetIdentifier())->toBe(456);
    });

    it('returns session ID when user not authenticated', function () {
        Auth::shouldReceive('check')->andReturn(false);
        Session::shouldReceive('getId')->andReturn('session_789');

        expect($this->class->testGetIdentifier())->toBe('session_789');
    });

    it('handles auth exception in getIdentifier', function () {
        Auth::shouldReceive('check')->andThrow(new \Exception('Auth error'));
        Session::shouldReceive('getId')->andReturn('fallback_session');

        expect($this->class->testGetIdentifier())->toBe('fallback_session');
    });

    it('generates test session ID when auth and session fail in testing', function () {
        $this->app->instance('env', 'testing');
        Auth::shouldReceive('check')->andReturn(false);
        Session::shouldReceive('getId')->andReturn(null);

        $result = $this->class->testGetIdentifier();
        expect($result)->toMatch('/^test_session_[a-f0-9]{32}$/');
    });

    it('throws exception when no identifier available in production', function () {
        $this->app->instance('env', 'production');
        Auth::shouldReceive('check')->andReturn(false);
        Session::shouldReceive('getId')->andReturn(null);

        expect(fn () => $this->class->testGetIdentifier())
            ->toThrow(\RuntimeException::class, 'Cart identifier cannot be determined');
    });
});
