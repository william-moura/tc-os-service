<?php

declare(strict_types=1);

namespace App\Domain\Events;

use DateTimeImmutable;

final class WorkOrderCreatedDomainEvent
{
    public function __construct(
        public readonly int $workOrderId,
        public readonly int $customerId,
        public readonly string $vehiclePlate,
        public readonly float $totalAmount,
        public readonly DateTimeImmutable $occurredOn = new DateTimeImmutable()
    ) {}

    /**
     * Serializa o evento para o formato exigido na fila do RabbitMQ.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'event' => 'workorder.created',
            'work_order_id' => $this->workOrderId,
            'customer_id' => $this->customerId,
            'vehicle_plate' => $this->vehiclePlate,
            'total_amount' => $this->totalAmount,
            'occurred_on' => $this->occurredOn->format(DATE_ATOM),
        ];
    }
}