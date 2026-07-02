<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('materials')->orderBy('name', 'asc')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['materials', 'purchaseOrders' => function($q) {
            $q->orderBy('created_at', 'desc')->take(10);
        }]);

        $allMaterials = Material::orderBy('name', 'asc')->get();

        return view('suppliers.show', compact('supplier', 'allMaterials'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier->id)->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Link a material to this supplier with optional pricing and preferred status.
     */
    public function linkMaterial(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'unit_price' => 'nullable|numeric|min:0',
            'is_preferred' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if already linked
        if ($supplier->materials()->where('material_id', $validated['material_id'])->exists()) {
            // Update existing link
            $supplier->materials()->updateExistingPivot($validated['material_id'], [
                'unit_price' => $validated['unit_price'] ?? null,
                'is_preferred' => $validated['is_preferred'] ?? false,
                'notes' => $validated['notes'] ?? null,
            ]);
            return redirect()->route('suppliers.show', $supplier->id)->with('success', 'Material link updated.');
        }

        // If marking as preferred, unmark others for this material
        if (!empty($validated['is_preferred'])) {
            \Illuminate\Support\Facades\DB::table('material_supplier')
                ->where('material_id', $validated['material_id'])
                ->update(['is_preferred' => false]);
        }

        $supplier->materials()->attach($validated['material_id'], [
            'unit_price' => $validated['unit_price'] ?? null,
            'is_preferred' => $validated['is_preferred'] ?? false,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('suppliers.show', $supplier->id)->with('success', 'Material linked successfully.');
    }

    /**
     * Remove a material link from this supplier.
     */
    public function unlinkMaterial(Supplier $supplier, Material $material)
    {
        $supplier->materials()->detach($material->id);
        return redirect()->route('suppliers.show', $supplier->id)->with('success', 'Material unlinked from supplier.');
    }
}
