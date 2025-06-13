<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gtn_master', function (Blueprint $table) {
            $table->id('gtn_id');

            // References
            $table->string('gtn_number')->unique();
            $table->foreignId('from_branch_id')->constrained('branches');
            $table->foreignId('to_branch_id')->constrained('branches');
            $table->foreignId('created_by')->constrained('employees');
            $table->foreignId('approved_by')->nullable()->constrained('employees');
            $table->foreignId('organization_id')->constrained('organizations');

            // Dates & Status
            $table->date('transfer_date');
            $table->string('status', 50)->default('Pending'); // Pending, In Transit, Completed, Cancelled

            // Notes
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gtn_master');
    }
};
