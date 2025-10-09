<?php

declare(strict_types=1);

use MasyukAI\Jnt\Exceptions\JntConfigurationException;

describe('JntConfigurationException', function () {
    it('creates exception for missing API key', function () {
        $exception = JntConfigurationException::missingApiKey();

        expect($exception)
            ->toBeInstanceOf(JntConfigurationException::class)
            ->getMessage()->toContain('API key is not configured')
            ->and($exception->configKey)->toBe('jnt.api_key');
    });

    it('creates exception for invalid API key', function () {
        $exception = JntConfigurationException::invalidApiKey();

        expect($exception)
            ->toBeInstanceOf(JntConfigurationException::class)
            ->getMessage()->toContain('API key format is invalid')
            ->and($exception->configKey)->toBe('jnt.api_key');
    });

    it('creates exception for missing private key', function () {
        $exception = JntConfigurationException::missingPrivateKey();

        expect($exception)
            ->toBeInstanceOf(JntConfigurationException::class)
            ->getMessage()->toContain('private key is not configured')
            ->and($exception->configKey)->toBe('jnt.private_key');
    });

    it('creates exception for invalid private key', function () {
        $exception = JntConfigurationException::invalidPrivateKey();

        expect($exception)
            ->toBeInstanceOf(JntConfigurationException::class)
            ->getMessage()->toContain('private key format is invalid')
            ->and($exception->configKey)->toBe('jnt.private_key');
    });

    it('creates exception for missing public key', function () {
        $exception = JntConfigurationException::missingPublicKey();

        expect($exception)
            ->toBeInstanceOf(JntConfigurationException::class)
            ->getMessage()->toContain('public key is not configured')
            ->and($exception->configKey)->toBe('jnt.public_key');
    });

    it('creates exception for invalid public key', function () {
        $exception = JntConfigurationException::invalidPublicKey();

        expect($exception)
            ->toBeInstanceOf(JntConfigurationException::class)
            ->getMessage()->toContain('public key format is invalid')
            ->and($exception->configKey)->toBe('jnt.public_key');
    });

    it('creates exception for missing API account', function () {
        $exception = JntConfigurationException::missingApiAccount();

        expect($exception)
            ->toBeInstanceOf(JntConfigurationException::class)
            ->getMessage()->toContain('API account is not configured')
            ->and($exception->configKey)->toBe('jnt.api_account');
    });

    it('creates exception for missing webhook URL', function () {
        $exception = JntConfigurationException::missingWebhookUrl();

        expect($exception)
            ->toBeInstanceOf(JntConfigurationException::class)
            ->getMessage()->toContain('webhook URL is not configured')
            ->and($exception->configKey)->toBe('jnt.webhook.url');
    });

    it('creates exception for invalid configuration value', function () {
        $exception = JntConfigurationException::invalidValue('jnt.timeout', 'Must be a positive integer');

        expect($exception)
            ->toBeInstanceOf(JntConfigurationException::class)
            ->getMessage()->toContain("Invalid configuration value for 'jnt.timeout'")
            ->getMessage()->toContain('Must be a positive integer')
            ->and($exception->configKey)->toBe('jnt.timeout');
    });

    it('creates exception for missing configuration key', function () {
        $exception = JntConfigurationException::missingKey('jnt.custom_setting');

        expect($exception)
            ->toBeInstanceOf(JntConfigurationException::class)
            ->getMessage()->toContain("Required configuration key 'jnt.custom_setting' is missing")
            ->and($exception->configKey)->toBe('jnt.custom_setting');
    });

    it('creates exception for invalid environment', function () {
        $exception = JntConfigurationException::invalidEnvironment('development');

        expect($exception)
            ->toBeInstanceOf(JntConfigurationException::class)
            ->getMessage()->toContain("Invalid J&T environment 'development'")
            ->getMessage()->toContain("Must be 'production' or 'sandbox'")
            ->and($exception->configKey)->toBe('jnt.environment');
    });
});
