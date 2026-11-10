<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItemModel extends Model
{
    protected $table = 'service_items';

    protected $fillable = [
        'work_order_id',
        'description',
        'quantity',
        'unit_price',
    ];
}