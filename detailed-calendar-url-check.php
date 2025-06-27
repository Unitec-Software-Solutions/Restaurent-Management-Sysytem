<?php

require_once __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DETAILED CALENDAR VIEW URL CHECK ===\n";

$calendarViewPath = 'resources/views/admin/menus/calendar.blade.php';
$content = file_get_contents($calendarViewPath);

echo "Checking for problematic URL patterns:\n\n";

// Check for /admin/menus/ patterns
if (strpos($content, '/admin/menus/') !== false) {
    echo "❌ Found '/admin/menus/' pattern:\n";
    $lines = explode("\n", $content);
    foreach ($lines as $index => $line) {
        if (strpos($line, '/admin/menus/') !== false) {
            echo "  Line " . ($index + 1) . ": " . trim($line) . "\n";
        }
    }
} else {
    echo "✅ No '/admin/menus/' patterns found\n";
}

// Check for hardcoded menu URLs
if (preg_match('/`[^{]*\/menus\//', $content)) {
    echo "❌ Found hardcoded menu URLs:\n";
    $lines = explode("\n", $content);
    foreach ($lines as $index => $line) {
        if (preg_match('/`[^{]*\/menus\//', $line)) {
            echo "  Line " . ($index + 1) . ": " . trim($line) . "\n";
        }
    }
} else {
    echo "✅ No hardcoded menu URLs found\n";
}

// Check current edit URL implementation
if (preg_match('/window\.location\.href = `([^`]+)`/', $content, $matches)) {
    echo "\nCurrent edit URL implementation:\n";
    echo "  " . $matches[1] . "\n";
    
    if (strpos($matches[1], "{{ url('menus') }}") !== false) {
        echo "✅ Using Laravel url() helper correctly\n";
    } else {
        echo "❌ Not using Laravel url() helper\n";
    }
} else {
    echo "\n❌ Edit URL implementation not found\n";
}

echo "\n=== CHECK COMPLETE ===\n";
