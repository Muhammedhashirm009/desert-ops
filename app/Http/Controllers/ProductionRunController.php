<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\Material;
use App\Models\ProductionRunMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionRunController extends Controller
{
    public function index()
    {
        $productionRuns = ProductionRun::with('product')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('production_runs.index', compact('productionRuns'));
    }

    public function create()
    {
        $products = Product::orderBy('name', 'asc')->get();
        $materials = Material::orderBy('name', 'asc')->get();
        return view('production_runs.create', compact('products', 'materials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_produced' => 'required|numeric|min:0.01',
            'prepared_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity_used' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            // Generate unique Run Number (Format: PR-YYYY-XXXX)
            $year = now()->year;
            $latest = ProductionRun::where('run_number', 'like', "PR-{$year}-%")->orderBy('id', 'desc')->first();
            $nextSerial = 1;
            if ($latest) {
                $parts = explode('-', $latest->run_number);
                $nextSerial = (int)end($parts) + 1;
            }
            $runNumber = 'PR-' . $year . '-' . str_pad($nextSerial, 4, '0', STR_PAD_LEFT);

            // Create Run
            $productionRun = ProductionRun::create([
                'run_number' => $runNumber,
                'product_id' => $request->product_id,
                'quantity_produced' => $request->quantity_produced,
                'prepared_date' => $request->prepared_date,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Save consumed materials
            foreach ($request->items as $item) {
                ProductionRunMaterial::create([
                    'production_run_id' => $productionRun->id,
                    'material_id' => $item['material_id'],
                    'quantity_used' => $item['quantity_used'],
                ]);
            }

            DB::commit();

            return redirect()->route('production-runs.show', $productionRun->id)
                ->with('success', "Production Run {$runNumber} created as pending.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating production run: ' . $e->getMessage());
        }
    }

    public function show(ProductionRun $productionRun)
    {
        $productionRun->load(['product', 'materials.material']);
        return view('production_runs.show', compact('productionRun'));
    }

    public function edit(ProductionRun $productionRun)
    {
        return redirect()->route('production-runs.show', $productionRun->id);
    }

    public function update(Request $request, ProductionRun $productionRun)
    {
        return redirect()->route('production-runs.show', $productionRun->id);
    }

    public function destroy(ProductionRun $productionRun)
    {
        if ($productionRun->status !== 'pending') {
            return back()->with('error', 'Only pending runs can be deleted.');
        }

        $productionRun->delete();

        return redirect()->route('production-runs.index')->with('success', 'Production Run deleted.');
    }

    public function complete(ProductionRun $productionRun)
    {
        if ($productionRun->status !== 'pending') {
            return back()->with('error', 'Only pending runs can be completed.');
        }

        $productionRun->load('materials.material');

        try {
            DB::beginTransaction();

            // 1. Stock Check (Verify kitchen_stock is sufficient)
            foreach ($productionRun->materials as $runMat) {
                $material = $runMat->material;
                if ($runMat->quantity_used > $material->kitchen_stock) {
                    throw new \Exception("Insufficient stock in Kitchen for material '{$material->name}'. Available in kitchen: {$material->kitchen_stock} {$material->unit}, Required: {$runMat->quantity_used} {$material->unit}. Please request more materials from the store manager.");
                }
            }

            // 2. Decrement kitchen stock for each material
            foreach ($productionRun->materials as $runMat) {
                $runMat->material->decrement('kitchen_stock', $runMat->quantity_used);
            }

            // 3. Update run status
            $productionRun->update(['status' => 'completed']);

            // 4. Increment finished goods stock in kitchen
            $product = Product::findOrFail($productionRun->product_id);
            $product->increment('current_kitchen_stock', $productionRun->quantity_produced);

            DB::commit();

            return redirect()->route('production-runs.show', $productionRun->id)
                ->with('success', "Production Run {$productionRun->run_number} completed. Kitchen stock consumed, and finished goods stock updated.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error completing production run: ' . $e->getMessage());
        }
    }
}
