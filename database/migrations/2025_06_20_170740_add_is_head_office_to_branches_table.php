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
        Schema::table('branches', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('branches', 'is_head_office')) {
                $table->boolean('is_head_office')->default(false)->after('organization_id');
            }
            if (!Schema::hasColumn('branches', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'is_head_office')) {
                $table->dropColumn('is_head_office');
            }
            if (Schema::hasColumn('branches', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
