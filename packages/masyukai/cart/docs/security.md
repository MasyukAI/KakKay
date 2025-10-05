# Security Checklist

Cart data is user-controlled. Follow these guidelines to keep your application resilient.

## Validation & Limits

- Built-in validation is always active and rejects missing IDs/names, negative prices, zero quantities, and oversized attribute payloads.
- Adjust `cart.limits.max_items`, `max_item_quantity`, and `max_data_size_bytes` to reflect business rules. Exceeding limits raises `InvalidCartItemException`.

## Sensitive Data

- Avoid storing payment details or personal data in item attributes or metadata. The cart serialises payloads into JSON for storage; treat it as semi-public.
- Metrics sanitise arguments when logging, masking keys containing `password`, `token`, `secret`, `card`, etc. Extend this by scrubbing payloads before calling the cart when possible.

## Storage Driver Considerations

- **Session driver:** inherits your session cookie security (HTTPS, HTTP-only). Ensure session cookies are protected.
- **Cache driver:** configure authentication/ACLs on Redis or Memcached so unauthorised actors cannot read keys.
- **Database driver:** index `identifier` and `instance` for faster lookups and use database credentials with least privilege.

## Concurrency Safety

- Database writes leverage optimistic locking. Handle `CartConflictException` and surface friendly UI messages to encourage users to refresh carts.
- For long-running event handlers, consider queueing them to avoid blocking requests.

## Logging

- Configure a dedicated log channel (e.g., `CART_METRICS_LOG_CHANNEL=cart`) with rotation to prevent metric logs from filling disks.
- Ensure log storage complies with privacy requirements; cart identifiers may involve user IDs.

## Input Sanitisation

- Price strings are sanitised (`'1 234,50'` → `1234.5`), but prefer storing canonical numeric values to avoid locale ambiguities.
- Attributes should contain scalar or array data only. Avoid passing objects to prevent unexpected serialisation output.

## Testing & Monitoring

- Add tests covering invalid payloads (`negative price`, `zero quantity`) to assert exceptions are thrown.
- Monitor metrics for conflict spikes or abnormal operation counts as early indicators of abuse.

Keeping these guardrails in place ensures carts remain reliable and tamper-resistant.
