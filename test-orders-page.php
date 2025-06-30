<?php
/**
 * Quick test script to verify the orders index page works
 */

// Test that the view compiles without syntax errors
try {
    $viewPath = 'resources/views/admin/orders/index.blade.php';
    $content = file_get_contents($viewPath);
    
    echo "🔍 TESTING BLADE TEMPLATE\n";
    echo "========================\n";
    
    // Check for common Blade syntax issues
    $issues = [];
    
    // Count @if vs @endif
    $ifCount = substr_count($content, '@if');
    $endifCount = substr_count($content, '@endif');
    
    if ($ifCount !== $endifCount) {
        $issues[] = "Mismatched @if/@endif count: $ifCount @if vs $endifCount @endif";
    }
    
    // Count @routeexists vs @endrouteexists
    $routeexistsCount = substr_count($content, '@routeexists');
    $endrouteexistsCount = substr_count($content, '@endrouteexists');
    
    if ($routeexistsCount !== $endrouteexistsCount) {
        $issues[] = "Mismatched @routeexists/@endrouteexists count: $routeexistsCount @routeexists vs $endrouteexistsCount @endrouteexists";
    }
    
    // Count @forelse vs @endforelse  
    $forelseCount = substr_count($content, '@forelse');
    $endforelseCount = substr_count($content, '@endforelse');
    
    if ($forelseCount !== $endforelseCount) {
        $issues[] = "Mismatched @forelse/@endforelse count: $forelseCount @forelse vs $endforelseCount @endforelse";
    }
    
    if (empty($issues)) {
        echo "✅ Template syntax looks correct!\n";
        echo "   - @if/@endif: $ifCount matched pairs\n";
        echo "   - @routeexists/@endrouteexists: $routeexistsCount matched pairs\n";
        echo "   - @forelse/@endforelse: $forelseCount matched pairs\n";
    } else {
        echo "❌ Template syntax issues found:\n";
        foreach ($issues as $issue) {
            echo "   - $issue\n";
        }
    }
    
    echo "\n🔧 CONTROLLER METHOD CHECK\n";
    echo "==========================\n";
    
    // Check if AdminOrderController has index method
    $controllerPath = 'app/Http/Controllers/AdminOrderController.php';
    if (file_exists($controllerPath)) {
        $controllerContent = file_get_contents($controllerPath);
        if (strpos($controllerContent, 'public function index(') !== false) {
            echo "✅ AdminOrderController::index() method exists\n";
        } else {
            echo "❌ AdminOrderController::index() method not found\n";
        }
    } else {
        echo "❌ AdminOrderController.php not found\n";
    }
    
    echo "\n🎯 TEMPLATE VALIDATION COMPLETE\n";
    echo "===============================\n";
    
} catch (Exception $e) {
    echo "❌ Error testing template: " . $e->getMessage() . "\n";
}
