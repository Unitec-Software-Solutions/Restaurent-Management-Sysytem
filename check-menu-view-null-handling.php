<?php

require_once 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== COMPREHENSIVE MENU VIEW NULL HANDLING CHECK ===\n\n";

$viewPath = resource_path('views/admin/menus/');
$viewFiles = ['list.blade.php', 'create.blade.php', 'edit.blade.php', 'show.blade.php', 'bulk-create.blade.php', 'preview.blade.php'];

$patterns = [
    // Array operations without null checks
    'array_map\(\s*[^,]+,\s*\$[^)]+\)' => 'Unsafe array_map usage',
    'implode\(\s*[^,]+,\s*\$[^)]+\)' => 'Unsafe implode usage',
    'foreach\(\s*\$[^)]+\s+as\s+' => 'Potential unsafe foreach',
    
    // Date operations without null checks
    '\$[^-]+->format\(' => 'Direct format() call without null check',
    'Carbon::parse\(\s*\$[^)]+\)' => 'Carbon parse without null check',
    
    // Property access that might be null
    '\$menu->available_days(?!\s*&&|\s*\?|\s*\|\|)' => 'Direct available_days access',
    '\$menu->created_at(?!\s*\?|\s*&&)' => 'Direct created_at access',
    '\$menu->updated_at(?!\s*\?|\s*&&)' => 'Direct updated_at access',
    '\$menu->valid_from(?!\s*\?|\s*&&)' => 'Direct valid_from access',
    '\$menu->valid_until(?!\s*\?|\s*&&)' => 'Direct valid_until access',
];

$issues = [];

foreach ($viewFiles as $viewFile) {
    $filePath = $viewPath . $viewFile;
    if (!file_exists($filePath)) {
        echo "⚠️  File not found: {$viewFile}\n";
        continue;
    }
    
    echo "Checking: {$viewFile}\n";
    $content = file_get_contents($filePath);
    
    foreach ($patterns as $pattern => $description) {
        if (preg_match_all("/{$pattern}/", $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $lineNumber = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                $issues[] = [
                    'file' => $viewFile,
                    'line' => $lineNumber,
                    'pattern' => $match[0],
                    'description' => $description
                ];
                echo "   ⚠️  Line {$lineNumber}: {$description} - '{$match[0]}'\n";
            }
        }
    }
}

echo "\n=== SUMMARY ===\n";
if (empty($issues)) {
    echo "✅ No potential null handling issues found!\n";
} else {
    echo "Found " . count($issues) . " potential issues:\n\n";
    
    foreach ($issues as $issue) {
        echo "📍 {$issue['file']}:{$issue['line']}\n";
        echo "   Issue: {$issue['description']}\n";
        echo "   Code: {$issue['pattern']}\n\n";
    }
}

// Test specific problematic patterns
echo "\n=== TESTING SPECIFIC PATTERNS ===\n";

// Test available_days patterns
$testPatterns = [
    'array_map.*available_days' => 'Available days array_map usage',
    'implode.*available_days' => 'Available days implode usage',
    'foreach.*available_days.*as' => 'Available days foreach usage',
];

foreach ($viewFiles as $viewFile) {
    $filePath = $viewPath . $viewFile;
    if (!file_exists($filePath)) continue;
    
    $content = file_get_contents($filePath);
    
    foreach ($testPatterns as $pattern => $description) {
        if (preg_match_all("/{$pattern}/i", $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $lineNumber = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                
                // Check if this line has proper null handling
                $lines = explode("\n", $content);
                $currentLine = $lines[$lineNumber - 1] ?? '';
                $contextLines = array_slice($lines, max(0, $lineNumber - 3), 6);
                $context = implode("\n", $contextLines);
                
                $hasSafetyCheck = preg_match('/if\s*\(\s*\$[^&]*&&.*is_array|if\s*\(\s*\$[^)]*\s*\?\s*/', $context);
                
                if (!$hasSafetyCheck) {
                    echo "⚠️  {$viewFile}:{$lineNumber} - {$description} without safety check\n";
                    echo "   Code: " . trim($currentLine) . "\n";
                } else {
                    echo "✅ {$viewFile}:{$lineNumber} - {$description} with safety check\n";
                }
            }
        }
    }
}

echo "\n=== FINAL VERIFICATION ===\n";

// Test with actual data
try {
    $menu = App\Models\Menu::first();
    if ($menu) {
        echo "Testing with actual menu data:\n";
        echo "   Available days: " . ($menu->available_days ? json_encode($menu->available_days) : 'NULL') . "\n";
        echo "   Created at: " . ($menu->created_at ? $menu->created_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
        echo "   Updated at: " . ($menu->updated_at ? $menu->updated_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
        echo "   Valid from: " . ($menu->valid_from ? $menu->valid_from : 'NULL') . "\n";
        echo "   Valid until: " . ($menu->valid_until ? $menu->valid_until : 'NULL') . "\n";
    }
} catch (Exception $e) {
    echo "Error testing with actual data: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
