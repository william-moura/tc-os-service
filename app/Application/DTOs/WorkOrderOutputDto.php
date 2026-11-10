<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Entities\WorkOrder;

final class WorkOrderOutputDto
{
    public function __construct(
        public readonly int $id,
        public readonly int $customerId,
        public readonly string $vehiclePlate,
        public readonly string $status,
        public readonly float $totalAmount,
        public readonly ?string $notes
    ) {}

    public static function fromDomain(WorkOrder $workOrder): self
    {
        return new self(
            id: $workOrder->getId() ?? 0,
            customerId: $workOrder->getCustomerId(),
            vehiclePlate: $workOrder->getVehiclePlate()->getValue(),
            status: $workOrder->getStatus()->value,
            totalAmount: $workOrder->getTotalAmount(),
            notes: $workOrder->getNotes()
        );
    }
}