<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\UpdateWorkOrderStatusUseCase;
use App\Domain\ValueObjects\WorkOrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class ApproveWorkOrderController extends Controller
{
    public function __construct(
        private readonly UpdateWorkOrderStatusUseCase $updateWorkOrderStatusUseCase
    ) {}

    public function __invoke(int $workOrderId): JsonResponse
    {
        $this->updateWorkOrderStatusUseCase->execute($workOrderId, WorkOrderStatus::APPROVED->value);

        return response()->json([
            'message' => 'Work order approved successfully',
        ]);
    }
}
