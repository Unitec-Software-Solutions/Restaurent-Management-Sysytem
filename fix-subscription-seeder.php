<?php

/**
 * Carefully fix PostgreSQL JSON field assignments
 * This script handles nested array structures properly
 */

$file = 'database/seeders/ExhaustiveSubscriptionSeeder.php';

if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}

$content = file_get_contents($file);
$originalContent = $content;

// Fix patterns step by step to avoid issues
$patterns = [
    // Pattern 1: Simple arrays that end with ],
    "/('modules'\s*=>\s*)\[((?:[^[\]{}]|\[(?:[^[\]{}]|\[[^\]]*\])*\])*)\],/ms",
    "/('features'\s*=>\s*)\[((?:[^[\]{}]|\[(?:[^[\]{}]|\[[^\]]*\])*\])*)\],/ms", 
    "/('restrictions'\s*=>\s*)\[((?:[^[\]{}]|\[(?:[^[\]{}]|\[[^\]]*\])*\])*)\],/ms",
    "/('permissions'\s*=>\s*)\[((?:[^[\]{}]|\[(?:[^[\]{}]|\[[^\]]*\])*\])*)\],/ms",
];

foreach ($patterns as $pattern) {
    $content = preg_replace_callback($pattern, function($matches) {
        return $matches[1] . 'json_encode([' . $matches[2] . ']),';
    }, $content);
}

// Write the fixed content
if ($content !== $originalContent) {
    file_put_contents($file, $content);
    echo "✅ Fixed JSON field assignments in $file\n";
} else {
    echo "ℹ️  No changes needed in $file\n";
}
