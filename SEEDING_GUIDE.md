# Restaurant Management System - Comprehensive Seeding Guide

## 🎯 Overview

This guide covers the comprehensive automated seeding system designed to populate your restaurant management system with realistic test data. The system creates a complete business scenario with multiple organizations, branches, staff, inventory, orders, and reservations.

## 🏗️ Architecture

### Seeder Hierarchy

```
1. PermissionModuleSubscriptionSeeder    (Foundation - Modules, permissions, plans)
2. SuperAdminOrganizationSeeder         (Super admin + 5 organizations)
3. BranchNetworkSeeder                  (Branch networks + staff + kitchens)
4. InventorySupplierSeeder              (Suppliers + inventory + stock levels)
5. ReservationLifecycleSeeder           (Tables + customers + reservations)
6. OrderSimulationSeeder                (Menu + orders + KOT/billing)
7. GuestActivitySeeder                  (Public interface + guest interactions)
```

### Key Services Used

- **OrganizationAutomationService**: Automates organization setup
- **BranchAutomationService**: Handles branch creation and configuration
- **MenuScheduleService**: Manages menu activation and scheduling
- **Stock Management**: Automated inventory tracking and alerts

## 🚀 Quick Start

### Option 1: PowerShell Script (Recommended)
```powershell
# Run the interactive setup script
.\setup-system.ps1
```

### Option 2: Command Line
```bash
# Fresh setup (drops all tables)
php artisan seed:comprehensive --fresh --verify --profile

# Seed only (keeps existing data)  
php artisan seed:comprehensive --verify --profile

# Run tests against seeded data
php artisan test:seeded-data --coverage
```

### Option 3: Individual Seeders
```bash
# Run in dependency order
php artisan db:seed --class=PermissionModuleSubscriptionSeeder
php artisan db:seed --class=SuperAdminOrganizationSeeder
php artisan db:seed --class=BranchNetworkSeeder
php artisan db:seed --class=InventorySupplierSeeder
php artisan db:seed --class=ReservationLifecycleSeeder
php artisan db:seed --class=OrderSimulationSeeder
php artisan db:seed --class=GuestActivitySeeder
```

## 📊 What Gets Created

### 1. Foundation Data
- **Modules**: Order, Reservation, Inventory, Customer, Menu, Report, Supplier, Production
- **Permissions**: 50+ granular permissions (manage, view, create, update, delete)
- **Subscription Plans**: Basic, Premium, Enterprise with feature matrices
- **System Roles**: Super Admin, Organization Admin, Branch Admin, Staff, Customer

### 2. Organization Structure
- **Super Admin**: super@admin.com / password
- **5 Organizations**: Restaurant chains with different subscription levels
- **15+ Branches**: Head offices + satellite locations
- **50+ Users**: Admins, managers, staff across all levels

### 3. Inventory System
- **100+ Suppliers**: Organization-scoped vendor relationships
- **500+ Inventory Items**: Food, beverages, supplies with variants
- **Stock Levels**: Realistic quantities with low-stock alerts
- **Categories**: Organized item categorization system

### 4. Menu & Kitchen
- **Kitchen Stations**: Prep, Grill, Beverage, Dessert stations per branch
- **Menu Categories**: Appetizers, Mains, Beverages, Desserts
- **200+ Menu Items**: With pricing, descriptions, and availability
- **Scheduled Activation**: Time-based menu availability

### 5. Reservation System
- **Tables**: Various sizes and configurations per branch
- **100+ Customers**: With contact information and preferences
- **150+ Reservations**: Past, current, and future bookings
- **State Transitions**: Pending → Confirmed → Checked-in → Completed

### 6. Order Processing
- **300+ Orders**: Dine-in, takeaway, and delivery orders
- **Order Items**: Detailed line items with quantities and pricing
- **KOT System**: Kitchen Order Tickets with station assignments
- **Billing**: Complete billing and payment tracking
- **Inventory Integration**: Stock deductions on order completion

### 7. Guest Interface
- **Guest Users**: Public-facing customer accounts
- **Menu Browsing**: Public menu access with stock indicators
- **Guest Orders**: Cart functionality and order placement
- **Order Tracking**: Real-time order status updates

## 🧪 Testing Coverage

### Test Scenarios Covered

| Module | Critical Test Cases |
|--------|-------------------|
| **Orders** | Dine-in without reservation rejection, Takeaway inventory deduction, KOT state transitions |
| **Reservations** | Overbooking prevention, Auto-order creation on check-in, Cancellation stock reversal |
| **Inventory** | Stock alert thresholds, Multi-branch transfers, Supplier order fulfillment |
| **Permissions** | Branch admin org-scope restriction, Super admin cross-org access |
| **Multi-tenancy** | Head office vs branch data isolation, Plan feature limitations |

### Running Tests

```bash
# Full test suite with coverage
php artisan test:seeded-data --coverage

# Quick integration tests
php artisan test:seeded-data --filter=Integration

# Performance testing
php artisan test:seeded-data --group=performance
```

## 📈 Performance Metrics

### Expected Seeding Times
- **Fresh Migration**: ~30-60 seconds
- **Foundation Seeding**: ~15-30 seconds
- **Full Data Population**: ~2-5 minutes
- **Test Suite Execution**: ~5-10 minutes

### Memory Usage
- **Peak Memory**: ~256-512 MB
- **Database Size**: ~50-100 MB after full seeding
- **File Cache**: ~10-20 MB

## 🔧 Configuration

### Environment Variables
```env
# Database configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=restaurant_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Seeding configuration
SEEDER_BATCH_SIZE=100
SEEDER_MEMORY_LIMIT=512M
SEEDER_TIMEOUT=300
```

### Customization Options

#### Organization Count
```php
// In SuperAdminOrganizationSeeder.php
$organizationCount = 5; // Change this value
```

#### Inventory Items per Organization
```php
// In InventorySupplierSeeder.php
$itemsPerOrg = 100; // Adjust as needed
```

#### Orders per Branch
```php
// In OrderSimulationSeeder.php
$ordersPerBranch = 20; // Modify for your needs
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Memory Exhaustion
```bash
# Increase PHP memory limit
php -d memory_limit=1G artisan seed:comprehensive
```

#### 2. Database Connection Timeout
```bash
# Increase timeout in config/database.php
'timeout' => 60,
'connect_timeout' => 60,
```

#### 3. Foreign Key Constraints
```bash
# Check seeder dependency order
# Ensure parent records exist before children
```

#### 4. Permission Denied
```bash
# Check storage directory permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### Debug Mode

```bash
# Enable verbose output
php artisan seed:comprehensive --verbose

# Check logs for detailed errors
tail -f storage/logs/laravel.log
```

## 📋 Verification Commands

### Data Integrity Checks
```bash
# Laravel Tinker - Quick checks
php artisan tinker

# Check seeded data counts
>>> DB::table('organizations')->count();
>>> DB::table('branches')->count();
>>> DB::table('orders')->count();

# Verify relationships
>>> $org = App\Models\Organization::first();
>>> $org->branches->count();
>>> $org->suppliers->count();

# Test permissions
>>> $user = App\Models\User::where('email', 'super@admin.com')->first();
>>> $user->hasPermission('manage_orders');
```

### System Health
```bash
# Check for orphaned records
>>> DB::table('branches')->whereNotExists(function($q) {
    $q->select(DB::raw(1))->from('organizations')
      ->whereRaw('organizations.id = branches.organization_id');
})->count();

# Verify stock levels
>>> App\Models\InventoryItem::where('quantity_available', '<', 'min_stock')->count();

# Check reservation conflicts
>>> App\Models\Reservation::checkTableAvailability($tableId, $dateTime);
```

## 🎯 Business Logic Validation

### Order Flow Testing
1. **Dine-in Orders**: Must have valid reservations
2. **Inventory Deduction**: Stock reduces on order completion
3. **KOT Generation**: Kitchen tickets created for food items
4. **Billing**: Bills generated with correct calculations

### Reservation Workflow
1. **Table Availability**: No double bookings allowed
2. **Customer Management**: Customer records linked to reservations
3. **State Transitions**: Proper flow through reservation states
4. **Staff Assignment**: Stewards assigned to reservations

### Inventory Management
1. **Stock Alerts**: Low stock triggers notifications
2. **Supplier Links**: Items connected to appropriate suppliers
3. **Category Organization**: Items properly categorized
4. **Multi-branch**: Stock levels maintained per branch

## 📚 Additional Resources

### Code Examples
- Check `database/seeders/` for implementation details
- Review `app/Services/` for automation service usage
- Examine `tests/` for test case examples

### API Documentation
- Use `php artisan route:list` to see available endpoints
- Test API endpoints with seeded data
- Verify permission-based access control

### Monitoring
- Monitor `storage/logs/laravel.log` during seeding
- Use Laravel Telescope for request debugging
- Check database slow query logs for optimization

---

## 🤝 Contributing

When adding new seeders:

1. **Follow Dependency Order**: Ensure parent entities exist first
2. **Use Automation Services**: Leverage existing services when possible
3. **Add Verification**: Include data integrity checks
4. **Document Changes**: Update this README with new features
5. **Test Integration**: Verify with existing test suite

For questions or issues, check the troubleshooting section or review the existing seeder implementations for guidance.
