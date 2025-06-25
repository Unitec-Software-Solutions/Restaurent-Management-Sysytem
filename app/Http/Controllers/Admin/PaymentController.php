<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    /**
     * Display a listing of the payments
     */
    public function index()
    {
        return view('admin.payments.index');
    }

    /**
     * Show the form for creating a new payment
     */
    public function create()
    {
        return view('admin.payments.create');
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        // TODO: Implement store logic
        return redirect()->route('admin.payments.index')->with('success', 'Payment created successfully');
    }

    /**
     * Display the specified payment
     */
    public function show($id)
    {
        return view('admin.payments.show', compact('id'));
    }

    /**
     * Show the form for editing the specified payment
     */
    public function edit($id)
    {
        return view('admin.payments.edit', compact('id'));
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, $id)
    {
        // TODO: Implement update logic
        return redirect()->route('admin.payments.show', $id)->with('success', 'Payment updated successfully');
    }

    /**
     * Print payment details
     */
    public function print($id)
    {
        return view('admin.payments.print', compact('id'));
    }

    public function destroy($id)
    {
        // TODO: Implement destroy logic
        return redirect()->back()->with('success', 'Deleted successfully');
    }

}
