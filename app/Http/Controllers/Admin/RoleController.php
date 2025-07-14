<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Organization;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use App\Services\PermissionSystemService;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    protected $permissionService;
    public function __construct(PermissionSystemService $permissionService)
    {}
    public function index(Request $request)
    {}
    public function create(Request $request)
    {}
    private function filterTemplatesByScope($roleTemplates, $admin): array
    {return [];} // placeholder
    private function getAvailablePermissions($admin, $permissionDefinitions): array
    {return [];} // placeholder
    public function store(Request $request)
    {}
}