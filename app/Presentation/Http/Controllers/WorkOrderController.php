<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\DTOs\CreateWorkOrderInputDto;
use App\Application\UseCases\CreateWorkOrderUseCase;
use App\Domain\Repositories\WorkOrderRepositoryInterface;
use App\Presentation\Http\Requests\CreateWorkOrderRequest;
use App\Presentation\Http\Resources\WorkOrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    public function show(int $id, WorkOrderRepositoryInterface $repository): WorkOrderResource
    {
        $order = $repository->findById($id);

        if (!$order) {
            throw new NotFoundHttpException('Work order not found', null, 404);
        }

        return new WorkOrderResource($order);
    }
}