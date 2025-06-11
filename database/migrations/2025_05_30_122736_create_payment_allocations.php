<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('grn_id')->nullable();
            $table->unsignedBigInteger('po_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamp('allocated_at');
            $table->unsignedBigInteger('allocated_by');
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('supp_payments_master')->onDelete('cascade');
            $table->foreign('grn_id')->references('grn_id')->on('grn_master')->onDelete('set null');
            $table->foreign('po_id')->references('po_id')->on('po_master')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_allocations');
    }
}
?>