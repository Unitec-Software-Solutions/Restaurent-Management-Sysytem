<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FixUserPassword extends Command
{
    protected $signature = 'debug:fix-user-password {email} {password}';
    protected $description = 'Fix a user password for testing';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '$email' not found!");
            return 1;
        }
        
        // Update password
        $user->password = Hash::make($password);
        $user->save();
        
        $this->info("✅ Password updated for user: {$user->name} ({$user->email})");
        
        // Test the password
        $passwordMatch = Hash::check($password, $user->password);
        $this->info("Password verification: " . ($passwordMatch ? '✅ MATCH' : '❌ NO MATCH'));
        
        return 0;
    }
}
