<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Module::class);
        $modules = Module::all();
        return view('admin.modules.index', compact('modules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Module::class);
        return view('admin.modules.form', ['module' => null]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Module::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:modules,slug',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $validated;
        $data['permissions'] = array_filter($request->input('permissions', [])); // Remove empty

        
        Module::updateOrCreate(['id' => null], $data);

        return redirect()->route('admin.modules.index')
                         ->with('success', 'Module created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Module $module)
    {
        $this->authorize('update', $module);
        return view('admin.modules.form', compact('module'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Module $module)
    {
        $this->authorize('update', $module);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:modules,slug,' . $module->id,
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $validated;
        $data['permissions'] = array_filter($request->input('permissions', []));

        $module->update($data);

        return redirect()->route('admin.modules.index')
                         ->with('success', 'Module updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Module $module)
    {
        $this->authorize('delete', $module);

        if ($module->roles()->count() > 0) {
            return redirect()->back()
                             ->with('error', 'Cannot delete module assigned to roles');
        }

        $module->delete();
        return redirect()->route('admin.modules.index')
                         ->with('success', 'Module deleted successfully');
    }
}
