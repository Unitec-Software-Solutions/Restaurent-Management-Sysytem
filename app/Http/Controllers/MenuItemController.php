<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function create()
    {
        return view('menu-items.create'); // Return the create form view
    }
} 