<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\ListWorkOrderUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class ListWorkOrderController extends Controller
{
    public function __construct(
        private readonly ListWorkOrderUseCase $listWorkOrderUseCase
    ) {}

    public function __invoke(): JsonResponse
    {
        $workOrders = $this->listWorkOrderUseCase->execute();

        return response()->json($workOrders);
    }
}
