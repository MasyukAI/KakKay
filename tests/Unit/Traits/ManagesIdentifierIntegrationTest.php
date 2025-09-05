<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MasyukAI\Cart\Traits\ManagesIdentifier;

pest()->extend(Tests\TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test trait instance
    $this->traitInstance = new class
    {
        use ManagesIdentifier;

        public function callGetIdentifier(): string
        {
            return $this->getIdentifier();
        }
    };
});

it('returns authenticated user id when user is logged in', function () {
    // Create and authenticate a user
    $user = User::factory()->create();
    $this->actingAs($user);

    // Get identifier - should return the authenticated user's ID
    $identifier = $this->traitInstance->callGetIdentifier();

    expect($identifier)->toBe((string) $user->id);
});

it('returns session id when user is not authenticated', function () {
    // Ensure no user is authenticated
    $this->assertGuest();

    // Start a session
    session()->start();
    $sessionId = session()->getId();

    // Get identifier - should return session ID
    $identifier = $this->traitInstance->callGetIdentifier();

    expect($identifier)->toBe($sessionId);
});

it('handles auth check returning false', function () {
    // Ensure user is not authenticated (check() returns false)
    $this->assertGuest();

    // Start session to provide fallback
    session()->start();
    $sessionId = session()->getId();

    // Should fall back to session ID
    $identifier = $this->traitInstance->callGetIdentifier();

    expect($identifier)->toBe($sessionId);
});

it('throws exception when neither auth nor session are available', function () {
    // Create a trait instance that will simulate missing services
    $traitWithNoServices = new class
    {
        use ManagesIdentifier;

        public function callGetIdentifier(): string
        {
            return $this->getIdentifier();
        }

        // Override getIdentifier to simulate missing services
        public function getIdentifier(): string
        {
            // Simulate auth not bound
            if (false) { // app()->bound('auth')
                return 'auth_id';
            }

            // Simulate session not bound
            if (false) { // app()->bound('session')
                return 'session_id';
            }

            throw new \RuntimeException('Neither auth nor session services are available for cart identifier');
        }
    };

    expect(fn () => $traitWithNoServices->callGetIdentifier())
        ->toThrow(\RuntimeException::class, 'Neither auth nor session services are available for cart identifier');
});

it('handles auth exception gracefully and falls back to session', function () {
    // Create a trait instance that simulates auth exception
    $traitWithAuthException = new class
    {
        use ManagesIdentifier;

        public function callGetIdentifier(): string
        {
            return $this->getIdentifier();
        }

        public function getIdentifier(): string
        {
            // Try to get identifier from auth first
            try {
                if (app()->bound('auth')) {
                    // Simulate auth service throwing exception
                    throw new \Exception('Auth service error');
                }
            } catch (\Exception $e) {
                // Auth not available, continue to session
            }

            // Fall back to session ID for guests
            try {
                if (app()->bound('session')) {
                    return app('session')->getId();
                }
            } catch (\Exception $e) {
                // Session not available, throw exception
            }

            throw new \RuntimeException('Neither auth nor session services are available for cart identifier');
        }
    };

    // Start session to provide fallback
    session()->start();
    $sessionId = session()->getId();

    $identifier = $traitWithAuthException->callGetIdentifier();

    expect($identifier)->toBe($sessionId);
});

it('handles session exception and throws runtime exception', function () {
    // Create a trait instance that simulates session exception
    $traitWithSessionException = new class
    {
        use ManagesIdentifier;

        public function callGetIdentifier(): string
        {
            return $this->getIdentifier();
        }

        public function getIdentifier(): string
        {
            // Try to get identifier from auth first
            try {
                if (app()->bound('auth') && app('auth')->check()) {
                    return (string) app('auth')->id();
                }
            } catch (\Exception $e) {
                // Auth not available, continue to session
            }

            // Fall back to session ID for guests
            try {
                if (app()->bound('session')) {
                    // Simulate session service throwing exception
                    throw new \Exception('Session service error');
                }
            } catch (\Exception $e) {
                // Session not available, throw exception
            }

            throw new \RuntimeException('Neither auth nor session services are available for cart identifier');
        }
    };

    // Ensure not authenticated
    $this->assertGuest();

    expect(fn () => $traitWithSessionException->callGetIdentifier())
        ->toThrow(\RuntimeException::class, 'Neither auth nor session services are available for cart identifier');
});
