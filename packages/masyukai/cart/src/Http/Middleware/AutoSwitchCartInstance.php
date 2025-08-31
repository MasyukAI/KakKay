<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MasyukAI\Cart\Facades\Cart;
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
        // Store the current instance to restore later
        $originalInstance = Cart::instance();

        try {
            // Determine the appropriate cart instance
            $targetInstance = $this->determineCartInstance($request);
            
            // Switch to the target instance
            Cart::setInstance($targetInstance);

            // Process the request
            return $next($request);
        } finally {
            // Always restore the original instance
            Cart::setInstance($originalInstance);
        }
    }

    /**
     * Determine the appropriate cart instance based on authentication status.
     */
    protected function determineCartInstance(Request $request): string
    {
        if (Auth::check()) {
            return $this->cartMigration->getInstanceName(Auth::id());
        }

        return $this->cartMigration->getInstanceName(null, $request->session()->getId());
    }
}
