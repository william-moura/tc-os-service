<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\WorkOrderOutputDto;
use App\Domain\Exceptions\WorkOrderNotFoundException;
use App\Domain\Repositories\WorkOrderRepositoryInterface;

final class GetWorkOrderUseCase
{
    public function __construct(
        private WorkOrderRepositoryInterface $repository
    ) {}

    public function execute(int $workOrderId): WorkOrderOutputDto
    {
        $workOrder = $this->repository->findById($workOrderId);

        if (!$workOrder) {
            throw new WorkOrderNotFoundException("Work order with ID {$workOrderId} not found.");
        }

        return WorkOrderOutputDto::fromDomain($workOrder);
    }
}