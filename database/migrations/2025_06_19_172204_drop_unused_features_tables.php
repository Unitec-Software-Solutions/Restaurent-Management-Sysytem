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
        // Drop customer-related tables
        Schema::dropIfExists('customer_authentication_methods');
        Schema::dropIfExists('customer_preferences');
        Schema::dropIfExists('customer_profiles');
        
        // Drop staff/shift management tables
        Schema::dropIfExists('staff_training_records');
        Schema::dropIfExists('staff_attendance');
        Schema::dropIfExists('staff_shifts');
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('shifts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This down method is intentionally left empty
        // as we are removing these features permanently.
        // If you need to restore these tables, you would need
        // to recreate the original migrations.
    }
};
