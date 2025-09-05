<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Foundation\Application;
use Illuminate\Session\SessionManager;
use MasyukAI\Cart\Traits\ManagesIdentifier;

describe('ManagesIdentifier Coverage Tests', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('returns authenticated user ID when auth is available and user is logged in', function () {
        // Create a trait instance that can mock the app() function behavior
        $trait = new class
        {
            use ManagesIdentifier;

            public function callGetIdentifier(): string
            {
                return $this->getIdentifier();
            }

            // Override the getApplication method to provide our mock
            protected function getApplication(): Application
            {
                $mockGuard = Mockery::mock();
                $mockGuard->shouldReceive('check')->andReturn(true);
                $mockGuard->shouldReceive('id')->andReturn(123);

                $mockAuth = Mockery::mock(AuthFactory::class);
                $mockAuth->shouldReceive('guard')->andReturn($mockGuard);

                $mockApp = Mockery::mock(Application::class);
                $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
                $mockApp->shouldReceive('make')->with(AuthFactory::class)->andReturn($mockAuth);
                $mockApp->shouldReceive('environment')->with('testing')->andReturn(true);

                return $mockApp;
            }
        };

        $identifier = $trait->callGetIdentifier();

        expect($identifier)->toBe('123');
    });

    it('returns session ID when auth is not available but session is', function () {
        // Create a trait instance that simulates session fallback
        $trait = new class
        {
            use ManagesIdentifier;

            public function callGetIdentifier(): string
            {
                return $this->getIdentifier();
            }

            // Override to test session fallback path
            protected function getApplication(): Application
            {
                $mockSession = Mockery::mock(SessionManager::class);
                $mockSession->shouldReceive('getId')->andReturn('session_abc123');

                $mockApp = Mockery::mock(Application::class);
                $mockApp->shouldReceive('bound')->with('auth')->andReturn(false);
                $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
                $mockApp->shouldReceive('make')->with(SessionManager::class)->andReturn($mockSession);
                $mockApp->shouldReceive('environment')->with('testing')->andReturn(true);

                return $mockApp;
            }
        };

        $identifier = $trait->callGetIdentifier();

        expect($identifier)->toBe('session_abc123');
    });

    it('returns test session ID when both auth and session are unavailable', function () {
        // Create a trait instance that simulates both services failing
        $trait = new class
        {
            use ManagesIdentifier;

            public function callGetIdentifier(): string
            {
                return $this->getIdentifier();
            }

            // Override to test final fallback path
            protected function getApplication(): Application
            {
                $mockApp = Mockery::mock(Application::class);
                $mockApp->shouldReceive('bound')->with('auth')->andReturn(false);
                $mockApp->shouldReceive('bound')->with('session')->andReturn(false);
                $mockApp->shouldReceive('environment')->with('testing')->andReturn(true);

                return $mockApp;
            }

            protected function getTestIdentifier(): string
            {
                return 'test_session_id';
            }
        };

        $identifier = $trait->callGetIdentifier();

        expect($identifier)->toBe('test_session_id');
    });

    it('handles auth exception and falls back to session', function () {
        // Create a trait instance that simulates auth throwing exception
        $trait = new class
        {
            use ManagesIdentifier;

            public function callGetIdentifier(): string
            {
                return $this->getIdentifier();
            }

            // Override to test auth exception path
            protected function getApplication(): Application
            {
                $mockSession = Mockery::mock(SessionManager::class);
                $mockSession->shouldReceive('getId')->andReturn('session_after_auth_exception');

                $mockApp = Mockery::mock(Application::class);
                $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
                $mockApp->shouldReceive('make')->with(AuthFactory::class)->andThrow(new \Exception('Auth service error'));
                $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
                $mockApp->shouldReceive('make')->with(SessionManager::class)->andReturn($mockSession);
                $mockApp->shouldReceive('environment')->with('testing')->andReturn(true);

                return $mockApp;
            }
        };

        $identifier = $trait->callGetIdentifier();

        expect($identifier)->toBe('session_after_auth_exception');
    });

    it('handles session exception and falls back to test identifier', function () {
        // Create a trait instance that simulates session throwing exception
        $trait = new class
        {
            use ManagesIdentifier;

            public function callGetIdentifier(): string
            {
                return $this->getIdentifier();
            }

            // Override to test session exception path
            protected function getApplication(): Application
            {
                $mockApp = Mockery::mock(Application::class);
                $mockApp->shouldReceive('bound')->with('auth')->andReturn(false);
                $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
                $mockApp->shouldReceive('make')->with(SessionManager::class)->andThrow(new \Exception('Session service error'));
                $mockApp->shouldReceive('environment')->with('testing')->andReturn(true);

                return $mockApp;
            }

            protected function getTestIdentifier(): string
            {
                return 'test_session_id';
            }
        };

        $identifier = $trait->callGetIdentifier();

        expect($identifier)->toBe('test_session_id');
    });

    it('handles user not authenticated but auth service available', function () {
        // Test when auth service is available but user is not logged in
        $trait = new class
        {
            use ManagesIdentifier;

            public function callGetIdentifier(): string
            {
                return $this->getIdentifier();
            }

            protected function getApplication(): Application
            {
                $mockGuard = Mockery::mock();
                $mockGuard->shouldReceive('check')->andReturn(false); // Not authenticated

                $mockAuth = Mockery::mock(AuthFactory::class);
                $mockAuth->shouldReceive('guard')->andReturn($mockGuard);

                $mockSession = Mockery::mock(SessionManager::class);
                $mockSession->shouldReceive('getId')->andReturn('guest_session_123');

                $mockApp = Mockery::mock(Application::class);
                $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
                $mockApp->shouldReceive('make')->with(AuthFactory::class)->andReturn($mockAuth);
                $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
                $mockApp->shouldReceive('make')->with(SessionManager::class)->andReturn($mockSession);
                $mockApp->shouldReceive('environment')->with('testing')->andReturn(true);

                return $mockApp;
            }
        };

        $identifier = $trait->callGetIdentifier();

        expect($identifier)->toBe('guest_session_123');
    });
});
