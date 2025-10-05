# Laravel Octane Support

Long-lived Octane workers require careful handling of shared state. MasyukAI Cart ships with defaults that make it safe out of the box.

## Recommended Driver

Prefer the **cache** or **database** driver. Session storage can work but requires sticky sessions to keep carts consistent across workers.

## Configuration Flags

```php
// config/cart.php
'octane' => [
    'auto_register_listeners' => true,
    'prefer_cache_storage' => true,
    'queue_events' => true,
    'reset_static_state' => true,
],
```


- **auto_register_listeners** – registers Octane-specific listeners so state is reset between requests.
- **prefer_cache_storage** – hints that cache is usually the safest driver under Octane; adjust manually if you pick another driver.
- **queue_events** – pushes cart events onto the queue instead of processing them inline, preventing long-running event handlers inside the request lifecycle.
- **reset_static_state** – clears per-request singletons that would otherwise leak between requests.

## Boot Checklist

1. Confirm your chosen storage driver is stateless (cache or database).
2. Ensure queues are configured if `queue_events` is `true`.
3. Warm up Octane after configuration changes to avoid stale config.
4. Monitor conflicts via logging; enable `lock_for_update` in database storage config if needed.

## Testing with Octane

Use Pest's Octane plugin or Laravel's `octane:test` runner to validate carts under worker mode. Focus on:

- Adding/removing items concurrently.
- Cart migration after login.
- Event handlers executing off the main thread.

```

Adjust config flags per environment if staging differs from production.
