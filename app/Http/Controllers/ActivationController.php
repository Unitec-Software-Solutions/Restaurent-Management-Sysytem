<?php

namespace App\Http\Controllers;

use App\Models\{Organization, Branch};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivationController extends Controller
{
    public function activateOrganization($key)
    {
        $org = Organization::where('activation_key', $key)->firstOrFail();

        DB::transaction(function () use ($org) {
            $org->update([
                'is_active' => true,
                'activated_at' => now(),
                'activation_key' => null
            ]);
            $org->subscriptions()->create([
                'plan_id' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
                'status' => 'active'
            ]);
            $org->branches()->where('type', 'head_office')->update(['is_active' => true]);
        });

        return response()->json(['status' => 'activated']);
    }

    public function activateBranch(Request $request)
    {
        $branch = Branch::where('activation_key', $request->key)->firstOrFail();
        $branch->update(['is_active' => true]);
        
        return response()->json(['message' => 'Branch activated']);
    }
}
