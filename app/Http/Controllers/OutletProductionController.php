<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Outlet;
use App\Models\OutletCatalogItem;
use App\Models\OutletProductionRun;
use App\Models\OutletProductionRunMaterial;
use App\Models\OutletStock;
use App\Models\OutletStockMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutletProductionController extends Controller
{
    /**
     * Ensure the current session user is an outlet_admin.
     */
    private function ensureOutletAdmin()
    {
        if (session('portal_employee_role') !== 'outlet_admin') {
            abort(403, 'Only outlet administrators can perform this action.');
        }
    }

    /**
     * Display a listing of production runs for this outlet.
     */
    public function index()
    {
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);

        $runs = OutletProductionRun::where('outlet_id', $outletId)
            ->with(['product', 'catalogItem'])
            ->orderByDesc('created_at')
            ->get();

        return view('portal.production.index', compact('outlet', 'runs'));
    }

    /**
     * Show the form for creating a new production run.
     */
    public function create()
    {
        $this->ensureOutletAdmin();
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);

        // Products that can be produced at this outlet
        $products = Product::orderBy('name')->get();

        // Catalog items assigned to this outlet
        $catalogItems = $outlet->assignedCatalogItems()
            ->where('is_active', true)
            ->with('ingredients.product', 'ingredients.material')
            ->orderBy('name')
            ->get();

        // Materials available in this outlet's kitchen
        $outletMaterials = OutletStock::where('outlet_id', $outletId)
            ->whereNotNull('material_id')
            ->where('kitchen_quantity', '>', 0)
            ->with('material')
            ->get();

        // Products available in this outlet's kitchen (for use as ingredients/toppings)
        $outletProducts = OutletStock::where('outlet_id', $outletId)
            ->whereNotNull('product_id')
            ->where('kitchen_quantity', '>', 0)
            ->with('product')
            ->get();

        return view('portal.production.create', compact('outlet', 'products', 'catalogItems', 'outletMaterials', 'outletProducts'));
    }

    /**
     * Store a newly created production run in storage.
     */
    public function store(Request $request)
    {
        $this->ensureOutletAdmin();
        $outletId = session('portal_outlet_id');

        $request->validate([
            'product_id' => 'required_without:outlet_catalog_item_id|nullable|exists:products,id',
            'outlet_catalog_item_id' => 'required_without:product_id|nullable|exists:outlet_catalog_items,id',
            'quantity_produced' => 'required|numeric|min:0.01',
            'prepared_date' => 'required|date',
            'qty_to_store' => 'required|numeric|min:0',
            'qty_to_showcase' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'materials' => 'required|array|min:1',
            'materials.*.type' => 'required|in:material,product',
            'materials.*.id' => 'required|integer',
            'materials.*.quantity_used' => 'required|numeric|min:0.01',
        ]);

        $qtyStore = (float) $request->qty_to_store;
        $qtyShowcase = (float) $request->qty_to_showcase;
        $qtyTotal = (float) $request->quantity_produced;

        if (($qtyStore + $qtyShowcase) != $qtyTotal) {
            return back()->withInput()->with('error', 'Store + Showcase quantities must equal the total quantity produced.');
        }

        if ($qtyStore <= 0 && $qtyShowcase <= 0) {
            return back()->withInput()->with('error', 'At least one destination must have a quantity greater than 0.');
        }

        // Set legacy destination field based on split
        $destination = $qtyShowcase > 0 ? 'showcase' : 'store';

        $run = OutletProductionRun::create([
            'outlet_id' => $outletId,
            'run_number' => OutletProductionRun::generateRunNumber(),
            'product_id' => $request->product_id,
            'outlet_catalog_item_id' => $request->outlet_catalog_item_id,
            'quantity_produced' => $qtyTotal,
            'prepared_date' => $request->prepared_date,
            'destination' => $destination,
            'qty_to_store' => $qtyStore,
            'qty_to_showcase' => $qtyShowcase,
            'status' => 'pending',
            'prepared_by' => session('portal_employee_name', 'Outlet Staff'),
            'notes' => $request->notes,
        ]);

        foreach ($request->materials as $mat) {
            OutletProductionRunMaterial::create([
                'outlet_production_run_id' => $run->id,
                'material_id' => $mat['type'] === 'material' ? $mat['id'] : null,
                'product_id' => $mat['type'] === 'product' ? $mat['id'] : null,
                'quantity_used' => $mat['quantity_used'],
            ]);
        }

        return redirect()->route('portal.production.show', $run)
            ->with('success', "Production run {$run->run_number} created. Review and complete when ready.");
    }

    /**
     * Display the specified production run.
     */
    public function show(OutletProductionRun $run)
    {
        $outletId = session('portal_outlet_id');
        if ($run->outlet_id != $outletId) {
            abort(403, 'Unauthorized access.');
        }

        $run->load(['product', 'catalogItem', 'materials.material', 'materials.product']);

        // Get current kitchen stock for validation display
        $kitchenStocks = [];
        foreach ($run->materials as $mat) {
            $stockQuery = OutletStock::where('outlet_id', $outletId);
            if ($mat->material_id) {
                $stockQuery->where('material_id', $mat->material_id);
            } else {
                $stockQuery->where('product_id', $mat->product_id);
            }
            $stock = $stockQuery->first();
            $key = ($mat->material_id ? 'mat_' : 'prod_') . ($mat->material_id ?? $mat->product_id);
            $kitchenStocks[$key] = $stock ? (float) $stock->kitchen_quantity : 0;
        }

        $isOutletAdmin = session('portal_employee_role', 'outlet_admin') === 'outlet_admin';

        return view('portal.production.show', compact('run', 'kitchenStocks', 'isOutletAdmin'));
    }

    /**
     * Complete a production run — consume materials and produce the finished product.
     */
    public function complete(OutletProductionRun $run)
    {
        $this->ensureOutletAdmin();
        $outletId = session('portal_outlet_id');

        if ($run->outlet_id != $outletId) {
            abort(403, 'Unauthorized access.');
        }

        if ($run->status !== 'pending') {
            return back()->with('error', 'This production run has already been completed.');
        }

        $run->load('materials');

        try {
            DB::beginTransaction();

            // 1. Verify and consume each material from kitchen stock
            foreach ($run->materials as $mat) {
                $stockQuery = OutletStock::where('outlet_id', $outletId);
                if ($mat->material_id) {
                    $stockQuery->where('material_id', $mat->material_id);
                } else {
                    $stockQuery->where('product_id', $mat->product_id);
                }
                $stock = $stockQuery->first();

                if (!$stock || (float) $stock->kitchen_quantity < (float) $mat->quantity_used) {
                    $itemName = $mat->material_id
                        ? ($mat->material ? $mat->material->name : 'Material #' . $mat->material_id)
                        : ($mat->product ? $mat->product->name : 'Product #' . $mat->product_id);
                    $available = $stock ? $stock->kitchen_quantity : 0;
                    throw new \Exception("Insufficient kitchen stock for {$itemName}. Available: {$available}, Required: {$mat->quantity_used}");
                }

                // Decrement kitchen stock
                $stock->decrement('kitchen_quantity', $mat->quantity_used);
                $stock->decrement('quantity', $mat->quantity_used);

                // Log movement: kitchen → consumed (production)
                OutletStockMovement::create([
                    'outlet_id' => $outletId,
                    'product_id' => $mat->product_id,
                    'material_id' => $mat->material_id,
                    'from_location' => 'kitchen',
                    'to_location' => 'consumed',
                    'quantity' => $mat->quantity_used,
                    'logged_by' => session('portal_employee_name', 'Outlet Staff'),
                    'reference' => $run->run_number,
                ]);
            }

            // 2. Add finished product to destination locations (split)
            if ($run->outlet_catalog_item_id) {
                // Producing a catalog item
                $productStock = OutletStock::firstOrCreate(
                    ['outlet_id' => $outletId, 'outlet_catalog_item_id' => $run->outlet_catalog_item_id, 'product_id' => null, 'material_id' => null],
                    ['quantity' => 0, 'store_quantity' => 0, 'kitchen_quantity' => 0, 'showcase_quantity' => 0]
                );
            } else {
                // Producing a regular product
                $productStock = OutletStock::firstOrCreate(
                    ['outlet_id' => $outletId, 'product_id' => $run->product_id, 'material_id' => null],
                    ['quantity' => 0, 'store_quantity' => 0, 'kitchen_quantity' => 0, 'showcase_quantity' => 0]
                );
            }

            $outputProductId = $run->product_id;
            $outputCatalogItemId = $run->outlet_catalog_item_id;

            $productStock->increment('quantity', $run->quantity_produced);

            // Send to store
            if ((float) $run->qty_to_store > 0) {
                $productStock->increment('store_quantity', $run->qty_to_store);
                OutletStockMovement::create([
                    'outlet_id' => $outletId,
                    'product_id' => $outputProductId,
                    'material_id' => null,
                    'outlet_catalog_item_id' => $outputCatalogItemId,
                    'from_location' => 'kitchen',
                    'to_location' => 'store',
                    'quantity' => $run->qty_to_store,
                    'logged_by' => session('portal_employee_name', 'Outlet Staff'),
                    'reference' => $run->run_number,
                ]);
            }

            // Send to showcase
            if ((float) $run->qty_to_showcase > 0) {
                $productStock->increment('showcase_quantity', $run->qty_to_showcase);
                OutletStockMovement::create([
                    'outlet_id' => $outletId,
                    'product_id' => $outputProductId,
                    'material_id' => null,
                    'outlet_catalog_item_id' => $outputCatalogItemId,
                    'from_location' => 'kitchen',
                    'to_location' => 'showcase',
                    'quantity' => $run->qty_to_showcase,
                    'logged_by' => session('portal_employee_name', 'Outlet Staff'),
                    'reference' => $run->run_number,
                ]);
            }

            // 3. Mark run as completed
            $run->update(['status' => 'completed']);

            DB::commit();

            // Increment data pulse version
            DB::table('universal_searches')->where('id', 1)->increment('data_pulse_version');

            $destParts = [];
            if ((float) $run->qty_to_store > 0) $destParts[] = number_format($run->qty_to_store, 0) . ' to Store';
            if ((float) $run->qty_to_showcase > 0) $destParts[] = number_format($run->qty_to_showcase, 0) . ' to Showcase';
            $destText = implode(', ', $destParts);

            return redirect()->route('portal.production.show', $run)
                ->with('success', "Production run {$run->run_number} completed! {$run->product->name}: {$destText}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a pending production run.
     */
    public function destroy(OutletProductionRun $run)
    {
        $this->ensureOutletAdmin();
        $outletId = session('portal_outlet_id');

        if ($run->outlet_id != $outletId) {
            abort(403, 'Unauthorized access.');
        }

        if ($run->status !== 'pending') {
            return back()->with('error', 'Only pending production runs can be deleted.');
        }

        $run->delete();

        return redirect()->route('portal.production.index')
            ->with('success', 'Production run deleted.');
    }
}
