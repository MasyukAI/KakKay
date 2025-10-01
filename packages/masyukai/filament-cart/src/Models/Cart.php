<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Models;

use Akaunting\Money\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Cart as CartPackage;

class Cart extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \MasyukAI\FilamentCart\Database\Factories\CartFactory
    {
        return \MasyukAI\FilamentCart\Database\Factories\CartFactory::new();
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'carts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'identifier',
        'instance',
        'items',
        'conditions',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'items' => 'array',
        'conditions' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The model's default attribute values.
     */
    // protected $attributes = [
    //     'identifier' => null,
    //     'instance' => 'default',
    //     'items' => null,
    //     'conditions' => null,
    //     'metadata' => null,
    // ];

    /**
     * Get the Cart package instance for this cart.
     * This allows us to use the cart package's calculations which properly handle conditions.
     */
    public function getCartInstance(): ?CartPackage
    {
        try {
            // Get the configured storage driver from the service container
            $storageDriver = config('cart.storage', 'session');
            $storage = app("cart.storage.{$storageDriver}");

            // Create cart instance with the same identifier and instance name
            $cart = new CartPackage(
                storage: $storage,
                identifier: $this->identifier,
                events: app(\Illuminate\Contracts\Events\Dispatcher::class),
                instanceName: $this->instance,
                eventsEnabled: false // Don't trigger events when just reading
            );

            return $cart;
        } catch (\Exception $e) {
            Log::warning('Failed to get cart instance', [
                'cart_id' => $this->id,
                'identifier' => $this->identifier,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get the items count for the cart.
     */
    protected function itemsCount(): Attribute
    {
        return Attribute::make(
            get: function () {
                $cart = $this->getCartInstance();

                return $cart ? $cart->count() : 0;
            },
        );
    }

    /**
     * Get the total quantity of items in the cart.
     */
    protected function totalQuantity(): Attribute
    {
        return Attribute::make(
            get: function () {
                $cart = $this->getCartInstance();

                return $cart ? $cart->getTotalQuantity() : 0;
            },
        );
    }

    /**
     * Get the subtotal of the cart (without conditions applied).
     */
    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: function () {
                $cart = $this->getCartInstance();

                if (! $cart) {
                    return 0;
                }

                // Get subtotal without conditions applied, convert to cents
                return $cart->subtotalWithoutConditions()->getAmount();
            },
        );
    }

    /**
     * Get the total of the cart (with conditions applied).
     */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: function () {
                $cart = $this->getCartInstance();

                if (! $cart) {
                    return 0;
                }

                // Get total with conditions applied, convert to cents
                return (int) ($cart->total()->getAmount());
            },
        );
    }

    /**
     * Get the savings from conditions (subtotal - total).
     */
    protected function savings(): Attribute
    {
        return Attribute::make(
            get: function () {
                $cart = $this->getCartInstance();

                if (! $cart) {
                    return 0;
                }

                // Get savings, convert to cents
                return (int) ($cart->savings()->getAmount());
            },
        );
    }

    /**
     * Check if cart is empty.
     */
    public function isEmpty(): bool
    {
        return $this->items_count === 0;
    }

    /**
     * Get subtotal in dollars (converted from cents).
     */
    public function getSubtotalInDollarsAttribute(): float
    {
        return $this->subtotal / 100;
    }

    /**
     * Get total in dollars (converted from cents).
     */
    public function getTotalInDollarsAttribute(): float
    {
        return $this->total / 100;
    }

    /**
     * Get savings in dollars (converted from cents).
     */
    public function getSavingsInDollarsAttribute(): float
    {
        return $this->savings / 100;
    }

    /**
     * Get formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return (string) Money::MYR($this->total);
    }

    /**
     * Get formatted savings.
     */
    public function getFormattedSavingsAttribute(): string
    {
        return (string) Money::MYR($this->savings);
    }

    /**
     * Scope to filter by instance.
     */
    public function scopeInstance($query, string $instance): void
    {
        $query->where('instance', $instance);
    }

    /**
     * Scope to filter by identifier.
     */
    public function scopeByIdentifier($query, string $identifier): void
    {
        $query->where('identifier', $identifier);
    }

    /**
     * Scope to get non-empty carts.
     */
    public function scopeNotEmpty($query): void
    {
        $connection = $query->getConnection();
        $driverName = $connection->getDriverName();

        $query->whereNotNull('items');

        if ($driverName === 'pgsql') {
            // PostgreSQL requires explicit casting
            $query->whereRaw("items::text != '[]'")
                ->whereRaw("items::text != '{}'");
        } else {
            // SQLite and MySQL can handle direct comparison
            $query->where('items', '!=', '[]')
                ->where('items', '!=', '{}');
        }
    }

    /**
     * Scope to get recent carts.
     */
    public function scopeRecent($query, int $days = 7): void
    {
        $query->where('updated_at', '>=', now()->subDays($days));
    }

    /**
     * Get the normalized cart items.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the normalized cart conditions.
     */
    public function cartConditions(): HasMany
    {
        return $this->hasMany(CartCondition::class);
    }

    /**
     * Get cart-level conditions only.
     */
    public function cartLevelConditions(): HasMany
    {
        return $this->hasMany(CartCondition::class)->cartLevel();
    }

    /**
     * Get item-level conditions only.
     */
    public function itemLevelConditions(): HasMany
    {
        return $this->hasMany(CartCondition::class)->itemLevel();
    }

    /**
     * Get the user associated with the cart (if identifier is a UUID).
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'identifier', 'id');
    }
}
