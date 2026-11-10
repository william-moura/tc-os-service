<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\WorkOrder;

interface WorkOrderRepositoryInterface
{
    public function save(WorkOrder $workOrder): WorkOrder;
    public function findById(int $id): ?WorkOrder;
}