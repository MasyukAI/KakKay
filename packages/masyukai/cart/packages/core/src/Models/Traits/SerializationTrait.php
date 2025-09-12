<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Models\Traits;

trait SerializationTrait
{
    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->rawPrice,
            'quantity' => $this->quantity,
            'subtotal' => method_exists($this, 'getSubtotal') ? $this->getSubtotal() : ($this->rawPrice * $this->quantity),
            'attributes' => $this->attributes->toArray(),
            'conditions' => $this->conditions->toArray(),
            'associated_model' => $this->getAssociatedModelArray(),
        ];
    }

    /**
     * Convert to JSON
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * JSON serialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert to string representation
     */
    public function __toString(): string
    {
        return sprintf(
            '%s (ID: %s, Price: %.2f, Quantity: %d)',
            $this->name,
            $this->id,
            $this->rawPrice,
            $this->quantity
        );
    }
}
