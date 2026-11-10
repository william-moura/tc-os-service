<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\ServiceItem;
use App\Domain\Entities\WorkOrder;
use App\Domain\Repositories\WorkOrderRepositoryInterface;
use App\Domain\ValueObjects\VehiclePlate;
use App\Domain\ValueObjects\WorkOrderStatus;
use App\Infrastructure\Persistence\Models\WorkOrderModel;
use Illuminate\Support\Facades\DB;

final class WorkOrderRepository implements WorkOrderRepositoryInterface
{
    public function save(WorkOrder $workOrder): WorkOrder
    {
        return DB::transaction(function () use ($workOrder) {
            $model = WorkOrderModel::updateOrCreate(
                ['id' => $workOrder->getId()],
                [
                    'customer_id' => $workOrder->getCustomerId(),
                    'vehicle_plate' => $workOrder->getVehiclePlate()->getValue(),
                    'status' => $workOrder->getStatus()->value,
                    'total_amount' => $workOrder->getTotalAmount(),
                    'notes' => $workOrder->getNotes(),
                ]
            );

            if ($workOrder->getId() === null) {
                foreach ($workOrder->getItems() as $item) {
                    $model->items()->create([
                        'description' => $item->getDescription(),
                        'quantity' => $item->getQuantity(),
                        'unit_price' => $item->getUnitPrice(),
                    ]);
                }
            }

            return $this->toDomain($model->fresh(['items']));
        });
    }

    public function findById(int $id): ?WorkOrder
    {
        $model = WorkOrderModel::with('items')->find($id);
        return $model ? $this->toDomain($model) : null;
    }

    private function toDomain(WorkOrderModel $model): WorkOrder
    {
        $items = $model->items->map(
            fn($item) => new ServiceItem(
                id: $item->id,
                description: $item->description,
                quantity: (int) $item->quantity,
                unitPrice: (float) $item->unit_price
            )
        )->all();

        return new WorkOrder(
            id: $model->id,
            customerId: (int) $model->customer_id,
            vehiclePlate: new VehiclePlate($model->vehicle_plate),
            status: WorkOrderStatus::from($model->status),
            items: $items,
            notes: $model->notes
        );
    }
}