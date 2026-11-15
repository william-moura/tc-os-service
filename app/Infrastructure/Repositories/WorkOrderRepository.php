<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\WorkOrder;
use App\Domain\Repositories\WorkOrderRepositoryInterface;
use App\Infrastructure\Persistence\Mappers\WorkOrderMapper;
use App\Infrastructure\Persistence\Models\WorkOrderModel;
use Illuminate\Support\Facades\DB;

final class WorkOrderRepository implements WorkOrderRepositoryInterface
{
    public function save(WorkOrder $workOrder): WorkOrder
    {
        return DB::transaction(function () use ($workOrder) {
            $model = WorkOrderModel::updateOrCreate(
                ['id' => $workOrder->getId()],
                WorkOrderMapper::toModelAttributes($workOrder)
            );

            if ($workOrder->getId() === null) {
                foreach ($workOrder->getItems() as $item) {
                    $model->items()->create(
                        WorkOrderMapper::toItemModelAttributes($item, $model->id)
                    );
                }
            }

            return WorkOrderMapper::toDomain($model->fresh(['items']));
        });
    }

    public function findById(int $id): ?WorkOrder
    {
        $model = WorkOrderModel::with('items')->find($id);

        return $model ? WorkOrderMapper::toDomain($model) : null;
    }
}