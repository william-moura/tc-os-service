<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\CreateWorkOrderInputDto;
use App\Application\DTOs\WorkOrderOutputDto;
use App\Application\Services\MessagePublisherInterface;
use App\Domain\Entities\ServiceItem;
use App\Domain\Entities\WorkOrder;
use App\Domain\Repositories\WorkOrderRepositoryInterface;
use App\Domain\ValueObjects\VehiclePlate;

final class CreateWorkOrderUseCase
{
    public function __construct(
        private WorkOrderRepositoryInterface $repository,
        private MessagePublisherInterface $publisher
    ) {}

    public function execute(CreateWorkOrderInputDto $input): WorkOrderOutputDto
    {
        $items = array_map(
            fn(array $item) => new ServiceItem(
                id: null,
                description: $item['description'],
                quantity: (int) $item['quantity'],
                unitPrice: (float) $item['unit_price']
            ),
            $input->items
        );

        $workOrder = WorkOrder::create(
            customerId: $input->customerId,
            vehiclePlate: new VehiclePlate($input->vehiclePlate),
            items: $items,
            notes: $input->notes
        );

        $savedOrder = $this->repository->save($workOrder);

        // Dispara o primeiro evento da Saga Pattern para a fila RabbitMQ
        $this->publisher->publish('queue.workorder.created', [
            'event' => 'workorder.created',
            'work_order_id' => $savedOrder->getId(),
            'customer_id' => $savedOrder->getCustomerId(),
            'total_amount' => $savedOrder->getTotalAmount(),
            'created_at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ]);

        return WorkOrderOutputDto::fromDomain($savedOrder);
    }
}