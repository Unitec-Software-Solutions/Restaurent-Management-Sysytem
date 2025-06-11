<?php

namespace App\Http\Controllers;

use App\Models\{Organization, Branch};
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    public function activateOrganization(Request $request)
    {
        $org = Organization::where('activation_key', $request->key)->firstOrFail();
        
        $org->update([
            'is_active' => true,
            'activated_at' => now()
        ]);
        
        $org->headOffice->update(['is_active' => true]);
        
        $org->subscriptions()->create([
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'is_active' => true
        ]);
        
        return response()->json(['message' => 'Organization activated']);
    }

    public function activateBranch(Request $request)
    {
        $branch = Branch::where('activation_key', $request->key)->firstOrFail();
        $branch->update(['is_active' => true]);
        
        return response()->json(['message' => 'Branch activated']);
    }
}
