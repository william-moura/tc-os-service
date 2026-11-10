<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use InvalidArgumentException;

final class ServiceItem
{
    public function __construct(
        private ?int $id,
        private string $description,
        private int $quantity,
        private float $unitPrice
    ) {
        if (trim($this->description) === '') {
            throw new InvalidArgumentException('Description cannot be empty');
        }

        if ($this->quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero');
        }

        if ($this->unitPrice < 0) {
            throw new InvalidArgumentException('Unit price cannot be negative');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getTotal(): float
    {
        return round($this->quantity * $this->unitPrice, 2);
    }
}