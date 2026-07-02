<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\MaterialRequest;
use App\Models\ProductionRun;
use App\Models\Outlet;
use App\Models\Dispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->role === 'accountant') {
            return redirect()->route('accounting.dashboard');
        }

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

        // Active Outlets count (Live)
        $activeOutletsCount = Outlet::count();
        $ownOutletsCount = Outlet::where('type', 'own')->count();
        $franchiseOutletsCount = Outlet::where('type', 'franchise')->count();
        $outletsBreakdown = "{$ownOutletsCount} own · {$franchiseOutletsCount} franchise";

        // MTD Revenue (Live)
        $mtdRevenue = DB::table('sales_log_items')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_revenue');

        // Dispatches Today (Live)
        $dispatchesTodayCount = DB::table('dispatches')
            ->whereDate('dispatch_date', today())
            ->count();

        // Live Outlet Stock Levels
        $dashboardOutlets = Outlet::withSum('stocks as total_stock', 'quantity')->get();

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

        // Today's Production Value (calculate from database runs)
        $todaysProductionValue = DB::table('production_runs')
            ->join('products', 'production_runs.product_id', '=', 'products.id')
            ->where('production_runs.status', 'completed')
            ->whereDate('production_runs.prepared_date', today())
            ->sum(DB::raw('production_runs.quantity_produced * products.retail_price'));

        // Yesterday's Production Value
        $yesterdaysProductionValue = DB::table('production_runs')
            ->join('products', 'production_runs.product_id', '=', 'products.id')
            ->where('production_runs.status', 'completed')
            ->whereDate('production_runs.prepared_date', today()->subDay())
            ->sum(DB::raw('production_runs.quantity_produced * products.retail_price'));

        // Production change trend
        $productionTrend = 0;
        if ($yesterdaysProductionValue > 0) {
            $productionTrend = round((($todaysProductionValue - $yesterdaysProductionValue) / $yesterdaysProductionValue) * 100, 1);
        }

        // Fulfillment Rate & On-Time Dispatch Rate
        $totalDispatches = Dispatch::where('status', '!=', 'cancelled')->count();
        $fulfilledDispatches = Dispatch::whereIn('status', ['dispatched', 'received'])->count();
        $receivedDispatches = Dispatch::where('status', 'received')->count();

        $fulfillmentRate = $totalDispatches > 0 ? round(($fulfilledDispatches / $totalDispatches) * 100, 1) : 100.0;
        $fulfillmentTrend = "{$fulfilledDispatches} of {$totalDispatches} fulfilled";

        $onTimeRate = $totalDispatches > 0 ? round(($receivedDispatches / $totalDispatches) * 100, 1) : 100.0;
        $onTimeTrend = "{$receivedDispatches} delivered";

        // Weekly Performance Chart Data (Last 7 Days)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayName = now()->subDays($i)->format('D');

            // Production sum (quantity produced)
            $prodQty = DB::table('production_runs')
                ->whereDate('prepared_date', $date)
                ->sum('quantity_produced');

            // Dispatches sum (quantity dispatched)
            $dispQty = DB::table('dispatch_items')
                ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
                ->whereDate('dispatches.dispatch_date', $date)
                ->sum('dispatch_items.quantity');

            // Sales sum (quantity sold)
            $salesQty = DB::table('sales_log_items')
                ->join('sales_logs', 'sales_log_items.sales_log_id', '=', 'sales_logs.id')
                ->whereDate('sales_logs.log_date', $date)
                ->sum('sales_log_items.quantity_sold');

            $chartData[] = [
                'day' => $dayName,
                'production' => (float)$prodQty,
                'dispatch' => (float)$dispQty,
                'sales' => (float)$salesQty,
            ];
        }

        $maxVal = collect($chartData)->max(function($d) {
            return max($d['production'], $d['dispatch'], $d['sales']);
        });

        foreach ($chartData as &$d) {
            if ($maxVal > 0) {
                $d['production_pct'] = round(($d['production'] / $maxVal) * 100);
                $d['dispatch_pct'] = round(($d['dispatch'] / $maxVal) * 100);
                $d['sales_pct'] = round(($d['sales'] / $maxVal) * 100);
            } else {
                $d['production_pct'] = 0;
                $d['dispatch_pct'] = 0;
                $d['sales_pct'] = 0;
            }
        }

        // Weekly aggregates (Last 7 Days values)
        $weekProduction = DB::table('production_runs')
            ->join('products', 'production_runs.product_id', '=', 'products.id')
            ->where('production_runs.status', 'completed')
            ->where('production_runs.prepared_date', '>=', now()->subDays(6)->startOfDay())
            ->sum(DB::raw('production_runs.quantity_produced * products.retail_price'));

        $weekDispatched = DB::table('dispatch_items')
            ->join('dispatches', 'dispatch_items.dispatch_id', '=', 'dispatches.id')
            ->join('products', 'dispatch_items.product_id', '=', 'products.id')
            ->whereIn('dispatches.status', ['dispatched', 'received'])
            ->where('dispatches.dispatch_date', '>=', now()->subDays(6)->startOfDay())
            ->sum(DB::raw('dispatch_items.quantity * products.retail_price'));

        $weekFulfillmentRate = $weekProduction > 0 ? round((min($weekProduction, $weekDispatched) / $weekProduction) * 100, 1) : 100.0;

        $formatCurrencyLakh = function($amount) {
            if ($amount >= 100000) {
                return '₹' . number_format($amount / 100000, 1) . 'L';
            } elseif ($amount >= 1000) {
                return '₹' . number_format($amount / 1000, 1) . 'K';
            } else {
                return '₹' . number_format($amount, 0);
            }
        };

        $weekProductionFormatted = $formatCurrencyLakh($weekProduction);
        $weekDispatchedFormatted = $formatCurrencyLakh($weekDispatched);

        // Top Dessert Products - MTD
        $topProducts = DB::table('sales_log_items')
            ->join('products', 'sales_log_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(sales_log_items.quantity_sold) as total_units'),
                DB::raw('SUM(sales_log_items.total_revenue) as total_rev')
            )
            ->whereMonth('sales_log_items.created_at', now()->month)
            ->whereYear('sales_log_items.created_at', now()->year)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_rev')
            ->take(3)
            ->get();

        $maxProductRev = collect($topProducts)->max('total_rev') ?: 1;

        return view('dashboard', compact(
            'openPoValue',
            'openPoCount',
            'lowStockCount',
            'recentPos',
            'activeOutletsCount',
            'outletsBreakdown',
            'mtdRevenue',
            'dispatchesTodayCount',
            'dashboardOutlets',
            'alerts',
            'todaysProductionValue',
            'chartData',
            'yesterdaysProductionValue',
            'productionTrend',
            'fulfillmentRate',
            'fulfillmentTrend',
            'onTimeRate',
            'onTimeTrend',
            'weekProductionFormatted',
            'weekDispatchedFormatted',
            'weekFulfillmentRate',
            'topProducts',
            'maxProductRev'
        ));
    }
}

