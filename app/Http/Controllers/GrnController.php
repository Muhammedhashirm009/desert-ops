<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteItem;
use App\Models\Material;
use App\Models\PriceHistory;
use App\Models\PurchaseOrder;
use App\Models\Account;
use App\Models\JournalTransaction;
use App\Models\JournalEntry;
use App\Models\SupplierBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrnController extends Controller
{
    public function index()
    {
        $grns = GoodsReceivedNote::with(['purchaseOrder.supplier'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('grns.index', compact('grns'));
    }

    public function create(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Only pending Purchase Orders can be marked as received.');
        }

        $purchaseOrder->load(['supplier', 'items.material']);
        return view('grns.create', compact('purchaseOrder'));
    }

    public function store(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', 'Only pending Purchase Orders can be marked as received.');
        }

        $request->validate([
            'received_by' => 'required|string|max:255',
            'received_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generate unique GRN Number (Format: GRN-YYYY-XXXX)
            $year = now()->year;
            $latest = GoodsReceivedNote::where('grn_number', 'like', "GRN-{$year}-%")->orderBy('id', 'desc')->first();
            $nextSerial = 1;
            if ($latest) {
                $parts = explode('-', $latest->grn_number);
                $nextSerial = (int)end($parts) + 1;
            }
            $grnNumber = 'GRN-' . $year . '-' . str_pad($nextSerial, 4, '0', STR_PAD_LEFT);

            // Create Goods Received Note
            $grn = GoodsReceivedNote::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $purchaseOrder->id,
                'received_date' => $request->received_date,
                'received_by' => $request->received_by,
                'notes' => $request->notes,
            ]);

            $totalGrnCost = 0;

            foreach ($request->items as $itemId => $itemData) {
                $qtyReceived = (float) $itemData['quantity_received'];
                $orderedItem = $purchaseOrder->items->where('material_id', $itemData['material_id'])->first();
                $unitCost = isset($itemData['unit_cost']) && $itemData['unit_cost'] !== '' && $itemData['unit_cost'] !== null 
                    ? (float) $itemData['unit_cost'] 
                    : (float) ($orderedItem->unit_price ?? 0);
                $lineCost = round($unitCost * $qtyReceived, 2);

                // Save GRN Item with cost
                GoodsReceivedNoteItem::create([
                    'goods_received_note_id' => $grn->id,
                    'material_id' => $itemData['material_id'],
                    'quantity_received' => $qtyReceived,
                    'unit_cost' => $unitCost,
                    'line_cost' => $lineCost,
                ]);

                $totalGrnCost += $lineCost;

                // Update material stock & WAC
                if ($qtyReceived > 0) {
                    $material = Material::findOrFail($itemData['material_id']);
                    $oldStock = (float) $material->current_stock;
                    $oldWAC = (float) $material->cost_price;

                    // WAC calculation
                    $totalOldValue = $oldStock * $oldWAC;
                    $totalNewValue = $qtyReceived * $unitCost;
                    $newTotalStock = $oldStock + $qtyReceived;
                    $newWAC = $newTotalStock > 0 ? round(($totalOldValue + $totalNewValue) / $newTotalStock, 2) : $unitCost;

                    // Log price history if cost changed
                    if (abs($newWAC - $oldWAC) > 0.001) {
                        PriceHistory::create([
                            'item_type' => 'material',
                            'item_id' => $material->id,
                            'old_cost_price' => $oldWAC,
                            'new_cost_price' => $newWAC,
                            'quantity_received' => $qtyReceived,
                            'unit_cost' => $unitCost,
                            'grn_id' => $grn->id,
                            'supplier_name' => $purchaseOrder->supplier->name ?? null,
                            'changed_by' => auth()->id(),
                        ]);
                    }

                    $material->update(['cost_price' => $newWAC]);
                    $material->increment('current_stock', $qtyReceived);
                }
            }

            // Update GRN total cost
            $grn->update(['total_cost' => $totalGrnCost]);

            // Update Purchase Order status to received
            $purchaseOrder->update(['status' => 'received']);

            // Accounting Integration: Generate Supplier Bill and General Ledger Transaction
            $billNumber = 'BILL-' . now()->year . '-' . str_pad($purchaseOrder->id, 4, '0', STR_PAD_LEFT);
            SupplierBill::create([
                'bill_number' => $billNumber,
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'amount' => $totalGrnCost,
                'status' => 'unpaid',
                'due_date' => now()->addDays(15),
                'notes' => "Auto-generated from PO receipt (GRN {$grnNumber})",
            ]);

            // Create Journal Transaction (Debit Inventory 1300, Credit AP 2100)
            $invAccount = Account::where('code', '1300')->first();
            $apAccount = Account::where('code', '2100')->first();
            if ($invAccount && $apAccount) {
                $tx = JournalTransaction::create([
                    'reference' => $purchaseOrder->po_number,
                    'description' => "Supplier Bill for Purchase Order {$purchaseOrder->po_number}",
                    'date' => now(),
                ]);
                JournalEntry::create([
                    'journal_transaction_id' => $tx->id,
                    'account_id' => $invAccount->id,
                    'debit' => $totalGrnCost,
                    'credit' => 0.00,
                ]);
                JournalEntry::create([
                    'journal_transaction_id' => $tx->id,
                    'account_id' => $apAccount->id,
                    'debit' => 0.00,
                    'credit' => $totalGrnCost,
                ]);
            }

            DB::commit();

            return redirect()->route('grns.show', $grn->id)
                ->with('success', "GRN {$grnNumber} generated successfully. Stock levels updated.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error generating GRN: ' . $e->getMessage());
        }
    }

    public function show(GoodsReceivedNote $grn)
    {
        $grn->load(['purchaseOrder.supplier', 'items.material']);
        return view('grns.show', compact('grn'));
    }
}
