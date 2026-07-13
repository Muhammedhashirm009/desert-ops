<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\OutletStock;
use App\Models\Product;
use App\Models\ShowcaseRequest;
use App\Models\ShowcaseRequestItem;
use App\Models\OutletStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortalShowcaseRequestController extends Controller
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
    public function index()
    {
        $outletId = session('portal_outlet_id');
        $showcaseRequests = ShowcaseRequest::where('outlet_id', $outletId)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('portal.showcase_requests.index', compact('showcaseRequests'));
    }

    public function create()
    {
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);
        $products = $outlet->assignedProducts()->orderBy('name', 'asc')->get();

        return view('portal.showcase_requests.create', compact('outlet', 'products'));
    }

    public function store(Request $request)
    {
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);

        $request->validate([
            'requested_by' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_requested' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $year = now()->year;
            $latest = ShowcaseRequest::where('request_number', 'like', "SR-{$year}-%")
                ->orderBy('id', 'desc')
                ->first();
            $nextSerial = 1;
            if ($latest) {
                $parts = explode('-', $latest->request_number);
                $nextSerial = intval(end($parts)) + 1;
            }
            $requestNumber = 'SR-' . $year . '-' . str_pad($nextSerial, 4, '0', STR_PAD_LEFT);

            $showcaseRequest = ShowcaseRequest::create([
                'request_number' => $requestNumber,
                'outlet_id' => $outletId,
                'requested_by' => $request->requested_by ?: session('portal_employee_name'),
                'requested_date' => now()->toDateString(),
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $showcaseRequest->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'quantity_released' => 0.00,
                    'release_source' => null,
                ]);
            }

            DB::commit();

            return redirect()->route('portal.showcase-requests.index')
                ->with('success', "Showcase Request {$requestNumber} submitted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error submitting showcase request: ' . $e->getMessage());
        }
    }

    public function show(ShowcaseRequest $showcaseRequest)
    {
        $outletId = session('portal_outlet_id');
        if ($showcaseRequest->outlet_id != $outletId) {
            abort(403, 'Unauthorized access to this request.');
        }

        $showcaseRequest->load('items.product');

        // Fetch current stocks for display and source selection validation
        $stocks = OutletStock::where('outlet_id', $outletId)
            ->pluck('quantity', 'product_id');
            
        // Detailed quantities
        $detailedStocks = OutletStock::where('outlet_id', $outletId)
            ->get()
            ->keyBy('product_id');

        return view('portal.showcase_requests.show', compact('showcaseRequest', 'stocks', 'detailedStocks'));
    }

    public function approve(ShowcaseRequest $showcaseRequest)
    {
        $this->ensureOutletAdmin();
        $outletId = session('portal_outlet_id');
        if ($showcaseRequest->outlet_id != $outletId) {
            abort(403, 'Unauthorized access.');
        }

        $showcaseRequest->update(['status' => 'approved']);

        return redirect()->route('portal.showcase-requests.show', $showcaseRequest)
            ->with('success', "Request {$showcaseRequest->request_number} has been approved.");
    }

    public function reject(ShowcaseRequest $showcaseRequest)
    {
        $this->ensureOutletAdmin();
        $outletId = session('portal_outlet_id');
        if ($showcaseRequest->outlet_id != $outletId) {
            abort(403, 'Unauthorized access.');
        }

        $showcaseRequest->update(['status' => 'rejected']);

        return redirect()->route('portal.showcase-requests.show', $showcaseRequest)
            ->with('success', "Request {$showcaseRequest->request_number} has been rejected.");
    }

    public function release(Request $request, ShowcaseRequest $showcaseRequest)
    {
        $this->ensureOutletAdmin();
        $outletId = session('portal_outlet_id');
        if ($showcaseRequest->outlet_id != $outletId) {
            abort(403, 'Unauthorized access.');
        }

        if ($showcaseRequest->status !== 'approved') {
            return back()->with('error', 'Only approved requests can be released.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.release_source' => 'required|in:store,kitchen',
            'items.*.quantity_released' => 'required|numeric|min:0.01',
        ]);

        $outlet = Outlet::findOrFail($outletId);

        try {
            DB::beginTransaction();

            foreach ($showcaseRequest->items as $item) {
                $itemId = $item->id;
                $inputItem = $request->input("items.{$itemId}");

                if (!$inputItem) {
                    throw new \Exception("Missing release source or quantity for " . $item->product->name);
                }

                $source = $inputItem['release_source'];
                $qtyReleased = (float)$inputItem['quantity_released'];

                // Get outlet stock for this product
                $stock = OutletStock::where('outlet_id', $outletId)
                    ->where('product_id', $item->product_id)
                    ->first();

                if (!$stock) {
                    throw new \Exception("No stock record found for product {$item->product->name} at this outlet.");
                }

                $sourceField = $source . '_quantity';
                $availableQty = (float)$stock->$sourceField;

                if ($qtyReleased > $availableQty) {
                    throw new \Exception("Insufficient stock in " . ucfirst($source) . " for product {$item->product->name}. Available: {$availableQty}, Requested: {$qtyReleased}");
                }

                // Decrement from chosen location stock
                $stock->decrement($sourceField, $qtyReleased);

                // Increment showcase_quantity and total quantity on OutletStock
                $stock->increment('showcase_quantity', $qtyReleased);
                $stock->increment('quantity', $qtyReleased);

                // Update item with released details
                $item->update([
                    'quantity_released' => $qtyReleased,
                    'release_source' => ucfirst($source),
                ]);

                // Log the movement in outlet_stock_movements
                // fields: outlet_id, product_id, from_location, to_location, quantity, logged_by, reference
                \App\Models\OutletStockMovement::create([
                    'outlet_id' => $outletId,
                    'product_id' => $item->product_id,
                    'material_id' => null,
                    'from_location' => $source,
                    'to_location' => 'showcase',
                    'quantity' => $qtyReleased,
                    'logged_by' => session('portal_employee_name', ($outlet->name . ' Staff')),
                    'reference' => $showcaseRequest->request_number,
                ]);
            }

            // Mark request status as 'released'
            $showcaseRequest->update(['status' => 'released']);

            DB::commit();

            // Increment data pulse version
            try {
                DB::table('universal_searches')->where('id', 1)->increment('data_pulse_version');
            } catch (\Exception $e) {}

            return redirect()->route('portal.showcase-requests.show', $showcaseRequest)
                ->with('success', "Showcase Request {$showcaseRequest->request_number} released successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error during release: ' . $e->getMessage());
        }
    }
}
