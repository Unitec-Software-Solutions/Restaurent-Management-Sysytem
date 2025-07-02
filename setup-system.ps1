# Restaurant Management System - Comprehensive Setup Script
# =========================================================

Write-Host "🍽️  Restaurant Management System - Comprehensive Setup" -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan

# Check if we're in the right directory
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Error: artisan file not found. Please run this script from the Laravel project root." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

function Show-Menu {
    Clear-Host
    Write-Host ""
    Write-Host "🚀 Restaurant Management System - Setup Options" -ForegroundColor Green
    Write-Host "================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "1. Fresh Setup (Drop DB + Migrate + Seed)" -ForegroundColor Yellow
    Write-Host "2. Seed Only (Keep existing data)" -ForegroundColor Yellow
    Write-Host "3. Run Comprehensive Test Suite" -ForegroundColor Yellow
    Write-Host "4. Quick Verification Tests" -ForegroundColor Yellow
    Write-Host "5. Performance Profiling" -ForegroundColor Yellow
    Write-Host "6. Full Pipeline (Fresh + Seed + Test)" -ForegroundColor Yellow
    Write-Host "7. Exit" -ForegroundColor Yellow
    Write-Host ""
}

function Invoke-FreshSetup {
    Write-Host ""
    Write-Host "🔄 Running Fresh Setup..." -ForegroundColor Cyan
    Write-Host "========================" -ForegroundColor Cyan
    Write-Host "⚠️  This will DROP ALL TABLES and recreate them." -ForegroundColor Yellow
    
    $confirm = Read-Host "Are you sure? (Y/N)"
    if ($confirm -ne "Y" -and $confirm -ne "y") {
        return
    }

    Write-Host "📊 Starting fresh migration and seeding..." -ForegroundColor Blue
    $result = & php artisan seed:comprehensive --fresh --verify --profile
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Fresh setup completed successfully!" -ForegroundColor Green
    } else {
        Write-Host "❌ Fresh setup failed!" -ForegroundColor Red
    }
    
    Read-Host "Press Enter to continue"
}

function Invoke-SeedOnly {
    Write-Host ""
    Write-Host "🌱 Seeding Comprehensive Test Data..." -ForegroundColor Cyan
    Write-Host "====================================" -ForegroundColor Cyan
    
    $result = & php artisan seed:comprehensive --verify --profile
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Seeding completed successfully!" -ForegroundColor Green
    } else {
        Write-Host "❌ Seeding failed!" -ForegroundColor Red
    }
    
    Read-Host "Press Enter to continue"
}

function Invoke-TestSuite {
    Write-Host ""
    Write-Host "🧪 Running Comprehensive Test Suite..." -ForegroundColor Cyan
    Write-Host "=====================================" -ForegroundColor Cyan
    
    $result = & php artisan test:seeded-data --coverage
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ All tests passed!" -ForegroundColor Green
        Write-Host "📋 Coverage report generated in tests/coverage/index.html" -ForegroundColor Blue
    } else {
        Write-Host "❌ Some tests failed!" -ForegroundColor Red
        Write-Host "📋 Check the coverage report in tests/coverage/index.html" -ForegroundColor Blue
    }
    
    Read-Host "Press Enter to continue"
}

function Invoke-QuickVerify {
    Write-Host ""
    Write-Host "🔍 Running Quick Verification..." -ForegroundColor Cyan
    Write-Host "===============================" -ForegroundColor Cyan
    
    $result = & php artisan test:seeded-data --filter=Integration
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Quick verification passed!" -ForegroundColor Green
    } else {
        Write-Host "❌ Verification failed!" -ForegroundColor Red
    }
    
    Read-Host "Press Enter to continue"
}

function Invoke-Performance {
    Write-Host ""
    Write-Host "⚡ Running Performance Profiling..." -ForegroundColor Cyan
    Write-Host "==================================" -ForegroundColor Cyan
    Write-Host "📊 Testing with profiling enabled..." -ForegroundColor Blue
    
    & php artisan seed:comprehensive --profile
    & php artisan test:seeded-data --group=performance
    
    Write-Host "✅ Performance profiling completed!" -ForegroundColor Green
    Read-Host "Press Enter to continue"
}

function Invoke-FullPipeline {
    Write-Host ""
    Write-Host "🎯 Running Full CI/CD Pipeline..." -ForegroundColor Cyan
    Write-Host "================================" -ForegroundColor Cyan
    Write-Host "⚠️  This will run the complete setup and testing pipeline." -ForegroundColor Yellow
    
    $confirm = Read-Host "Continue? (Y/N)"
    if ($confirm -ne "Y" -and $confirm -ne "y") {
        return
    }

    Write-Host "📊 Step 1/3: Fresh Setup..." -ForegroundColor Blue
    & php artisan seed:comprehensive --fresh --profile
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Pipeline failed at setup step!" -ForegroundColor Red
        Read-Host "Press Enter to continue"
        return
    }

    Write-Host "📊 Step 2/3: Running Test Suite..." -ForegroundColor Blue
    & php artisan test:seeded-data --coverage
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Pipeline failed at testing step!" -ForegroundColor Red
        Read-Host "Press Enter to continue"
        return
    }

    Write-Host "📊 Step 3/3: Final Verification..." -ForegroundColor Blue
    & php artisan test --group=integration
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Pipeline failed at verification step!" -ForegroundColor Red
        Read-Host "Press Enter to continue"
        return
    }

    Write-Host "🎉 Full pipeline completed successfully!" -ForegroundColor Green
    Write-Host "📋 Coverage report: tests/coverage/index.html" -ForegroundColor Blue
    Write-Host "🚀 Your system is ready for production!" -ForegroundColor Green
    Read-Host "Press Enter to continue"
}

# Main execution loop
do {
    Show-Menu
    $choice = Read-Host "Select an option (1-7)"
    
    switch ($choice) {
        "1" { Invoke-FreshSetup }
        "2" { Invoke-SeedOnly }
        "3" { Invoke-TestSuite }
        "4" { Invoke-QuickVerify }
        "5" { Invoke-Performance }
        "6" { Invoke-FullPipeline }
        "7" { 
            Write-Host ""
            Write-Host "👋 Thanks for using the Restaurant Management System setup!" -ForegroundColor Green
            Write-Host "📚 Documentation: README.md" -ForegroundColor Blue
            Write-Host "🐛 Issues: Check storage/logs/laravel.log" -ForegroundColor Blue
            Write-Host ""
            exit 0
        }
        default {
            Write-Host "Invalid choice. Please try again." -ForegroundColor Red
            Start-Sleep -Seconds 2
        }
    }
} while ($true)
