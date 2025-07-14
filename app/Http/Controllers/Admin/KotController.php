<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Kot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class KotController extends Controller
{
    public function generateKot(Request $request, Order $order)
    {
        // ...existing code...
    }

    public function print(Kot $kot)
    {
        // ...existing code...
    }

    public function updateKotItemStatus(Request $request, $kotId, $itemId)
    {
        // ...existing code...
    }

    private function generateKotNumber($branchId = null): string
    {
        // ...existing code...
    }

    private function determineKotPriority(Order $order): string
    {
        // ...existing code...
    }
}
