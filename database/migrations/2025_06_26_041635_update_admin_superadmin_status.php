<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all admins
        $admins = DB::table('admins')->get();
        
        if ($admins->isEmpty()) {
            echo "No admins found in database.\n";
            return;
        }
        
        // Update the first admin or any admin with admin-like email/name
        $firstAdmin = $admins->first();
        DB::table('admins')->where('id', $firstAdmin->id)->update([
            'is_super_admin' => true,
            'role' => 'superadmin',
            'status' => 'active',
            'is_active' => true
        ]);
        
        echo "Updated admin {$firstAdmin->name} ({$firstAdmin->email}) to super admin.\n";

        // Update any admin with email containing 'admin' or 'super' to be super admin
        $updated = DB::table('admins')
            ->where(function($query) {
                $query->where('email', 'like', '%admin%')
                      ->orWhere('email', 'like', '%super%')
                      ->orWhere('name', 'like', '%admin%')
                      ->orWhere('name', 'like', '%super%');
            })
            ->update([
                'is_super_admin' => true,
                'role' => 'superadmin'
            ]);
            
        echo "Updated {$updated} additional admins based on name/email patterns.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset super admin status - be careful with this in production
        DB::table('admins')->update([
            'is_super_admin' => false,
            'role' => 'admin'
        ]);
    }
};
