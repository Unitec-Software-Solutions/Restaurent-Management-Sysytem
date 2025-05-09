<?php

// database/migrations/xxxx_xx_xx_make_menu_category_id_nullable_in_menu_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeMenuCategoryIdNullableInMenuItemsTable extends Migration
{
    public function up()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_category_id')->nullable()->change(); // Make the column nullable
        });
    }

    public function down()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_category_id')->nullable(false)->change(); // Revert to not nullable
        });
    }
}