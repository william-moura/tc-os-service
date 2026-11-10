<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\UseCases\UpdateWorkOrderStatusUseCase;
use App\Domain\Entities\WorkOrder;
use App\Domain\Exceptions\InvalidStatusTransitionException;
use App\Domain\Exceptions\WorkOrderNotFoundException;
use App\Domain\Repositories\WorkOrderRepositoryInterface;
use App\Domain\ValueObjects\VehiclePlate;
use App\Domain\ValueObjects\WorkOrderStatus;
use PHPUnit\Framework\TestCase;

final class UpdateWorkOrderStatusUseCaseTest extends TestCase
{
    public function test_should_update_status_successfully(): void
    {
        $workOrder = new WorkOrder(
            id: 1,
            customerId: 100,
            vehiclePlate: new VehiclePlate('ABC1234'),
            status: WorkOrderStatus::CREATED,
            items: []
        );

        $repository = $this->createMock(WorkOrderRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($workOrder);

        $repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn(WorkOrder $wo) => $wo);

        $useCase = new UpdateWorkOrderStatusUseCase($repository);
        $output = $useCase->execute(1, WorkOrderStatus::AWAITING_PAYMENT->value);

        $this->assertSame(WorkOrderStatus::AWAITING_PAYMENT->value, $output->status);
    }

    public function test_should_throw_exception_when_work_order_not_found(): void
    {
        $this->expectException(WorkOrderNotFoundException::class);

        $repository = $this->createMock(WorkOrderRepositoryInterface::class);
        $repository->method('findById')->willReturn(null);

        $useCase = new UpdateWorkOrderStatusUseCase($repository);
        $useCase->execute(999, WorkOrderStatus::AWAITING_PAYMENT->value);
    }

    public function test_should_throw_exception_on_illegal_status_transition(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $workOrder = new WorkOrder(
            id: 1,
            customerId: 100,
            vehiclePlate: new VehiclePlate('ABC1234'),
            status: WorkOrderStatus::CREATED,
            items: []
        );

        $repository = $this->createMock(WorkOrderRepositoryInterface::class);
        $repository->method('findById')->willReturn($workOrder);

        $useCase = new UpdateWorkOrderStatusUseCase($repository);
        // Transição inválida direta de CREATED para COMPLETED
        $useCase->execute(1, WorkOrderStatus::COMPLETED->value);
    }
}