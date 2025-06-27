<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Insert missing permissions if they don't exist
        $permissions = [
            'inventory.view' => 'View inventory items and stock levels',
            'inventory.manage' => 'Manage inventory items and stock',
            'inventory.create' => 'Create new inventory items',
            'inventory.edit' => 'Edit inventory items',
            'inventory.delete' => 'Delete inventory items',
            'suppliers.view' => 'View suppliers',
            'suppliers.manage' => 'Manage suppliers',
            'suppliers.create' => 'Create new suppliers',
            'suppliers.edit' => 'Edit suppliers',
            'suppliers.delete' => 'Delete suppliers',
            'grn.view' => 'View Goods Receipt Notes',
            'grn.manage' => 'Manage Goods Receipt Notes',
            'grn.create' => 'Create new GRNs',
        ];

        foreach ($permissions as $slug => $description) {
            DB::table('permissions')->insertOrIgnore([
                'name' => ucwords(str_replace(['.', '_'], ' ', $slug)),
                'slug' => $slug,
                'description' => $description,
                'category' => explode('.', $slug)[0],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        // Remove the permissions
        $slugs = [
            'inventory.view', 'inventory.manage', 'inventory.create', 'inventory.edit', 'inventory.delete',
            'suppliers.view', 'suppliers.manage', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'grn.view', 'grn.manage', 'grn.create'
        ];
        
        DB::table('permissions')->whereIn('slug', $slugs)->delete();
    }
};
