<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MasyukAI\Cart\Services\CartMigrationService;
use Symfony\Component\HttpFoundation\Response;

class AutoSwitchCartInstance
{
    public function __construct(
        protected CartMigrationService $cartMigration
    ) {}

    /**
     * Handle an incoming request and automatically switch cart instance
     * based on authentication status.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Automatically switch to appropriate cart instance
        $this->cartMigration->autoSwitchCartInstance();

        // Process the request
        return $next($request);
    }
}
