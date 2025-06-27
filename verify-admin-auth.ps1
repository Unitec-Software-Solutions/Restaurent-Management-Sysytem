# Admin Authentication Verification Script
# This PowerShell script tests the authentication flow and diagnoses issues

Write-Host "🔍 Admin Authentication Verification Script" -ForegroundColor Blue
Write-Host "=" * 50

# Check if we're in the Laravel project directory
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Error: Please run this script from the Laravel project root directory" -ForegroundColor Red
    exit 1
}

Write-Host "1. 📋 Checking Laravel Configuration..." -ForegroundColor Yellow

# Check authentication configuration
Write-Host "`n   Checking auth configuration..." -ForegroundColor Green
php artisan config:show auth.defaults.guard
php artisan config:show auth.guards.admin
php artisan config:show auth.providers.admins

# Check session configuration
Write-Host "`n   Checking session configuration..." -ForegroundColor Green
php artisan config:show session.driver
php artisan config:show session.lifetime

Write-Host "`n2. 🗄️ Checking Database..." -ForegroundColor Yellow

# Test database connection
Write-Host "`n   Testing database connection..." -ForegroundColor Green
php artisan db:show

# Check if admin table exists and has data
Write-Host "`n   Checking admin table..." -ForegroundColor Green
php artisan tinker --execute="echo 'Admin count: ' . App\Models\Admin::count();"

Write-Host "`n3. 🧹 Clearing Cache and Sessions..." -ForegroundColor Yellow

# Clear various caches
Write-Host "`n   Clearing application cache..." -ForegroundColor Green
php artisan cache:clear

Write-Host "`n   Clearing configuration cache..." -ForegroundColor Green
php artisan config:clear

Write-Host "`n   Clearing route cache..." -ForegroundColor Green
php artisan route:clear

Write-Host "`n   Clearing view cache..." -ForegroundColor Green
php artisan view:clear

Write-Host "`n   Clearing sessions..." -ForegroundColor Green
php artisan admin:troubleshoot-auth --fix-sessions

Write-Host "`n4. 🔧 Running Authentication Diagnostics..." -ForegroundColor Yellow

# Run our custom troubleshooting command
php artisan admin:troubleshoot-auth

Write-Host "`n5. 🌐 Testing Routes..." -ForegroundColor Yellow

# Check if key routes are accessible
Write-Host "`n   Checking route list..." -ForegroundColor Green
php artisan route:list --name="admin.*" | Select-String "admin\.(login|dashboard|inventory)"

Write-Host "`n6. 📝 Checking Logs..." -ForegroundColor Yellow

# Check recent logs
$logFile = "storage/logs/laravel.log"
if (Test-Path $logFile) {
    Write-Host "`n   Recent log entries:" -ForegroundColor Green
    Get-Content $logFile -Tail 10 | ForEach-Object { 
        if ($_ -match "admin|auth|login") {
            Write-Host "   $_" -ForegroundColor Cyan
        }
    }
} else {
    Write-Host "`n   No log file found" -ForegroundColor Yellow
}

Write-Host "`n7. 🎯 Manual Testing Instructions:" -ForegroundColor Yellow
Write-Host "`n   To manually test authentication:" -ForegroundColor Green
Write-Host "   1. Start the development server: php artisan serve" -ForegroundColor White
Write-Host "   2. Visit: http://localhost:8000/admin/login" -ForegroundColor White
Write-Host "   3. Try logging in with admin credentials" -ForegroundColor White
Write-Host "   4. Check browser Network tab for failed requests" -ForegroundColor White
Write-Host "   5. Visit debug endpoint: http://localhost:8000/admin/auth/debug" -ForegroundColor White

Write-Host "`n8. 🔍 Debug URLs (Development Only):" -ForegroundColor Yellow
Write-Host "`n   Authentication Debug: http://localhost:8000/admin/auth/debug" -ForegroundColor Cyan
Write-Host "   Auth Test (requires login): http://localhost:8000/admin/auth/test" -ForegroundColor Cyan
Write-Host "   Add ?debug_auth=1 to any admin URL for detailed auth logging" -ForegroundColor Cyan

Write-Host "`n✅ Verification complete!" -ForegroundColor Green
Write-Host "Check the output above for any issues. If problems persist," -ForegroundColor White
Write-Host "check the Laravel logs and use the debug URLs provided." -ForegroundColor White
