<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE work_orders DROP CONSTRAINT IF EXISTS work_orders_status_check');

        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('status', 50)->default('CREATED')->change();
        });
    }

    public function down(): void
    {
        //
    }
};