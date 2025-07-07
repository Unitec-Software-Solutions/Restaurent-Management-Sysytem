<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Schema::table('item_master', function (Blueprint $table) {
        //     // Add clear flags for item types
        //     $table->boolean('requires_production')->default(false)->after('is_menu_item');
        //     $table->enum('item_type', ['buy_sell', 'kot_production'])->default('buy_sell')->after('requires_production');

        //     // Add foreign key constraint for categories with cascade
        //     $table->foreign('item_category_id')
        //           ->references('id')
        //           ->on('item_categories')
        //           ->onUpdate('cascade')
        //           ->onDelete('set null');
        // });
    }

    public function down()
    {
        // Schema::table('item_master', function (Blueprint $table) {
        //     $table->dropForeign(['item_category_id']);
        //     $table->dropColumn(['requires_production', 'item_type']);
        // });
    }
};
