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
        $dispatch->load(['outlet', 'items.product']);
        return view('dispatches.show', compact('dispatch'));
    }

    public function dispatch(Dispatch $dispatch)
    {
        if ($dispatch->status !== 'pending') {
            return back()->with('error', 'Shipment is already dispatched or received.');
        }

        $dispatch->load('items.product');

        try {
            DB::beginTransaction();

            // 1. Stock Check: Verify Central Kitchen finished stock is sufficient
            foreach ($dispatch->items as $item) {
                $product = $item->product;
                if ($item->quantity > $product->current_kitchen_stock) {
                    throw new \Exception("Insufficient stock in Central Kitchen for dessert '{$product->name}'. Available in kitchen: {$product->current_kitchen_stock} units, Required to ship: {$item->quantity} units.");
                }
            }

            // 2. Decrement Central Kitchen stock for each item
            foreach ($dispatch->items as $item) {
                $item->product->decrement('current_kitchen_stock', $item->quantity);
            }

            // 3. Update status
            $dispatch->update(['status' => 'dispatched']);

            DB::commit();

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
            foreach ($dispatch->items as $item) {
                $outletStock = OutletStock::firstOrCreate(
                    [
                        'outlet_id' => $dispatch->outlet_id,
                        'product_id' => $item->product_id,
                    ],
                    [
                        'quantity' => 0.00,
                    ]
                );

                $outletStock->increment('quantity', $item->quantity);
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
            return back()->with('error', 'Only pending shipments can be deleted.');
        }

        $dispatch->delete();
        return redirect()->route('dispatches.index')->with('success', 'Dispatch shipment deleted.');
    }
}
