<?php
namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\SalesLog;
use App\Models\SalesLogItem;
use App\Models\ProductionRun;
use App\Models\Outlet;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CentralKitchenReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        // Dispatch cost (COGS) — only received dispatches
        $totalDispatchCost = Dispatch::where('status', 'received')
            ->whereBetween('dispatch_date', [$dateFrom, $dateTo])
            ->sum('total_cost');

        // Sales revenue — from all outlets
        $totalRevenue = SalesLogItem::whereHas('salesLog', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('log_date', [$dateFrom, $dateTo]);
        })->sum('total_revenue');

        $totalNetRevenue = SalesLogItem::whereHas('salesLog', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('log_date', [$dateFrom, $dateTo]);
        })->sum('net_revenue');

        $grossProfit = $totalRevenue - $totalDispatchCost;
        $profitMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        // Production qty
        $totalProduced = ProductionRun::where('status', 'completed')
            ->whereBetween('prepared_date', [$dateFrom, $dateTo])
            ->sum('quantity_produced');

        // Per-outlet breakdown
        $outletBreakdown = Outlet::all()->map(function ($outlet) use ($dateFrom, $dateTo) {
            $dispatchCost = Dispatch::where('outlet_id', $outlet->id)
                ->where('status', 'received')
                ->whereBetween('dispatch_date', [$dateFrom, $dateTo])
                ->sum('total_cost');

            $revenue = SalesLogItem::whereHas('salesLog', function ($q) use ($outlet, $dateFrom, $dateTo) {
                $q->where('outlet_id', $outlet->id)->whereBetween('log_date', [$dateFrom, $dateTo]);
            })->sum('total_revenue');

            $unitsSold = SalesLogItem::whereHas('salesLog', function ($q) use ($outlet, $dateFrom, $dateTo) {
                $q->where('outlet_id', $outlet->id)->whereBetween('log_date', [$dateFrom, $dateTo]);
            })->sum('quantity_sold');

            $unitsDispatched = DispatchItem::whereHas('dispatch', function ($q) use ($outlet, $dateFrom, $dateTo) {
                $q->where('outlet_id', $outlet->id)->where('status', 'received')->whereBetween('dispatch_date', [$dateFrom, $dateTo]);
            })->sum('quantity');

            return [
                'name' => $outlet->name,
                'type' => $outlet->type,
                'dispatch_cost' => (float) $dispatchCost,
                'revenue' => (float) $revenue,
                'profit' => (float) $revenue - (float) $dispatchCost,
                'margin' => $revenue > 0 ? round((($revenue - $dispatchCost) / $revenue) * 100, 1) : 0,
                'units_dispatched' => (float) $unitsDispatched,
                'units_sold' => (float) $unitsSold,
                'sell_through' => $unitsDispatched > 0 ? round(($unitsSold / $unitsDispatched) * 100, 1) : 0,
            ];
        })->filter(fn($o) => $o['dispatch_cost'] > 0 || $o['revenue'] > 0);

        // Per-product breakdown
        $productBreakdown = Product::all()->map(function ($product) use ($dateFrom, $dateTo) {
            $dispatchQty = DispatchItem::where('product_id', $product->id)
                ->whereHas('dispatch', function ($q) use ($dateFrom, $dateTo) {
                    $q->where('status', 'received')->whereBetween('dispatch_date', [$dateFrom, $dateTo]);
                })->sum('quantity');

            $dispatchCost = DispatchItem::where('product_id', $product->id)
                ->whereHas('dispatch', function ($q) use ($dateFrom, $dateTo) {
                    $q->where('status', 'received')->whereBetween('dispatch_date', [$dateFrom, $dateTo]);
                })->sum('line_cost');

            $soldQty = SalesLogItem::where('product_id', $product->id)
                ->whereHas('salesLog', function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('log_date', [$dateFrom, $dateTo]);
                })->sum('quantity_sold');

            $revenue = SalesLogItem::where('product_id', $product->id)
                ->whereHas('salesLog', function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('log_date', [$dateFrom, $dateTo]);
                })->sum('total_revenue');

            return [
                'name' => $product->name,
                'sku' => $product->sku,
                'cost_price' => (float) $product->cost_price,
                'retail_price' => (float) $product->retail_price,
                'margin_per_unit' => (float) $product->retail_price - (float) $product->cost_price,
                'dispatched_qty' => (float) $dispatchQty,
                'dispatch_cost' => (float) $dispatchCost,
                'sold_qty' => (float) $soldQty,
                'revenue' => (float) $revenue,
                'profit' => (float) $revenue - (float) $dispatchCost,
            ];
        })->filter(fn($p) => $p['dispatched_qty'] > 0 || $p['sold_qty'] > 0);

        return view('reports.central_kitchen', compact(
            'dateFrom', 'dateTo',
            'totalDispatchCost', 'totalRevenue', 'totalNetRevenue',
            'grossProfit', 'profitMargin', 'totalProduced',
            'outletBreakdown', 'productBreakdown'
        ));
    }
}
