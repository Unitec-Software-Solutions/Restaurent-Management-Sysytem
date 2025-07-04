<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CheckUserDetails extends Command
{
    protected $signature = 'debug:user-details {email}';
    protected $description = 'Check user details and authentication';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '$email' not found!");
            return 1;
        }

        $this->info("=== USER DETAILS ===");
        $this->table(['Field', 'Value'], [
            ['ID', $user->id],
            ['Name', $user->name],
            ['Email', $user->email],
            ['Organization ID', $user->organization_id ?? 'NULL'],
            ['Branch ID', $user->branch_id ?? 'NULL'],
            ['Role ID', $user->role_id ?? 'NULL'],
            ['Is Active', $user->is_active ? 'Yes' : 'No'],
            ['Is Admin', $user->is_admin ? 'Yes' : 'No'],
            ['Is Super Admin', $user->is_super_admin ? 'Yes' : 'No'],
            ['Created At', $user->created_at],
            ['Updated At', $user->updated_at],
            ['Password Hash Length', strlen($user->password)],
        ]);

        // Test password
        $this->info("\n=== PASSWORD TEST ===");
        $testPasswords = ['TestPassword123!', 'password', 'Password123'];
        
        foreach ($testPasswords as $password) {
            $match = Hash::check($password, $user->password);
            $this->line("Password '$password': " . ($match ? '✅ MATCH' : '❌ NO MATCH'));
        }

        // Check role
        if ($user->userRole) {
            $this->info("\n=== ROLE DETAILS ===");
            $this->table(['Field', 'Value'], [
                ['Role ID', $user->userRole->id],
                ['Role Name', $user->userRole->name],
                ['Role Organization ID', $user->userRole->organization_id ?? 'NULL'],
            ]);
        } else {
            $this->warn("No role assigned to this user!");
        }

        // Check organization
        if ($user->organization) {
            $this->info("\n=== ORGANIZATION DETAILS ===");
            $this->table(['Field', 'Value'], [
                ['Org ID', $user->organization->id],
                ['Org Name', $user->organization->name],
                ['Is Active', $user->organization->is_active ? 'Yes' : 'No'],
            ]);
        } else {
            $this->warn("No organization assigned to this user!");
        }

        return 0;
    }
}
