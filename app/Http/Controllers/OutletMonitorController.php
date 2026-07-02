<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\OutletStockMovement;
use App\Models\ShowcaseRequest;
use Illuminate\Http\Request;

class OutletMonitorController extends Controller
{
    public function index()
    {
        $outlets = Outlet::with(['stocks.product', 'stocks.material'])
            ->orderBy('name', 'asc')
            ->get();

        $movements = OutletStockMovement::with(['outlet', 'product', 'material'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('outlets.monitor', compact('outlets', 'movements'));
    }

    public function showcaseRequests()
    {
        $requests = ShowcaseRequest::with(['outlet', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('outlets.showcase_requests', compact('requests'));
    }
}
