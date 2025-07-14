<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class BranchController extends Controller
{
    public function index(Organization $organization)
    {}
    public function show(Organization $organization, Branch $branch)
    {}
    public function store(Request $request, Organization $organization)
    {}
    public function update(Request $request, Organization $organization, Branch $branch)
    {}
    public function deactivate(Branch $branch)
    {}
    public function create(Organization $organization)
    {}
    public function activateAll(Organization $organization)
    {}
}
