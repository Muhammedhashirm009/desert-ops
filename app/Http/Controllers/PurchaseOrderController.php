<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'items.material']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->get();
        return view('purchase_orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $materials = Material::with('suppliers')->orderBy('name', 'asc')->get();

        if ($suppliers->isEmpty() || $materials->isEmpty()) {
            return redirect()->route('purchase-orders.index')->with('error', 'Please ensure you have created at least one Supplier and one Material before creating a Purchase Order.');
        }

        return view('purchase_orders.create', compact('suppliers', 'materials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'eta' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.supplier_id' => 'nullable|exists:suppliers,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $items = [];
            foreach ($request->items as $item) {
                $supplierId = $item['supplier_id'] ?? $request->supplier_id;
                if (!$supplierId) {
                    return back()->withInput()->with('error', 'Please select a supplier for each material item.');
                }
                $item['supplier_id'] = $supplierId;
                $items[] = $item;
            }

            $itemsBySupplier = collect($items)->groupBy('supplier_id');
            $createdPOIds = [];
            $poNumbers = [];

            foreach ($itemsBySupplier as $supplierId => $supplierItems) {
                // Generate unique PO Number (Format: PO-YYYY-XXXX)
                $year = now()->year;
                $latest = PurchaseOrder::where('po_number', 'like', "PO-{$year}-%")->orderBy('id', 'desc')->first();
                $nextSerial = 1;
                if ($latest) {
                    $parts = explode('-', $latest->po_number);
                    $nextSerial = (int)end($parts) + 1;
                }
                $poNumber = 'PO-' . $year . '-' . str_pad($nextSerial, 4, '0', STR_PAD_LEFT);

                // Calculate total amount (handle null unit_prices)
                $totalAmount = 0;
                foreach ($supplierItems as $item) {
                    $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : 0;
                    $totalAmount += $item['quantity'] * $unitPrice;
                }

                // Create Purchase Order
                $purchaseOrder = PurchaseOrder::create([
                    'po_number' => $poNumber,
                    'supplier_id' => $supplierId,
                    'status' => 'pending',
                    'total_amount' => $totalAmount,
                    'notes' => $request->notes,
                    'eta' => $request->eta,
                ]);

                // Create PO Items
                foreach ($supplierItems as $item) {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'material_id' => $item['material_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                    ]);
                }

                $createdPOIds[] = $purchaseOrder->id;
                $poNumbers[] = $poNumber;
            }

            DB::commit();

            if (count($createdPOIds) === 1) {
                return redirect()->route('purchase-orders.show', $createdPOIds[0])
                    ->with('success', "Purchase Order {$poNumbers[0]} generated successfully.");
            } else {
                return redirect()->route('purchase-orders.index')
                    ->with('success', "Multiple Purchase Orders created successfully: " . implode(', ', $poNumbers));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error generating Purchase Order: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.material', 'grn']);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        return redirect()->route('purchase-orders.show', $purchaseOrder->id);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        return redirect()->route('purchase-orders.show', $purchaseOrder->id);
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return back()->with('error', 'Only pending Purchase Orders can be cancelled.');
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()->route('purchase-orders.index')->with('success', "Purchase Order {$purchaseOrder->po_number} has been cancelled.");
    }
}
