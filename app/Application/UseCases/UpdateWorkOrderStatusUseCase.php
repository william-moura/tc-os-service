<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\WorkOrderOutputDto;
use App\Domain\Exceptions\WorkOrderNotFoundException;
use App\Domain\Repositories\WorkOrderRepositoryInterface;
use App\Domain\ValueObjects\WorkOrderStatus;

final class UpdateWorkOrderStatusUseCase
{
    public function __construct(
        private WorkOrderRepositoryInterface $repository
    ) {}

    public function execute(int $workOrderId, string $status): WorkOrderOutputDto
    {
        $workOrder = $this->repository->findById($workOrderId);

        if (!$workOrder) {
            throw new WorkOrderNotFoundException("Work order with ID {$workOrderId} not found.");
        }

        $newStatus = WorkOrderStatus::from($status);
        $workOrder->transitionTo($newStatus);

        $updatedOrder = $this->repository->save($workOrder);

        return WorkOrderOutputDto::fromDomain($updatedOrder);
    }
}