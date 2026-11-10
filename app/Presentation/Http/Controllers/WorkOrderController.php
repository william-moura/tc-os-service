<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\CreateWorkOrderInputDto;
use App\Application\UseCases\CreateWorkOrderUseCase;
use App\Domain\Repositories\WorkOrderRepositoryInterface;
use App\Presentation\Http\Requests\CreateWorkOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class WorkOrderController extends Controller
{
    public function store(CreateWorkOrderRequest $request, CreateWorkOrderUseCase $useCase): JsonResponse
    {
        $dto = new CreateWorkOrderInputDto(
            customerId: (int) $request->input('customer_id'),
            vehiclePlate: (string) $request->input('vehicle_plate'),
            items: (array) $request->input('items'),
            notes: $request->input('notes')
        );

        $output = $useCase->execute($dto);

        return response()->json($output, 201);
    }

    public function show(int $id, WorkOrderRepositoryInterface $repository): JsonResponse
    {
        $order = $repository->findById($id);

        if (!$order) {
            return response()->json(['message' => 'Work order not found'], 404);
        }

        return response()->json([
            'id' => $order->getId(),
            'customer_id' => $order->getCustomerId(),
            'vehicle_plate' => $order->getVehiclePlate()->getValue(),
            'status' => $order->getStatus()->value,
            'total_amount' => $order->getTotalAmount(),
            'notes' => $order->getNotes(),
        ]);
    }
}