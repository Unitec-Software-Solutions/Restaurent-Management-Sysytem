<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity_to_produce', 10, 2);
            $table->decimal('quantity_produced', 10, 2)->default(0);
            $table->decimal('quantity_wasted', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('production_order_id')->references('id')->on('production_orders');
            $table->foreign('item_id')->references('id')->on('item_master');
            $table->index(['production_order_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_items');
    }
};
