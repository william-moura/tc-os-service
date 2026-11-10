<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrderModel extends Model
{
    protected $table = 'work_orders';

    protected $fillable = [
        'customer_id',
        'vehicle_plate',
        'status',
        'total_amount',
        'notes',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItemModel::class, 'work_order_id');
    }
}