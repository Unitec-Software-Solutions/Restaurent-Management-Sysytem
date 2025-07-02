@echo off
REM Restaurant Management System - Comprehensive Seeding and Testing Script
REM ========================================================================

echo 🍽️  Restaurant Management System - Comprehensive Setup
echo ========================================================

REM Set error handling
setlocal enabledelayedexpansion

REM Check if we're in the right directory
if not exist "artisan" (
    echo ❌ Error: artisan file not found. Please run this script from the Laravel project root.
    pause
    exit /b 1
)

REM Main menu
:MAIN_MENU
cls
echo.
echo 🚀 Restaurant Management System - Setup Options
echo ================================================
echo.
echo 1. Fresh Setup (Drop DB + Migrate + Seed)
echo 2. Seed Only (Keep existing data)
echo 3. Run Comprehensive Test Suite
echo 4. Quick Verification Tests
echo 5. Performance Profiling
echo 6. Full Pipeline (Fresh + Seed + Test)
echo 7. Exit
echo.
set /p choice="Select an option (1-7): "

if "%choice%"=="1" goto FRESH_SETUP
if "%choice%"=="2" goto SEED_ONLY
if "%choice%"=="3" goto TEST_SUITE
if "%choice%"=="4" goto QUICK_VERIFY
if "%choice%"=="5" goto PERFORMANCE
if "%choice%"=="6" goto FULL_PIPELINE
if "%choice%"=="7" goto EXIT

echo Invalid choice. Please try again.
timeout /t 2 > nul
goto MAIN_MENU

:FRESH_SETUP
echo.
echo 🔄 Running Fresh Setup...
echo ========================
echo ⚠️  This will DROP ALL TABLES and recreate them.
set /p confirm="Are you sure? (Y/N): "
if /i not "%confirm%"=="Y" goto MAIN_MENU

echo 📊 Starting fresh migration and seeding...
php artisan seed:comprehensive --fresh --verify --profile
if errorlevel 1 (
    echo ❌ Fresh setup failed!
    pause
    goto MAIN_MENU
)
echo ✅ Fresh setup completed successfully!
pause
goto MAIN_MENU

:SEED_ONLY
echo.
echo 🌱 Seeding Comprehensive Test Data...
echo ====================================
php artisan seed:comprehensive --verify --profile
if errorlevel 1 (
    echo ❌ Seeding failed!
    pause
    goto MAIN_MENU
)
echo ✅ Seeding completed successfully!
pause
goto MAIN_MENU

:TEST_SUITE
echo.
echo 🧪 Running Comprehensive Test Suite...
echo =====================================
php artisan test:seeded-data --coverage
if errorlevel 1 (
    echo ❌ Some tests failed!
    echo 📋 Check the coverage report in tests/coverage/index.html
    pause
    goto MAIN_MENU
)
echo ✅ All tests passed!
echo 📋 Coverage report generated in tests/coverage/index.html
pause
goto MAIN_MENU

:QUICK_VERIFY
echo.
echo 🔍 Running Quick Verification...
echo ===============================
php artisan test:seeded-data --filter=Integration
if errorlevel 1 (
    echo ❌ Verification failed!
    pause
    goto MAIN_MENU
)
echo ✅ Quick verification passed!
pause
goto MAIN_MENU

:PERFORMANCE
echo.
echo ⚡ Running Performance Profiling...
echo ==================================
echo 📊 Testing with profiling enabled...
php artisan seed:comprehensive --profile
php artisan test:seeded-data --group=performance
echo ✅ Performance profiling completed!
pause
goto MAIN_MENU

:FULL_PIPELINE
echo.
echo 🎯 Running Full CI/CD Pipeline...
echo ================================
echo ⚠️  This will run the complete setup and testing pipeline.
set /p confirm="Continue? (Y/N): "
if /i not "%confirm%"=="Y" goto MAIN_MENU

echo 📊 Step 1/3: Fresh Setup...
php artisan seed:comprehensive --fresh --profile
if errorlevel 1 (
    echo ❌ Pipeline failed at setup step!
    pause
    goto MAIN_MENU
)

echo 📊 Step 2/3: Running Test Suite...
php artisan test:seeded-data --coverage
if errorlevel 1 (
    echo ❌ Pipeline failed at testing step!
    pause
    goto MAIN_MENU
)

echo 📊 Step 3/3: Final Verification...
php artisan test --group=integration
if errorlevel 1 (
    echo ❌ Pipeline failed at verification step!
    pause
    goto MAIN_MENU
)

echo 🎉 Full pipeline completed successfully!
echo 📋 Coverage report: tests/coverage/index.html
echo 🚀 Your system is ready for production!
pause
goto MAIN_MENU

:EXIT
echo.
echo 👋 Thanks for using the Restaurant Management System setup!
echo 📚 Documentation: README.md
echo 🐛 Issues: Check storage/logs/laravel.log
echo.
exit /b 0
