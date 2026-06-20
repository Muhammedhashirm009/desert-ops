<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

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

        // Dynamic system alerts based on inventory
        $alerts = [];
        
        foreach ($lowStockMaterials as $mat) {
            $alerts[] = [
                'type' => 'critical',
                'title' => "Low Stock — {$mat->name}",
                'details' => "SKU: {$mat->sku} current stock is {$mat->current_stock} {$mat->unit} (min limit {$mat->min_stock_alert} {$mat->unit}). Reorder required.",
                'time' => $mat->updated_at->diffForHumans()
            ];
        }

        // Add some default informational alerts if we don't have enough critical alerts
        if (count($alerts) < 4) {
            $alerts[] = [
                'type' => 'grn',
                'title' => 'System Ready',
                'details' => 'DessertOps ERP Module 1 is active. Database initialized.',
                'time' => 'Just now'
            ];
        }

        return view('dashboard', compact(
            'openPoValue',
            'openPoCount',
            'lowStockCount',
            'recentPos',
            'activeOutletsCount',
            'alerts'
        ));
    }
}
