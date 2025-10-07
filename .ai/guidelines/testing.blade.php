# Testing Guidelines

These guidelines ensure the codebase is reliable, maintainable, and functions as intended using Pest (v4) as the exclusive testing framework.

## Foundational Context
All tests **must** use Pest (v4) and adhere to its conventions. Use the `context7` tool to access official documentation for Pest, Laravel, and relevant packages to ensure correct and idiomatic implementations for both tests and the codebase.

## Conventions
- Follow existing test structure, naming, and organization in the `tests/` directory.
- Structure the `tests/` directory with `Feature` and `Unit` folders, mirroring the app's structure (e.g., `Controllers`, `Models`, `Services`).
  - Example: Place tests for `App\Http\Controllers\UserController` in `tests/Feature/Controllers/UserControllerTest.php`.
  - Example: Place unit tests for `App\Services\UserService` in `tests/Unit/Services/UserServiceTest.php`.
- Use descriptive test file and method names following Laravel conventions (e.g., `UserControllerTest.php` with `test_user_can_login`, not `TestLogin`).
- Use descriptive test names (e.g., `testUserCanRegisterForDiscounts`, not `testDiscount`).
- Reuse existing test helpers, fixtures, or utilities before creating new ones.
- Reference `context7` documentation to align tests with best practices.
- The test should prove the code is working as intended not the other way around.

## Test Enforcement
- Every codebase change **must** include a corresponding Pest unit or feature test.
- Create tests with `php artisan make:test --pest <name>`.
- Run minimal tests affected by changes using filters (e.g., `vendor/bin/pest --filter=test_user_can_login` or `vendor/bin/pest tests/Feature/Controllers/UserControllerTest.php`).
- Use `--parallel` to optimize speed (e.g., `vendor/bin/pest --parallel`), ensuring no race conditions or test interference.
- Confirm with the user to run the full test suite after relevant tests pass.
- **CRITICAL:** Tests for packages **must** be placed within the correct package directory (e.g., `packages/<package-name>/tests/`) so they use the package's test setup (Pest.php, phpunit.xml, etc.). Placing package tests in the application's `tests/` directory will cause failing tests and errors due to incorrect configuration and autoloading.
- For package testing, **always** run tests from within the package directory using `vendor/bin/pest` or `vendor/bin/pest --parallel`. Application tests should be run from the root directory.

## Purpose of Tests
- Tests verify that the codebase functions as intended, ensuring reliability.
- Do **not** alter the codebase solely to pass tests, as this introduces bugs or false positives. Focus on fixing genuine code issues.

## Primacy of Codebase Correctness
- Ensure codebase correctness **first** using `context7` to consult relevant documentation.
- Tests are reliable only when validating a correct codebase. Tests validate, not drive, code changes.

## Best Practices for Test Implementation
- Tests must be self-contained and independent to ensure isolation:
  - Avoid external configuration files; set option values directly in test code.
  - Ensure consistent test execution across environments.
- Use Pest’s dataset feature for repetitive data (e.g., validation rules), guided by `context7`.
- Leverage Pest-specific assertions (e.g., `assertSuccessful`, `assertForbidden`) instead of generic status checks, per `context7` documentation.

## Handling Failing Tests
- Failing tests may be outdated due to codebase changes, such as deprecated or removed classes, methods, or properties:
  - Verify codebase correctness using `context7` documentation to confirm the current implementation.
  - Check if the test references deprecated or removed classes, methods, or properties. Update the test to use the current equivalents or remove the test if it no longer applies.
  - Update test assertions, logic, and configuration to align with current codebase behavior.
- Regularly maintain tests to ensure alignment with the codebase.

## Running Tests with Pest
- Run all tests: `vendor/bin/pest`.
- Run specific tests: `vendor/bin/pest tests/Feature/Controllers/UserControllerTest.php` or `vendor/bin/pest --filter=test_user_can_login`.
- Use `--parallel` for faster execution, ensuring test isolation.
- Tests reside in `tests/Feature` and `tests/Unit` directories, organized to mirror the app’s structure (e.g., `tests/Feature/Controllers`, `tests/Unit/Models`).

## Pest-Specific Testing Practices
- Use `it()` or `test()` for readable test definitions, per `context7`.
- Use `Pest\Laravel\mock` for mocking (e.g., `use function Pest\Laravel\mock;`), following `context7` guidance.

```php
it('verifies user can login', function () {
    $user = User::factory()->create();
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    $response->assertSuccessful();
    expect(auth()->check())->toBeTrue();
});
```

## Webhook Testing with Cloudflare Tunnel

For end-to-end testing of webhooks (e.g., payment gateway callbacks), use Cloudflare Tunnel to create a public URL.

### Setup
1. **Start Cloudflare Tunnel:** Run `cloudflared tunnel run kakkay-local` in a **dedicated terminal session**
2. **Keep Terminal Open:** The `cloudflared` command MUST stay running - do not close the terminal or run other commands
3. **Public URL:** The tunnel is configured at `https://local.kakkay.my` (persistent subdomain)
4. **Tunnel Config:** Located at `~/.cloudflared/config.yml`
5. **Tunnel ID:** `de670c7a-c0a9-4603-b5b5-c807f9d57872`
6. **Service URL:** Points to `http://kakkay.test:80` (Herd handles SSL separately)

⚠️ **CRITICAL:** If you close the terminal running `cloudflared`, the tunnel will close immediately and webhooks will fail.

### Configuration
The tunnel is pre-configured with:
- **DNS Record:** `local.kakkay.my` → `de670c7a-c0a9-4603-b5b5-c807f9d57872.cfargotunnel.com`
- **Herd Domain:** `local.kakkay.my` linked to `/Users/Saiffil/Herd/kakkay`
- **SSL:** Enabled via `herd secure local.kakkay.my`
- **Session Domain:** `.kakkay.my` (configured in `.env` as `SESSION_DOMAIN=.kakkay.my`)

### Usage in Tests
- The public URL `https://local.kakkay.my` should be used for webhook callbacks
- Format: `https://local.kakkay.my/webhooks/chip/{webhook_id}` for specific webhooks
- Format: `https://local.kakkay.my/callbacks/chip/success` for success callbacks
- External payment gateways can POST to this URL and reach your local application

### Example Workflow
```bash
# Terminal 1: Start the tunnel (KEEP THIS RUNNING!)
cloudflared tunnel run kakkay-local

# Output shows:
# Connection registered with 4 connections (Singapore)
# DO NOT CLOSE THIS TERMINAL OR RUN OTHER COMMANDS HERE!

# Terminal 2: Verify configuration
cd /Users/Saiffil/Herd/kakkay

# Ensure .env has the correct public URL
# PUBLIC_URL=https://local.kakkay.my
# SESSION_DOMAIN=.kakkay.my
# SESSION_SECURE_COOKIE=true

php artisan config:clear
php artisan config:cache

# Test webhook accessibility
curl https://local.kakkay.my/webhooks/chip/wh_test -X POST \
  -H "Content-Type: application/json" \
  -d '{"event":"purchase.paid","data":{"id":"test"}}'
```

### Best Practices
- **Persistent URL:** Unlike Expose, the URL `local.kakkay.my` is permanent (no expiration)
- **Session Configuration:** Ensure `SESSION_DOMAIN=.kakkay.my` to avoid "page expired" errors
- **Secure Cookies:** Set `SESSION_SECURE_COOKIE=true` for HTTPS tunnel
- **Security:** Only use `local.` subdomain for testing; production uses root domain
- **Monitoring:** Check tunnel status with `cloudflared tunnel info kakkay-local`
- **Verification:** Test that the webhook route is publicly accessible before running full tests

### Testing Webhook Flow
1. Start `cloudflared tunnel run kakkay-local` to activate tunnel
2. Configure payment gateway to use `https://local.kakkay.my/webhooks/chip/{webhook_id}` as callback
3. Trigger payment flow through browser testing
4. Payment gateway will POST webhook to public URL
5. Webhook reaches local application through tunnel
6. Verify order/payment creation in local database

### Troubleshooting
- **If webhook doesn't arrive:** Check that `cloudflared` is still running in terminal
- **If webhook fails:** Check logs at `storage/logs/laravel.log` for errors
- **If "page expired" errors:** Verify `SESSION_DOMAIN=.kakkay.my` in `.env` and run `php artisan config:clear`
- **If tunnel disconnects:** Restart with `cloudflared tunnel run kakkay-local`
- **Check tunnel status:** Run `cloudflared tunnel info kakkay-local` to see active connections
- **View tunnel logs:** Check `~/.cloudflared/*.log` for detailed tunnel logs

### Cloudflare Tunnel Commands
```bash
# Start tunnel
cloudflared tunnel run kakkay-local

# Check tunnel info
cloudflared tunnel info kakkay-local

# List all tunnels
cloudflared tunnel list

# View tunnel configuration
cat ~/.cloudflared/config.yml
```