# CHIP Integration – Engineering Playbook

These instructions guide automated agents working with the `masyukai/chip` package. Follow them when adding or modifying code.

## Project layout

- Package root: `packages/masyukai/chip/`
- Primary namespaces: `MasyukAI\Chip` (services, data objects, builders).
- Facades: `MasyukAI\Chip\Facades\Chip` (Collect) and `MasyukAI\Chip\Facades\ChipSend` (Send).
- Core services: `src/Services/ChipCollectService.php`, `src/Services/ChipSendService.php`, `src/Services/WebhookService.php`.

## Required conventions

1. **Configuration** lives in `config/chip.php`. Do not add new environment variables unless the official CHIP docs require them. Respect existing keys such as `collect.api_key`, `collect.brand_id`, `collect.environment`, `send.api_key`, `send.api_secret`, `webhooks.public_key`, etc.
2. **HTTP clients** already implement authentication, retry and logging. Reuse `ChipCollectClient` and `ChipSendClient`; do not create ad‑hoc Guzzle calls. Clients retry only on connection or 5xx errors – CHIP does not emit HTTP 429, so do not add logic for it.
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
- Use Laravel’s HTTP fakes to simulate CHIP responses in tests.
- Run `vendor/bin/pest` and `vendor/bin/pint --dirty` before submitting changes.
- Keep `docs/CHIP_API_REFERENCE.md` current when the API evolves.

Following this playbook keeps the package aligned with CHIP’s official API and the project’s engineering standards.
