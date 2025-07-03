<?php

use Illuminate\Support\Facades\Route;
use App\Models\ItemMaster;
use Illuminate\Support\Facades\DB;

Route::get('/test-item-master', function () {
    try {
        echo "Testing ItemMaster in web context...<br>";
        echo "Database: " . config('database.default') . "<br>";
        echo "Connection: " . DB::connection()->getName() . "<br>";
        
        // Test table existence
        $exists = DB::select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'item_master')");
        echo "Table exists: " . ($exists[0]->exists ? 'YES' : 'NO') . "<br>";
        
        // Test direct query
        $count = DB::table('item_master')->count();
        echo "Direct count: $count<br>";
        
        // Test model count
        $modelCount = ItemMaster::count();
        echo "Model count: $modelCount<br>";
        
        // Test with relationship
        $query = ItemMaster::with('itemCategory');
        $relationCount = $query->count();
        echo "With relation count: $relationCount<br>";
        
        // Test pagination
        $pagination = $query->paginate(15);
        echo "Pagination count: " . $pagination->count() . "<br>";
        
        echo "All tests passed!";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
        echo "File: " . $e->getFile() . "<br>";
        echo "Line: " . $e->getLine() . "<br>";
        if ($e instanceof \Illuminate\Database\QueryException) {
            echo "SQL: " . $e->getSql() . "<br>";
            echo "Bindings: " . json_encode($e->getBindings()) . "<br>";
        }
    }
});
