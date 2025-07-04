<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        // Determine organization_id
        if ($user->is_super_admin) {
            $organizationId = $request->input('organization_id');
            if (!$organizationId) {
                return response()->view('errors.generic', [
                    'errorTitle' => 'Organization Required',
                    'errorCode' => '400',
                    'errorHeading' => 'Organization Not Specified',
                    'errorMessage' => 'Super admin must specify an organization to create a menu item.',
                    'headerClass' => 'bg-gradient-warning',
                    'errorIcon' => 'fas fa-exclamation-triangle',
                    'mainIcon' => 'fas fa-exclamation-triangle',
                    'iconBgClass' => 'bg-yellow-100',
                    'iconColor' => 'text-yellow-500',
                    'buttonClass' => 'bg-[#FF9800] hover:bg-[#e68a00]',
                ], 400);
            }
        } else {
            $organizationId = $user->organization_id;
            if (!$organizationId) {
                return response()->view('errors.generic', [
                    'errorTitle' => 'Organization Error',
                    'errorCode' => '400',
                    'errorHeading' => 'Organization Not Found',
                    'errorMessage' => 'Your account is not linked to any organization.',
                    'headerClass' => 'bg-gradient-warning',
                    'errorIcon' => 'fas fa-exclamation-triangle',
                    'mainIcon' => 'fas fa-exclamation-triangle',
                    'iconBgClass' => 'bg-yellow-100',
                    'iconColor' => 'text-yellow-500',
                    'buttonClass' => 'bg-[#FF9800] hover:bg-[#e68a00]',
                ], 400);
            }
        }

        $menuItem = new MenuItem([
            // ...existing fields...
            'organization_id' => $organizationId,
            // ...existing fields...
        ]);

        // ...existing code...
    }
}
