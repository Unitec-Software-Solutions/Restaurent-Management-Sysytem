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
            // Only create a subscription if one does not exist
            if (!$org->subscriptions()->exists()) {
                $org->subscriptions()->create([
                    'plan_id' => 1, // or your default plan id
                    'start_date' => now(),
                    'end_date' => now()->addYear(),
                    'status' => 'active',
                    'activated_at' => now()
                ]);
            }
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
