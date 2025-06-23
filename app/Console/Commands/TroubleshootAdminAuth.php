<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Admin;

class TroubleshootAdminAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:troubleshoot-auth 
                            {--email= : Admin email to check}
                            {--fix-sessions : Clear all admin sessions}
                            {--check-config : Check authentication configuration}
                            {--check-sessions : Check session configuration and status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Troubleshoot admin authentication issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Admin Authentication Troubleshooting');
        $this->newLine();

        if ($this->option('check-config')) {
            $this->checkConfiguration();
        }

        if ($this->option('check-sessions')) {
            $this->checkSessions();
        }

        if ($this->option('fix-sessions')) {
            $this->clearSessions();
        }

        $email = $this->option('email');
        if ($email) {
            $this->checkAdminUser($email);
        }

        if (!$this->option('check-config') && !$this->option('fix-sessions') && !$email && !$this->option('check-sessions')) {
            $this->runFullDiagnostic();
        }

        return 0;
    }

    private function checkConfiguration()
    {
        $this->info('📋 Checking Authentication Configuration');
        
        // Check auth config
        $defaultGuard = config('auth.defaults.guard');
        $guards = config('auth.guards');
        $providers = config('auth.providers');

        $this->table(['Setting', 'Value'], [
            ['Default Guard', $defaultGuard],
            ['Session Driver', config('session.driver')],
            ['Session Lifetime', config('session.lifetime') . ' minutes'],
            ['Session Encrypt', config('session.encrypt') ? 'Yes' : 'No'],
            ['Admin Guard Driver', $guards['admin']['driver'] ?? 'Not set'],
            ['Admin Provider', $guards['admin']['provider'] ?? 'Not set'],
            ['Admin Model', $providers['admins']['model'] ?? 'Not set'],
        ]);

        // Check database tables
        $this->info('🗄️ Checking Database Tables');
        $tables = ['admins', 'sessions'];
        foreach ($tables as $table) {
            $exists = Schema::hasTable($table);
            $this->line("Table '{$table}': " . ($exists ? '✅ Exists' : '❌ Missing'));
            
            if ($exists && $table === 'admins') {
                $count = DB::table($table)->count();
                $this->line("Admin records: {$count}");
            }
        }
    }

    private function clearSessions()
    {
        $this->info('🧹 Clearing Admin Sessions');
        
        try {
            // Clear database sessions
            if (config('session.driver') === 'database') {
                $deleted = DB::table(config('session.table', 'sessions'))->delete();
                $this->info("Deleted {$deleted} session records from database");
            }

            // Clear file sessions if applicable
            if (config('session.driver') === 'file') {
                $sessionPath = config('session.files');
                $files = glob($sessionPath . '/sess_*');
                $count = 0;
                foreach ($files as $file) {
                    if (unlink($file)) {
                        $count++;
                    }
                }
                $this->info("Deleted {$count} session files");
            }

            $this->info('✅ Sessions cleared successfully');
        } catch (\Exception $e) {
            $this->error('❌ Failed to clear sessions: ' . $e->getMessage());
        }
    }

    private function checkAdminUser($email)
    {
        $this->info("👤 Checking Admin User: {$email}");
        
        try {
            $admin = Admin::where('email', $email)->first();
            
            if (!$admin) {
                $this->error("❌ Admin with email '{$email}' not found");
                return;
            }

            $this->table(['Field', 'Value'], [
                ['ID', $admin->id],
                ['Name', $admin->name],
                ['Email', $admin->email],
                ['Email Verified', $admin->email_verified_at ? '✅ Yes' : '❌ No'],
                ['Created', $admin->created_at],
                ['Updated', $admin->updated_at],
                ['Organization ID', $admin->organization_id ?? 'None'],
                ['Branch ID', $admin->branch_id ?? 'None'],
                ['Is Super Admin', $admin->is_super_admin ? '✅ Yes' : '❌ No'],
            ]);

        } catch (\Exception $e) {
            $this->error('❌ Error checking admin user: ' . $e->getMessage());
        }
    }

    private function runFullDiagnostic()
    {
        $this->info('🔍 Running Full Diagnostic');
        $this->newLine();
        
        $this->checkConfiguration();
        $this->newLine();
        
        $this->info('👥 Admin Users Summary');
        try {
            $adminCount = Admin::count();
            $superAdminCount = Admin::where('is_super_admin', true)->count();
            
            $this->table(['Metric', 'Count'], [
                ['Total Admins', $adminCount],
                ['Super Admins', $superAdminCount],
            ]);

            if ($adminCount > 0) {
                $this->info('Recent Admin Logins:');
                $recentAdmins = Admin::orderBy('updated_at', 'desc')->limit(5)->get();
                $this->table(['Name', 'Email', 'Last Updated'], 
                    $recentAdmins->map(fn($admin) => [
                        $admin->name, 
                        $admin->email, 
                        $admin->updated_at->diffForHumans()
                    ])->toArray()
                );
            }
        } catch (\Exception $e) {
            $this->error('❌ Error getting admin statistics: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('💡 Recommended Actions:');
        $this->line('1. Check browser console for JavaScript errors');
        $this->line('2. Clear browser cache and cookies');
        $this->line('3. Verify admin credentials are correct');
        $this->line('4. Check Laravel logs in storage/logs/');
        $this->line('5. Use --fix-sessions to clear stale sessions');
    }

    private function checkSessions()
    {
        $this->info('🔍 Checking Session Configuration and Status');
        
        try {
            $sessionConfig = [
                'Driver' => config('session.driver'),
                'Lifetime' => config('session.lifetime') . ' minutes',
                'Table' => config('session.table'),
                'Cookie Name' => config('session.cookie'),
                'Encrypt' => config('session.encrypt') ? 'Yes' : 'No',
                'HTTP Only' => config('session.http_only') ? 'Yes' : 'No',
                'Same Site' => config('session.same_site'),
                'Secure' => config('session.secure') ? 'Yes' : 'No',
            ];

            $this->table(['Setting', 'Value'], 
                collect($sessionConfig)->map(fn($value, $key) => [$key, $value])->toArray()
            );

            // Check session table if using database driver
            if (config('session.driver') === 'database') {
                $this->newLine();
                $this->info('📊 Database Session Statistics');
                
                $sessionTable = config('session.table', 'sessions');
                
                if (Schema::hasTable($sessionTable)) {
                    $totalSessions = DB::table($sessionTable)->count();
                    $activeSessions = DB::table($sessionTable)
                        ->where('last_activity', '>', now()->subMinutes(config('session.lifetime', 120))->timestamp)
                        ->count();
                    $expiredSessions = $totalSessions - $activeSessions;

                    $this->table(['Metric', 'Count'], [
                        ['Total Sessions', $totalSessions],
                        ['Active Sessions', $activeSessions],
                        ['Expired Sessions', $expiredSessions],
                    ]);

                    // Show recent sessions
                    if ($totalSessions > 0) {
                        $this->newLine();
                        $this->info('Recent Sessions (last 5):');
                        $recentSessions = DB::table($sessionTable)
                            ->orderBy('last_activity', 'desc')
                            ->limit(5)
                            ->get(['id', 'user_id', 'ip_address', 'last_activity']);
                        
                        $sessionData = $recentSessions->map(function($session) {
                            return [
                                substr($session->id, 0, 10) . '...',
                                $session->user_id ?? 'Guest',
                                $session->ip_address ?? 'Unknown',
                                date('Y-m-d H:i:s', $session->last_activity)
                            ];
                        })->toArray();

                        $this->table(['Session ID', 'User ID', 'IP Address', 'Last Activity'], $sessionData);
                    }
                } else {
                    $this->error("❌ Sessions table '{$sessionTable}' does not exist");
                }
            } else {
                $this->info('📝 File-based sessions - checking session directory...');
                $sessionPath = config('session.files');
                if (is_dir($sessionPath)) {
                    $files = glob($sessionPath . '/sess_*');
                    $this->info("Session files found: " . count($files));
                } else {
                    $this->error("❌ Session directory does not exist: {$sessionPath}");
                }
            }

        } catch (\Exception $e) {
            $this->error('❌ Error checking sessions: ' . $e->getMessage());
        }
    }
}
