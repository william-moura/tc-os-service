<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\WorkOrderRepositoryInterface;
use RuntimeException;

final class CancelWorkOrderUseCase
{
    public function __construct(
        private WorkOrderRepositoryInterface $repository
    ) {}

    public function execute(int $workOrderId, string $reason): void
    {
        $workOrder = $this->repository->findById($workOrderId);

        if (!$workOrder) {
            throw new RuntimeException("Work order not found: {$workOrderId}");
        }

        $workOrder->cancel($reason);
        $this->repository->save($workOrder);
    }
}