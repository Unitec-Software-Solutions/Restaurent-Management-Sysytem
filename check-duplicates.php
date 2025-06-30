<?php
/**
 * Check for duplicate method definitions in controller files
 */

function checkForDuplicates($directory) {
    $issues = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $methods = [];
            
            // Find all method definitions
            if (preg_match_all('/public\s+function\s+(\w+)\s*\(/', $content, $matches)) {
                foreach ($matches[1] as $method) {
                    if (isset($methods[$method])) {
                        $methods[$method]++;
                    } else {
                        $methods[$method] = 1;
                    }
                }
                
                // Check for duplicates
                foreach ($methods as $methodName => $count) {
                    if ($count > 1) {
                        $issues[] = [
                            'file' => $file->getPathname(),
                            'method' => $methodName,
                            'count' => $count
                        ];
                    }
                }
            }
        }
    }
    
    return $issues;
}

echo "🔍 CHECKING FOR DUPLICATE METHODS\n";
echo "================================\n";

$duplicates = checkForDuplicates('app/Http/Controllers');

if (empty($duplicates)) {
    echo "✅ No duplicate methods found!\n";
} else {
    foreach ($duplicates as $duplicate) {
        echo "❌ {$duplicate['file']}: {$duplicate['method']} ({$duplicate['count']} times)\n";
    }
}

echo "\nCheck complete.\n";
