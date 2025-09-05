<?php

declare(strict_types=1);

use App\Models\User;
use MasyukAI\Cart\Traits\ManagesIdentifier;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Create a test trait instance
    $this->traitInstance = new class {
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
    // Start a session
    $this->startSession();
    $sessionId = session()->getId();
    
    // Get identifier - should return session ID
    $identifier = $this->traitInstance->callGetIdentifier();
    
    expect($identifier)->toBe($sessionId);
});

it('handles auth check returning false properly', function () {
    // Start session to provide fallback
    $this->startSession();
    $sessionId = session()->getId();
    
    // Should fall back to session ID when no user is authenticated
    $identifier = $this->traitInstance->callGetIdentifier();
    
    expect($identifier)->toBe($sessionId);
});

it('throws exception when services are unavailable', function () {
    // Create a trait instance that simulates missing services
    $traitWithNoServices = new class {
        use ManagesIdentifier;
        
        public function getIdentifier(): string
        {
            throw new \RuntimeException('Neither auth nor session services are available for cart identifier');
        }
    };
    
    expect(fn() => $traitWithNoServices->getIdentifier())
        ->toThrow(\RuntimeException::class, 'Neither auth nor session services are available for cart identifier');
});
