# Database Seeder Validation & Error Resolution System
# Comprehensive Testing and Validation Commands

Write-Host "🌱 Database Seeder Validation System" -ForegroundColor Green
Write-Host "=" * 50 -ForegroundColor Green
Write-Host ""

# Function to run commands with error handling
function Invoke-SafeCommand {
    param(
        [string]$Command,
        [string]$Description,
        [switch]$ContinueOnError
    )
    
    Write-Host "📋 $Description" -ForegroundColor Cyan
    Write-Host "-" * 30 -ForegroundColor DarkGray
    Write-Host "Command: $Command" -ForegroundColor DarkGray
    Write-Host ""
    
    try {
        Invoke-Expression $Command
        Write-Host "✅ Success: $Description" -ForegroundColor Green
    }
    catch {
        Write-Host "❌ Failed: $Description" -ForegroundColor Red
        Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
        if (-not $ContinueOnError) {
            exit 1
        }
    }
    Write-Host ""
}

# Test 1: Check Laravel Application Status
Invoke-SafeCommand -Command "php artisan --version" -Description "Laravel Version Check" -ContinueOnError

# Test 2: Run our custom validation test script
Invoke-SafeCommand -Command "php test-seeder-validation.php" -Description "Service Registration & Method Availability Test" -ContinueOnError

# Test 3: Check if migrations are up to date
Invoke-SafeCommand -Command "php artisan migrate:status" -Description "Migration Status Check" -ContinueOnError

# Test 4: Run database integrity check command
Write-Host "📋 Database Integrity Check" -ForegroundColor Cyan
Write-Host "-" * 30 -ForegroundColor DarkGray
Write-Host "This command checks for constraint violations, orphaned records, and data integrity issues."
Write-Host ""
Invoke-SafeCommand -Command "php artisan db:integrity-check" -Description "Comprehensive Database Integrity Check" -ContinueOnError

# Test 5: Test safe seeding with dry run
Write-Host "📋 Safe Seeding Dry Run Test" -ForegroundColor Cyan
Write-Host "-" * 30 -ForegroundColor DarkGray
Write-Host "This will show what issues would be fixed without making changes."
Write-Host ""
Invoke-SafeCommand -Command "php artisan db:seed-safe --dry-run --auto-fix" -Description "Safe Seeding Dry Run with Auto-fix Preview" -ContinueOnError

# Test 6: Test safe seeding with validation report
Write-Host "📋 Safe Seeding with Report Generation" -ForegroundColor Cyan
Write-Host "-" * 30 -ForegroundColor DarkGray
Write-Host "This will generate a detailed validation report."
Write-Host ""
Invoke-SafeCommand -Command "php artisan db:seed-safe --report" -Description "Safe Seeding with Validation Report" -ContinueOnError

Write-Host ""
Write-Host "🎯 Advanced Testing Commands" -ForegroundColor Yellow
Write-Host "=" * 50 -ForegroundColor Yellow

# Advanced Test 1: Test specific seeder with auto-fix
Write-Host ""
Write-Host "1. Test KitchenStationSeeder specifically with auto-fix:"
Write-Host "   php artisan db:seed-safe --class=KitchenStationSeeder --auto-fix" -ForegroundColor White

# Advanced Test 2: Force seeding despite warnings
Write-Host ""
Write-Host "2. Force seeding despite validation warnings:"
Write-Host "   php artisan db:seed-safe --force" -ForegroundColor White

# Advanced Test 3: Full validation and auto-fix workflow
Write-Host ""
Write-Host "3. Complete validation and fix workflow:"
Write-Host "   php artisan db:integrity-check" -ForegroundColor White
Write-Host "   php artisan db:seed-safe --auto-fix --report" -ForegroundColor White

# Advanced Test 4: Manual validation steps
Write-Host ""
Write-Host "4. Manual validation steps:" -ForegroundColor Cyan
Write-Host "   a) Check migration status: php artisan migrate:status" -ForegroundColor White
Write-Host "   b) Run integrity check: php artisan db:integrity-check" -ForegroundColor White
Write-Host "   c) Preview fixes: php artisan db:seed-safe --dry-run --auto-fix" -ForegroundColor White
Write-Host "   d) Apply fixes: php artisan db:seed-safe --auto-fix" -ForegroundColor White
Write-Host "   e) Generate report: php artisan db:seed-safe --report" -ForegroundColor White

Write-Host ""
Write-Host "📊 Validation Report Locations" -ForegroundColor Magenta
Write-Host "=" * 50 -ForegroundColor Magenta
Write-Host "Reports are saved to: storage/logs/" -ForegroundColor White
Write-Host "- Seeding validation reports: seeding-validation-report-*.json" -ForegroundColor White
Write-Host "- Error logs: laravel.log" -ForegroundColor White
Write-Host "- Integrity check reports: integrity-check-*.json" -ForegroundColor White

Write-Host ""
Write-Host "🔧 Troubleshooting Commands" -ForegroundColor Red
Write-Host "=" * 50 -ForegroundColor Red

Write-Host ""
Write-Host "If you encounter issues:" -ForegroundColor Yellow

Write-Host ""
Write-Host "1. Clear application cache:"
Write-Host "   php artisan config:clear" -ForegroundColor White
Write-Host "   php artisan cache:clear" -ForegroundColor White
Write-Host "   php artisan route:clear" -ForegroundColor White

Write-Host ""
Write-Host "2. Re-run migrations:"
Write-Host "   php artisan migrate:fresh" -ForegroundColor White

Write-Host ""
Write-Host "3. Check database connection:"
Write-Host "   php artisan tinker" -ForegroundColor White
Write-Host "   DB::connection()->getPdo();" -ForegroundColor White

Write-Host ""
Write-Host "4. Check service registration:"
Write-Host "   php artisan list | grep -E 'db:(seed-safe|integrity-check)'" -ForegroundColor White

Write-Host ""
Write-Host "5. View detailed logs:"
Write-Host "   tail -f storage/logs/laravel.log" -ForegroundColor White

Write-Host ""
Write-Host "✅ Testing Script Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Cyan
Write-Host "1. Review any error messages above" -ForegroundColor White
Write-Host "2. Run the suggested commands to test specific functionality" -ForegroundColor White
Write-Host "3. Check generated reports in storage/logs/" -ForegroundColor White
Write-Host "4. Use --auto-fix option for automated issue resolution" -ForegroundColor White

Write-Host ""
