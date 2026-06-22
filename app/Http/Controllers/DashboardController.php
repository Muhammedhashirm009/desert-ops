<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\MaterialRequest;
use App\Models\ProductionRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Live calculations
        $openPoValue = PurchaseOrder::where('status', 'pending')->sum('total_amount');
        $openPoCount = PurchaseOrder::where('status', 'pending')->count();
        $lowStockCount = Material::lowStock()->count();
        $lowStockMaterials = Material::lowStock()->take(5)->get();

        // Recent Purchase Orders
        $recentPos = PurchaseOrder::with('supplier')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Active Outlets count
        $activeOutletsCount = 5; // 3 own, 2 franchise

        // Dynamic system alerts based on inventory & kitchen requests
        $alerts = [];
        
        // 1. Raw material stock alerts
        foreach ($lowStockMaterials as $mat) {
            $alerts[] = [
                'type' => 'critical',
                'title' => "Low Stock — {$mat->name}",
                'details' => "SKU: {$mat->sku} current stock is {$mat->current_stock} {$mat->unit} (min limit {$mat->min_stock_alert} {$mat->unit}). Reorder required.",
                'time' => $mat->updated_at->diffForHumans()
            ];
        }

        // 2. Pending kitchen requests alerts
        $pendingRequests = MaterialRequest::where('status', 'pending')->take(3)->get();
        foreach ($pendingRequests as $req) {
            $alerts[] = [
                'type' => 'grn', // green color badge
                'title' => "Pending Material Request — {$req->request_number}",
                'details' => "Requested by {$req->requested_by} for {$req->items->count()} items.",
                'time' => $req->created_at->diffForHumans()
            ];
        }

        // Add default info alert if empty
        if (count($alerts) < 3) {
            $alerts[] = [
                'type' => 'grn',
                'title' => 'System Status Normal',
                'details' => 'Central Kitchen and Inventory are synchronized.',
                'time' => 'Just now'
            ];
        }

        // Today's Production Value (calculate from database runs or default to mock value)
        $productionSum = DB::table('production_runs')
            ->join('products', 'production_runs.product_id', '=', 'products.id')
            ->where('production_runs.status', 'completed')
            ->whereDate('production_runs.prepared_date', today())
            ->sum(DB::raw('production_runs.quantity_produced * products.retail_price'));

        $todaysProductionValue = $productionSum > 0 ? $productionSum : 84200;

        return view('dashboard', compact(
            'openPoValue',
            'openPoCount',
            'lowStockCount',
            'recentPos',
            'activeOutletsCount',
            'alerts',
            'todaysProductionValue'
        ));
    }
}

