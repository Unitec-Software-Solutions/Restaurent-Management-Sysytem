<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_request_master_id');
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity_requested', 10, 2);
            $table->decimal('quantity_approved', 10, 2)->nullable();
            $table->decimal('quantity_produced', 10, 2)->default(0);
            $table->decimal('quantity_distributed', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('production_request_master_id')->references('id')->on('production_requests_master');
            $table->foreign('item_id')->references('id')->on('item_master');
            $table->index(['production_request_master_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_request_items');
    }
};
