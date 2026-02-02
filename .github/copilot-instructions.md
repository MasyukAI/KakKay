<laravel-boost-guidelines>
=== .ai/cart rules ===

# MasyukAI Cart Coding Guidelines

These rules support AI-assisted development inside the `MasyukAI\Cart` package within the `commerce` monorepo. Follow them before editing source, tests, or docs.

## Technology Baseline

- PHP 8.4, Laravel 12, Livewire v3, Filament v4, Octane v2, Pest v4, PHPUnit 12, Tailwind 4.
- Storage drivers: session, cache, database (with optimistic locking). Akaunting\Money handles currency.
- Traits organise cart behaviour (items, conditions, totals, metadata, storage, instances).

## Style & Syntax

- Strict types everywhere (`declare(strict_types=1);`).
- Promote constructor properties; no empty constructors.
- Use typed method signatures and return types.
- Prefer PHPDoc only for complex array shapes; avoid inline comments unless absolutely necessary.
- Follow existing naming and file placement conventions (e.g., traits in `src/Traits`, services in `src/Services`).

## Error Handling

- Throw domain exceptions (`InvalidCartItemException`, `InvalidCartConditionException`, `CartConflictException`, etc.) rather than generic ones.
- When extending storage logic, honour `StorageInterface` and maintain identifier swap support.

## Money & Conditions

- Keep monetary amounts inside `Money` instances when returning data from public APIs.
- Sanitise string prices, but never auto-convert currency.
- Respect condition ordering: item → subtotal → total.

## Events & Metrics

- Guard code paths that dispatch events with `eventsEnabled` and `$events` checks.

## Concurrency

- Database driver uses optimistic locking with version numbers; ensure new writes increment versions and throw `CartConflictException` on conflicts.
- Handle conflicts explicitly at the application level with try/catch blocks.

## Testing

- All tests use Pest. Place feature specs under `packages/commerce/tests/Feature`, unit specs under `packages/commerce/tests/Unit`.
- Reuse Testbench config (`packages/commerce/tests/TestCase.php`). Clear carts between tests and flush cache when relevant.
- Add or update tests for every behavioural change and run the targeted suite.

## Tooling

- Run `vendor/bin/pint --dirty` after edits.
- Use `vendor/bin/pest` (optionally filtered) for test runs.
- Avoid adding new dependencies without approval.

## Documentation

- Docs live under `packages/commerce/packages/cart/docs/`. Keep them aligned with the rewritten structure (see `docs/index.md`). Update docs when behaviours change.
- Troubleshooting and configuration sections must remain accurate for storage drivers and migration flows.

## Pull Request Ready Checklist

1. No TODOs or commented-out code.
2. Tests updated & passing locally.
3. Pint formatting applied.
4. Documentation updated when public behaviour changes.
5. Events properly guarded with config checks.

=== .ai/chip rules ===

# CHIP Integration – Engineering Playbook

These instructions guide automated agents working with the `masyukai/chip` package. Follow them when adding or modifying code.

## Project layout

- Package root: `packages/chip/`
- Primary namespaces: `MasyukAI\Chip` (services, data objects, builders).
- Facades: `MasyukAI\Chip\Facades\Chip` (Collect) and `MasyukAI\Chip\Facades\ChipSend` (Send).
- Core services: `src/Services/ChipCollectService.php`, `src/Services/ChipSendService.php`, `src/Services/WebhookService.php`.

## Required conventions

1. **Configuration** lives in `config/chip.php`. Do not add new environment variables unless the official CHIP docs require them. Respect existing keys such as `collect.api_key`, `collect.brand_id`, `collect.environment`, `send.api_key`, `send.api_secret`, `webhooks.public_key`, etc. Note that CHIP Collect uses the same base URL (`https://gate.chip-in.asia/api/v1/`) for both sandbox and production – the API key determines the environment. CHIP Send uses different URLs for sandbox and production.
2. **HTTP clients** already implement authentication, retry and logging. Reuse `ChipCollectClient` and `ChipSendClient`; do not create ad‑hoc Guzzle calls. Clients retry only on connection or 5xx errors – CHIP does not emit HTTP 429, so do not add logic for it.
3. **Purchases** must include either a full `client` payload (with `email`) or a `client_id`. Ensure `brand_id` is present (builder/service fills this automatically). Never send empty strings for optional fields; omit them instead.
4. **CHIP Send** support is restricted to documented endpoints: accounts, bank accounts (+ resend webhook), send instructions (+ cancel/delete/resend webhook), groups, and webhooks. Do not reintroduce removed endpoints like `/send/send_limits`, `/send/balance`, or bank-account validation routes.
5. **Webhooks** use RSA signatures. Always verify signatures using the raw request body and public key via `WebhookService::verifySignature`. Payloads should not be logged unless masked.
6. **Events**: dispatching of purchase events can be toggled with `chip.events.dispatch_purchase_events`. Honour this flag in any new event-emitting code.
7. **Caching**: public keys and payment methods are cached via `chip.cache.*` settings. Use the existing helpers instead of introducing new cache keys.
8. **Testing**: all new tests must use Pest (`tests/` directory) and bootstrap via Orchestra Testbench when interacting with package services. Update or add coverage whenever behaviour changes.
9. **Formatting**: run `vendor/bin/pint --dirty` after edits. Adhere to PHP 8.4 typing (scalar + return types) and existing code style.

## API quick reference

Consult `docs/CHIP_API_REFERENCE.md` for the complete reference. Supported endpoints include:

### CHIP Collect

- Purchases: `POST /purchases/`, `GET /purchases/{id}/`, `POST /purchases/{id}/{cancel|refund|capture|release|mark_as_paid|resend_invoice|charge}/`, `DELETE /purchases/{id}/recurring_token/`
- Payment methods: `GET /payment_methods/`
- Clients & tokens: full CRUD on `/clients/{id?}` plus `/clients/{id}/recurring_tokens`
- Webhooks: full CRUD on `/webhooks/{id?}`
- Utilities: `GET /public_key/`, `GET /account/balance/`, `GET /account/turnover/`, `GET /company_statements/`, `GET /company_statements/{id}/`, `POST /company_statements/{id}/cancel/`

### CHIP Send

- Accounts: `GET /send/accounts`
- Bank accounts: `POST|GET|PUT|DELETE /send/bank_accounts/{id?}`, `POST /send/bank_accounts/{id}/resend_webhook`
- Send instructions: `POST|GET /send/send_instructions`, `GET /send/send_instructions/{id}`, `POST /send/send_instructions/{id}/cancel`, `DELETE /send/send_instructions/{id}/delete`, `POST /send/send_instructions/{id}/resend_webhook`
- Groups: `POST|GET|PUT|DELETE /send/groups/{id?}`
- Webhooks: `POST|GET|PUT|DELETE /send/webhooks/{id?}`

## Coding tips

- Use the provided facades (`Chip`, `ChipSend`) or services for business logic. The `PurchaseBuilder` streamlines complex payload assembly.
- Map API responses through data objects (`Purchase::fromArray`, `SendInstruction::fromArray`, etc.) rather than dealing with raw arrays.
- Respect logging configuration (`chip.logging.*`) and masking when logging outbound/inbound traffic.
- Health checks and CLI tooling should follow the pattern in `ChipHealthCheckCommand` (clear output + exit codes).
- Extract shared helpers when both services require similar behaviour to avoid duplication.

## QA checklist

- Add/adjust Pest coverage for every behavioural change.
- Use Laravel's HTTP fakes to simulate CHIP responses in tests.
- Run `vendor/bin/pest` and `vendor/bin/pint --dirty` before submitting changes.
- Keep `docs/CHIP_API_REFERENCE.md` current when the API evolves.

Following this playbook keeps the package aligned with CHIP's official API and the project's engineering standards.

=== .ai/general rules ===

- All generated database code must remain compatible with PostgreSQL.
- When choosing JSON column types or operators, prioritize `jsonb` over `json`.
- All code must adhere to PHPStan level 6.
- If needed validation always consider using laravel built in validation first. Search the docs about the many options.

=== .ai/package-development rules ===

# Package Development Guidelines

These guidelines ensure packages are reliable and modern, with no backward compatibility or deprecated functions, targeting PHP 8.4.

## Core Principles

- Develop exclusively for PHP 8.4. No support for older versions.
- Remove deprecated functions immediately without maintaining compatibility.
- Use `context7` documentation for PHP 8.4 and package development best practices.

=== .ai/style rules ===

# Style Guidelines

## General Principles

- Always make the application look **futuristic**
- Use modern design patterns and cutting-edge UI/UX approaches
- Prioritize visual innovation and forward-thinking aesthetics
- Ensure the interface feels advanced and technologically sophisticated

## Implementation

- Apply futuristic design elements consistently across all components
- Use advanced color schemes, typography, and layout techniques
- Incorporate subtle animations and transitions that enhance the futuristic feel
- Maintain a cohesive visual language that suggests innovation and progress

=== .ai/testing rules ===

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

### Testing Workflow

**IMPORTANT:** Always run browser tests through the local domain (`kakkay.test`) due to session domain restrictions, while keeping Cloudflare tunnel running for webhook/callback reception.

1. **Start Cloudflare Tunnel** in dedicated terminal:
   ```bash
   cloudflared tunnel run kakkay-local
   ```

2. **Run Browser Tests** through local domain in separate terminal:
   ```bash
   # Access application at https://kakkay.test

   # Webhooks/callbacks will be received at https://local.kakkay.my

   ```

3. **Verify Configuration:**
   ```bash
   cd /Users/Saiffil/Herd/kakkay
   php artisan config:clear
   php artisan config:cache
   ```

### Usage in Tests

- **Browser Navigation:** Use `https://kakkay.test` for all browser interactions to maintain proper session handling
- **Webhook URLs:** External services post to `https://local.kakkay.my/webhooks/chip/{webhook_id}` 
- **Success Callbacks:** Post to `https://local.kakkay.my/callbacks/chip/success`
- **Local Testing:** Use curl to simulate webhooks to the public tunnel URL

### Example Workflow

```bash

# Terminal 1: Start the tunnel (KEEP THIS RUNNING!)

cloudflared tunnel run kakkay-local

# Output shows:

# Connection registered with 4 connections (Singapore)

# DO NOT CLOSE THIS TERMINAL OR RUN OTHER COMMANDS HERE!

# Terminal 2: Run browser tests through local domain

cd /Users/Saiffil/Herd/kakkay

# Ensure .env has the correct public URL

# PUBLIC_URL=https://local.kakkay.my

# SESSION_DOMAIN=.kakkay.my

# SESSION_SECURE_COOKIE=true

php artisan config:clear
php artisan config:cache

# Test webhook accessibility via tunnel

curl https://local.kakkay.my/webhooks/chip/wh_test -X POST \
  -H "Content-Type: application/json" \
  -d '{"event":"purchase.paid","data":{"id":"test"}}'

# Run browser tests at http://kakkay.test

# Webhooks will be received through the tunnel

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

### Chrome DevTools MCP Simulation

For manual testing and debugging of webhooks and success callbacks, use Chrome DevTools MCP tools to simulate requests:

**Setup:**
- Open Chrome browser to `http://kakkay.test` (local domain for session handling)
- Cloudflare tunnel must be running for webhook reception at `https://local.kakkay.my`
- Use MCP Chrome DevTools tools to simulate HTTP requests

**Simulating Webhooks:**
```bash

# Use MCP tools to POST to webhook endpoint

POST https://local.kakkay.my/webhooks/chip/wh_test123
Headers:
  Content-Type: application/json
  X-Signature: {valid_signature}
Body:
{
  "event": "purchase.paid",
  "data": {
    "id": "test_purchase_123",
    "reference": "cart_ref_456",
    "status": "paid"
  }
}
```

**Simulating Success Callbacks:**
```bash

# Use MCP tools to POST to success callback endpoint

POST https://local.kakkay.my/callbacks/chip/success
Headers:
  Content-Type: application/json
  X-Signature: {valid_signature}
Body:
{
  "event": "purchase.success",
  "purchase_id": "test_purchase_123",
  "reference": "cart_ref_456"
}
```

**Verification:**
- Check Laravel logs for webhook/callback processing messages
- Verify database for created orders/payments
- Monitor browser for success page redirects
- Use Chrome DevTools Network tab to observe requests

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

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.16
- filament/filament (FILAMENT) - v5
- laravel/framework (LARAVEL) - v12
- laravel/octane (OCTANE) - v2
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- laravel/telescope (TELESCOPE) - v5
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `fluxui-development` — Develops UIs with Flux UI Free components. Activates when creating buttons, forms, modals, inputs, dropdowns, checkboxes, or UI components; replacing HTML form elements with Flux; working with flux: components; or when the user mentions Flux, component library, UI components, form fields, or asks about available Flux components.
- `pest-testing` — Tests applications using the Pest 4 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, browser testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd and will be available at: `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs for the user.
- You must not run any commands to make the site available via HTTP(S). It is always available through Laravel Herd.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== fluxui-free/core rules ===

# Flux UI Free

- Flux UI is the official Livewire component library. This project uses the free edition, which includes all free components and variants but not Pro components.
- Use `<flux:*>` components when available; they are the recommended way to build Livewire interfaces.
- IMPORTANT: Activate `fluxui-development` when working with Flux UI components.

=== pint/core rules ===

# Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

=== filament/filament rules ===

## Filament

- Filament is used by this application. Follow existing conventions for how and where it's implemented.
- Filament is a Server-Driven UI (SDUI) framework for Laravel that lets you define user interfaces in PHP using structured configuration objects. Built on Livewire, Alpine.js, and Tailwind CSS.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices.

### Artisan

- Use Filament-specific Artisan commands to create files. Find them with `list-artisan-commands` or `php artisan --help`.
- Inspect required options and always pass `--no-interaction`.

### Patterns

Use static `make()` methods to initialize components. Most configuration methods accept a `Closure` for dynamic values.

Use `Get $get` to read other form field values for conditional logic:

<code-snippet name="Conditional form field" lang="php">
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

Select::make('type')
    ->options(CompanyType::class)
    ->required()
    ->live(),

TextInput::make('company_name')
    ->required()
    ->visible(fn (Get $get): bool => $get('type') === 'business'),

</code-snippet>

Use `state()` with a `Closure` to compute derived column values:

<code-snippet name="Computed table column" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

</code-snippet>

Actions encapsulate a button with optional modal form and logic:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

Action::make('updateEmail')
    ->form([
        TextInput::make('email')->email()->required(),
    ])
    ->action(fn (array $data, User $record): void => $record->update($data)),

</code-snippet>

### Testing

Authenticate before testing panel functionality. Filament uses Livewire, so use `livewire()` or `Livewire::test()`:

<code-snippet name="Filament Table Test" lang="php">
    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->searchTable($users->first()->name)
        ->assertCanSeeTableRecords($users->take(1))
        ->assertCanNotSeeTableRecords($users->skip(1));

</code-snippet>

<code-snippet name="Filament Create Resource Test" lang="php">
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Test',
            'email' => 'test@example.com',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(User::class, [
        'name' => 'Test',
        'email' => 'test@example.com',
    ]);

</code-snippet>

<code-snippet name="Testing Validation" lang="php">
    livewire(CreateUser::class)
        ->fillForm([
            'name' => null,
            'email' => 'invalid-email',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'email' => 'email',
        ])
        ->assertNotNotified();

</code-snippet>

<code-snippet name="Calling Actions" lang="php">
    use Filament\Actions\DeleteAction;
    use Filament\Actions\Testing\TestAction;

    livewire(EditUser::class, ['record' => $user->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    livewire(ListUsers::class)
        ->callAction(TestAction::make('promote')->table($user), [
            'role' => 'admin',
        ])
        ->assertNotified();

</code-snippet>

### Common Mistakes

**Commonly Incorrect Namespaces:**
- Form fields (TextInput, Select, etc.): `Filament\Forms\Components\`
- Infolist entries (for read-only views) (TextEntry, IconEntry, etc.): `Filament\Infolists\Components\`
- Layout components (Grid, Section, Fieldset, Tabs, Wizard, etc.): `Filament\Schemas\Components\`
- Schema utilities (Get, Set, etc.): `Filament\Schemas\Components\Utilities\`
- Actions: `Filament\Actions\` (no `Filament\Tables\Actions\` etc.)
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

**Recent breaking changes to Filament:**
- File visibility is `private` by default. Use `->visibility('public')` for public access.
- `Grid`, `Section`, and `Fieldset` no longer span all columns by default.

=== filament/blueprint rules ===

## Filament Blueprint

You are writing Filament v5 implementation plans. Plans must be specific enough
that an implementing agent can write code without making decisions.

**Start here**: Read
`/vendor/filament/blueprint/resources/markdown/planning/overview.md` for plan format,
required sections, and what to clarify with the user before planning.
</laravel-boost-guidelines>
