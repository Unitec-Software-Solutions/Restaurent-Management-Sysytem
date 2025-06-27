<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\MenuSystemService;
use App\Models\Branch;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MenuController extends Controller
{
    private MenuSystemService $menuService;

    public function __construct(MenuSystemService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function index(Request $request, Branch $branch)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();
        $menu = $this->menuService->getDailyMenu($branch, $date);
        
        return view('menu.index', [
            'branch' => $branch,
            'menu' => $menu,
            'selectedDate' => $date,
            'availableDates' => $this->getAvailableDates($branch)
        ]);
    }

    public function show(Request $request, Branch $branch, $itemId)
    {
        $item = \App\Models\MenuItem::where('branch_id', $branch->id)
            ->findOrFail($itemId);
            
        return view('menu.show', [
            'branch' => $branch,
            'item' => $item,
            'availability' => $this->menuService->checkItemAvailability($item)
        ]);
    }

    private function getAvailableDates(Branch $branch): array
    {
        return collect(range(0, 7))->map(function ($days) {
            $date = Carbon::now()->addDays($days);
            return [
                'date' => $date->format('Y-m-d'),
                'display' => $date->format('l, M j'),
                'is_today' => $days === 0
            ];
        })->toArray();
    }
}