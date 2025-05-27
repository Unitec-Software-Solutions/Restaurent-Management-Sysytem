<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // Remove order_type and status check constraints
    DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_order_type_check');
    DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');
}

public function down()
{
    // Optionally, you can re-add the constraints here if needed
}
};
