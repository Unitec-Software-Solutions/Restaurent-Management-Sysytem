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
            $table->foreignId('payment_id')->constrained('supp_payments_master');
            $table->foreignId('grn_id')->constrained('grn_master');
            $table->decimal('amount', 12, 2);
            $table->timestamp('allocated_at');
            $table->foreignId('allocated_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_allocations');
    }
};