<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use App\Domain\Entities\ServiceItem;
use App\Domain\Entities\WorkOrder;
use App\Domain\ValueObjects\VehiclePlate;
use App\Domain\ValueObjects\WorkOrderStatus;
use App\Infrastructure\Persistence\Mappers\WorkOrderMapper;
use App\Infrastructure\Persistence\Models\ServiceItemModel;
use App\Infrastructure\Persistence\Models\WorkOrderModel;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

final class WorkOrderMapperTest extends TestCase
{
    public function test_should_map_domain_to_model_attributes(): void
    {
        $workOrder = new WorkOrder(
            id: 10,
            customerId: 5,
            vehiclePlate: new VehiclePlate('ABC1D23'),
            status: WorkOrderStatus::CREATED,
            items: [
                new ServiceItem(null, 'Óleo', 1, 100.0),
            ],
            notes: 'Urgente'
        );

        $attributes = WorkOrderMapper::toModelAttributes($workOrder);

        $this->assertSame(5, $attributes['customer_id']);
        $this->assertSame('ABC1D23', $attributes['vehicle_plate']);
        $this->assertSame('CREATED', $attributes['status']);
        $this->assertSame(100.0, $attributes['total_amount']);
        $this->assertSame('Urgente', $attributes['notes']);
    }

    public function test_should_map_model_to_domain_entity(): void
    {
        $model = new WorkOrderModel([
            'customer_id' => 12,
            'vehicle_plate' => 'XYZ9876',
            'status' => 'IN_PROGRESS',
            'total_amount' => 50.0,
            'notes' => 'Observação',
        ]);
        $model->id = 7;

        $itemModel = new ServiceItemModel([
            'description' => 'Filtro',
            'quantity' => 1,
            'unit_price' => 50.0,
        ]);
        $itemModel->id = 1;

        $model->setRelation('items', new Collection([$itemModel]));

        $domain = WorkOrderMapper::toDomain($model);

        $this->assertSame(7, $domain->getId());
        $this->assertSame(12, $domain->getCustomerId());
        $this->assertSame('XYZ9876', $domain->getVehiclePlate()->getValue());
        $this->assertSame(WorkOrderStatus::IN_PROGRESS, $domain->getStatus());
        $this->assertCount(1, $domain->getItems());
        $this->assertSame('Filtro', $domain->getItems()[0]->getDescription());
    }
}