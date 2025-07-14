<?php

namespace App\Http\Controllers\Admin;

use App\Mail\UserInvitation;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionSystemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    protected $permissionService;
    public function __construct(PermissionSystemService $permissionService)
    {}
    public function index()
    {}
    public function create(Request $request)
    {}
}