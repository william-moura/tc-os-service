<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class ServiceItemDto
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $description,
        public readonly int $quantity,
        public readonly float $unitPrice
    ) {}

    /**
     * Instancia o DTO a partir de um array simples de dados (ex: vindo da Request HTTP).
     *
     * @param array{id?: int|null, description: string, quantity: int|numeric, unit_price: float|numeric} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            description: (string) $data['description'],
            quantity: (int) $data['quantity'],
            unitPrice: (float) $data['unit_price']
        );
    }

    /**
     * Calcula o total acumulado do item.
     */
    public function getTotal(): float
    {
        return round($this->quantity * $this->unitPrice, 2);
    }

    /**
     * Converte o DTO para array associativo primitivo.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'total' => $this->getTotal(),
        ];
    }
}