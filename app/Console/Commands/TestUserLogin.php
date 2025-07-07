<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TestUserLogin extends Command
{
    protected $signature = 'test:user-login {email} {password}';
    protected $description = 'Test user login credentials';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $this->info("Testing login for: {$email}");
        
        // Find user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User not found with email: {$email}");
            return 1;
        }
        
        $this->info("User found:");
        $this->info("- ID: {$user->id}");
        $this->info("- Email: {$user->email}");
        $this->info("- Name: {$user->name}");
        $this->info("- Is Admin: " . ($user->is_admin ? 'Yes' : 'No'));
        $this->info("- Is Super Admin: " . ($user->is_super_admin ? 'Yes' : 'No'));
        $this->info("- Organization ID: " . ($user->organization_id ?: 'None'));
        $this->info("- Branch ID: " . ($user->branch_id ?: 'None'));
        $this->info("- Role ID: " . ($user->role_id ?: 'None'));
        
        // Test password
        if (Hash::check($password, $user->password)) {
            $this->info("✓ Password check passed");
        } else {
            $this->error("✗ Password check failed");
            $this->info("Stored hash: " . substr($user->password, 0, 30) . "...");
            $this->info("Plain password for testing: {$password}");
            return 1;
        }
        
        // Test Auth attempt
        $credentials = ['email' => $email, 'password' => $password];
        
        if (Auth::guard('web')->attempt($credentials)) {
            $this->info("✓ Auth::guard('web')->attempt() passed");
            Auth::guard('web')->logout(); // Clean up
        } else {
            $this->error("✗ Auth::guard('web')->attempt() failed");
            return 1;
        }
        
        $this->info("All login tests passed!");
        return 0;
    }
}
