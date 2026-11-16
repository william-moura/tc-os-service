<?php

use App\Presentation\Http\Controllers\ApproveWorkOrderController;
use App\Presentation\Http\Controllers\ListWorkOrderController;
use App\Presentation\Http\Controllers\WorkOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'v1'], function () {
    Route::post('work-orders', [WorkOrderController::class, 'store']);
    Route::get('work-orders', ListWorkOrderController::class);
    Route::post('work-orders/{workOrderId}/approve', ApproveWorkOrderController::class);
});
