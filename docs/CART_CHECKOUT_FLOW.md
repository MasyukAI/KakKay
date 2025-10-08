# Cart → Checkout → Order Flow Reference

This guide maps every moving piece involved in taking a customer from adding items to their cart all the way to the CHIP payment callback and post-payment pages. It’s intended as a deep reference so you can confidently plug in follow-on3. **Add webhook retry mechanism** – implement exponential backoff for failed webhook deliveries to CHIP, with manual retry endpoints for support team intervention on critical payment processing failures. **Not needed** - cart changes are handled transparently by creating new payment intents automatically without disrupting user experience.features like emails, invoices, and shipment fulfillment.

---

## 1. High-level sequence

1. **Cart interaction** – Livewire Cart component (`App\Livewire\Cart`) orchestrates cart updates through the `MasyukAI\Cart` package and keeps the UI in sync via Livewire events.
2. **Checkout entry** – Livewire Checkout component (`App\Livewire\Checkout`) renders the form, reads cart data, and prepares customer details.
3. **Payment intent** – `CheckoutService::processCheckout()` asks `PaymentService` to create or reuse a CHIP purchase. The resulting intent snapshot is stored in cart metadata.
4. **Redirect** – Customer is redirected to the CHIP hosted checkout using the URL returned by `ChipPaymentGateway::createPurchase()`.
5. **Callbacks** – CHIP fires an immediate success callback (`POST /webhooks/chip`) and a follow-up webhook (`POST /webhooks/chip/{webhook_id}`). `ChipController::handle()` verifies signatures and forwards both payloads to `WebhookProcessor`.
6. **Order creation** – `WebhookProcessor::handlePurchasePaid()` calls `CheckoutService::handlePaymentSuccess()`, which validates the intent against the payload, creates the order + payment, and clears the cart (idempotently).
7. **Customer redirected back** – CHIP redirects the customer to `/checkout/success/{reference}` (or failure/cancel routes). `CheckoutService::prepareSuccessView()` reconstructs all view data, pulling from the database and (if needed) CHIP’s API.

---

## 2. Entry points & routing

| Step | Route / Component | File | Method / Property | Notes |
| --- | --- | --- | --- | --- |
| Cart page | `Volt::route('/cart', 'cart')` | `routes/web.php` | Blade + Volt | Uses `resources/views/livewire/cart.blade.php` rendered by `App\Livewire\Cart` |
| Checkout page | `Route::get('/checkout', App\Livewire\Checkout::class)` | `routes/web.php` | `App\Livewire\Checkout` | Livewire component handles form + submission |
| Success | `Route::get('/checkout/success/{reference}', ...)` | `routes/web.php` | `CheckoutController::success` | Uses `resources/views/checkout/success.blade.php` |
| Failure | `/checkout/failure/{reference}` | `CheckoutController::failure` | | Marks payment as failed when appropriate |
| Cancel | `/checkout/cancel/{reference}` | `CheckoutController::cancel` | | Marks payment as cancelled |
| Webhooks | `Route::post('/webhooks/chip/{webhook?}', ...)` | `routes/web.php` | `ChipController::handle` | Handles success callbacks (no `{webhook}`) and webhook retries |

For local testing the dev route group (`routes/dev.php`) exposes webhook simulators.

---

## 3. Cart state management

### 3.1 Livewire cart component (`App\Livewire\Cart`)

Key properties:

- `public array $cartItems` – hydrated from `CartFacade::getItems()`.
- `public string $voucherCode`, `public $suggestedProducts` – UI state.

Important methods:

- `mount()` → `loadCartItems()` + `loadSuggestedProducts()` to prime data.
- `loadCartItems()` – maps cart items into the array shape expected by the Blade view, falls back to `CartFacade::clear()` when empty.
- `incrementQuantity()`, `decrementQuantity()`, `updateQuantity()` – all use `CartFacade::update()` with the cart package’s quantity signature (`['quantity' => ['value' => $newQuantity]]`). They dispatch Livewire event `cart-updated` so other components (checkout header badge) refresh.
- `addToCart()` and `removeItem()` integrate with Filament notifications for customer feedback.

### 3.2 Cart package internals

The cart implementation lives in `packages/masyukai/cart`:

- `MasyukAI\Cart\Cart` exposes `getItems()`, `getVersion()`, `getId()`, `setMetadata()`, etc.
- Storage layer persists carts in the `carts` table (migration `create_carts_table.php`). Important columns:
  - `metadata` (JSONB) – stores the payment intent snapshot.
  - `version` – incremented whenever the cart mutates (used to detect stale payment intents).

Because the package supports optimistic locking, every metadata change (like storing a payment intent) also bumps the cart version via `PaymentService::createPaymentIntent()`.

---

## 4. Checkout Livewire component (`app/Livewire/Checkout.php`)

### 4.1 Public state

- `public ?array $data = []` – Livewire form state bound through Filament Form schemas.
- `public array $cartItems`, `public array $availablePaymentMethods` – primed in `mount()` (currently commented out as not used in UI).
- `public ?array $activePaymentIntent`, `public bool $cartChangedSinceIntent`, `public bool $showCartChangeWarning` – derived from `CheckoutService::getCartChangeStatus()`.
- `public string $selectedCountryCode = '+60'`, `public string $selectedPaymentGroup = 'card'` – UI defaults.

### 4.2 Lifecycle

`mount()` executes the following sequence:

1. Loads items via `CartFacade::getItems()`; redirects back to the cart if empty.
2. Reads the cart version and previously stored session version to detect changes.
3. Calls `CheckoutService::getCartChangeStatus()` which in turn uses `PaymentService::validateCartPaymentIntent()` to detect a reusable intent.
4. Stores the current cart version in the session, fills default form values, and preloads payment methods via `PaymentService::getAvailablePaymentMethods()` (currently commented out as not used in UI).

### 4.3 Form & submission

- The Filament schema (see `form()` method) renders shipping address fields; the payment method selector is currently commented out but the logic for `selectPaymentGroup()` / `selectPaymentMethod()` keeps metadata ready for CHIP’s whitelist.
- `submitCheckout()` pulls the form state, normalizes customer data, and delegates to `CheckoutService::processCheckout()`.
  - On success it redirects the browser to the CHIP hosted checkout URL.
  - When an existing intent is reused the session flash message informs the user.

Computed properties (`#[Computed]`) expose totals by delegating to `CartFacade` monetized getters. These are used by the Blade sidebar summary.

---

## 5. Payment and intent orchestration

### 5.1 `CheckoutService::processCheckout()`

Steps performed:

1. Guard against empty carts.
2. Inspect the cart metadata via `PaymentService::validateCartPaymentIntent()`.
3. If the intent is still valid (matching cart version and `status === 'created'`), reuse it and bubble up the `checkout_url`.
4. Otherwise, call `PaymentService::clearPaymentIntent()` to wipe stale metadata and proceed to create a fresh intent.
5. Persist the newly created purchase ID and reference to the session (`chip_last_purchase_id`, `chip_last_reference`).

Return shape:

```php
[
    'success' => true,
    'purchase_id' => 'chip_purchase_uuid',
    'checkout_url' => 'https://gate.chip-in.asia/...',
    'reused_intent' => false, // only set when reusing
]
```

### 5.2 `PaymentService`

Key responsibilities:

- `createPaymentIntent(Cart $cart, array $customerData)` –
  - Calls `PaymentGatewayInterface::createPurchase()` (implemented by `ChipPaymentGateway`).
  - Calculates `cart_version = current_version + 1` to anticipate metadata writes.
  - Stores the intent under `metadata['payment_intent']` with the following structure:

    ```json
    {
      "purchase_id": "...",
      "amount": 12900,
      "cart_version": 7,
      "cart_snapshot": {
        "items": [...],
        "conditions": [...],
        "totals": {"subtotal": 11200, "total": 12900, "savings": 0}
      },
      "customer_data": {
        "reference": "cart-uuid",
        "email": "customer@example.com",
        "name": "Customer Name",
        "street1": "...",
        "payment_method_whitelist": ["fpx_b2c"]
      },
      "created_at": "2025-09-01T12:34:56Z",
      "status": "created",
      "checkout_url": "https://...",
      "reference": "cart-uuid"
    }
    ```

- `validateCartPaymentIntent(Cart $cart)` – ensures the cart version hasn’t changed and the status is still `created`.
- `validatePaymentWebhook(array $intent, array $webhookData)` – cross-checks the purchase ID and amount before allowing order creation.
- `clearPaymentIntent()` / `updatePaymentIntentStatus()` – maintenance helpers.

### 5.3 `ChipPaymentGateway`

- Translates cart items into CHIP product DTOs (`MasyukAI\Chip\DataObjects\Product`).
- Builds redirect URLs using the cart reference: success/failure/cancel routes include `{reference}`.
- Constructs the success callback URL with `config('app.public_url')` fallback to named route `webhooks.chip`.
- Calls `ChipCollectService::createCheckoutPurchase()` and returns the purchase ID + hosted checkout URL.
- `getAvailablePaymentMethods()` fetches CHIP catalog once and maps them into UI-friendly options (id, name, description, icon, group).
- `getPurchaseStatus()` wraps CHIP’s purchase retrieval for success-page reconciliation.

Configuration lives in `config/chip.php` (API keys, brand ID, public key, etc.), while the underlying HTTP client is provided by the `masyukai/chip` package.

---

## 6. Webhook ingestion & recording

### 6.1 `ChipController::handle()`

1. Determines the request type (`success_callback` vs `webhook`) based on the optional `{webhook}` route segment.
2. Fetches CHIP’s RSA public key via `WebhookService::getPublicKey()` and verifies the `X-Signature` header.
3. Instantiates a strongly typed webhook object with `App\Support\ChipWebhookFactory::fromRequest()`.
4. Persists the raw payload via `ChipDataRecorder::recordWebhook()` (if the optional `chip_webhooks` table exists).
5. Delegates to `WebhookProcessor::handle()` and records success/failure for observability; on failure, sends `WebhookProcessingFailed` notification.

### 6.2 `WebhookProcessor`

- `handle(Webhook $webhook)` routes events using a `match` expression:
  - `purchase.paid` → `handlePurchasePaid()`
  - `purchase.payment_failure` → `handlePaymentFailure()`
  - else → informational log only.

`handlePurchasePaid()` merges request data with the webhook metadata (`webhook_id`, `source`, sanitized amount/reference) before calling `CheckoutService::handlePaymentSuccess()`.

`handlePaymentFailure()` updates the `payments` row (if it exists) to `failed`, cascades a status change on the order, and notifies via `OrderCreationFailed` email. It’s the hook you’ll extend when adding customer communications for failures.

### 6.3 Data recorder (`ChipDataRecorder`)

- Writes webhook payloads to `{prefix}webhooks` and purchase snapshots to `{prefix}purchases` if those tables are present.
- Maintains processing metadata (`processed`, `processed_at`, retry counts).
- Gives you an audit trail for debugging missing orders or replaying payloads.

---

## 7. Order & payment finalization

### 7.1 `CheckoutService::handlePaymentSuccess()`

End-to-end responsibilities:

1. **Idempotency guard** – looks for any `Payment` rows where `gateway_payment_id` matches either the session’s purchase ID or the webhook’s ID; if found, returns the existing order immediately.
2. **Cart lookup** – tries several strategies in order:
   - `reference` field provided in the webhook payload.
   - Scan the `carts` table for metadata containing the purchase ID (only when reference absent).
   - Fallback to the customer’s session cart if it still carries the same purchase ID.
   - CHIP API (`PaymentService::getPurchaseStatus()`) to fetch the reference.
3. **Intent validation** – pulls `metadata['payment_intent']` and asks `PaymentService::validatePaymentWebhook()` to ensure purchase ID & total match and the status is still `created`.
4. **Transactional order creation** (`DB::transaction()`):
   - Calls `createOrderFromCartSnapshot()` → `OrderService::createOrder()` to build the `orders` row using the snapshot stored in metadata (so changes made after checkout don’t alter totals).
   - Persists order items via `OrderService::createOrderItems()` using the snapshot (resolves product IDs to maintain referential integrity).
   - Creates a `Payment` row with `status = 'completed'`, `gateway_payment_id`, and `gateway_response` (stores full payload for compliance).
   - Updates the order status to `completed` via `OrderService::updateOrderStatus()`.
5. **Cart cleanup** – upon successful transaction commit, clears the cart (`$cart->clear()`) and logs the outcome.

### 7.2 Order-related models & tables

- `App\Models\Order`
  - Fillable columns include `order_number`, `cart_items`, `checkout_form_data`, `status`, `total`.
  - Casts `cart_items` and `checkout_form_data` to arrays.
  - Relationships: `address()`, `orderItems()`, `payments()`, `shipments()`, `statusHistories()`.
- `App\Models\Payment`
  - Tracks gateway metadata (`gateway_payment_id`, `gateway_response` JSONB).
  - Includes helpful scopes (`completed`, `pending`, etc.) and formatted accessor.
- Migrations: `2025_08_24_231432_create_orders_table.php`, `2025_08_24_231503_create_order_items_table.php`, `2025_08_24_234140_create_payments_table.php`.

### 7.3 `OrderService`

- `createOrder()` – orchestrates user creation/lookup, address creation, and delegates to `createOrderItems()`.
- `createOrderItems()` – resolves product IDs in bulk to prevent N+1 queries and stores unit prices at time of purchase.
- `resolveTotals()` – prefers the cart snapshot totals, falling back to live cart calculations when snapshot missing.

---

## 8. Redirect pages & post-payment UX

### 8.1 `CheckoutController::success()`

- Calls `CheckoutService::prepareSuccessView(reference)` which:
  - Fetches the cart row (if it still exists) to read the intent snapshot.
  - Loads related payment/order if already created by the webhook.
  - When the webhook hasn’t arrived yet, queries CHIP (`PaymentService::getPurchaseStatus()`) and triggers `handlePaymentSuccess()` manually if the purchase is paid.
  - Returns a payload containing `order`, `payment`, `cartSnapshot`, `customerSnapshot`, and boolean flags `isCompleted` / `isPending` for the Blade view.

### 8.2 `CheckoutController::failure()` & `cancel()`

- Both look up the cart metadata to find the purchase ID and linked payment.
- If a pending payment record exists it is marked as `failed` (failure) or `cancelled` (cancel) and annotated with a note for support.
- Views reside in `resources/views/checkout/failure.blade.php` and `.../cancel.blade.php` and reuse Tailwind v4 components.

---

## 9. Tests & local tooling

- `tests/Feature/CheckoutServiceTest.php` covers:
  - Snapshot correctness when cart contents change after the intent is created.
  - Full flow for `prepareSuccessView()` including order & payment hydration.
- The tests use mocked `PaymentGatewayInterface` responses, ensuring the service layer remains decoupled from CHIP during CI.
- Dev sandbox routes (`routes/dev.php`) provide webhook simulation endpoints for manual testing.

When you extend the flow (e.g., add invoice creation) mirror these tests or add new Pest specs under `tests/Feature/Checkout` to guarantee regressions are caught.

---

## 10. Observability & logging

Key log statements (all `Log::debug/info/error`):

- `CheckoutService::handlePaymentSuccess()` – logs invocation parameters, cart lookup path, validation results, and order creation success/failure.
- `CheckoutService::prepareSuccessView()` – emits step-by-step breadcrumbs for the success page reconciliation.
- `ChipController` & `WebhookProcessor` – log receipt, signature status, and outcome for each webhook delivery.

These logs live in `storage/logs/laravel.log` by default and are invaluable when diagnosing missing orders or duplicates.

---

## 11. Suggested improvement ideas

1. **Publish domain events after order creation** – dispatch a dedicated event (e.g., `OrderPaid`) inside `handlePaymentSuccess()` once the transaction commits. Downstream features (emails, invoices, shipping) can subscribe via queued listeners without touching the webhook pipeline.
2. **Queue webhook processing** – wrap `WebhookProcessor::handle()` in a queued job to isolate long-running logic and retry transient failures without blocking the CHIP callback response.
3. **Expose payment intent diagnostics** – surface the status from `validateCartPaymentIntent()` on the checkout UI (e.g., “Cart changed since payment intent, please refresh”) to reduce abandoned checkouts and support tickets.
4. **Cache payment method catalog** – CHIP payment methods seldom change; caching `ChipPaymentGateway::getAvailablePaymentMethods()` in Redis would shave off one API request per checkout load (currently not applicable as payment methods are not loaded in checkout UI).

These upgrades keep the current flow intact while making room for the additional automation you have planned.

---

## 12. Quick reference index

| Concern | Primary Classes | Location |
| --- | --- | --- |
| Cart UI | `App\Livewire\Cart`, `resources/views/livewire/cart.blade.php` | `app/Livewire`, `resources/views` |
| Checkout UI | `App\Livewire\Checkout`, `resources/views/livewire/checkout.blade.php` | `app/Livewire`, `resources/views` |
| Service layer | `CheckoutService`, `PaymentService`, `OrderService` | `app/Services` |
| Payment gateway | `ChipPaymentGateway`, `App\Contracts\PaymentGatewayInterface` | `app/Services`, `app/Contracts` |
| Webhooks | `ChipController`, `WebhookProcessor`, `ChipDataRecorder`, `ChipWebhookFactory` | `app/Http/Controllers`, `app/Services/Chip`, `app/Support` |
| Persistence | `Order`, `Payment`, `OrderItem`, `Address` models | `app/Models` |
| Tests | `tests/Feature/CheckoutServiceTest.php` | `tests/Feature` |

Use this map to trace dependencies quickly when extending the flow with notifications, digital fulfillment, or other post-payment automation.
