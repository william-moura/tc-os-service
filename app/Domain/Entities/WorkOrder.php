<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\WorkOrderStatus;
use App\Domain\ValueObjects\VehiclePlate;
use App\Domain\Exceptions\InvalidStatusTransitionException;
use App\Domain\Entities\ServiceItem;

final class WorkOrder
{
    /** @param ServiceItem[] $items */
    public function __construct(
        private ?int $id,
        private int $customerId,
        private VehiclePlate $vehiclePlate,
        private WorkOrderStatus $status,
        private array $items = [],
        private ?string $notes = null
    ) {}

    public static function create(int $customerId, VehiclePlate $vehiclePlate, array $items, ?string $notes = null): self
    {
        return new self(
            id: null,
            customerId: $customerId,
            vehiclePlate: $vehiclePlate,
            status: WorkOrderStatus::CREATED,
            items: $items,
            notes: $notes
        );
    }

    public function transitionTo(WorkOrderStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new InvalidStatusTransitionException(
                "Cannot transition work order from {$this->status->value} to {$newStatus->value}"
            );
        }

        $this->status = $newStatus;
    }

    public function cancel(string $reason = ''): void
    {
        $this->transitionTo(WorkOrderStatus::CANCELED);
        if ($reason !== '') {
            $this->notes .= " [Canceled: {$reason}]";
        }
    }

    public function getTotalAmount(): float
    {
        return array_reduce(
            $this->items,
            fn(float $carry, ServiceItem $item) => $carry + $item->getTotal(),
            0.0
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getCustomerId(): int { return $this->customerId; }
    public function getVehiclePlate(): VehiclePlate { return $this->vehiclePlate; }
    public function getStatus(): WorkOrderStatus { return $this->status; }
    public function getItems(): array { return $this->items; }
    public function getNotes(): ?string { return $this->notes; }
}