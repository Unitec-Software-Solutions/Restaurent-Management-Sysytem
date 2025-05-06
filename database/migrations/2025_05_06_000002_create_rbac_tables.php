<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('module')->comment('Module this permission belongs to');
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('staff_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default roles
        DB::table('roles')->insert([
            ['name' => 'System Admin', 'description' => 'Full system access', 'is_system' => true],
            ['name' => 'Head Office Admin', 'description' => 'Organization-wide management', 'is_system' => true],
            ['name' => 'Branch Manager', 'description' => 'Branch management', 'is_system' => true],
            ['name' => 'Waiter', 'description' => 'Order and table management', 'is_system' => true],
            ['name' => 'Chef', 'description' => 'Kitchen operations', 'is_system' => true],
            ['name' => 'Cashier', 'description' => 'Payment processing', 'is_system' => true],
            ['name' => 'Inventory Manager', 'description' => 'Stock management', 'is_system' => true],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('staff_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
}; 