# Filament Cart - Queue Configuration

## Queue Synchronization

The Filament Cart package supports both synchronous and asynchronous (queued) synchronization of cart data to normalized database tables.

### Configuration

Queue synchronization is controlled by the `queue_sync` setting in `config/filament-cart.php`:

```php
'synchronization' => [
    'queue_sync' => false, // Set to true to enable queue processing
    'queue_connection' => 'default',
    'queue_name' => 'cart-sync',
],
```

### Synchronous Mode (Default)

By default, `queue_sync` is set to `false`, which means cart synchronization happens immediately when cart events are fired. This ensures:

- ✅ **No queue configuration required**
- ✅ **Immediate consistency**
- ✅ **Simplified setup**
- ⚠️ Slower response times for complex cart operations

### Asynchronous Mode (Queued)

When `queue_sync` is enabled, cart synchronization is processed in the background via Laravel queues. This provides:

- ✅ **Better performance** for high-traffic applications
- ✅ **Non-blocking cart operations**
- ⚠️ **Requires queue configuration** in your Laravel app
- ⚠️ **Eventual consistency** (small delay in Filament resources)

### Enabling Queue Synchronization

1. **Configure Laravel Queues** first in your main application:
   ```bash
   # For database queues
   php artisan queue:table
   php artisan migrate
   
   # Or configure Redis, SQS, etc. in config/queue.php
   ```

2. **Update the config**:
   ```php
   'queue_sync' => true,
   ```

3. **Run queue workers**:
   ```bash
   php artisan queue:work --queue=cart-sync
   ```

### Troubleshooting

#### Serialization Error
If you encounter `Serialization of 'Pdo\Pgsql' is not allowed`, it means:
- Queue sync is enabled but queues aren't properly configured
- **Solution**: Either configure Laravel queues properly or disable queue sync

#### Performance Issues
If cart operations are slow:
- Enable queue synchronization for better performance
- Ensure queue workers are running
- Monitor queue performance with Laravel Horizon (if using Redis)

### Testing

Both synchronous and asynchronous modes are fully tested. The test suite automatically uses synchronous mode for consistent test results.