<?php

declare(strict_types=1);

use MasyukAI\Cart\Traits\ManagesIdentifier;

describe('ManagesIdentifier Trait', function () {
    it('returns test session ID in testing environment', function () {
        // Create test class using the trait
        $testClass = new class
        {
            use ManagesIdentifier;

            public function getTestIdentifier(): string
            {
                return $this->getIdentifier();
            }
        };

        $result = $testClass->getTestIdentifier();

        // In testing environment, should return test fallback
        expect($result)->toBe('test_session_id');
    });

    it('handles trait methods correctly', function () {
        // Test that the trait can be used properly
        $testClass = new class
        {
            use ManagesIdentifier;

            public function callGetIdentifier(): string
            {
                return $this->getIdentifier();
            }
        };

        $result = $testClass->callGetIdentifier();

        // Should return a valid string identifier
        expect($result)->toBeString();
        expect(strlen($result))->toBeGreaterThan(0);
    });

    it('provides consistent identifier results', function () {
        // Test that multiple calls return consistent results
        $testClass = new class
        {
            use ManagesIdentifier;

            public function getTestIdentifier(): string
            {
                return $this->getIdentifier();
            }
        };

        $first = $testClass->getTestIdentifier();
        $second = $testClass->getTestIdentifier();

        // Should be consistent
        expect($first)->toBe($second);
        expect($first)->toBeString();
    });
});
