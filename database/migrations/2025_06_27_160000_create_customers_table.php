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
        Schema::create('customers', function (Blueprint $table) {
            $table->string('phone')->primary(); // Phone as primary key
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->enum('preferred_contact', ['email', 'sms'])->default('email');
            $table->date('date_of_birth')->nullable();
            $table->date('anniversary_date')->nullable();
            $table->text('dietary_preferences')->nullable();
            $table->text('special_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_visit_date')->nullable();
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0.00);
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['phone', 'is_active']);
            $table->index('email');
            $table->index('last_visit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
