<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RealtimeDashboardController extends Controller
{
    protected $inventoryService;
    protected $orderService;
    protected $catalogService;
    public function __construct(
        \App\Services\ProductCatalogService $catalogService
    ) {}
}