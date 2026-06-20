<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteItem;
use App\Models\Material;
use App\Models\PurchaseOrder;
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

            // Save items, increment stock levels
            foreach ($request->items as $itemId => $itemData) {
                // Save GRN Item
                GoodsReceivedNoteItem::create([
                    'goods_received_note_id' => $grn->id,
                    'material_id' => $itemData['material_id'],
                    'quantity_received' => $itemData['quantity_received'],
                ]);

                // Increment stock level in Materials
                if ($itemData['quantity_received'] > 0) {
                    $material = Material::findOrFail($itemData['material_id']);
                    $material->increment('current_stock', $itemData['quantity_received']);
                }
            }

            // Update Purchase Order status to received
            $purchaseOrder->update(['status' => 'received']);

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
