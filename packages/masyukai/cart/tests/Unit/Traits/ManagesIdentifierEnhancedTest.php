<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Session\SessionManager;
use MasyukAI\Cart\Traits\ManagesIdentifier;

describe('ManagesIdentifier Enhanced Trait', function () {
    beforeEach(function () {
        $this->trait = new class
        {
            use ManagesIdentifier;

            protected $mockApp;

            public function setMockApplication($app): void
            {
                $this->mockApp = $app;
            }

            protected function getApplication(): Application
            {
                return $this->mockApp ?? app();
            }

            public function callGetIdentifier(): string
            {
                return $this->getIdentifier();
            }

            public function callGetAuthenticatedUserId($app): ?string
            {
                return $this->getAuthenticatedUserId($app);
            }

            public function callGetSessionId($app): ?string
            {
                return $this->getSessionId($app);
            }

            public function callIsTestingEnvironment($app): bool
            {
                return $this->isTestingEnvironment($app);
            }
        };
    });

    describe('getIdentifier method', function () {
        it('returns authenticated user ID when user is logged in', function () {
            $mockGuard = mock(Guard::class);
            $mockGuard->shouldReceive('check')->andReturn(true);
            $mockGuard->shouldReceive('id')->andReturn(123);

            $mockAuth = mock(AuthFactory::class);
            $mockAuth->shouldReceive('guard')->andReturn($mockGuard);

            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andReturn($mockAuth);

            $this->trait->setMockApplication($mockApp);

            expect($this->trait->callGetIdentifier())->toBe('123');
        });

        it('returns session ID when user is not authenticated', function () {
            $mockGuard = mock(Guard::class);
            $mockGuard->shouldReceive('check')->andReturn(false);

            $mockAuth = mock(AuthFactory::class);
            $mockAuth->shouldReceive('guard')->andReturn($mockGuard);

            $mockSession = mock(SessionManager::class);
            $mockSession->shouldReceive('getId')->andReturn('session_abc123');

            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andReturn($mockAuth);
            $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
            $mockApp->shouldReceive('make')->with(SessionManager::class)->andReturn($mockSession);

            $this->trait->setMockApplication($mockApp);

            expect($this->trait->callGetIdentifier())->toBe('session_abc123');
        });

        it('returns test identifier in testing environment', function () {
            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(false);
            $mockApp->shouldReceive('bound')->with('session')->andReturn(false);
            $mockApp->shouldReceive('environment')->with('testing')->andReturn(true);

            $this->trait->setMockApplication($mockApp);

            expect($this->trait->callGetIdentifier())->toBe('test_session_id');
        });

        it('throws exception when services are unavailable in production', function () {
            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(false);
            $mockApp->shouldReceive('bound')->with('session')->andReturn(false);
            $mockApp->shouldReceive('environment')->with('testing')->andReturn(false);

            $this->trait->setMockApplication($mockApp);

            expect(fn () => $this->trait->callGetIdentifier())
                ->toThrow(\RuntimeException::class, 'Cart identifier cannot be determined: neither auth nor session services are available');
        });
    });

    describe('getAuthenticatedUserId method', function () {
        it('returns null when auth is not bound', function () {
            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(false);

            expect($this->trait->callGetAuthenticatedUserId($mockApp))->toBeNull();
        });

        it('returns user ID when user is authenticated', function () {
            $mockGuard = mock(Guard::class);
            $mockGuard->shouldReceive('check')->andReturn(true);
            $mockGuard->shouldReceive('id')->andReturn(456);

            $mockAuth = mock(AuthFactory::class);
            $mockAuth->shouldReceive('guard')->andReturn($mockGuard);

            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andReturn($mockAuth);

            expect($this->trait->callGetAuthenticatedUserId($mockApp))->toBe('456');
        });

        it('returns null when user is not authenticated', function () {
            $mockGuard = mock(Guard::class);
            $mockGuard->shouldReceive('check')->andReturn(false);

            $mockAuth = mock(AuthFactory::class);
            $mockAuth->shouldReceive('guard')->andReturn($mockGuard);

            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andReturn($mockAuth);

            expect($this->trait->callGetAuthenticatedUserId($mockApp))->toBeNull();
        });

        it('returns null when auth service throws exception', function () {
            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andThrow(new \Exception('Auth error'));
            $mockApp->shouldReceive('environment')->with('testing')->andReturn(true);

            expect($this->trait->callGetAuthenticatedUserId($mockApp))->toBeNull();
        });
    });

    describe('getSessionId method', function () {
        it('returns null when session is not bound', function () {
            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('session')->andReturn(false);

            expect($this->trait->callGetSessionId($mockApp))->toBeNull();
        });

        it('returns session ID when session is available', function () {
            $mockSession = mock(SessionManager::class);
            $mockSession->shouldReceive('getId')->andReturn('session_xyz789');

            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
            $mockApp->shouldReceive('make')->with(SessionManager::class)->andReturn($mockSession);

            expect($this->trait->callGetSessionId($mockApp))->toBe('session_xyz789');
        });

        it('returns null when session service throws exception', function () {
            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
            $mockApp->shouldReceive('make')->with(SessionManager::class)->andThrow(new \Exception('Session error'));
            $mockApp->shouldReceive('environment')->with('testing')->andReturn(true);

            expect($this->trait->callGetSessionId($mockApp))->toBeNull();
        });
    });

    describe('isTestingEnvironment method', function () {
        it('returns true when environment is testing', function () {
            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('environment')->with('testing')->andReturn(true);

            expect($this->trait->callIsTestingEnvironment($mockApp))->toBeTrue();
        });

        it('returns false when environment is production', function () {
            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('environment')->with('testing')->andReturn(false);

            expect($this->trait->callIsTestingEnvironment($mockApp))->toBeFalse();
        });
    });

    describe('edge cases and error handling', function () {
        it('handles auth check returning false and falls back to session', function () {
            $mockGuard = mock(Guard::class);
            $mockGuard->shouldReceive('check')->andReturn(false);

            $mockAuth = mock(AuthFactory::class);
            $mockAuth->shouldReceive('guard')->andReturn($mockGuard);

            $mockSession = mock(SessionManager::class);
            $mockSession->shouldReceive('getId')->andReturn('fallback_session_id');

            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andReturn($mockAuth);
            $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
            $mockApp->shouldReceive('make')->with(SessionManager::class)->andReturn($mockSession);

            $this->trait->setMockApplication($mockApp);

            expect($this->trait->callGetIdentifier())->toBe('fallback_session_id');
        });

        it('handles both auth and session failure in testing environment', function () {
            $mockApp = mock(Application::class);
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andThrow(new \Exception('Auth error'));
            $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
            $mockApp->shouldReceive('make')->with(SessionManager::class)->andThrow(new \Exception('Session error'));
            $mockApp->shouldReceive('environment')->with('testing')->andReturn(true);

            $this->trait->setMockApplication($mockApp);

            expect($this->trait->callGetIdentifier())->toBe('test_session_id');
        });
    });
});
