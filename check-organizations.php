<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Organizations in database:\n";
echo "========================\n";

$organizations = \App\Models\Organization::all(['id', 'name']);

if ($organizations->isEmpty()) {
    echo "No organizations found in database.\n";
} else {
    foreach ($organizations as $org) {
        echo "ID: {$org->id}, Name: '{$org->name}'\n";
    }
}

echo "\nTotal organizations: " . $organizations->count() . "\n";
