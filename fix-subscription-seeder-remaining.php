<?php

/**
 * Fix remaining array assignments in ExhaustiveSubscriptionSeeder
 */

$seederFile = 'database/seeders/ExhaustiveSubscriptionSeeder.php';

if (!file_exists($seederFile)) {
    die("Seeder file not found: $seederFile\n");
}

$content = file_get_contents($seederFile);

// Fix restrictions arrays that are not JSON encoded
$content = preg_replace(
    "/'restrictions'\s*=>\s*\[\s*\],/",
    "'restrictions' => json_encode([]),",
    $content
);

$content = preg_replace(
    "/'restrictions'\s*=>\s*\[\s*([^\]]+)\s*\],/",
    "'restrictions' => json_encode([$1]),",
    $content
);

// Fix billing_cycle null values
$content = str_replace(
    "'billing_cycle' => null,",
    "'billing_cycle' => 'one_time',",
    $content
);

// Write the fixed content
file_put_contents($seederFile, $content);

echo "Fixed remaining array assignments in ExhaustiveSubscriptionSeeder\n";
echo "Changes made:\n";
echo "- Fixed restrictions array assignments\n";
echo "- Replaced null billing_cycle with 'one_time'\n";
