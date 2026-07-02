<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRequest;
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
        return view('materials.edit', compact('material'));
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
        ]);

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

        $recentReleases = MaterialRequest::with('items.material')
            ->where('status', 'released')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return view('materials.kitchen_stock', compact('materials', 'recentReleases'));
    }
}
