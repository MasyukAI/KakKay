<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for the Cart system.
 *
 * @method static \MasyukAI\Cart\CartManager setInstance(string $name)
 * @method static string instance()
 * @method static \MasyukAI\Cart\Storage\StorageInterface storage()
 * @method static \MasyukAI\Cart\Cart getCurrentCart()
 * @method static \MasyukAI\Cart\Storage\StorageInterface session(?string $sessionKey = null)
 * @method static self formatted()
 * @method static self raw()
 * @method static self currency(?string $currency = null)
 * @method static \MasyukAI\Cart\Models\CartItem|\MasyukAI\Cart\Collections\CartCollection add(string|array $id, ?string $name = null, float|string|null $price = null, int $quantity = 1, array $attributes = [], array|\MasyukAI\Cart\Conditions\CartCondition|null $conditions = null, string|object|null $associatedModel = null)
 * @method static \MasyukAI\Cart\Models\CartItem|null update(string $id, array $data)
 * @method static \MasyukAI\Cart\Models\CartItem|null remove(string $id)
 * @method static \MasyukAI\Cart\Models\CartItem|null get(string $id)
 * @method static \MasyukAI\Cart\Collections\CartCollection getItems()
 * @method static array getContent()
 * @method static bool isEmpty()
 * @method static int getTotalQuantity()
 * @method static \Akaunting\Money\Money subtotal()
 * @method static \Akaunting\Money\Money subtotalWithoutConditions()
 * @method static \Akaunting\Money\Money total()
 * @method static \Akaunting\Money\Money totalWithoutConditions()
 * @method static \Akaunting\Money\Money savings()
 * @method static float getRawSubtotal()
 * @method static float getRawTotal()
 * @method static float getRawSubtotalWithoutConditions()
 * @method static bool clear()
 * @method static \MasyukAI\Cart\Cart addCondition(\MasyukAI\Cart\Conditions\CartCondition|array $condition)
 * @method static \MasyukAI\Cart\Collections\CartConditionCollection getConditions()
 * @method static \MasyukAI\Cart\Conditions\CartCondition|null getCondition(string $name)
 * @method static bool removeCondition(string $name)
 * @method static bool clearConditions()
 * @method static \MasyukAI\Cart\Collections\CartConditionCollection getConditionsByType(string $type)
 * @method static bool removeConditionsByType(string $type)
 * @method static bool addItemCondition(string $itemId, \MasyukAI\Cart\Conditions\CartCondition $condition)
 * @method static bool removeItemCondition(string $itemId, string $conditionName)
 * @method static bool clearItemConditions(string $itemId)
 * @method static self addDiscount(string $name, string $value, string $target = 'subtotal')
 * @method static self addTax(string $name, string $value, string $target = 'subtotal')
 * @method static self addFee(string $name, string $value, string $target = 'total')
 * @method static self addShipping(string $name, string|float $value, string $method = 'standard', array $attributes = [])
 * @method static void removeShipping()
 * @method static \MasyukAI\Cart\Conditions\CartCondition|null getShipping()
 * @method static string|null getShippingMethod()
 * @method static float|null getShippingValue()
 * @method static int count()
 * @method static int countItems()
 * @method static array toArray()
 * @method static self setMetadata(string $key, mixed $value)
 * @method static mixed getMetadata(string $key, mixed $default = null)
 * @method static bool hasMetadata(string $key)
 * @method static self removeMetadata(string $key)
 * @method static self setMetadataBatch(array $metadata)
 * @method static bool swap(string $oldIdentifier, string $newIdentifier, string $instance = 'default')
 *
 * @see \MasyukAI\Cart\CartManager
 */
class Cart extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'cart';
    }

    /**
     * Handle dynamic, static calls to the facade.
     * This allows us to handle the setInstance method properly for chaining.
     */
    public static function __callStatic($method, $args): mixed
    {
        $instance = static::getFacadeRoot();

        // For setInstance, we return the CartManager for chaining
        // All other methods will be proxied to the current cart via __call
        return $instance->$method(...$args);
    }
}
