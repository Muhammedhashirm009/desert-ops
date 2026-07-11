<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use App\Models\Material;
use App\Models\Dispatch;
use App\Models\SalesLog;
use App\Models\SalesLogItem;
use App\Models\DispatchItem;
use App\Models\Account;
use App\Models\JournalTransaction;
use App\Models\JournalEntry;
use App\Models\FranchiseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutletPortalController extends Controller
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
     * Get the current employee's display name for audit trail.
     */
    private function getLoggedByName(): string
    {
        return session('portal_employee_name', 'Outlet Staff');
    }
    public function showLogin()
    {
        // If already logged in, redirect to dashboard
        if (\Illuminate\Support\Facades\Auth::guard('outlet')->check()) {
            return redirect()->route('portal.dashboard');
        }

        return redirect()->route('login', ['tab' => 'outlet']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|exists:outlets,email',
            'password' => 'required',
        ]);

        if (\Illuminate\Support\Facades\Auth::guard('outlet')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $outlet = \Illuminate\Support\Facades\Auth::guard('outlet')->user();
            session(['portal_outlet_id' => $outlet->id]);

            return redirect()->route('portal.dashboard')
                ->with('success', "Logged in to {$outlet->name} portal.");
        }

        return back()->withErrors([
            'email' => 'The provided store credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Logout from outlet_employee guard
        \Illuminate\Support\Facades\Auth::guard('outlet_employee')->logout();
        // Logout from legacy outlet guard
        \Illuminate\Support\Facades\Auth::guard('outlet')->logout();

        // Clear all portal session data
        session()->forget(['portal_outlet_id', 'portal_employee_id', 'portal_employee_role', 'portal_employee_name']);
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out of store portal.');
    }

    public function dashboard()
    {
        $outlet = Outlet::findOrFail(session('portal_outlet_id'));
        $outlet->load(['stocks.product', 'stocks.material', 'stocks.catalogItem']);

        // Calculate opening/closing stocks using today's stock movements
        $today = now()->startOfDay();
        $movementsToday = \App\Models\OutletStockMovement::where('outlet_id', $outlet->id)
            ->where('created_at', '>=', $today)
            ->get();

        foreach ($outlet->stocks as $stock) {
            $isProduct = (bool)$stock->product_id;
            $itemId = $isProduct ? $stock->product_id : $stock->material_id;
            $itemMovements = $movementsToday->filter(function($m) use ($isProduct, $itemId) {
                return $isProduct 
                    ? ($m->product_id === $itemId && $m->material_id === null)
                    : ($m->material_id === $itemId && $m->product_id === null);
            });

            // Calculate additions (external additions to outlet stock)
            $additions = $itemMovements->filter(function($m) {
                $toIn = in_array($m->to_location, ['store', 'kitchen', 'showcase']);
                $fromOut = !in_array($m->from_location, ['store', 'kitchen', 'showcase']);
                return $toIn && $fromOut;
            })->sum('quantity');

            // Calculate subtractions (removals from outlet stock)
            $subtractions = $itemMovements->filter(function($m) {
                $fromIn = in_array($m->from_location, ['store', 'kitchen', 'showcase']);
                $toOut = !in_array($m->to_location, ['store', 'kitchen', 'showcase']);
                return $fromIn && $toOut;
            })->sum('quantity');

            // Opening stock = Current total - additions + subtractions
            $stock->opening_stock = $stock->quantity - $additions + $subtractions;
            $stock->closing_stock = $stock->quantity;
        }

        // Pending dispatches in transit to this outlet
        $incomingCount = Dispatch::where('outlet_id', $outlet->id)
            ->where('status', 'dispatched')
            ->count();

        // Recent sales history at this outlet
        $recentSales = SalesLog::where('outlet_id', $outlet->id)
            ->with('items')
            ->orderBy('log_date', 'desc')
            ->take(5)
            ->get();

        foreach ($recentSales as $log) {
            $log->total_revenue = $log->items->sum('total_revenue');
            $log->net_revenue = $log->items->sum('net_revenue');
        }

        return view('portal.dashboard', compact('outlet', 'incomingCount', 'recentSales'));
    }

    public function stockReport(Request $request)
    {
        $outlet = Outlet::findOrFail(session('portal_outlet_id'));
        $outlet->load(['stocks.product', 'stocks.material']);

        $dateStr = $request->input('date', now()->toDateString());
        $targetDate = \Carbon\Carbon::parse($dateStr);
        $targetStart = $targetDate->copy()->startOfDay();
        $targetEnd = $targetDate->copy()->endOfDay();

        $movementsAfter = \App\Models\OutletStockMovement::where('outlet_id', $outlet->id)
            ->where('created_at', '>', $targetEnd)
            ->get();

        $movementsOn = \App\Models\OutletStockMovement::where('outlet_id', $outlet->id)
            ->whereBetween('created_at', [$targetStart, $targetEnd])
            ->get();

        $reportData = [];

        foreach ($outlet->stocks as $stock) {
            $isProduct = (bool)$stock->product_id;
            $itemId = $isProduct ? $stock->product_id : $stock->material_id;

            // Filter movements after selected date
            $itemMovementsAfter = $movementsAfter->filter(function($m) use ($isProduct, $itemId) {
                return $isProduct 
                    ? ($m->product_id === $itemId && $m->material_id === null)
                    : ($m->material_id === $itemId && $m->product_id === null);
            });

            $additionsAfter = $itemMovementsAfter->filter(function($m) {
                return in_array($m->to_location, ['store', 'kitchen', 'showcase']) && !in_array($m->from_location, ['store', 'kitchen', 'showcase']);
            })->sum('quantity');

            $subtractionsAfter = $itemMovementsAfter->filter(function($m) {
                return in_array($m->from_location, ['store', 'kitchen', 'showcase']) && !in_array($m->to_location, ['store', 'kitchen', 'showcase']);
            })->sum('quantity');

            // Closing stock on target date
            $closingStock = $stock->quantity - $additionsAfter + $subtractionsAfter;

            // Filter movements on selected date
            $itemMovementsOn = $movementsOn->filter(function($m) use ($isProduct, $itemId) {
                return $isProduct 
                    ? ($m->product_id === $itemId && $m->material_id === null)
                    : ($m->material_id === $itemId && $m->product_id === null);
            });

            $additionsOn = $itemMovementsOn->filter(function($m) {
                return in_array($m->to_location, ['store', 'kitchen', 'showcase']) && !in_array($m->from_location, ['store', 'kitchen', 'showcase']);
            })->sum('quantity');

            $subtractionsOn = $itemMovementsOn->filter(function($m) {
                return in_array($m->from_location, ['store', 'kitchen', 'showcase']) && !in_array($m->to_location, ['store', 'kitchen', 'showcase']);
            })->sum('quantity');

            // Opening stock on target date
            $openingStock = $closingStock - $additionsOn + $subtractionsOn;

            $reportData[] = (object)[
                'sku' => $isProduct ? $stock->product->sku : $stock->material->sku,
                'name' => $isProduct ? $stock->product->name : $stock->material->name,
                'category' => $isProduct ? 'Product' : 'Packaging',
                'unit' => $isProduct ? 'Units' : 'Pieces',
                'opening_stock' => $openingStock,
                'additions' => $additionsOn,
                'subtractions' => $subtractionsOn,
                'closing_stock' => $closingStock,
            ];
        }

        $isOutletAdmin = session('portal_employee_role', 'outlet_admin') === 'outlet_admin';

        return view('portal.stock_report', compact('outlet', 'reportData', 'dateStr', 'isOutletAdmin'));
    }

    public function dispatches()
    {
        $outletId = session('portal_outlet_id');
        $dispatches = Dispatch::where('outlet_id', $outletId)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('portal.dispatches', compact('dispatches'));
    }

    public function receiveDispatch(Request $request, Dispatch $dispatch)
    {
        $this->ensureOutletAdmin();
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);
        
        if ($dispatch->outlet_id != $outletId) {
            return back()->with('error', 'Unauthorized action for this outlet.');
        }

        if ($dispatch->status !== 'dispatched') {
            return back()->with('error', 'Only dispatched shipments in transit can be received.');
        }

        $dispatch->load('items');

        try {
            DB::beginTransaction();

            // 1. Increment outlet stocks and log movements
            // Load product type assignments for this outlet
            $productAssignments = \Illuminate\Support\Facades\DB::table('outlet_product')
                ->where('outlet_id', $outletId)
                ->whereNotNull('product_id')
                ->pluck('type', 'product_id');

            foreach ($dispatch->items as $item) {
                $search = ['outlet_id' => $outletId];
                if ($item->product_id) {
                    $search['product_id'] = $item->product_id;
                    $search['material_id'] = null;
                } else {
                    $search['product_id'] = null;
                    $search['material_id'] = $item->material_id;
                }

                $outletStock = OutletStock::firstOrCreate(
                    $search,
                    [
                        'quantity' => 0.00,
                        'store_quantity' => 0.00,
                        'kitchen_quantity' => 0.00,
                        'showcase_quantity' => 0.00,
                    ]
                );

                // Determine destination based on product type classification
                $destinationField = 'store_quantity';
                $destinationLabel = 'store';
                if ($item->product_id) {
                    $productType = $productAssignments[$item->product_id] ?? 'pre_cooked';
                    if ($productType === 'half_prepared') {
                        $destinationField = 'kitchen_quantity';
                        $destinationLabel = 'kitchen';
                    }
                }

                $outletStock->increment($destinationField, $item->quantity);
                $outletStock->increment('quantity', $item->quantity);

                // Log movement
                \App\Models\OutletStockMovement::create([
                    'outlet_id' => $outletId,
                    'product_id' => $item->product_id,
                    'material_id' => $item->material_id,
                    'from_location' => 'external',
                    'to_location' => $destinationLabel,
                    'quantity' => $item->quantity,
                    'logged_by' => $this->getLoggedByName(),
                    'reference' => $dispatch->dispatch_number,
                ]);
            }

            // 2. Set status to received
            $dispatch->update(['status' => 'received']);

            DB::commit();

            // Send notification to authorized roles (Admins, GMs, and Kitchen Chefs)
            $recipients = \App\Models\User::whereIn('role', ['admin', 'gm', 'laban_chef', 'baklava_chef', 'dough_chef'])->get();
            \Illuminate\Support\Facades\Notification::send($recipients, new \App\Notifications\ShipmentReceived($dispatch));

            return redirect()->route('portal.dispatches')
                ->with('success', "Shipment {$dispatch->dispatch_number} successfully received. Local stocks updated.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error receiving shipment: ' . $e->getMessage());
        }
    }

    public function salesIndex()
    {
        $outletId = session('portal_outlet_id');
        $salesLogs = SalesLog::where('outlet_id', $outletId)
            ->with('items.product')
            ->orderBy('log_date', 'desc')
            ->get();

        foreach ($salesLogs as $log) {
            $log->total_revenue = $log->items->sum('total_revenue');
            $log->commission_amount = $log->items->sum('commission_amount');
            $log->net_revenue = $log->items->sum('net_revenue');
        }

        return view('portal.sales_index', compact('salesLogs'));
    }

    public function salesCreate()
    {
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);

        // Only show products assigned to this outlet
        $assignedProductIds = $outlet->assignedProducts()->pluck('products.id');
        if ($assignedProductIds->isEmpty()) {
            $products = collect(); // No products assigned
        } else {
            $products = Product::whereIn('id', $assignedProductIds)->orderBy('name', 'asc')->get();
        }

        // Pass this outlet's current showcase stocks
        $stocks = OutletStock::where('outlet_id', $outletId)
            ->pluck('showcase_quantity', 'product_id');

        // Catalog items with showcase stock available for sale
        $catalogStocks = OutletStock::where('outlet_id', $outletId)
            ->whereNotNull('outlet_catalog_item_id')
            ->where('showcase_quantity', '>', 0)
            ->with('catalogItem')
            ->get();

        return view('portal.sales_create', compact('outlet', 'products', 'stocks', 'catalogStocks'));
    }

    public function salesStore(Request $request)
    {
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);

        $request->validate([
            'log_date' => 'required|date|before_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.outlet_catalog_item_id' => 'nullable|exists:outlet_catalog_items,id',
            'items.*.quantity_sold' => 'required|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            $salesLog = SalesLog::create([
                'outlet_id' => $outletId,
                'log_date' => $request->log_date,
                'logged_by_employee_id' => session('portal_employee_id'),
            ]);

            foreach ($request->items as $item) {
                $isCatalogItem = !empty($item['outlet_catalog_item_id']);

                if ($isCatalogItem) {
                    $catalogItem = \App\Models\OutletCatalogItem::findOrFail($item['outlet_catalog_item_id']);
                    $itemName = $catalogItem->name;
                    $unitPrice = (float) $catalogItem->retail_price;

                    // Stock check for catalog item
                    $outletStock = OutletStock::where('outlet_id', $outletId)
                        ->where('outlet_catalog_item_id', $catalogItem->id)
                        ->first();
                } else {
                    $product = Product::findOrFail($item['product_id']);
                    $itemName = $product->name;
                    $unitPrice = (float) $product->retail_price;

                    // Stock check for product
                    $outletStock = OutletStock::where('outlet_id', $outletId)
                        ->where('product_id', $product->id)
                        ->first();
                }

                $availableQty = $outletStock ? (float)$outletStock->showcase_quantity : 0.00;
                $quantitySold = (float)$item['quantity_sold'];

                if ($quantitySold > $availableQty) {
                    throw new \Exception("Insufficient stock for '{$itemName}'. Available at showcase: {$availableQty} units, Sold: {$quantitySold} units.");
                }

                // 2. Decrement outlet stock
                $outletStock->decrement('showcase_quantity', $quantitySold);
                $outletStock->decrement('quantity', $quantitySold);

                // Log movement
                \App\Models\OutletStockMovement::create([
                    'outlet_id' => $outletId,
                    'product_id' => $isCatalogItem ? null : $product->id,
                    'outlet_catalog_item_id' => $isCatalogItem ? $catalogItem->id : null,
                    'from_location' => 'showcase',
                    'to_location' => 'consumed',
                    'quantity' => $quantitySold,
                    'logged_by' => $this->getLoggedByName(),
                    'reference' => 'Sales Log #' . $salesLog->id,
                ]);

                // 3. Financial calculations
                $totalRevenue = $quantitySold * $unitPrice;
                
                $commissionAmount = 0.00;
                if ($outlet->type === 'franchise') {
                    $commissionAmount = $totalRevenue * ($outlet->commission_rate / 100);
                }
                
                $netRevenue = $totalRevenue - $commissionAmount;

                // 4. Save item
                SalesLogItem::create([
                    'sales_log_id' => $salesLog->id,
                    'product_id' => $isCatalogItem ? null : $product->id,
                    'outlet_catalog_item_id' => $isCatalogItem ? $catalogItem->id : null,
                    'quantity_sold' => $quantitySold,
                    'unit_price' => $unitPrice,
                    'total_revenue' => $totalRevenue,
                    'commission_amount' => $commissionAmount,
                    'net_revenue' => $netRevenue,
                ]);
            }

            // Calculate aggregates
            $totalRev = $salesLog->items()->sum('total_revenue');
            $netRev = $salesLog->items()->sum('net_revenue');

            if ($outlet->type === 'own') {
                // Direct retail sales (Debit Cash/Bank 1020, Credit Direct Sales 4010)
                $bankAccount = Account::where('code', '1020')->first();
                $salesAccount = Account::where('code', '4010')->first();
                if ($bankAccount && $salesAccount) {
                    $tx = JournalTransaction::create([
                        'reference' => "SL-{$salesLog->id}",
                        'description' => "Direct retail sales from Own Outlet: {$outlet->name}",
                        'date' => $salesLog->log_date,
                    ]);
                    JournalEntry::create([
                        'journal_transaction_id' => $tx->id,
                        'account_id' => $bankAccount->id,
                        'debit' => $totalRev,
                        'credit' => 0.00,
                    ]);
                    JournalEntry::create([
                        'journal_transaction_id' => $tx->id,
                        'account_id' => $salesAccount->id,
                        'debit' => 0.00,
                        'credit' => $totalRev,
                    ]);
                }
            } else {
                // Franchise sales: Generate Franchise Invoice (Receivable)
                $invoiceNumber = 'INV-' . now()->year . '-' . str_pad($salesLog->id, 4, '0', STR_PAD_LEFT);
                FranchiseInvoice::create([
                    'invoice_number' => $invoiceNumber,
                    'sales_log_id' => $salesLog->id,
                    'outlet_id' => $outlet->id,
                    'amount' => $netRev,
                    'status' => 'unpaid',
                    'due_date' => now()->addDays(7),
                    'notes' => "Auto-generated franchise share invoice for Sales Log #{$salesLog->id}",
                ]);

                // Create Journal Entry (Debit AR 1200, Credit Franchise Sales 4020)
                $arAccount = Account::where('code', '1200')->first();
                $salesAccount = Account::where('code', '4020')->first();
                if ($arAccount && $salesAccount) {
                    $tx = JournalTransaction::create([
                        'reference' => $invoiceNumber,
                        'description' => "Franchise invoice for sales log #{$salesLog->id} ({$outlet->name})",
                        'date' => $salesLog->log_date,
                    ]);
                    JournalEntry::create([
                        'journal_transaction_id' => $tx->id,
                        'account_id' => $arAccount->id,
                        'debit' => $netRev,
                        'credit' => 0.00,
                    ]);
                    JournalEntry::create([
                        'journal_transaction_id' => $tx->id,
                        'account_id' => $salesAccount->id,
                        'debit' => 0.00,
                        'credit' => $netRev,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('portal.sales.index')
                ->with('success', "Daily sales report logged successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error logging sales: ' . $e->getMessage());
        }
    }

    public function requestsCreate()
    {
        $this->ensureOutletAdmin();
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);

        // Only show products assigned to this outlet
        $assignedProductIds = $outlet->assignedProducts()->pluck('products.id');
        if ($assignedProductIds->isEmpty()) {
            $products = collect();
        } else {
            $products = Product::whereIn('id', $assignedProductIds)->orderBy('name', 'asc')->get();
        }

        // Only show packaging materials assigned to this outlet
        $assignedMaterialIds = $outlet->assignedMaterials()->pluck('materials.id');
        if ($assignedMaterialIds->isEmpty()) {
            $packagingMaterials = collect();
        } else {
            $packagingMaterials = Material::whereIn('id', $assignedMaterialIds)
                ->where('category', 'packaging')
                ->orderBy('name', 'asc')
                ->get();
        }

        return view('portal.requests_create', compact('outlet', 'products', 'packagingMaterials'));
    }

    public function requestsStore(Request $request)
    {
        $this->ensureOutletAdmin();
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);

        $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Generate unique Dispatch Number (Format: DISP-YYYY-XXXX)
            $year = now()->year;
            $latest = Dispatch::where('dispatch_number', 'like', "DISP-{$year}-%")->orderBy('id', 'desc')->first();
            $nextSerial = 1;
            if ($latest) {
                $parts = explode('-', $latest->dispatch_number);
                $nextSerial = (int)end($parts) + 1;
            }
            $dispatchNumber = 'DISP-' . $year . '-' . str_pad($nextSerial, 4, '0', STR_PAD_LEFT);

            $dispatch = Dispatch::create([
                'dispatch_number' => $dispatchNumber,
                'outlet_id' => $outlet->id,
                'dispatch_date' => now()->format('Y-m-d'),
                'status' => 'pending',
                'notes' => $request->notes ?? 'Requested via Outlet Portal.',
            ]);

            foreach ($request->items as $item) {
                list($type, $id) = explode(':', $item['item_id']);
                if ($type === 'product') {
                    DispatchItem::create([
                        'dispatch_id' => $dispatch->id,
                        'product_id' => $id,
                        'material_id' => null,
                        'quantity' => $item['quantity'],
                    ]);
                } else {
                    DispatchItem::create([
                        'dispatch_id' => $dispatch->id,
                        'product_id' => null,
                        'material_id' => $id,
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            DB::commit();

            // Trigger notification to authorized roles (Admins, GMs, Kitchen Chefs)
            $recipients = \App\Models\User::whereIn('role', ['admin', 'gm', 'laban_chef', 'baklava_chef', 'dough_chef'])->get();
            if ($recipients->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($recipients, new \App\Notifications\ProductRequestReceived($dispatch));
            }

            return redirect()->route('portal.dispatches')
                ->with('success', "Product request {$dispatchNumber} has been sent to the Central Kitchen.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error submitting product request: ' . $e->getMessage());
        }
    }

    public function moveStock(Request $request)
    {
        $this->ensureOutletAdmin();
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);
        
        $request->validate([
            'stock_id' => 'required|exists:outlet_stocks,id',
            'from_location' => 'required|in:store,kitchen',
            'to_location' => 'required|in:kitchen,showcase,consumed',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $stock = OutletStock::where('outlet_id', $outletId)->findOrFail($request->stock_id);
        $qtyToMove = (float)$request->quantity;

        $sourceField = $request->from_location . '_quantity';
        $currentSourceQty = (float)$stock->$sourceField;

        if ($qtyToMove > $currentSourceQty) {
            return back()->with('error', "Insufficient stock in {$request->from_location}. Available: {$currentSourceQty}, Requested: {$qtyToMove}");
        }

        try {
            DB::beginTransaction();

            // 1. Decrement source location
            $stock->decrement($sourceField, $qtyToMove);

            // 2. Increment target location or handle consumption
            if ($request->to_location === 'consumed') {
                $stock->decrement('quantity', $qtyToMove);
                $msg = "Recorded consumption of {$qtyToMove} units of " . ($stock->product_id ? $stock->product->name : $stock->material->name) . ".";
            } else {
                $targetField = $request->to_location . '_quantity';
                
                if ($request->to_location === 'showcase' && !$stock->product_id) {
                    throw new \Exception("Only finished products can be moved to the showcase.");
                }
                
                $stock->increment($targetField, $qtyToMove);
                $msg = "Successfully transferred {$qtyToMove} units from " . ucfirst($request->from_location) . " to " . ucfirst($request->to_location) . ".";
            }

            // Create movement log
            \App\Models\OutletStockMovement::create([
                'outlet_id' => $outletId,
                'product_id' => $stock->product_id,
                'material_id' => $stock->material_id,
                'from_location' => $request->from_location,
                'to_location' => $request->to_location,
                'quantity' => $qtyToMove,
                'logged_by' => $this->getLoggedByName(),
                'reference' => 'Manual Move',
            ]);

            DB::commit();

            // Increment data pulse version
            DB::table('universal_searches')->where('id', 1)->increment('data_pulse_version');

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error transferring stock: ' . $e->getMessage());
        }
    }
}
