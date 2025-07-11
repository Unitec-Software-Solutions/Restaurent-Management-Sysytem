<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Table;
use App\Http\Requests\BranchRequest;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    public function users()
    {
        // TODO: Implement users logic
        return view('admin.users');
    }

    public function store(BranchRequest $request)
    {
        DB::transaction(function () use ($request) {
            $branch = Branch::create($request->validated());
            if ($request->has('tables')) {
                foreach ($request->input('tables') as $tableData) {
                    Table::create([
                        'branch_id' => $branch->id,
                        'number' => $tableData['table_id'],
                        'capacity' => $tableData['capacity'],
                        'status' => 'available',
                    ]);
                }
            }
        });
        return redirect()->route('admin.branches.index', ['organization' => $request->organization_id])
            ->with('success', 'Branch created successfully!');
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        DB::transaction(function () use ($request, $branch) {
            $branch->update($request->validated());
            // Remove all existing tables for this branch
            $branch->tables()->delete();
            if ($request->has('tables')) {
                foreach ($request->input('tables') as $tableData) {
                    Table::create([
                        'branch_id' => $branch->id,
                        'number' => $tableData['table_id'],
                        'capacity' => $tableData['capacity'],
                        'status' => 'available',
                    ]);
                }
            }
        });
        return redirect()->route('admin.branches.index', ['organization' => $branch->organization_id])
            ->with('success', 'Branch updated successfully!');
    }
}
