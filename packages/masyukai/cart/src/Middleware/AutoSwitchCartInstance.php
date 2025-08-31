<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Middleware;

use Closure;
use Illuminate\Http\Request;
use MasyukAI\Cart\Services\CartMigrationService;
use Symfony\Component\HttpFoundation\Response;

class AutoSwitchCartInstance
{
    public function __construct(
        private CartMigrationService $migrationService
    ) {}

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Automatically switch to appropriate cart instance
        $this->migrationService->autoSwitchCartInstance();

        return $next($request);
    }
}
