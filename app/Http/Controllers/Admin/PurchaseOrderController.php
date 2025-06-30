<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PurchaseOrderController extends Controller
{
    public function edit($id)
    {
        // TODO: Implement edit logic
        return view('admin.edit', compact('id'));
    }


    public function show($id)
    {
        try {
            $model = $this->getModel()::findOrFail($id);
            return view('admin.show', compact('model'));
        } catch (\Exception $e) {
            return back()->with('error', 'Show failed: ' . $e->getMessage());
        }
    }
}
