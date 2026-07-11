<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Outlet;
use App\Models\OutletCatalogItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function index()
    {
        $outlets = Outlet::withCount('stocks')
            ->orderBy('name', 'asc')
            ->get();
        return view('outlets.index', compact('outlets'));
    }

    public function create()
    {
        return view('outlets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:own,franchise',
            'commission_rate' => 'required_if:type,franchise|numeric|min:0|max:100',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'email' => 'required|email|unique:outlets,email',
            'password' => 'required|string|min:4',
        ]);

        // Force commission rate to 0 for own outlets
        if ($validated['type'] === 'own') {
            $validated['commission_rate'] = 0.00;
        }

        Outlet::create($validated);

        return redirect()->route('outlets.index')->with('success', 'Outlet added successfully.');
    }

    public function show(Outlet $outlet)
    {
        $outlet->load(['stocks.product', 'stocks.material', 'stocks.catalogItem', 'dispatches' => function($q) {
            $q->orderBy('created_at', 'desc')->take(10);
        }, 'salesLogs' => function($q) {
            $q->orderBy('created_at', 'desc')->take(10);
        }, 'assignedProducts', 'assignedMaterials', 'assignedCatalogItems']);

        $allProducts = Product::orderBy('name', 'asc')->get();
        $allPackagingMaterials = Material::where('category', 'packaging')->orderBy('name', 'asc')->get();
        $allCatalogItems = OutletCatalogItem::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('outlets.show', compact('outlet', 'allProducts', 'allPackagingMaterials', 'allCatalogItems'));
    }

    public function miniDashboard(Outlet $outlet)
    {
        $mtdRevenue = \Illuminate\Support\Facades\DB::table('sales_log_items')
            ->join('sales_logs', 'sales_log_items.sales_log_id', '=', 'sales_logs.id')
            ->where('sales_logs.outlet_id', $outlet->id)
            ->whereMonth('sales_logs.log_date', now()->month)
            ->whereYear('sales_logs.log_date', now()->year)
            ->sum('sales_log_items.total_revenue');

        $incomingCount = $outlet->dispatches()
            ->where('status', 'dispatched')
            ->count();

        $stocks = $outlet->stocks()
            ->with(['product', 'material'])
            ->get()
            ->map(function ($stock) {
                return [
                    'name' => $stock->product ? $stock->product->name : ($stock->material ? $stock->material->name : 'Unknown Item'),
                    'sku' => $stock->product ? $stock->product->sku : ($stock->material ? $stock->material->sku : 'N/A'),
                    'quantity' => (float)$stock->quantity,
                    'unit' => $stock->product ? 'pcs' : ($stock->material ? $stock->material->unit : 'pcs'),
                ];
            });

        $recentSales = $outlet->salesLogs()
            ->with('items.product')
            ->orderBy('log_date', 'desc')
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'date' => $log->log_date ? $log->log_date->format('d M Y') : 'N/A',
                    'total' => (float)$log->items->sum('total_revenue'),
                    'items_count' => $log->items->count(),
                ];
            });

        return response()->json([
            'outlet' => [
                'id' => $outlet->id,
                'name' => $outlet->name,
                'type' => ucfirst($outlet->type),
                'contact_person' => $outlet->contact_person ?? 'N/A',
                'phone' => $outlet->phone ?? 'N/A',
                'email' => $outlet->email ?? 'N/A',
                'address' => $outlet->address ?? 'N/A',
                'url' => route('outlets.show', $outlet->id),
            ],
            'mtd_revenue' => (float)$mtdRevenue,
            'incoming_count' => $incomingCount,
            'stocks_count' => $stocks->count(),
            'stocks' => $stocks,
            'recent_sales' => $recentSales,
        ]);
    }

    public function edit(Outlet $outlet)
    {
        return view('outlets.edit', compact('outlet'));
    }

    public function update(Request $request, Outlet $outlet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:own,franchise',
            'commission_rate' => 'required_if:type,franchise|numeric|min:0|max:100',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'email' => 'required|email|unique:outlets,email,' . $outlet->id,
            'password' => 'nullable|string|min:4',
        ]);

        if ($validated['type'] === 'own') {
            $validated['commission_rate'] = 0.00;
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $outlet->update($validated);

        return redirect()->route('outlets.index')->with('success', 'Outlet updated successfully.');
    }

    public function destroy(Outlet $outlet)
    {
        $outlet->delete();
        return redirect()->route('outlets.index')->with('success', 'Outlet deleted successfully.');
    }

    /**
     * Update product, material, and catalog item assignments for an outlet.
     */
    public function updateAssignments(Request $request, Outlet $outlet)
    {
        $validated = $request->validate([
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'product_types' => 'nullable|array',
            'product_types.*' => 'in:pre_cooked,half_prepared',
            'material_ids' => 'nullable|array',
            'material_ids.*' => 'exists:materials,id',
            'catalog_item_ids' => 'nullable|array',
            'catalog_item_ids.*' => 'exists:outlet_catalog_items,id',
        ]);

        // Sync products with type pivot data
        $productSync = [];
        $productIds = $validated['product_ids'] ?? [];
        $productTypes = $validated['product_types'] ?? [];
        foreach ($productIds as $productId) {
            $productSync[$productId] = [
                'type' => $productTypes[$productId] ?? 'pre_cooked',
            ];
        }
        $outlet->assignedProducts()->sync($productSync);

        $outlet->assignedMaterials()->sync($validated['material_ids'] ?? []);
        $outlet->assignedCatalogItems()->sync($validated['catalog_item_ids'] ?? []);

        return redirect()->route('outlets.show', $outlet->id)->with('success', 'Product assignments updated successfully.');
    }

    /**
     * API endpoint: returns assigned product and material IDs for an outlet.
     */
    public function assignedProducts(Outlet $outlet)
    {
        $productIds = $outlet->assignedProducts()->pluck('products.id');
        $materialIds = $outlet->assignedMaterials()->pluck('materials.id');

        $products = Product::whereIn('id', $productIds)->get(['id', 'name', 'sku', 'retail_price', 'current_kitchen_stock']);
        $materials = Material::whereIn('id', $materialIds)->where('category', 'packaging')->get(['id', 'name', 'sku', 'retail_price', 'kitchen_stock', 'per_box_qty']);

        return response()->json([
            'products' => $products,
            'materials' => $materials,
        ]);
    }
}
