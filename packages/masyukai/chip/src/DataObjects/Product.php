<?php

declare(strict_types=1);

namespace MasyukAI\Chip\DataObjects;

class Product
{
    public function __construct(
        public readonly string $name,
        public readonly float $quantity,
        public readonly int $price,
        public readonly int $discount,
        public readonly float $tax_percent,
        public readonly ?string $category,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            quantity: (float) ($data['quantity'] ?? 1),
            price: (int) $data['price'],
            discount: (int) ($data['discount'] ?? 0),
            tax_percent: (float) ($data['tax_percent'] ?? 0.0),
            category: $data['category'] ?? null,
        );
    }

    public function getPriceInCurrency(): float
    {
        return $this->price / 100;
    }

    public function getDiscountInCurrency(): float
    {
        return $this->discount / 100;
    }

    public function getTotalPrice(): float
    {
        return ($this->price - $this->discount) * $this->quantity;
    }

    public function getTotalPriceInCurrency(): float
    {
        return $this->getTotalPrice() / 100;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'discount' => $this->discount,
            'tax_percent' => $this->tax_percent,
            'category' => $this->category,
        ];
    }
}
