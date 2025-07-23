<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grn_payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('grn_id')->constrained('grn_master', 'grn_id');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method', 50);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('paid_by_user_id')->constrained('users', 'id');
            $table->foreignId('organization_id')->constrained('organizations');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grn_payments');
    }
};
