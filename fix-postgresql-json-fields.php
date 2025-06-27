<?php

/**
 * Fix PostgreSQL JSON Field Compatibility
 * This script will fix all PHP array assignments to JSON fields in seeder files
 */

$seederFiles = [
    'database/seeders/ExhaustiveSubscriptionSeeder.php',
    'database/seeders/ExhaustiveUserPermissionSeeder.php',
    'database/seeders/ExhaustiveMenuSeeder.php',
    'database/seeders/ExhaustiveInventorySeeder.php',
    'database/seeders/ExhaustiveOrderSeeder.php',
    'database/seeders/ExhaustiveReservationSeeder.php',
    'database/seeders/ExhaustiveOrganizationSeeder.php',
    'database/seeders/ExhaustiveBranchSeeder.php',
    'database/seeders/ExhaustiveRoleSeeder.php',
    'database/seeders/ExhaustiveKitchenWorkflowSeeder.php',
    'database/seeders/ExhaustiveEdgeCaseSeeder.php',
    'database/seeders/ExhaustiveValidationSeeder.php',
];

$jsonFields = [
    'modules',
    'features',
    'restrictions',
    'permissions',
    'skills',
    'certifications',
    'preferences',
    'settings',
    'attributes',
    'configuration',
    'metadata',
    'plan_snapshot',
    'allergens',
    'nutrition_info',
    'special_instructions',
    'customizations',
    'order_modifications',
    'inventory_alerts',
    'notifications',
    'workflow_steps',
    'kitchen_instructions'
];

function fixJsonFieldsInFile($filePath, $jsonFields)
{
    if (!file_exists($filePath)) {
        echo "⚠️  File not found: $filePath\n";
        return false;
    }

    $content = file_get_contents($filePath);
    $originalContent = $content;
    $changes = 0;

    foreach ($jsonFields as $field) {
        // Pattern 1: 'field_name' => [
        $pattern1 = "/('$field'\s*=>\s*)\[/m";
        if (preg_match($pattern1, $content)) {
            $content = preg_replace_callback(
                "/('$field'\s*=>\s*)\[(.*?)\]/ms",
                function($matches) {
                    return $matches[1] . 'json_encode([' . $matches[2] . '])';
                },
                $content
            );
            $changes++;
        }

        // Pattern 2: "field_name" => [
        $pattern2 = "/(\"$field\"\s*=>\s*)\[/m";
        if (preg_match($pattern2, $content)) {
            $content = preg_replace_callback(
                "/(\"$field\"\s*=>\s*)\[(.*?)\]/ms",
                function($matches) {
                    return $matches[1] . 'json_encode([' . $matches[2] . '])';
                },
                $content
            );
            $changes++;
        }
    }

    if ($changes > 0 && $content !== $originalContent) {
        // Create backup
        $backupPath = $filePath . '.backup.' . date('Y-m-d-H-i-s');
        file_put_contents($backupPath, $originalContent);
        
        // Write fixed content
        file_put_contents($filePath, $content);
        echo "✅ Fixed $changes JSON field assignments in $filePath\n";
        echo "   📁 Backup created: $backupPath\n";
        return true;
    } else {
        echo "ℹ️  No JSON field fixes needed in $filePath\n";
        return false;
    }
}

echo "🔧 PostgreSQL JSON Field Compatibility Fixer\n";
echo "═══════════════════════════════════════════════\n\n";

$totalFixed = 0;
$totalFiles = 0;

foreach ($seederFiles as $seederFile) {
    $fullPath = __DIR__ . '/' . $seederFile;
    echo "🔍 Checking: $seederFile\n";
    
    if (fixJsonFieldsInFile($fullPath, $jsonFields)) {
        $totalFixed++;
    }
    $totalFiles++;
    echo "\n";
}

echo "📊 SUMMARY\n";
echo "═══════════════════════════════════════════════\n";
echo "Files checked: $totalFiles\n";
echo "Files fixed: $totalFixed\n";

if ($totalFixed > 0) {
    echo "\n🎯 All JSON field assignments have been fixed for PostgreSQL compatibility!\n";
    echo "You can now run the exhaustive seeder:\n";
    echo "   php artisan db:seed --class=ExhaustiveSystemSeeder\n";
} else {
    echo "\n✅ All files are already PostgreSQL compatible!\n";
}
