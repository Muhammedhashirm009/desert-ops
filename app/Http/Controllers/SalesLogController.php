<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use App\Models\SalesLog;
use App\Models\SalesLogItem;
use App\Models\Account;
use App\Models\JournalTransaction;
use App\Models\JournalEntry;
use App\Models\FranchiseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesLogController extends Controller
{
    public function index()
    {
        $salesLogs = SalesLog::with(['outlet', 'items'])
            ->orderBy('log_date', 'desc')
            ->get();
            
        // Calculate totals for each log
        foreach ($salesLogs as $log) {
            $log->total_revenue = $log->items->sum('total_revenue');
            $log->commission_amount = $log->items->sum('commission_amount');
            $log->net_revenue = $log->items->sum('net_revenue');
        }

        return view('sales_logs.index', compact('salesLogs'));
    }

    public function create()
    {
        $outlets = Outlet::orderBy('name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();
        
        // Pass outlet stocks so we can reference them in JS
        $outletStocks = OutletStock::get()
            ->groupBy('outlet_id')
            ->map(function ($items) {
                return $items->pluck('quantity', 'product_id');
            });

        return view('sales_logs.create', compact('outlets', 'products', 'outletStocks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'log_date' => 'required|date|before_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_sold' => 'required|numeric|min:1',
        ]);

        $outlet = Outlet::findOrFail($request->outlet_id);

        try {
            DB::beginTransaction();

            $salesLog = SalesLog::create([
                'outlet_id' => $request->outlet_id,
                'log_date' => $request->log_date,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // 1. Stock Check: Fetch outlet stock and verify availability
                $outletStock = OutletStock::where('outlet_id', $outlet->id)
                    ->where('product_id', $product->id)
                    ->first();
                
                $availableQty = $outletStock ? (float)$outletStock->quantity : 0.00;
                $quantitySold = (float)$item['quantity_sold'];

                if ($quantitySold > $availableQty) {
                    throw new \Exception("Insufficient stock at outlet '{$outlet->name}' for product '{$product->name}'. Available at outlet: {$availableQty} units, Sold: {$quantitySold} units.");
                }

                // 2. Decrement outlet stock
                $outletStock->decrement('quantity', $quantitySold);

                // 3. Calculate financial metrics
                $unitPrice = $product->retail_price;
                $totalRevenue = $quantitySold * $unitPrice;
                
                $commissionAmount = 0.00;
                if ($outlet->type === 'franchise') {
                    $commissionAmount = $totalRevenue * ($outlet->commission_rate / 100);
                }
                
                $netRevenue = $totalRevenue - $commissionAmount;

                // 4. Create sales log item
                SalesLogItem::create([
                    'sales_log_id' => $salesLog->id,
                    'product_id' => $product->id,
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

            return redirect()->route('sales-logs.show', $salesLog->id)
                ->with('success', "Daily sales report for {$outlet->name} logged successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error logging sales: ' . $e->getMessage());
        }
    }

    public function show(SalesLog $salesLog)
    {
        $salesLog->load(['outlet', 'items.product']);
        
        $salesLog->total_revenue = $salesLog->items->sum('total_revenue');
        $salesLog->commission_amount = $salesLog->items->sum('commission_amount');
        $salesLog->net_revenue = $salesLog->items->sum('net_revenue');

        return view('sales_logs.show', compact('salesLog'));
    }

    public function destroy(SalesLog $salesLog)
    {
        try {
            DB::beginTransaction();

            // Revert stocks back to the outlet upon deletion of sales log!
            $salesLog->load('items');
            foreach ($salesLog->items as $item) {
                $outletStock = OutletStock::where('outlet_id', $salesLog->outlet_id)
                    ->where('product_id', $item->product_id)
                    ->first();
                if ($outletStock) {
                    $outletStock->increment('quantity', $item->quantity_sold);
                }
            }

            $salesLog->delete();

            DB::commit();
            return redirect()->route('sales-logs.index')->with('success', 'Sales log deleted and stocks restored.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting sales log: ' . $e->getMessage());
        }
    }
}
