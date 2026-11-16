<?php

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\GetWorkOrderUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

final class GetWorkOrderByWorkOrderIdController extends Controller
{
    public function __construct(
        private readonly GetWorkOrderUseCase $useCase
    ) {}

    public function __invoke(Request $request, int $workOrderId): JsonResponse
    {
        $workOrder = $this->useCase->execute($workOrderId);        
        return response()->json($workOrder, Response::HTTP_OK);
    }
}