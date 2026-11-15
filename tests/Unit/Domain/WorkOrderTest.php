<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Entities\ServiceItem;
use App\Domain\Entities\WorkOrder;
use App\Domain\Exceptions\InvalidStatusTransitionException;
use App\Domain\ValueObjects\VehiclePlate;
use App\Domain\ValueObjects\WorkOrderStatus;
use PHPUnit\Framework\TestCase;

final class WorkOrderTest extends TestCase
{
    public function test_should_create_work_order_with_created_status_and_calculate_total(): void
    {
        $items = [
            new ServiceItem(null, 'Troca de Óleo', 1, 150.00),
            new ServiceItem(null, 'Filtro de Ar', 2, 50.00),
        ];

        $workOrder = WorkOrder::create(
            customerId: 10,
            vehiclePlate: new VehiclePlate('BRA2E19'),
            items: $items
        );

        $this->assertEquals(WorkOrderStatus::CREATED, $workOrder->getStatus());
        $this->assertEquals(250.00, $workOrder->getTotalAmount());
        $this->assertEquals('BRA2E19', $workOrder->getVehiclePlate()->getValue());
    }

    public function test_should_cancel_work_order(): void
    {
        $workOrder = WorkOrder::create(1, new VehiclePlate('ABC1234'), []);
        $workOrder->cancel('Payment timeout');

        $this->assertEquals(WorkOrderStatus::CANCELED, $workOrder->getStatus());
        $this->assertStringContainsString('Payment timeout', $workOrder->getNotes());
    }

    public function test_should_fail_when_transitioning_from_canceled(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $workOrder = WorkOrder::create(1, new VehiclePlate('ABC1234'), []);
        $workOrder->cancel('Test');
        $workOrder->transitionTo(WorkOrderStatus::IN_PROGRESS);
    }
}