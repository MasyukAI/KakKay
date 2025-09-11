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

## Test Enforcement
- Every codebase change **must** include a corresponding Pest unit or feature test.
- Create tests with `php artisan make:test --pest <name>`.
- Run minimal tests affected by changes using filters (e.g., `vendor/bin/pest --filter=test_user_can_login` or `vendor/bin/pest tests/Feature/Controllers/UserControllerTest.php`).
- Use `--parallel` to optimize speed (e.g., `vendor/bin/pest --parallel`), ensuring no race conditions or test interference.
- Confirm with the user to run the full test suite after relevant tests pass.
- For package testing, run tests from the package directory (e.g., `packages/<package-name>/tests/`) using `vendor/bin/pest --parallel`. Application tests can be run from the root using the same command.

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