<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')
                ->constrained('work_orders')
                ->onDelete('cascade');
            $table->string('description', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->index(['work_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_items');
    }
};