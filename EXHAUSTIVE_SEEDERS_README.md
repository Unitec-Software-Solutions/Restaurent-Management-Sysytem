# Exhaustive Restaurant Management System Seeders

This comprehensive seeding system creates realistic test data covering all possible scenarios for a restaurant management system. It includes subscription plan variations, organization types, user permissions, menu configurations, order lifecycles, inventory management, and reservation handling.

## 📋 Overview

The seeding system is designed to create realistic data distributions that mirror real-world restaurant operations, from single-location cafés to multi-branch franchise operations.

### 🎯 Coverage Areas

1. **Subscription Plan Scenarios**
   - Basic/Freemium plans with limited modules
   - Premium plans with all modules
   - Expired/disabled plans
   - Plan upgrade/downgrade cases

2. **Organization Onboarding Variations**
   - Single-branch operations
   - Multi-branch franchises
   - Organizations with different subscription plans
   - Organizations with expired subscriptions

3. **Branch Creation Cases**
   - Head office branches
   - Regular branches
   - Temporary/seasonal branches
   - Branches with custom kitchen stations

4. **User Permission Instances**
   - SuperAdmin with full access
   - OrgAdmin with multi-branch access
   - BranchAdmin with single-branch access
   - Staff with mixed permissions
   - Guests with order/reservation capabilities

5. **Menu Configuration Examples**
   - Daily rotating menus (Mon-Sun)
   - Special event menus
   - Time-based availability (breakfast/lunch/dinner)
   - Menu versioning with recipe changes

6. **Order Lifecycle Scenarios**
   - Guest orders with cart functionality
   - Staff-managed orders
   - Orders with partial fulfillment
   - Cancelled/refunded orders
   - Orders with special requests

7. **Inventory Edge Cases**
   - Low stock scenarios
   - Stock adjustments/waste tracking
   - Supplier deliveries
   - Multi-branch transfers

8. **Reservation Complexities**
   - Date conflicts
   - Large group reservations
   - Recurring reservations
   - No-shows and cancellations

## 🗂️ Seeder Structure

### Main Orchestration
- `ExhaustiveSystemSeeder.php` - Main orchestration seeder that runs all scenarios in phases

### Core Infrastructure Seeders
- `ExhaustiveSubscriptionSeeder.php` - Subscription plan variations
- `ExhaustiveOrganizationSeeder.php` - Organization onboarding scenarios
- `ExhaustiveBranchSeeder.php` - Branch creation and configuration

### User Management Seeders
- `ExhaustiveUserPermissionSeeder.php` - User permission hierarchies
- `ExhaustiveRoleSeeder.php` - Role and permission management

### Business Operations Seeders
- `ExhaustiveMenuSeeder.php` - Menu configuration scenarios
- `ExhaustiveOrderSeeder.php` - Order lifecycle management
- `ExhaustiveInventorySeeder.php` - Inventory edge cases
- `ExhaustiveReservationSeeder.php` - Reservation complexities

### System Integration Seeders
- `ExhaustiveKitchenWorkflowSeeder.php` - Kitchen operations and workflows
- `ExhaustiveEdgeCaseSeeder.php` - System boundary and edge case testing
- `ExhaustiveValidationSeeder.php` - Automated verification and validation

## 🚀 Usage

### Quick Start

```bash
# Run the complete seeding system
php artisan db:seed --class=ExhaustiveSystemSeeder
```

### With Fresh Migration

```bash
# Reset database and run comprehensive seeding
php artisan migrate:fresh --seed --seeder=ExhaustiveSystemSeeder
```

### Using Test Scripts

#### Unix/Linux/Mac
```bash
# Make executable and run
chmod +x test-comprehensive-seeding.php
./test-comprehensive-seeding.php
```

#### Windows PowerShell
```powershell
# Run with automatic confirmation
.\test-comprehensive-seeding.ps1 -Force

# Run with skip confirmation
.\test-comprehensive-seeding.ps1 -SkipConfirmation
```

## 📊 Expected Data Volumes

After running the exhaustive seeding system, you should expect approximately:

- **Subscription Plans**: 8-12 different plans
- **Organizations**: 15-20 organizations of various types
- **Branches**: 25-40 branches across all organizations
- **Users & Admins**: 80-120 user accounts with various roles
- **Menu Items**: 200-400 menu items with variations
- **Orders**: 150-300 orders with different statuses
- **Reservations**: 100-200 reservations with various scenarios
- **Inventory Items**: 300-500 inventory items with edge cases
- **Kitchen Stations**: 40-80 stations across all branches

## 🔍 Validation Features

The seeding system includes built-in validation to ensure:

- **Data Integrity**: All foreign key relationships are maintained
- **Business Logic**: Realistic scenarios that follow business rules
- **Edge Cases**: Boundary conditions and error scenarios
- **Performance**: Reasonable data volumes for testing
- **Consistency**: Coherent data across all modules

## 🛠️ Customization

### Adding New Scenarios

1. Create a new seeder class extending `Illuminate\Database\Seeder`
2. Implement comprehensive scenarios in the `run()` method
3. Add your seeder to `ExhaustiveSystemSeeder.php`
4. Update the test scripts to validate your new scenarios

### Modifying Data Volumes

You can adjust data volumes by modifying the loop counts and random ranges in individual seeders. Look for:

```php
for ($i = 0; $i < rand(min, max); $i++) {
    // Seeding logic
}
```

### Custom Business Rules

Each seeder includes business logic specific to restaurant operations. You can modify these rules to match your specific requirements:

- **Subscription limits** in `ExhaustiveSubscriptionSeeder.php`
- **Branch types** in `ExhaustiveBranchSeeder.php`
- **Menu patterns** in `ExhaustiveMenuSeeder.php`
- **Order workflows** in `ExhaustiveOrderSeeder.php`

## 🔧 Troubleshooting

### Common Issues

1. **Foreign Key Constraints**
   - The system disables foreign key checks during seeding
   - If you encounter issues, check your model relationships

2. **Memory Limits**
   - Large datasets may require increased PHP memory limits
   - Use `ini_set('memory_limit', '512M')` if needed

3. **Execution Time**
   - Complete seeding may take 2-5 minutes
   - Use `set_time_limit(0)` for unlimited execution time

### Performance Optimization

- **Batch Inserts**: Use `DB::table()->insert()` for large datasets
- **Disable Timestamps**: Use `timestamps = false` during bulk operations
- **Index Optimization**: Ensure proper database indexes exist

## 📝 Data Quality Features

### Realistic Data
- **Sri Lankan Context**: Names, addresses, and business types reflect local context
- **Seasonal Patterns**: Menu items and inventory reflect seasonal availability
- **Business Hours**: Realistic operating hours and schedules
- **Currency**: Local currency formatting and amounts

### Edge Case Coverage
- **Boundary Testing**: Min/max values for all numeric fields
- **State Transitions**: All possible status changes
- **Concurrent Operations**: Overlapping reservations and resource conflicts
- **Data Validation**: Input validation and constraint testing

## 🎯 Testing Scenarios

The seeded data enables testing of:

### Functional Testing
- User authentication and authorization
- Order processing workflows
- Inventory management operations
- Reservation handling

### Performance Testing
- Database query optimization
- Large dataset handling
- Concurrent user operations
- Report generation speed

### Integration Testing
- Multi-branch operations
- Cross-module data consistency
- External API integrations
- Payment processing workflows

## 📈 Reporting and Analytics

The seeded data includes scenarios for testing:

- **Financial Reports**: Revenue, costs, and profitability analysis
- **Operational Reports**: Order volumes, popular items, and peak times
- **Inventory Reports**: Stock levels, turnover rates, and supplier performance
- **Staff Reports**: Performance metrics and scheduling optimization

## 🔒 Security Testing

The seeding system creates scenarios for testing:

- **Permission Boundaries**: Users attempting unauthorized actions
- **Data Isolation**: Multi-tenant data separation
- **Audit Trails**: Change tracking and accountability
- **Input Validation**: SQL injection and XSS prevention

## 🚀 Getting Started

1. **Prerequisites**: Ensure Laravel environment is properly configured
2. **Database Setup**: Run migrations before seeding
3. **Seeding**: Execute the comprehensive seeding system
4. **Validation**: Use provided test scripts to verify results
5. **Testing**: Begin comprehensive system testing with realistic data

For detailed implementation examples and advanced usage patterns, refer to the individual seeder class documentation and inline comments.
