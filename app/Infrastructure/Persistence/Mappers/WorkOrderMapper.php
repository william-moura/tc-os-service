<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Entities\ServiceItem;
use App\Domain\Entities\WorkOrder;
use App\Domain\ValueObjects\VehiclePlate;
use App\Domain\ValueObjects\WorkOrderStatus;
use App\Infrastructure\Persistence\Models\ServiceItemModel;
use App\Infrastructure\Persistence\Models\WorkOrderModel;

final class WorkOrderMapper
{
    /**
     * Converte o Eloquent Model (e as suas relações) para a Entidade de Domínio.
     */
    public static function toDomain(WorkOrderModel $model): WorkOrder
    {
        $items = [];

        if ($model->relationLoaded('items') && $model->items !== null) {
            $items = $model->items->map(
                fn (ServiceItemModel $item) => new ServiceItem(
                    id: $item->id,
                    description: $item->description,
                    quantity: (int) $item->quantity,
                    unitPrice: (float) $item->unit_price
                )
            )->all();
        }

        return new WorkOrder(
            id: $model->id,
            customerId: (int) $model->customer_id,
            vehiclePlate: new VehiclePlate($model->vehicle_plate),
            status: WorkOrderStatus::from($model->status),
            items: $items,
            notes: $model->notes
        );
    }

    /**
     * Mapeia os dados da Entidade de Domínio para atributos do modelo Eloquent de WorkOrder.
     *
     * @return array<string, mixed>
     */
    public static function toModelAttributes(WorkOrder $workOrder): array
    {
        return [
            'customer_id' => $workOrder->getCustomerId(),
            'vehicle_plate' => $workOrder->getVehiclePlate()->getValue(),
            'status' => $workOrder->getStatus()->value,
            'total_amount' => $workOrder->getTotalAmount(),
            'notes' => $workOrder->getNotes(),
        ];
    }

    /**
     * Mapeia um item de domínio para atributos do modelo Eloquent de ServiceItem.
     *
     * @return array<string, mixed>
     */
    public static function toItemModelAttributes(ServiceItem $item, int $workOrderId): array
    {
        return [
            'work_order_id' => $workOrderId,
            'description' => $item->getDescription(),
            'quantity' => $item->getQuantity(),
            'unit_price' => $item->getUnitPrice(),
        ];
    }
}