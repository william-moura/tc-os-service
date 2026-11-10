<?php

declare(strict_types=1);

namespace App\Domain\Events;

use DateTimeImmutable;

final class WorkOrderCanceledDomainEvent
{
    public function __construct(
        public readonly int $workOrderId,
        public readonly string $reason,
        public readonly DateTimeImmutable $occurredOn = new DateTimeImmutable()
    ) {}

    /**
     * Serializa o evento de compensação para auditoria ou filas de rollback.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'event' => 'workorder.canceled',
            'work_order_id' => $this->workOrderId,
            'reason' => $this->reason,
            'occurred_on' => $this->occurredOn->format(DATE_ATOM),
        ];
    }
}