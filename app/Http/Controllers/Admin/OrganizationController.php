<?php

namespace App\Http\Controllers\Admin;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Services\OrganizationAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class OrganizationController extends Controller
{
    protected $organizationAutomationService;
    public function __construct(OrganizationAutomationService $organizationAutomationService)
    {}
    public function index()
    {}
    public function create()
    {}
    public function store(Request $request)
    {}
}