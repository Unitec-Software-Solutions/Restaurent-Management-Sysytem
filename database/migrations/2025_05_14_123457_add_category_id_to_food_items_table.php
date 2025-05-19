<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdToFoodItemsTable extends Migration
{
    public function up()
    {
        Schema::table('food_items', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('item_id');
            $table->foreign('category_id')->references('id')->on('inventory_categories')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('food_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
} 