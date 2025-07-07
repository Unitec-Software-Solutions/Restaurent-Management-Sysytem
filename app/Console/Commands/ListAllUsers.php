<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Admin;

class ListAllUsers extends Command
{
    protected $signature = 'debug:list-users';
    protected $description = 'List all users and admins in the database';

    public function handle()
    {
        $this->info("=== USERS TABLE ===");
        $users = User::all();
        
        if ($users->count() > 0) {
            $userData = [];
            foreach ($users as $user) {
                $userData[] = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->organization_id ?? 'NULL',
                    $user->role_id ?? 'NULL',
                    $user->is_active ? 'Yes' : 'No',
                    $user->created_at->format('Y-m-d H:i:s')
                ];
            }
            $this->table(['ID', 'Name', 'Email', 'Org ID', 'Role ID', 'Active', 'Created'], $userData);
        } else {
            $this->warn("No users found in the database!");
        }

        $this->info("\n=== ADMINS TABLE ===");
        $admins = Admin::all();
        
        if ($admins->count() > 0) {
            $adminData = [];
            foreach ($admins as $admin) {
                $adminData[] = [
                    $admin->id,
                    $admin->name,
                    $admin->email,
                    $admin->organization_id ?? 'NULL',
                    $admin->is_super_admin ? 'Yes' : 'No',
                    $admin->is_active ? 'Yes' : 'No',
                    $admin->created_at->format('Y-m-d H:i:s')
                ];
            }
            $this->table(['ID', 'Name', 'Email', 'Org ID', 'Super Admin', 'Active', 'Created'], $adminData);
        } else {
            $this->warn("No admins found in the database!");
        }

        return 0;
    }
}
