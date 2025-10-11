<?php

declare(strict_types=1);

namespace AIArmada\FilamentCart\Services;

use AIArmada\Cart\Cart as BaseCart;
use AIArmada\Cart\Conditions\CartCondition as BaseCartCondition;
use AIArmada\Cart\Models\CartItem as BaseCartItem;
use AIArmada\FilamentCart\Models\Cart;
use AIArmada\FilamentCart\Models\CartCondition;
use AIArmada\FilamentCart\Models\CartItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use function assert;

final class NormalizedCartSynchronizer
{
    public function syncFromCart(BaseCart $cart): void
    {
        DB::transaction(function () use ($cart): void {
            $identifier = $cart->getIdentifier();
            $instance = $cart->instance();

            $items = $cart->getItems();
            $conditions = $cart->getConditions();

            if ($items->isEmpty() && $conditions->isEmpty()) {
                $this->clearCart($identifier, $instance);

                return;
            }

            $currency = $this->resolveCurrency();

            $cartModel = Cart::query()
                ->firstOrNew([
                    'identifier' => $identifier,
                    'instance' => $instance,
                ]);

            /** @phpstan-ignore assign.propertyReadOnly */
            $cartModel->items = $items->toArray();
            /** @phpstan-ignore assign.propertyReadOnly */
            $cartModel->conditions = $conditions->isEmpty() ? null : $conditions->toArray();
            /** @phpstan-ignore assign.propertyReadOnly */
            $cartModel->items_count = $cart->countItems();
            /** @phpstan-ignore assign.propertyReadOnly */
            $cartModel->quantity = $cart->getTotalQuantity();
            /** @phpstan-ignore assign.propertyReadOnly */
            $cartModel->subtotal = (int) $cart->subtotalWithoutConditions()->getAmount();
            /** @phpstan-ignore assign.propertyReadOnly */
            $cartModel->total = (int) $cart->total()->getAmount();
            /** @phpstan-ignore assign.propertyReadOnly */
            $cartModel->savings = (int) $cart->savings()->getAmount();
            /** @phpstan-ignore assign.propertyReadOnly */
            $cartModel->currency = $currency;
            $cartModel->save();

            $itemModels = $this->syncItems($cartModel, $items);
            $this->syncConditions($cartModel, $conditions, $itemModels, $items->all());
        });
    }

    public function clearCart(string $identifier, string $instance): void
    {
        $cartModel = Cart::query()
            ->where('identifier', $identifier)
            ->where('instance', $instance)
            ->first();

        if (! $cartModel) {
            return;
        }

        CartCondition::query()->where('cart_id', $cartModel->id)->delete();
        CartItem::query()->where('cart_id', $cartModel->id)->delete();
        $cartModel->delete();
    }

    /**
     * @return array<string, CartItem>
     */
    /** @param \Illuminate\Support\Collection<int, BaseCartItem> $items */
    /** @phpstan-ignore missingType.iterableValue, missingType.generics */
    private function syncItems(Cart $cartModel, \Illuminate\Support\Collection $items): array
    {
        $persisted = [];
        $storedItemIds = [];

        foreach ($items as $item) {
            assert($item instanceof BaseCartItem);

            $attributes = $item->attributes->toArray();
            $conditions = $item->conditions->isEmpty() ? null : $item->conditions->toArray();

            $cartItemModel = CartItem::query()->updateOrCreate(
                [
                    'cart_id' => $cartModel->id,
                    'item_id' => $item->id,
                ],
                [
                    'name' => $item->name,
                    'price' => (int) $item->getRawPriceWithoutConditions(),
                    'quantity' => $item->quantity,
                    'attributes' => empty($attributes) ? null : $attributes,
                    'conditions' => $conditions,
                    'associated_model' => $this->resolveAssociatedModel($item->associatedModel),
                ]
            );

            $persisted[$item->id] = $cartItemModel;
            $storedItemIds[] = $item->id;
        }

        if ($storedItemIds !== []) {
            CartItem::query()
                ->where('cart_id', $cartModel->id)
                ->whereNotIn('item_id', $storedItemIds)
                ->delete();
        } else {
            CartItem::query()->where('cart_id', $cartModel->id)->delete();
        }

        return $persisted;
    }

    /** @phpstan-ignore-next-line */
    private function syncConditions(Cart $cartModel, \Illuminate\Support\Collection $conditions, array $itemModels, array $originalItems): void
    {
        $persistedKeys = [];

        foreach ($conditions as $condition) {
            assert($condition instanceof BaseCartCondition);
            $persistedKeys[] = $this->persistCondition(
                cartModel: $cartModel,
                condition: $condition,
                cartItemModel: null,
                itemId: null
            );
        }

        foreach ($originalItems as $item) {
            assert($item instanceof BaseCartItem);

            if (! isset($itemModels[$item->id])) {
                continue;
            }

            foreach ($item->conditions as $condition) {
                assert($condition instanceof BaseCartCondition);

                $persistedKeys[] = $this->persistCondition(
                    cartModel: $cartModel,
                    condition: $condition,
                    cartItemModel: $itemModels[$item->id],
                    itemId: $item->id
                );
            }
        }

        $existing = CartCondition::query()
            ->where('cart_id', $cartModel->id)
            ->get(['id', 'name', 'item_id', 'cart_item_id']);

        foreach ($existing as $existingCondition) {
            /** @phpstan-ignore property.notFound */
            $key = $existingCondition->cart_item_id === null
                ? $this->conditionKey($existingCondition->name)
                : $this->conditionKey($existingCondition->name, $existingCondition->item_id);

            if (! in_array($key, $persistedKeys, true)) {
                $existingCondition->delete();
            }
        }
    }

    private function persistCondition(
        Cart $cartModel,
        BaseCartCondition $condition,
        ?CartItem $cartItemModel,
        ?string $itemId
    ): string {
        $data = $condition->toArray();

        CartCondition::query()->updateOrCreate(
            [
                'cart_id' => $cartModel->id,
                'cart_item_id' => $cartItemModel?->id,
                'name' => $condition->getName(),
                'item_id' => $itemId,
            ],
            [
                'type' => $data['type'],
                'target' => $data['target'],
                'value' => (string) $data['value'],
                'order' => $data['order'],
                'attributes' => Arr::get($data, 'attributes') ?: null,
                'operator' => $data['operator'] ?? null,
                'is_charge' => (bool) ($data['is_charge'] ?? false),
                'is_dynamic' => (bool) ($data['is_dynamic'] ?? false),
                'is_discount' => (bool) ($data['is_discount'] ?? false),
                'is_percentage' => (bool) ($data['is_percentage'] ?? false),
                'is_global' => (bool) ($data['attributes']['is_global'] ?? false),
                'parsed_value' => isset($data['parsed_value']) ? (string) $data['parsed_value'] : null,
                'rules' => $data['rules'] ?? null,
            ]
        );

        return $this->conditionKey($condition->getName(), $itemId);
    }

    private function conditionKey(string $name, ?string $itemId = null): string
    {
        return $itemId === null ? "cart:{$name}" : "item:{$itemId}:{$name}";
    }

    private function resolveCurrency(): string
    {
        return mb_strtoupper(config('cart.money.default_currency', 'USD'));
    }

    private function resolveAssociatedModel(string|object|null $associatedModel): ?string
    {
        if (is_string($associatedModel)) {
            return $associatedModel;
        }

        return $associatedModel ? get_class($associatedModel) : null;
    }
}
