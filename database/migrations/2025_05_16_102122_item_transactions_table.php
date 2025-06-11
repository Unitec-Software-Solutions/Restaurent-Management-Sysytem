<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('inventory_item_id')->constrained('item_master');
            $table->string('transaction_type', 50);
            $table->foreignId('incoming_branch_id')->nullable()->constrained('branches');
            $table->foreignId('receiver_user_id')->nullable()->constrained('users');
            $table->decimal('quantity', 12, 2);
            $table->decimal('received_quantity', 12, 2)->default(0);
            $table->decimal('damaged_quantity', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 4)->default(0.0000);
            $table->decimal('unit_price', 12, 4)->default(0.0000);
            $table->string('source_id')->nullable(); // Only define once as string
            $table->string('source_type', 50)->nullable();
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_transactions');
    }
};
