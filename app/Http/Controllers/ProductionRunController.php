<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionRun;
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
        return view('production_runs.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_produced' => 'required|numeric|min:0.01',
            'prepared_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
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
        $productionRun->load('product');
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

        try {
            DB::beginTransaction();

            // Update status
            $productionRun->update(['status' => 'completed']);

            // Increment finished goods stock
            $product = Product::findOrFail($productionRun->product_id);
            $product->increment('current_kitchen_stock', $productionRun->quantity_produced);

            DB::commit();

            return redirect()->route('production-runs.show', $productionRun->id)
                ->with('success', "Production Run {$productionRun->run_number} completed. Finished goods stock updated.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error completing production run: ' . $e->getMessage());
        }
    }
}
