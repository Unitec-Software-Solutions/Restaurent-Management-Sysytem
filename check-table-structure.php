<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "📋 ORDER_ITEMS TABLE STRUCTURE\n";
echo "==============================\n";

$columns = Schema::getColumnListing('order_items');

foreach ($columns as $column) {
    echo "• $column\n";
}

echo "\n🔍 CHECKING FOR REQUIRED FIELDS...\n";
$requiredFields = ['item_name', 'organization_id', 'order_date'];

foreach ($requiredFields as $field) {
    if (in_array($field, $columns)) {
        echo "✅ $field exists\n";
    } else {
        echo "❌ $field missing\n";
    }
}
