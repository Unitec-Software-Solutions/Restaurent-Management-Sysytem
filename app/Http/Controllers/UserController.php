<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Mail\UserInvitation;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function index(Organization $organization)
{
    Gate::authorize('viewAny', [User::class, $organization]);
    return $organization->users()->with(['branch', 'role'])->get();
}

public function invite(Request $request, Organization $organization)
{
    Gate::authorize('invite', [User::class, $organization]);
    $request->validate([
        'email' => 'required|email',
        'branch_id' => 'nullable|exists:branches,id,organization_id,'.$organization->id,
        'role_id' => 'required|exists:roles,id,organization_id,'.$organization->id
    ]);
    $invitationToken = Str::random(40);
    $user = $organization->users()->create([
        'email' => $request->email,
        'branch_id' => $request->branch_id,
        'role_id' => $request->role_id,
        'invitation_token' => $invitationToken,
        'password' => Hash::make(Str::random(24)),
    ]);
    Mail::to($request->email)->send(new UserInvitation($user));
    return response()->json(['message' => 'Invitation sent']);
}

public function completeRegistration(Request $request)
{
    $request->validate([
        'token' => 'required|string',
        'name' => 'required|string|max:255',
        'password' => 'required|string|min:8|confirmed'
    ]);
    $user = User::where('invitation_token', $request->token)
                ->whereNull('email_verified_at')
                ->firstOrFail();
    $user->update([
        'name' => $request->name,
        'password' => Hash::make($request->password),
        'email_verified_at' => now(),
        'invitation_token' => null
    ]);
    return response()->json(['message' => 'Registration completed']);
}

public function updateRole(Request $request, User $user)
{
    Gate::authorize('updateRole', $user);
    $request->validate([
        'role_id' => 'required|exists:roles,id,organization_id,'.$user->organization_id
    ]);
    $user->update(['role_id' => $request->role_id]);
    return response()->json($user);
}

public function deactivate(User $user)
{
    Gate::authorize('deactivate', $user);
    $user->update(['is_active' => false]);
    return response()->json(['message' => 'User deactivated']);
}

public function store(Request $request, $branchId)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'branch_id' => $branchId,
    ]);

    return redirect()->route('users.index', $branchId)->with('success', 'User created successfully.');
}
}
