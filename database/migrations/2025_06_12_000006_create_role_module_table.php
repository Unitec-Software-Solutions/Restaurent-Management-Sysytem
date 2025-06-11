<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleModuleTable extends Migration
{
    public function up()
    {
        Schema::create('role_module', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'module_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_module');
    }
}
