<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use MasyukAI\Jnt\Console\Commands\ConfigCheckCommand;

describe('ConfigCheckCommand', function (): void {
    beforeEach(function (): void {
        // Set valid config by default
        Config::set('jnt.api_account', 'test_account');
        Config::set('jnt.private_key', "-----BEGIN RSA PRIVATE KEY-----\ntest\n-----END RSA PRIVATE KEY-----");
        Config::set('jnt.public_key', "-----BEGIN PUBLIC KEY-----\ntest\n-----END PUBLIC KEY-----");
        Config::set('jnt.environment', 'sandbox');
        Config::set('jnt.base_url', 'https://api.jnt.com');
    });

    it('passes all checks with valid configuration', function (): void {
        Http::fake([
            '*' => Http::response('OK', 200),
        ]);

        $this->artisan(ConfigCheckCommand::class)
            ->expectsOutput('J&T Express Configuration Check')
            ->assertExitCode(0);
    });

    it('fails when API account is missing', function (): void {
        Config::set('jnt.api_account', null);

        $this->artisan(ConfigCheckCommand::class)
            ->expectsOutput('Configuration validation failed. Please fix the errors above.')
            ->assertExitCode(1);
    });

    it('fails when private key is missing', function (): void {
        Config::set('jnt.private_key', null);

        $this->artisan(ConfigCheckCommand::class)
            ->expectsOutput('Configuration validation failed. Please fix the errors above.')
            ->assertExitCode(1);
    });

    it('fails when private key has invalid format', function (): void {
        Config::set('jnt.private_key', 'invalid-key-format');

        $this->artisan(ConfigCheckCommand::class)
            ->expectsOutput('Configuration validation failed. Please fix the errors above.')
            ->assertExitCode(1);
    });

    it('fails when public key is missing', function (): void {
        Config::set('jnt.public_key', null);

        $this->artisan(ConfigCheckCommand::class)
            ->expectsOutput('Configuration validation failed. Please fix the errors above.')
            ->assertExitCode(1);
    });

    it('fails when public key has invalid format', function (): void {
        Config::set('jnt.public_key', 'invalid-key-format');

        $this->artisan(ConfigCheckCommand::class)
            ->expectsOutput('Configuration validation failed. Please fix the errors above.')
            ->assertExitCode(1);
    });

    it('fails when environment is invalid', function (): void {
        Config::set('jnt.environment', 'invalid');

        $this->artisan(ConfigCheckCommand::class)
            ->expectsOutput('Configuration validation failed. Please fix the errors above.')
            ->assertExitCode(1);
    });

    it('fails when base URL is missing', function (): void {
        Config::set('jnt.base_url', null);

        $this->artisan(ConfigCheckCommand::class)
            ->expectsOutput('Configuration validation failed. Please fix the errors above.')
            ->assertExitCode(1);
    });

    it('fails when base URL is invalid', function (): void {
        Config::set('jnt.base_url', 'not-a-valid-url');

        $this->artisan(ConfigCheckCommand::class)
            ->expectsOutput('Configuration validation failed. Please fix the errors above.')
            ->assertExitCode(1);
    });

    it('fails when connectivity test fails', function (): void {
        Http::fake([
            '*' => Http::response('Error', 500),
        ]);

        $this->artisan(ConfigCheckCommand::class)
            ->assertExitCode(1);
    });

    it('shows success message when all checks pass', function (): void {
        Http::fake([
            '*' => Http::response('OK', 200),
        ]);

        $this->artisan(ConfigCheckCommand::class)
            ->expectsOutput('✓ All checks passed! J&T Express is properly configured.')
            ->assertExitCode(0);
    });
});
