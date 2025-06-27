@echo off
echo.
echo 🔍 Admin Authentication Quick Test
echo ==========================================
echo.

echo 1. Testing authentication configuration...
php artisan admin:troubleshoot-auth --check-config
echo.

echo 2. Checking critical routes...
echo Inventory Dashboard Route:
php artisan route:list --name="admin.inventory.dashboard"
echo.
echo Suppliers Index Route:
php artisan route:list --name="admin.suppliers.index"
echo.

echo 3. Testing database connectivity...
php artisan tinker --execute="echo 'Admin count: ' . App\Models\Admin::count(); echo PHP_EOL . 'Session table exists: ' . (Schema::hasTable('sessions') ? 'Yes' : 'No');"
echo.

echo 4. Verifying middleware registration...
echo Checking if debug middleware is registered...
php artisan route:list --name="admin.dashboard" | findstr "auth:admin"
echo.

echo ✅ Quick test complete!
echo.
echo 📝 Next steps:
echo 1. Start server: php artisan serve
echo 2. Visit: http://localhost:8000/admin/login
echo 3. Test inventory and supplier navigation
echo 4. If issues persist, run: .\verify-admin-auth.ps1
echo.
