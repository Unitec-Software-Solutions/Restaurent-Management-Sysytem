<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_release_note_master', function (Blueprint $table) {
            $table->id();
            $table->string('srn_number')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->foreignId('organization_id')->nullable()->constrained('organizations');

            $table->foreignId('released_by_user_id')->nullable(); // user who released the stock
            $table->timestamp('released_at')->nullable(); // timestamp when the stock was released

            $table->foreignId('received_by_user_id')->nullable(); // if stock was released to a user, then who received the stock, if applicable
            $table->timestamp('received_at')->nullable(); // timestamp when the stock was received, if applicable

            $table->foreignId('verified_by_user_id')->nullable(); // user who verified the stock release note
            $table->timestamp('verified_at')->nullable(); // timestamp when the stock release note was verified

            $table->date('release_date')->nullable(); // date of stock release
            $table->string('release_type', 50)->nullable(); // e.g. 'wastage', 'sale', 'transfer' , to kitchen station

            $table->decimal('total_amount', 12, 2)->default(0.00)->nullable();
            $table->string('status', 50)->default('Pending')->nullable(); // Pending, Verified, Rejected

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->nullable();
            $table->foreignId('created_by')->nullable(); // User who created the Stock release note

            $table->unsignedBigInteger('document_id')->nullable(); // ID of related document
            $table->string('document_type')->nullable(); // Table name or type (e.g. 'production_orders', 'gtn_master', etc.)

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_release_note_master');
    }
};
