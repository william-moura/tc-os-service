<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\WorkOrderOutputDto;
use App\Domain\Entities\WorkOrder;
use App\Domain\Repositories\WorkOrderRepositoryInterface;

final class ListWorkOrderUseCase
{
    public function __construct(
        private readonly WorkOrderRepositoryInterface $workOrderRepository
    ) {}

    public function execute(): array
    {
        $workOrders = $this->workOrderRepository->findAll();
        $workOrdersOutput = array_map(function (WorkOrder $workOrder) {
            return WorkOrderOutputDto::fromDomain($workOrder);
        }, $workOrders);
        return $workOrdersOutput;
    }
}