<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class CreateWorkOrderInputDto
{
    /**
     * @param ServiceItemDto[] $items
     */
    public function __construct(
        public readonly int $customerId,
        public readonly string $vehiclePlate,
        public readonly array $items,
        public readonly ?string $notes = null
    ) {}
}