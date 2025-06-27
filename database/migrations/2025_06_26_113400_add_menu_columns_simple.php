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
        Schema::table('menus', function (Blueprint $table) {
            // Add missing columns that MenuController expects
            if (!Schema::hasColumn('menus', 'valid_from')) {
                $table->datetime('valid_from')->nullable();
            }
            
            if (!Schema::hasColumn('menus', 'valid_until')) {
                $table->datetime('valid_until')->nullable();
            }
            
            if (!Schema::hasColumn('menus', 'available_days')) {
                $table->json('available_days')->nullable();
            }
            
            if (!Schema::hasColumn('menus', 'start_time')) {
                $table->time('start_time')->nullable();
            }
            
            if (!Schema::hasColumn('menus', 'end_time')) {
                $table->time('end_time')->nullable();
            }
            
            if (!Schema::hasColumn('menus', 'type')) {
                $table->enum('type', ['breakfast', 'lunch', 'dinner', 'all_day', 'special'])->default('all_day');
            }
            
            if (!Schema::hasColumn('menus', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable();
            }
            
            if (!Schema::hasColumn('menus', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn([
                'valid_from',
                'valid_until', 
                'available_days',
                'start_time',
                'end_time',
                'type',
                'branch_id',
                'created_by'
            ]);
        });
    }
};
