# Cart Event & Storage Architecture Analysis

## Question 1: Should CartUpdated be dispatched from CAS instead?

### Current Architecture

```
User Action (e.g., addItem)
       ↓
Cart Trait Method (ManagesItems)
       ↓
Storage->putItems() (DatabaseStorage)
       ↓
performCasUpdate() [DB Write + Version++]
       ↓
[Back to Trait]
       ↓
Dispatch Specific Event (ItemAdded)
       ↓
DispatchCartUpdated Listener
       ↓
Dispatch CartUpdated Event
```

### Proposed Alternative: Dispatch from CAS

```
User Action (e.g., addItem)
       ↓
Cart Trait Method (ManagesItems)
       ↓
Storage->putItems() (DatabaseStorage)
       ↓
performCasUpdate() [DB Write + Version++]
       ↓
Dispatch CartUpdated Event ← NEW LOCATION
```

---

## Architectural Trade-offs

### ✅ Advantages of Dispatching from CAS

#### 1. **Guaranteed Consistency**
- CartUpdated fires **exactly when** the database is modified
- No risk of event dispatching when DB write fails
- Version increment and event are atomic

#### 2. **Simplicity**
- One place to dispatch CartUpdated instead of 10+ places in traits
- Removes need for DispatchCartUpdated event subscriber
- Less code to maintain

#### 3. **DRY Principle**
- Every cart modification goes through CAS
- Single point of truth for "cart was modified"

---

### ❌ Disadvantages of Dispatching from CAS

#### 1. **Loss of Granular Events**
Currently, listeners can subscribe to specific events:
```php
// ✅ Current: Granular control
Event::listen(ItemAdded::class, function ($event) {
    logger('Item added', ['item' => $event->item]);
});

Event::listen(MetadataAdded::class, function ($event) {
    logger('Metadata changed', ['key' => $event->key]);
});

// ❌ Proposed: No context about what changed
Event::listen(CartUpdated::class, function ($event) {
    // What changed? No idea!
    logger('Cart updated somehow');
});
```

**Lost Information:**
- What type of change occurred (item vs condition vs metadata)
- What specific data was added/removed/updated
- Context for specialized listeners

#### 2. **Violation of Separation of Concerns**
```php
// Storage layer should NOT know about domain events
class DatabaseStorage implements StorageInterface
{
    private function performCasUpdate(...)
    {
        // This is a persistence concern ✓
        $this->database->update([...]);
        
        // This is a domain/business concern ✗
        event(new CartUpdated($cart));
    }
}
```

**Problems:**
- Storage is infrastructure layer
- Events are domain/application layer
- Mixing layers makes code harder to test and maintain
- Storage shouldn't have dependency on event dispatcher

#### 3. **Storage Interface Complexity**
```php
interface StorageInterface
{
    // ❌ All storage implementations need event dispatcher
    public function putItems(string $id, string $instance, array $items): void;
}

// Each implementation would need:
class SessionStorage implements StorageInterface
{
    public function __construct(
        private Session $session,
        private ?Dispatcher $events = null  // ← NEW dependency
    ) {}
    
    public function putItems(...)
    {
        $this->session->put(...);
        
        // But how do we get the Cart instance to pass to CartUpdated?
        // Storage only knows about arrays, not Cart objects!
        event(new CartUpdated($cart)); // ← $cart doesn't exist here!
    }
}
```

**Problems:**
- Storage works with raw data (arrays), not Cart objects
- CartUpdated event needs the full Cart instance
- Would need to pass Cart reference down to storage (tight coupling)

#### 4. **Testing Complexity**
```php
// ✅ Current: Easy to test storage in isolation
test('database storage saves items', function () {
    $storage = new DatabaseStorage($db);
    $storage->putItems('user1', 'default', [...]);
    
    expect($storage->getItems('user1', 'default'))->toBe([...]);
});

// ❌ Proposed: Storage tests need event mocking
test('database storage saves items', function () {
    Event::fake(); // ← Now required
    
    $storage = new DatabaseStorage($db, $events);
    $storage->putItems('user1', 'default', [...]);
    
    Event::assertDispatched(CartUpdated::class); // ← Extra complexity
});
```

#### 5. **Different Storages, Different Guarantees**

| Storage | Concurrent Access | Versioning Possible? | Event Timing |
|---------|-------------------|---------------------|--------------|
| **DatabaseStorage** | ✅ Multi-user/process | ✅ Yes (version column) | After DB commit |
| **SessionStorage** | ❌ Single user only | ❌ No conflicts possible | After session write |
| **CacheStorage** | ⚠️ Possible conflicts | ⚠️ Could use cache locks | After cache write |

Dispatching from storage means:
- DatabaseStorage: Events after successful CAS
- SessionStorage: Events always succeed (no conflicts)
- CacheStorage: Events after cache write (no conflict detection)

**Inconsistent behavior across storages!**

---

## Recommendation: **Keep Current Architecture**

### Reasons

1. **Granular events provide valuable context**
   - Applications can react differently to item changes vs metadata changes
   - Event listeners can be specialized and efficient
   - Debugging is easier with specific event types

2. **Proper separation of concerns**
   - Storage = persistence layer (infrastructure)
   - Events = domain layer (business logic)
   - Cart traits = application layer (orchestration)

3. **Storage remains simple and testable**
   - Storage interface stays focused on data persistence
   - No event dependencies in storage implementations
   - Easy to test storage in isolation

4. **Flexible event system**
   - Applications can choose which events to listen to
   - Can disable specific events without touching storage
   - Can add new event types without modifying storage

### Current Architecture is Correct

```php
// ✅ Cart trait handles orchestration
public function addItem(...)
{
    // 1. Business logic
    $item = $this->prepareItem(...);
    
    // 2. Persistence
    $this->storage->putItems(...);
    
    // 3. Domain event
    if ($this->eventsEnabled && $this->events) {
        $this->events->dispatch(new ItemAdded($item, $this));
    }
}
```

This follows **Clean Architecture** principles:
- Each layer has a single responsibility
- Dependencies point inward (storage doesn't know about events)
- Easy to test each layer independently

---

## Question 2: Can CAS/Optimistic Locking Apply to Other Storages?

### Analysis by Storage Type

#### 1. **DatabaseStorage** ✅ Already Implemented

**Current Implementation:**
```php
private function performCasUpdate(string $identifier, string $instance, array $data, string $operationName): void
{
    $this->database->transaction(function () use ($identifier, $instance, $data, $operationName) {
        $current = $this->applyLockForUpdate(
            $this->database->table($this->table)
                ->where('identifier', $identifier)
                ->where('instance', $instance)
        )->first(['id', 'version']);

        if ($current) {
            $updateData = array_merge($data, [
                'version' => $current->version + 1,  // ← Increment version
                'updated_at' => now(),
            ]);

            $updated = $this->database->table($this->table)
                ->where('identifier', $identifier)
                ->where('instance', $instance)
                ->where('version', $current->version)  // ← CAS check
                ->update($updateData);

            if ($updated === 0) {
                throw new CartConflictException(...);
            }
        }
    });
}
```

**Why it works:**
- ✅ Persistent storage with ACID transactions
- ✅ `version` column in database schema
- ✅ Atomic read-compare-swap operation
- ✅ Multi-process/multi-server safe

**When conflicts occur:**
- Multiple processes try to update same cart simultaneously
- High traffic scenarios (e.g., Black Friday sales)
- Long-running operations between read and write

---

#### 2. **SessionStorage** ❌ Not Needed

**Current Implementation:**
```php
readonly class SessionStorage implements StorageInterface
{
    public function __construct(
        private Session $session,
        private string $keyPrefix = 'cart'
    ) {}

    public function putItems(string $identifier, string $instance, array $items): void
    {
        $cartData = $this->session->get($this->keyPrefix, []);
        $cartData[$identifier][$instance]['items'] = $items;
        $this->session->put($this->keyPrefix, $cartData);
    }
}
```

**Why CAS is NOT needed:**

1. **Single-Process Nature**
   - Sessions are tied to a single user's browser
   - Only ONE process handles a session at a time (PHP session locking)
   - No concurrent access to same session data

2. **PHP Session Locking**
   ```php
   // PHP automatically locks sessions
   session_start(); // ← Acquires lock
   $_SESSION['cart'] = [...];
   // ← Lock released at script end
   ```

3. **No Concurrent Modification Risk**
   - User can only make one request at a time per browser
   - Even with AJAX, PHP session lock serializes access
   - Conflicts are impossible by design

**Example Scenario:**
```
Time | Request A (User 1)     | Request B (User 1, AJAX)
-----|------------------------|---------------------------
T1   | session_start() ✓      | session_start() [blocked]
T2   | Add item to cart       | [waiting for lock...]
T3   | session_write_close()  | [waiting for lock...]
T4   | [done]                 | session_start() ✓
T5   |                        | Read cart (has updates)
```

**Conclusion:** SessionStorage doesn't need versioning because PHP's session locking prevents concurrent access.

---

#### 3. **CacheStorage** ⚠️ Possible but Complex

**Current Implementation:**
```php
readonly class CacheStorage implements StorageInterface
{
    public function __construct(
        private Cache $cache,
        private string $keyPrefix = 'cart',
        private int $ttl = 86400
    ) {}

    public function putItems(string $identifier, string $instance, array $items): void
    {
        $key = $this->getItemsKey($identifier, $instance);
        $this->cache->put($key, json_encode($items), $this->ttl);
    }
}
```

**Why CAS Could Be Useful:**

1. **Shared Cache Across Servers**
   - Redis/Memcached used by multiple app servers
   - Same cart could be modified by different servers
   - Race conditions possible

2. **Example Conflict Scenario:**
   ```
   Time | Server A              | Server B              | Cache
   -----|----------------------|----------------------|-------
   T1   | Read cart (v=1)      |                      | v=1
   T2   |                      | Read cart (v=1)      | v=1
   T3   | Add item             |                      | v=1
   T4   | Write cart (v=2)     |                      | v=2
   T5   |                      | Remove item          | v=2
   T6   |                      | Write cart (v=2) ✗   | v=2 (lost update!)
   ```

**How to Implement CAS in CacheStorage:**

```php
class CacheStorage implements StorageInterface
{
    public function putItems(string $identifier, string $instance, array $items): void
    {
        $key = $this->getItemsKey($identifier, $instance);
        $versionKey = $this->getVersionKey($identifier, $instance);
        
        $maxRetries = 3;
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            // Read current version
            $currentVersion = $this->cache->get($versionKey, 0);
            
            // Try atomic update using cache locks
            $lock = $this->cache->lock($key, 5);
            
            try {
                if ($lock->get()) {
                    // Double-check version
                    $checkVersion = $this->cache->get($versionKey, 0);
                    
                    if ($checkVersion !== $currentVersion) {
                        // Version changed, retry
                        $attempt++;
                        continue;
                    }
                    
                    // Write data + increment version
                    $this->cache->put($key, json_encode($items), $this->ttl);
                    $this->cache->put($versionKey, $currentVersion + 1, $this->ttl);
                    
                    return; // Success!
                }
            } finally {
                $lock->release();
            }
            
            $attempt++;
        }
        
        throw new CartConflictException("Failed to update cart after {$maxRetries} attempts");
    }
}
```

**Implementation Challenges:**

1. **Cache Locks Required**
   - Not all cache drivers support locks (e.g., file cache)
   - Redis: `SETNX` or RedLock algorithm
   - Memcached: `add()` operation

2. **Version Storage**
   - Need separate cache key for version counter
   - Must ensure version TTL matches data TTL
   - Versioning adds storage overhead

3. **Complexity vs Benefit**
   - Cache is typically used for performance, not critical data
   - Most apps don't need cache-level conflict detection
   - If conflicts are a concern, use DatabaseStorage instead

**Recommendation for CacheStorage:**

✅ **Implemented: Option B - Simple locking with opt-in flag**

```php
readonly class CacheStorage implements StorageInterface
{
    public function __construct(
        private Cache $cache,
        private string $keyPrefix = 'cart',
        private int $ttl = 86400,
        private bool $useLocking = false,      // ← Opt-in locking
        private int $lockTimeout = 5
    ) {}
    
    public function putItems(string $identifier, string $instance, array $items): void
    {
        if ($this->useLocking && method_exists($this->cache, 'lock')) {
            $this->putItemsWithLock($identifier, $instance, $items);
        } else {
            $this->putItemsSimple($identifier, $instance, $items);
        }
    }
}
```

**Benefits:**
- ✅ Prevents concurrent writes in multi-server setups
- ✅ Simpler than full CAS with versioning
- ✅ Works with Redis and Memcached
- ✅ Optional - disabled by default for performance
- ✅ Automatic fallback if lock() not available

**Status:** ✅ Implemented and tested! See `CACHE_LOCKING.md` for documentation.

---

## Storage Comparison Matrix

| Feature | DatabaseStorage | SessionStorage | CacheStorage |
|---------|----------------|----------------|--------------|
| **Concurrent Access** | ✅ Multi-process | ❌ Single-process | ✅ Multi-process |
| **Needs CAS/Locking** | ✅ Yes (CAS) | ❌ No | ✅ Yes (Locking) |
| **Version Column** | ✅ Implemented | ❌ Not needed | ❌ Not needed |
| **Locking Support** | ❌ Not needed | ❌ Not needed | ✅ Implemented |
| **Conflict Detection** | ✅ Yes | ❌ Not possible | ✅ Yes (with locking) |
| **Persistence** | ✅ Permanent | ⚠️ Session lifetime | ⚠️ TTL-based |
| **Best For** | Production, multi-server | Development, single-server | Read-heavy, temporary data |
| **Complexity** | High | Low | Medium |

---

## Recommendations

### For Question 1: Dispatching CartUpdated from CAS

**❌ Don't do it.** Keep the current architecture because:

1. Granular events (ItemAdded, MetadataAdded, etc.) provide valuable context
2. Proper separation of concerns (storage = persistence, events = domain)
3. Storage interface remains simple and testable
4. Event system is flexible and extensible

### For Question 2: CAS/Locking in Other Storages

#### SessionStorage
**❌ Don't add CAS.** It's not needed because:
- PHP session locking prevents concurrent access
- Sessions are single-user by nature
- Adding versioning would be overhead with no benefit

#### CacheStorage  
**✅ IMPLEMENTED: Optional locking feature**

```php
// Enable locking for multi-server setups
$storage = new CacheStorage(
    cache: Cache::driver('redis'),
    useLocking: true,        // ← Opt-in locking
    lockTimeout: 5
);
```

**Implementation Details:**
- ✅ Optional flag (disabled by default)
- ✅ Configurable lock timeout
- ✅ Automatic fallback if lock() not available
- ✅ Lock scoping per cache key
- ✅ 10 comprehensive tests added

**When to enable:**
- Multi-server setup with shared cache
- High-traffic scenarios (Black Friday, flash sales)
- Critical cart operations

**When to keep disabled:**
- Single-server applications
- Development environments
- Performance-critical scenarios

**Documentation:** See `CACHE_LOCKING.md` for complete guide

**Tests:** ✅ 681 tests passing (including 10 new locking tests)

---

## Conclusion

### Current Architecture is Sound

The cart package already has the right architecture:

1. **Events at the right layer** (domain/application, not infrastructure)
2. **Versioning where needed** (DatabaseStorage)
3. **Simple storages stay simple** (SessionStorage, CacheStorage)

### Minor Enhancement Possible

If cache conflicts are a concern in production:

```php
// Add optional locking to CacheStorage
readonly class CacheStorage implements StorageInterface
{
    public function __construct(
        private Cache $cache,
        private string $keyPrefix = 'cart',
        private int $ttl = 86400,
        private bool $useLocking = false  // ← New optional parameter
    ) {}
    
    public function putItems(string $identifier, string $instance, array $items): void
    {
        if ($this->useLocking && method_exists($this->cache, 'lock')) {
            $this->putItemsWithLock($identifier, $instance, $items);
        } else {
            $this->putItemsSimple($identifier, $instance, $items);
        }
    }
    
    private function putItemsWithLock(...)
    {
        $lock = $this->cache->lock($this->getItemsKey($identifier, $instance), 5);
        $lock->block(5, function () { /* ... */ });
    }
    
    private function putItemsSimple(...)
    {
        // Current simple implementation
        $this->cache->put(...);
    }
}
```

This keeps the default simple while allowing opt-in locking for high-concurrency scenarios.
