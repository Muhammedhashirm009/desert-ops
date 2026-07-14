<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Product;
use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\PriceHistory;
use App\Models\Account;
use App\Models\JournalTransaction;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryInitializationController extends Controller
{
    public function index()
    {
        $materials = Material::orderBy('name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();
        $outlets = Outlet::with(['assignedProducts', 'assignedCatalogItems'])->orderBy('name', 'asc')->get();

        // Load existing outlet stock map for pre-populating in UI
        $outletStocks = OutletStock::all()->groupBy('outlet_id');

        return view('admin.inventory_initialize', compact('materials', 'products', 'outlets', 'outletStocks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'materials' => 'nullable|array',
            'materials.*.id' => 'required|exists:materials,id',
            'materials.*.current_stock' => 'required|numeric|min:0',
            'materials.*.kitchen_stock' => 'required|numeric|min:0',
            'materials.*.cost_price' => 'required|numeric|min:0',

            'products' => 'nullable|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.current_kitchen_stock' => 'required|numeric|min:0',
            'products.*.cost_price' => 'required|numeric|min:0',

            'outlet_stocks' => 'nullable|array',
            'outlet_stocks.*.outlet_id' => 'required|exists:outlets,id',
            'outlet_stocks.*.product_id' => 'nullable|exists:products,id',
            'outlet_stocks.*.outlet_catalog_item_id' => 'nullable|exists:outlet_catalog_items,id',
            'outlet_stocks.*.store_quantity' => 'required|numeric|min:0',
            'outlet_stocks.*.kitchen_quantity' => 'required|numeric|min:0',
            'outlet_stocks.*.showcase_quantity' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalRawValue = 0;
            $totalFinishedValue = 0;

            // 1. Initialize Raw Materials
            if ($request->has('materials')) {
                foreach ($request->materials as $matData) {
                    $material = Material::findOrFail($matData['id']);
                    $oldCost = (float) $material->cost_price;
                    $newCost = (float) $matData['cost_price'];
                    $storeStock = (float) $matData['current_stock'];
                    $kitchenStock = (float) $matData['kitchen_stock'];

                    // Log price history if cost changed
                    if (abs($newCost - $oldCost) > 0.001) {
                        PriceHistory::create([
                            'item_type' => 'material',
                            'item_id' => $material->id,
                            'old_cost_price' => $oldCost,
                            'new_cost_price' => $newCost,
                            'notes' => 'Initialized bulk stock levels.',
                            'changed_by' => auth()->id(),
                        ]);
                    }

                    $material->update([
                        'current_stock' => $storeStock,
                        'kitchen_stock' => $kitchenStock,
                        'cost_price' => $newCost,
                    ]);

                    $totalRawValue += ($storeStock + $kitchenStock) * $newCost;
                }
            }

            // 2. Initialize Central Kitchen Finished Products
            if ($request->has('products')) {
                foreach ($request->products as $prodData) {
                    $product = Product::findOrFail($prodData['id']);
                    $oldCost = (float) $product->cost_price;
                    $newCost = (float) $prodData['cost_price'];
                    $kitchenStock = (float) $prodData['current_kitchen_stock'];

                    // Log price history if cost changed
                    if (abs($newCost - $oldCost) > 0.001) {
                        PriceHistory::create([
                            'item_type' => 'product',
                            'item_id' => $product->id,
                            'old_cost_price' => $oldCost,
                            'new_cost_price' => $newCost,
                            'notes' => 'Initialized bulk stock levels.',
                            'changed_by' => auth()->id(),
                        ]);
                    }

                    $product->update([
                        'current_kitchen_stock' => $kitchenStock,
                        'cost_price' => $newCost,
                    ]);

                    $totalFinishedValue += $kitchenStock * $newCost;
                }
            }

            // 3. Initialize Outlet Stocks
            if ($request->has('outlet_stocks')) {
                foreach ($request->outlet_stocks as $osData) {
                    $search = [
                        'outlet_id' => $osData['outlet_id'],
                        'product_id' => $osData['product_id'] ?: null,
                        'outlet_catalog_item_id' => $osData['outlet_catalog_item_id'] ?: null,
                    ];

                    $storeQty = (float) $osData['store_quantity'];
                    $kitchenQty = (float) $osData['kitchen_quantity'];
                    $showcaseQty = (float) $osData['showcase_quantity'];
                    $totalQty = $storeQty + $kitchenQty + $showcaseQty;

                    OutletStock::updateOrCreate(
                        $search,
                        [
                            'store_quantity' => $storeQty,
                            'kitchen_quantity' => $kitchenQty,
                            'showcase_quantity' => $showcaseQty,
                            'quantity' => $totalQty,
                        ]
                    );
                }
            }

            // 4. Create Accounting Initialization Entry
            $totalInventoryValue = round($totalRawValue + $totalFinishedValue, 2);
            if ($totalInventoryValue > 0) {
                $invAccount = Account::where('code', '1300')->first(); // Inventory
                $equityAccount = Account::where('code', '3000')->first(); // Capital/Equity

                if ($invAccount && $equityAccount) {
                    $tx = JournalTransaction::create([
                        'reference' => 'SYS-INIT-' . now()->format('Ymd'),
                        'description' => 'System Inventory Initialization (Starting Stock & Costs)',
                        'date' => now(),
                    ]);

                    JournalEntry::create([
                        'journal_transaction_id' => $tx->id,
                        'account_id' => $invAccount->id,
                        'debit' => $totalInventoryValue,
                        'credit' => 0.00,
                    ]);

                    JournalEntry::create([
                        'journal_transaction_id' => $tx->id,
                        'account_id' => $equityAccount->id,
                        'debit' => 0.00,
                        'credit' => $totalInventoryValue,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('dashboard')
                ->with('success', 'Starting inventory levels, costs, and opening balances initialized successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error initializing starting inventory: ' . $e->getMessage());
        }
    }
}
