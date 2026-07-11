<?php

namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchController extends Controller
{
    public function index()
    {
        $dispatches = Dispatch::with(['outlet', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('dispatches.index', compact('dispatches'));
    }

    public function create()
    {
        $outlets = Outlet::orderBy('name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();
        return view('dispatches.create', compact('outlets', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'dispatch_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
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
                'outlet_id' => $request->outlet_id,
                'dispatch_date' => $request->dispatch_date,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                DispatchItem::create([
                    'dispatch_id' => $dispatch->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            return redirect()->route('dispatches.show', $dispatch->id)
                ->with('success', "Dispatch shipment {$dispatchNumber} created successfully in pending state.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating dispatch shipment: ' . $e->getMessage());
        }
    }

    public function show(Dispatch $dispatch)
    {
        $dispatch->load(['outlet', 'items.product', 'items.material']);
        return view('dispatches.show', compact('dispatch'));
    }

    public function dispatch(Dispatch $dispatch)
    {
        if ($dispatch->status !== 'pending') {
            return back()->with('error', 'Shipment is already dispatched or received.');
        }

        $dispatch->load(['items.product', 'items.material', 'outlet']);

        try {
            DB::beginTransaction();

            // 1. Stock Check: Verify stock in Central Kitchen is sufficient
            foreach ($dispatch->items as $item) {
                if ($item->product_id) {
                    $product = $item->product;
                    if ($item->quantity > $product->current_kitchen_stock) {
                        throw new \Exception("Insufficient stock in Central Kitchen for dessert '{$product->name}'. Available in kitchen: {$product->current_kitchen_stock} units, Required to ship: {$item->quantity} units.");
                    }
                } else {
                    $material = $item->material;
                    $perBox = $material->per_box_qty ?: 1;
                    $reqBoxes = $item->quantity / $perBox;
                    if ($reqBoxes > $material->kitchen_stock) {
                        $availablePieces = $material->kitchen_stock * $perBox;
                        throw new \Exception("Insufficient stock in Central Kitchen for packaging '{$material->name}'. Available in kitchen: {$availablePieces} pieces ({$material->kitchen_stock} boxes), Required to ship: {$item->quantity} pieces.");
                    }
                }
            }

            // 2. Decrement Central Kitchen stock for each item
            foreach ($dispatch->items as $item) {
                if ($item->product_id) {
                    $item->product->decrement('current_kitchen_stock', $item->quantity);
                } else {
                    $material = $item->material;
                    $perBox = $material->per_box_qty ?: 1;
                    $reqBoxes = $item->quantity / $perBox;
                    $material->decrement('kitchen_stock', $reqBoxes);
                }
            }

            // 3. Update status
            $dispatch->update(['status' => 'dispatched']);

            DB::commit();

            // Send notification to outlet
            $dispatch->outlet->notify(new \App\Notifications\ShipmentDispatched($dispatch));

            return redirect()->route('dispatches.show', $dispatch->id)
                ->with('success', "Shipment {$dispatch->dispatch_number} marked as DISPATCHED. Stock decremented from Central Kitchen.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error dispatching shipment: ' . $e->getMessage());
        }
    }

    public function receive(Dispatch $dispatch)
    {
        if ($dispatch->status !== 'dispatched') {
            return back()->with('error', 'Only dispatched shipments can be marked as received.');
        }

        $dispatch->load('items');

        try {
            DB::beginTransaction();

            // 1. Increment target outlet stocks
            // Load product type assignments for this outlet
            $productAssignments = \Illuminate\Support\Facades\DB::table('outlet_product')
                ->where('outlet_id', $dispatch->outlet_id)
                ->whereNotNull('product_id')
                ->pluck('type', 'product_id');

            foreach ($dispatch->items as $item) {
                $search = ['outlet_id' => $dispatch->outlet_id];
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
                    'outlet_id' => $dispatch->outlet_id,
                    'product_id' => $item->product_id,
                    'material_id' => $item->material_id,
                    'from_location' => 'external',
                    'to_location' => $destinationLabel,
                    'quantity' => $item->quantity,
                    'logged_by' => 'Central Kitchen Dispatch',
                    'reference' => $dispatch->dispatch_number,
                ]);
            }

            // 2. Update status
            $dispatch->update(['status' => 'received']);

            DB::commit();

            return redirect()->route('dispatches.show', $dispatch->id)
                ->with('success', "Shipment {$dispatch->dispatch_number} marked as RECEIVED. Stock incremented at the destination outlet.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error marking shipment as received: ' . $e->getMessage());
        }
    }

    public function destroy(Dispatch $dispatch)
    {
        if ($dispatch->status !== 'pending') {
            return back()->with('error', 'Only pending shipments can be cancelled.');
        }

        $dispatch->update(['status' => 'cancelled']);
        return redirect()->back()->with('success', 'Order cancelled successfully.');
    }

    public function outletOrders()
    {
        $dispatches = Dispatch::with(['outlet', 'items.product', 'items.material'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $cancelledDispatches = Dispatch::with(['outlet', 'items.product', 'items.material'])
            ->where('status', 'cancelled')
            ->orderBy('updated_at', 'desc')
            ->take(15)
            ->get();

        $requirements = [];
        foreach ($dispatches as $disp) {
            foreach ($disp->items as $item) {
                if ($item->product_id) {
                    $key = 'product:' . $item->product_id;
                    if (!isset($requirements[$key])) {
                        $requirements[$key] = [
                            'is_product' => true,
                            'name' => $item->product->name,
                            'sku' => $item->product->sku,
                            'requested_qty' => 0,
                            'stock' => $item->product->current_kitchen_stock,
                            'unit' => 'Units',
                        ];
                    }
                    $requirements[$key]['requested_qty'] += $item->quantity;
                } else {
                    $key = 'material:' . $item->material_id;
                    if (!isset($requirements[$key])) {
                        $perBox = $item->material->per_box_qty ?: 1;
                        $requirements[$key] = [
                            'is_product' => false,
                            'name' => $item->material->name,
                            'sku' => $item->material->sku,
                            'requested_qty' => 0,
                            'stock' => $item->material->kitchen_stock * $perBox,
                            'unit' => 'Pieces',
                            'per_box' => $perBox,
                        ];
                    }
                    $requirements[$key]['requested_qty'] += $item->quantity;
                }
            }
        }
        $requirements = collect($requirements)->sortBy('name');

        return view('dispatches.orders', compact('dispatches', 'cancelledDispatches', 'requirements'));
    }

}
