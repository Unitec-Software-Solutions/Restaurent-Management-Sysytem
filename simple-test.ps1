# Simple Database Validation Test Script
Write-Host "🌱 Database Seeder Validation System Test" -ForegroundColor Green
Write-Host "=======================================" -ForegroundColor Green
Write-Host ""

# Test 1: Check commands are available
Write-Host "📋 Test 1: Command Availability" -ForegroundColor Cyan
Write-Host "Commands available:" -ForegroundColor White
php artisan list | findstr "db:"

Write-Host ""
Write-Host "📋 Test 2: Integrity Check" -ForegroundColor Cyan
php artisan db:integrity-check

Write-Host ""
Write-Host "📋 Test 3: Database Status" -ForegroundColor Cyan
Write-Host "Organizations:" -ForegroundColor White
php artisan tinker --execute="echo 'Organizations: ' . App\Models\Organization::count()"

Write-Host "Branches:" -ForegroundColor White  
php artisan tinker --execute="echo 'Branches: ' . App\Models\Branch::count()"

Write-Host "Kitchen Stations:" -ForegroundColor White
php artisan tinker --execute="echo 'Kitchen Stations: ' . App\Models\KitchenStation::count()"

Write-Host ""
Write-Host "✅ Test Complete!" -ForegroundColor Green
