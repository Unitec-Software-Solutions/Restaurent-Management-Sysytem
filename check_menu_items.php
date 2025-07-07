<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MenuItem;

echo "Checking Menu Items in Database:\n";
echo "================================\n";

$items = MenuItem::select('id', 'name', 'is_active', 'branch_id')->get();

if ($items->count() === 0) {
    echo "No menu items found in database!\n";
} else {
    foreach ($items as $item) {
        echo "ID: {$item->id} - {$item->name} (Branch: {$item->branch_id}, Active: " . ($item->is_active ? 'Yes' : 'No') . ")\n";
    }
}

echo "\nTotal menu items: " . $items->count() . "\n";
