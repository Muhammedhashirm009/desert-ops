<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::withCount('suppliers')->orderBy('name', 'asc')->get();
        return view('materials.index', compact('materials'));
    }

    public function create()
    {
        return view('materials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:materials,sku|max:100',
            'category' => 'required|string|in:ingredient,packaging',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'min_stock_alert' => 'required|numeric|min:0',
            'per_box_qty' => 'nullable|integer|min:1',
            'retail_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
        ]);

        Material::create($validated);

        return redirect()->route('materials.index')->with('success', 'Material added successfully.');
    }

    public function show(Material $material)
    {
        return redirect()->route('materials.index');
    }

    public function edit(Material $material)
    {
        $priceHistories = \App\Models\PriceHistory::where('item_type', 'material')
            ->where('item_id', $material->id)
            ->with(['grn', 'changedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('materials.edit', compact('material', 'priceHistories'));
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:materials,sku,' . $material->id . '|max:100',
            'category' => 'required|string|in:ingredient,packaging',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'min_stock_alert' => 'required|numeric|min:0',
            'per_box_qty' => 'nullable|integer|min:1',
            'retail_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
        ]);

        $oldCost = (float) $material->cost_price;
        $newCost = $request->has('cost_price') ? (float) $request->cost_price : 0;

        if (abs($newCost - $oldCost) > 0.001) {
            \App\Models\PriceHistory::create([
                'item_type' => 'material',
                'item_id' => $material->id,
                'old_cost_price' => $oldCost,
                'new_cost_price' => $newCost,
                'notes' => 'Manually updated from Raw Material settings page.',
                'changed_by' => auth()->id(),
            ]);
        }

        $material->update($validated);

        return redirect()->route('materials.index')->with('success', 'Material updated successfully.');
    }

    public function destroy(Material $material)
    {
        $material->delete();
        return redirect()->route('materials.index')->with('success', 'Material deleted successfully.');
    }

    public function kitchenStock()
    {
        $materials = Material::orderBy('name', 'asc')->get();

        $today = now()->startOfDay();

        // Query today's material request releases
        $todayReleases = \App\Models\MaterialRequestItem::whereHas('materialRequest', function($q) use ($today) {
            $q->where('status', 'released')->where('updated_at', '>=', $today);
        })->get();

        // Query today's production run material usage
        $todayProductionUsage = \App\Models\ProductionRunMaterial::whereHas('productionRun', function($q) use ($today) {
            $q->where('status', 'completed')->where('prepared_date', '>=', $today);
        })->get();

        foreach ($materials as $material) {
            $additions = $todayReleases->where('material_id', $material->id)->sum('quantity_released');
            $subtractions = $todayProductionUsage->where('material_id', $material->id)->sum('quantity_used');

            $material->opening_stock = $material->kitchen_stock - $additions + $subtractions;
            $material->closing_stock = $material->kitchen_stock;
        }

        $recentReleases = MaterialRequest::with('items.material')
            ->where('status', 'released')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return view('materials.kitchen_stock', compact('materials', 'recentReleases'));
    }

    public function kitchenStockReport(Request $request)
    {
        $dateStr = $request->input('date', now()->toDateString());
        $targetDate = \Carbon\Carbon::parse($dateStr);
        $targetStart = $targetDate->copy()->startOfDay();
        $targetEnd = $targetDate->copy()->endOfDay();

        // Raw Materials report
        $materials = Material::orderBy('name', 'asc')->get();

        $releasesAfter = \App\Models\MaterialRequestItem::whereHas('materialRequest', function($q) use ($targetEnd) {
            $q->where('status', 'released')->where('updated_at', '>', $targetEnd);
        })->get();

        $releasesOn = \App\Models\MaterialRequestItem::whereHas('materialRequest', function($q) use ($targetStart, $targetEnd) {
            $q->where('status', 'released')->whereBetween('updated_at', [$targetStart, $targetEnd]);
        })->get();

        $usageAfter = \App\Models\ProductionRunMaterial::whereHas('productionRun', function($q) use ($targetEnd) {
            $q->where('status', 'completed')->where('prepared_date', '>', $targetEnd);
        })->get();

        $usageOn = \App\Models\ProductionRunMaterial::whereHas('productionRun', function($q) use ($targetStart, $targetEnd) {
            $q->where('status', 'completed')->whereBetween('prepared_date', [$targetStart, $targetEnd]);
        })->get();

        $materialData = [];
        foreach ($materials as $material) {
            $additionsAfter = $releasesAfter->where('material_id', $material->id)->sum('quantity_released');
            $subtractionsAfter = $usageAfter->where('material_id', $material->id)->sum('quantity_used');

            $closingStock = $material->kitchen_stock - $additionsAfter + $subtractionsAfter;

            $additionsOn = $releasesOn->where('material_id', $material->id)->sum('quantity_released');
            $subtractionsOn = $usageOn->where('material_id', $material->id)->sum('quantity_used');

            $openingStock = $closingStock - $additionsOn + $subtractionsOn;

            $materialData[] = (object)[
                'sku' => $material->sku,
                'name' => $material->name,
                'unit' => $material->unit,
                'opening_stock' => $openingStock,
                'additions' => $additionsOn,
                'subtractions' => $subtractionsOn,
                'closing_stock' => $closingStock,
            ];
        }

        // Finished Products report
        $products = Product::orderBy('name', 'asc')->get();

        $prodAfter = \App\Models\ProductionRun::where('status', 'completed')
            ->where('prepared_date', '>', $targetEnd)
            ->get();

        $prodOn = \App\Models\ProductionRun::where('status', 'completed')
            ->whereBetween('prepared_date', [$targetStart, $targetEnd])
            ->get();

        $dispatchAfter = \App\Models\DispatchItem::whereHas('dispatch', function($q) use ($targetEnd) {
            $q->whereIn('status', ['dispatched', 'received'])->where('dispatch_date', '>', $targetEnd);
        })->get();

        $dispatchOn = \App\Models\DispatchItem::whereHas('dispatch', function($q) use ($targetStart, $targetEnd) {
            $q->whereIn('status', ['dispatched', 'received'])->whereBetween('dispatch_date', [$targetStart, $targetEnd]);
        })->get();

        $productData = [];
        foreach ($products as $product) {
            $additionsAfter = $prodAfter->where('product_id', $product->id)->sum('quantity_produced');
            $subtractionsAfter = $dispatchAfter->where('product_id', $product->id)->sum('quantity');

            $closingStock = $product->current_kitchen_stock - $additionsAfter + $subtractionsAfter;

            $additionsOn = $prodOn->where('product_id', $product->id)->sum('quantity_produced');
            $subtractionsOn = $dispatchOn->where('product_id', $product->id)->sum('quantity');

            $openingStock = $closingStock - $additionsOn + $subtractionsOn;

            $productData[] = (object)[
                'sku' => $product->sku,
                'name' => $product->name,
                'unit' => 'Units',
                'opening_stock' => $openingStock,
                'additions' => $additionsOn,
                'subtractions' => $subtractionsOn,
                'closing_stock' => $closingStock,
            ];
        }

        return view('materials.kitchen_stock_report', compact('materialData', 'productData', 'dateStr'));
    }
}
