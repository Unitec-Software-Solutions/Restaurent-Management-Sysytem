<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GrnController extends Controller
{
    public function verify()
    {
        // TODO: Implement verify logic
        return view('admin.verify');
    }


    public function linkPayment()
    {
        return view('admin.grn.link-payment');
    }


    public function update(Request $request, $id)
    {
        try {
            $model = $this->getModel()::findOrFail($id);
            $model->update($request->validated());
            
            return redirect()->back()->with('success', 'Updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        try {
            $model = $this->getModel()::findOrFail($id);
            return view('admin.print.document', compact('model'));
        } catch (\Exception $e) {
            return back()->with('error', 'Print failed: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $model = $this->getModel()::findOrFail($id);
            return view('admin.edit', compact('model'));
        } catch (\Exception $e) {
            return back()->with('error', 'Edit failed: ' . $e->getMessage());
        }
    }
}
