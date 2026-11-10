<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final class VehiclePlate
{
    private string $value;

    public function __construct(string $plate)
    {
        $normalized = strtoupper(trim(str_replace('-', '', $plate)));

        // Valida placa padrão Mercosul (ABC1D23) ou antigo (ABC1234)
        if (!preg_match('/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', $normalized)) {
            throw new InvalidArgumentException("Invalid vehicle plate format: {$plate}");
        }

        $this->value = $normalized;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}