<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\Container;
use Illuminate\Session\SessionManager;
use MasyukAI\Cart\Traits\ManagesIdentifier;

beforeEach(function () {
    $this->trait = new class
    {
        use ManagesIdentifier;

        // Make protected methods public for testing
        public function publicGetAuthenticatedUserId(Container $app): ?string
        {
            return $this->getAuthenticatedUserId($app);
        }

        public function publicGetSessionId(Container $app): ?string
        {
            return $this->getSessionId($app);
        }

        public function publicGetApplication(): Container
        {
            return $this->getApplication();
        }
    };
});

describe('ManagesIdentifier Trait', function () {
    describe('getIdentifier method', function () {
        it('returns authenticated user ID when user is logged in', function () {
            // Mock the application container
            $mockApp = Mockery::mock(Container::class);

            // Mock auth factory and guard
            $mockAuth = Mockery::mock(AuthFactory::class);
            $mockGuard = Mockery::mock(Guard::class);

            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andReturn($mockAuth);
            $mockAuth->shouldReceive('guard')->andReturn($mockGuard);
            $mockGuard->shouldReceive('check')->andReturn(true);
            $mockGuard->shouldReceive('id')->andReturn(123);

            // Mock the getApplication method
            $trait = new class
            {
                use ManagesIdentifier;

                protected function getApplication(): Container
                {
                    global $mockApp;

                    return $mockApp;
                }
            };

            $GLOBALS['mockApp'] = $mockApp;

            expect($trait->getIdentifier())->toBe('123');
        });

        it('returns session ID when user is not authenticated', function () {
            // Mock the application container
            $mockApp = Mockery::mock(Container::class);

            // Mock auth (returning null for unauthenticated user)
            $mockAuth = Mockery::mock(AuthFactory::class);
            $mockGuard = Mockery::mock(Guard::class);

            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andReturn($mockAuth);
            $mockAuth->shouldReceive('guard')->andReturn($mockGuard);
            $mockGuard->shouldReceive('check')->andReturn(false);

            // Mock session manager
            $mockSession = Mockery::mock(SessionManager::class);
            $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
            $mockApp->shouldReceive('make')->with(SessionManager::class)->andReturn($mockSession);
            $mockSession->shouldReceive('getId')->andReturn('session_abc123');

            // Mock the getApplication method
            $trait = new class
            {
                use ManagesIdentifier;

                protected function getApplication(): Container
                {
                    global $mockApp;

                    return $mockApp;
                }
            };

            $GLOBALS['mockApp'] = $mockApp;

            expect($trait->getIdentifier())->toBe('session_abc123');
        });

        it('throws exception when neither auth nor session are available', function () {
            // Mock the application container
            $mockApp = Mockery::mock(Container::class);

            // Mock auth not bound
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(false);

            // Mock session not bound
            $mockApp->shouldReceive('bound')->with('session')->andReturn(false);

            // Mock the getApplication method
            $trait = new class
            {
                use ManagesIdentifier;

                protected function getApplication(): Container
                {
                    global $mockApp;

                    return $mockApp;
                }
            };

            $GLOBALS['mockApp'] = $mockApp;

            expect(fn () => $trait->getIdentifier())
                ->toThrow(RuntimeException::class, 'Cart identifier cannot be determined: neither auth nor session services are available');
        });

        it('returns session ID when auth service throws exception', function () {
            // Mock the application container
            $mockApp = Mockery::mock(Container::class);

            // Mock auth throwing exception
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andThrow(new Exception('Auth service error'));

            // Mock session manager
            $mockSession = Mockery::mock(SessionManager::class);
            $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
            $mockApp->shouldReceive('make')->with(SessionManager::class)->andReturn($mockSession);
            $mockSession->shouldReceive('getId')->andReturn('fallback_session');

            // Mock the getApplication method
            $trait = new class
            {
                use ManagesIdentifier;

                protected function getApplication(): Container
                {
                    global $mockApp;

                    return $mockApp;
                }
            };

            $GLOBALS['mockApp'] = $mockApp;

            expect($trait->getIdentifier())->toBe('fallback_session');
        });

        it('throws exception when both auth and session services throw exceptions', function () {
            // Mock the application container
            $mockApp = Mockery::mock(Container::class);

            // Mock auth throwing exception
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andThrow(new Exception('Auth service error'));

            // Mock session throwing exception
            $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
            $mockApp->shouldReceive('make')->with(SessionManager::class)->andThrow(new Exception('Session service error'));

            // Mock the getApplication method
            $trait = new class
            {
                use ManagesIdentifier;

                protected function getApplication(): Container
                {
                    global $mockApp;

                    return $mockApp;
                }
            };

            $GLOBALS['mockApp'] = $mockApp;

            expect(fn () => $trait->getIdentifier())
                ->toThrow(RuntimeException::class, 'Cart identifier cannot be determined: neither auth nor session services are available');
        });
    });

    describe('getAuthenticatedUserId method', function () {
        it('returns null when auth service is not bound', function () {
            $mockApp = Mockery::mock(Container::class);
            $mockApp->shouldReceive('bound')->with('auth')->andReturn(false);

            $result = $this->trait->publicGetAuthenticatedUserId($mockApp);

            expect($result)->toBeNull();
        });

        it('returns user ID as string when user is authenticated', function () {
            $mockApp = Mockery::mock(Container::class);
            $mockAuth = Mockery::mock(AuthFactory::class);
            $mockGuard = Mockery::mock(Guard::class);

            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andReturn($mockAuth);
            $mockAuth->shouldReceive('guard')->andReturn($mockGuard);
            $mockGuard->shouldReceive('check')->andReturn(true);
            $mockGuard->shouldReceive('id')->andReturn(456);

            $result = $this->trait->publicGetAuthenticatedUserId($mockApp);

            expect($result)->toBe('456');
        });

        it('returns null when user is not authenticated', function () {
            $mockApp = Mockery::mock(Container::class);
            $mockAuth = Mockery::mock(AuthFactory::class);
            $mockGuard = Mockery::mock(Guard::class);

            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andReturn($mockAuth);
            $mockAuth->shouldReceive('guard')->andReturn($mockGuard);
            $mockGuard->shouldReceive('check')->andReturn(false);

            $result = $this->trait->publicGetAuthenticatedUserId($mockApp);

            expect($result)->toBeNull();
        });

        it('returns null when auth service throws exception', function () {
            $mockApp = Mockery::mock(Container::class);

            $mockApp->shouldReceive('bound')->with('auth')->andReturn(true);
            $mockApp->shouldReceive('make')->with(AuthFactory::class)->andThrow(new Exception('Auth service error'));

            $result = $this->trait->publicGetAuthenticatedUserId($mockApp);

            expect($result)->toBeNull();
        });
    });

    describe('getSessionId method', function () {
        it('returns null when session service is not bound', function () {
            $mockApp = Mockery::mock(Container::class);
            $mockApp->shouldReceive('bound')->with('session')->andReturn(false);

            $result = $this->trait->publicGetSessionId($mockApp);

            expect($result)->toBeNull();
        });

        it('returns session ID when session service is available', function () {
            $mockApp = Mockery::mock(Container::class);
            $mockSession = Mockery::mock(SessionManager::class);

            $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
            $mockApp->shouldReceive('make')->with(SessionManager::class)->andReturn($mockSession);
            $mockSession->shouldReceive('getId')->andReturn('test_session_id');

            $result = $this->trait->publicGetSessionId($mockApp);

            expect($result)->toBe('test_session_id');
        });

        it('returns null when session service throws exception', function () {
            $mockApp = Mockery::mock(Container::class);

            $mockApp->shouldReceive('bound')->with('session')->andReturn(true);
            $mockApp->shouldReceive('make')->with(SessionManager::class)->andThrow(new Exception('Session service error'));

            $result = $this->trait->publicGetSessionId($mockApp);

            expect($result)->toBeNull();
        });
    });

    describe('getApplication method', function () {
        it('returns the Laravel application instance', function () {
            $result = $this->trait->publicGetApplication();

            expect($result)->toBeInstanceOf(Container::class);
        });
    });
});

afterEach(function () {
    Mockery::close();
});
