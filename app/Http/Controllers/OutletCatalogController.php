<?php
namespace App\Http\Controllers;

use App\Models\OutletCatalogItem;
use App\Models\OutletCatalogIngredient;
use App\Models\Product;
use App\Models\Material;
use App\Models\OutletProductionRun;
use Illuminate\Http\Request;

class OutletCatalogController extends Controller
{
    public function index()
    {
        $catalogItems = OutletCatalogItem::withCount(['ingredients', 'outlets'])
            ->orderBy('name', 'asc')
            ->get();
        return view('outlet_catalog.index', compact('catalogItems'));
    }

    public function create()
    {
        $products = Product::orderBy('name', 'asc')->get();
        $materials = Material::orderBy('name', 'asc')->get();
        return view('outlet_catalog.create', compact('products', 'materials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:outlet_catalog_items,sku|max:100',
            'retail_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.type' => 'required|in:material,product',
            'ingredients.*.id' => 'required|integer',
            'ingredients.*.default_quantity' => 'required|numeric|min:0.01',
        ]);

        $item = OutletCatalogItem::create([
            'name' => $request->name,
            'sku' => $request->sku,
            'retail_price' => $request->retail_price,
            'cost_price' => $request->cost_price,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        foreach ($request->ingredients as $ingredient) {
            OutletCatalogIngredient::create([
                'outlet_catalog_item_id' => $item->id,
                'product_id' => $ingredient['type'] === 'product' ? $ingredient['id'] : null,
                'material_id' => $ingredient['type'] === 'material' ? $ingredient['id'] : null,
                'default_quantity' => $ingredient['default_quantity'],
            ]);
        }

        return redirect()->route('outlet-catalog.index')->with('success', 'Catalog item "' . $item->name . '" created successfully.');
    }

    public function show(OutletCatalogItem $outlet_catalog)
    {
        $outlet_catalog->load(['ingredients.product', 'ingredients.material', 'outlets', 'creator']);
        return view('outlet_catalog.show', compact('outlet_catalog'));
    }

    public function edit(OutletCatalogItem $outlet_catalog)
    {
        $outlet_catalog->load(['ingredients.product', 'ingredients.material']);
        $products = Product::orderBy('name', 'asc')->get();
        $materials = Material::orderBy('name', 'asc')->get();
        return view('outlet_catalog.edit', compact('outlet_catalog', 'products', 'materials'));
    }

    public function update(Request $request, OutletCatalogItem $outlet_catalog)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:outlet_catalog_items,sku,' . $outlet_catalog->id . '|max:100',
            'retail_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.type' => 'required|in:material,product',
            'ingredients.*.id' => 'required|integer',
            'ingredients.*.default_quantity' => 'required|numeric|min:0.01',
        ]);

        $outlet_catalog->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'retail_price' => $request->retail_price,
            'cost_price' => $request->cost_price,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Delete old ingredients and recreate
        $outlet_catalog->ingredients()->delete();
        foreach ($request->ingredients as $ingredient) {
            OutletCatalogIngredient::create([
                'outlet_catalog_item_id' => $outlet_catalog->id,
                'product_id' => $ingredient['type'] === 'product' ? $ingredient['id'] : null,
                'material_id' => $ingredient['type'] === 'material' ? $ingredient['id'] : null,
                'default_quantity' => $ingredient['default_quantity'],
            ]);
        }

        return redirect()->route('outlet-catalog.index')->with('success', 'Catalog item updated successfully.');
    }

    public function destroy(OutletCatalogItem $outlet_catalog)
    {
        if (OutletProductionRun::where('outlet_catalog_item_id', $outlet_catalog->id)->exists()) {
            return back()->with('error', 'Cannot delete — this catalog item has been used in production runs.');
        }
        $outlet_catalog->delete();
        return redirect()->route('outlet-catalog.index')->with('success', 'Catalog item deleted successfully.');
    }

    /**
     * API: Return ingredients for a catalog item (used by production form auto-populate).
     */
    public function ingredients(OutletCatalogItem $outlet_catalog)
    {
        $outlet_catalog->load(['ingredients.product', 'ingredients.material']);
        return response()->json($outlet_catalog->ingredients->map(function ($ing) {
            return [
                'type' => $ing->product_id ? 'product' : 'material',
                'id' => $ing->product_id ?? $ing->material_id,
                'name' => $ing->item_name,
                'sku' => $ing->item_sku,
                'default_quantity' => (float) $ing->default_quantity,
            ];
        }));
    }
}
