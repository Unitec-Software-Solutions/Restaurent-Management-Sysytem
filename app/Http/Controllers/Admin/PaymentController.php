<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        //
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified payment
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified payment
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Print payment details
     */
    public function print($id)
    {
        //
    }

    /**
     * Remove the specified payment from storage
     */
    public function destroy($id)
    {
        //
    }
}
