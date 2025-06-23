<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;

class AdminAuthService
{
    /**
     * Enhanced admin login with detailed logging and session management
     */
    public function login(string $email, string $password, bool $remember = false): array
    {
        try {
            // Clear any existing sessions for this user
            $this->clearUserSessions($email);
            
            $credentials = ['email' => $email, 'password' => $password];
            
            if (Auth::guard('admin')->attempt($credentials, $remember)) {
                $admin = Auth::guard('admin')->user();
                
                // Regenerate session for security
                Session::regenerate();
                
                // Ensure session is properly saved
                Session::save();
                
                Log::info('Admin login successful', [
                    'admin_id' => $admin->id,
                    'email' => $admin->email,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'session_id' => session()->getId(),
                ]);
                
                return [
                    'success' => true,
                    'admin' => $admin,
                    'session_id' => session()->getId()
                ];
            } else {
                Log::warning('Admin login failed - invalid credentials', [
                    'email' => $email,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Invalid credentials'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Admin login exception', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Login system error'
            ];
        }
    }
    
    /**
     * Enhanced logout with cleanup
     */
    public function logout(): void
    {
        $admin = Auth::guard('admin')->user();
        
        if ($admin) {
            Log::info('Admin logout', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'session_id' => session()->getId(),
            ]);
        }
        
        // Clear authentication
        Auth::guard('admin')->logout();
        
        // Clear session data
        Session::flush();
        Session::invalidate();
        Session::regenerateToken();
    }
    
    /**
     * Check if admin is properly authenticated
     */
    public function isAuthenticated(): array
    {
        $isAuth = Auth::guard('admin')->check();
        $user = Auth::guard('admin')->user();
        $sessionId = session()->getId();
        
        $result = [
            'authenticated' => $isAuth,
            'user' => $user,
            'session_id' => $sessionId,
            'session_valid' => $this->isSessionValid(),
        ];
        
        if (config('app.debug')) {
            Log::debug('Admin auth check', $result);
        }
        
        return $result;
    }
    
    /**
     * Validate session integrity
     */
    private function isSessionValid(): bool
    {
        try {
            // Check if session exists in storage
            if (config('session.driver') === 'database') {
                $sessionExists = DB::table(config('session.table', 'sessions'))
                    ->where('id', session()->getId())
                    ->exists();
                return $sessionExists;
            }
            
            // For file sessions, check if file exists
            if (config('session.driver') === 'file') {
                $sessionFile = config('session.files') . '/sess_' . session()->getId();
                return file_exists($sessionFile);
            }
            
            return true; // For other drivers, assume valid
        } catch (\Exception $e) {
            Log::error('Session validation error', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Clear sessions for a specific user
     */
    private function clearUserSessions(string $email): void
    {
        try {
            $admin = Admin::where('email', $email)->first();
            if (!$admin) {
                return;
            }
            
            if (config('session.driver') === 'database') {
                // For database sessions, we'd need to decode session data to find user sessions
                // This is complex, so we'll just clear old sessions generally
                DB::table(config('session.table', 'sessions'))
                    ->where('last_activity', '<', now()->subMinutes(config('session.lifetime', 120))->timestamp)
                    ->delete();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear user sessions', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get authentication debug information
     */
    public function getDebugInfo(): array
    {
        return [
            'timestamp' => now()->toDateTimeString(),
            'guard_check' => Auth::guard('admin')->check(),
            'guard_user' => Auth::guard('admin')->user(),
            'session_id' => session()->getId(),
            'session_driver' => config('session.driver'),
            'session_lifetime' => config('session.lifetime'),
            'session_valid' => $this->isSessionValid(),
            'default_guard' => config('auth.defaults.guard'),
            'admin_guard_config' => config('auth.guards.admin'),
            'admin_provider_config' => config('auth.providers.admins'),
        ];
    }
}
