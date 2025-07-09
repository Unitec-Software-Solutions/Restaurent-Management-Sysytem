<?php

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ModuleController extends Controller
{
    use Exportable;

    public function index(Request $request)
    {
        // ...existing code...
    }

    public function create()
    {
        // ...existing code...
    }

    public function store(Request $request)
    {
        // ...existing code...
    }

    public function show(string $id)
    {
        // ...existing code...
    }

    public function edit(Module $module)
    {
        // ...existing code...
    }

    public function update(Request $request, Module $module)
    {
        // ...existing code...
    }

    public function destroy(Module $module)
    {
        // ...existing code...
    }

    protected function getSearchableColumns(): array
    {
        // ...existing code...
    }
}
