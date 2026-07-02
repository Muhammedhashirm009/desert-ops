<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialRequestController extends Controller
{
    public function index()
    {
        $materialRequests = MaterialRequest::with('items.material')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('material_requests.index', compact('materialRequests'));
    }

    public function create()
    {
        $materials = Material::orderBy('name', 'asc')->get();
        return view('material_requests.create', compact('materials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'requested_by' => 'required|string|max:255',
            'requested_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity_requested' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            // Generate unique Request Number (Format: MR-YYYY-XXXX)
            $year = now()->year;
            $latest = MaterialRequest::where('request_number', 'like', "MR-{$year}-%")->orderBy('id', 'desc')->first();
            $nextSerial = 1;
            if ($latest) {
                $parts = explode('-', $latest->request_number);
                $nextSerial = (int)end($parts) + 1;
            }
            $requestNumber = 'MR-' . $year . '-' . str_pad($nextSerial, 4, '0', STR_PAD_LEFT);

            // Create Request
            $materialRequest = MaterialRequest::create([
                'request_number' => $requestNumber,
                'requested_by' => $request->requested_by,
                'requested_date' => $request->requested_date,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Save Items
            foreach ($request->items as $item) {
                MaterialRequestItem::create([
                    'material_request_id' => $materialRequest->id,
                    'material_id' => $item['material_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'quantity_released' => 0,
                ]);
            }

            DB::commit();

            // Notify Store Managers, GMs, and Admins
            $recipients = \App\Models\User::whereIn('role', ['admin', 'gm', 'store_manager'])->get();
            if ($recipients->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($recipients, new \App\Notifications\MaterialRequestCreated($materialRequest));
            }

            return redirect()->route('material-requests.show', $materialRequest->id)
                ->with('success', "Material Request {$requestNumber} submitted to Store Manager.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error submitting request: ' . $e->getMessage());
        }
    }

    public function show(MaterialRequest $materialRequest)
    {
        $materialRequest->load('items.material');
        return view('material_requests.show', compact('materialRequest'));
    }

    public function edit(MaterialRequest $materialRequest)
    {
        return redirect()->route('material-requests.show', $materialRequest->id);
    }

    public function update(Request $request, MaterialRequest $materialRequest)
    {
        return redirect()->route('material-requests.show', $materialRequest->id);
    }

    public function destroy(MaterialRequest $materialRequest)
    {
        if ($materialRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        $materialRequest->delete();

        return redirect()->route('material-requests.index')->with('success', 'Material Request cancelled.');
    }

    public function approve(MaterialRequest $materialRequest)
    {
        if ($materialRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $materialRequest->update(['status' => 'approved']);

        return redirect()->route('material-requests.show', $materialRequest->id)
            ->with('success', 'Material Request approved. Waiting for release.');
    }

    public function release(Request $request, MaterialRequest $materialRequest)
    {
        if ($materialRequest->status !== 'approved') {
            return back()->with('error', 'Only approved requests can be released.');
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity_released' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Stock Check first
            foreach ($request->items as $itemId => $itemData) {
                $material = Material::findOrFail($itemData['material_id']);
                $qtyReleased = $itemData['quantity_released'];

                if ($qtyReleased > $material->current_stock) {
                    throw new \Exception("Insufficient stock for material '{$material->name}'. Available: {$material->current_stock} {$material->unit}, Requested Release: {$qtyReleased} {$material->unit}.");
                }
            }

            // Perform release and stock decrement
            foreach ($request->items as $itemId => $itemData) {
                $material = Material::findOrFail($itemData['material_id']);
                $qtyReleased = $itemData['quantity_released'];

                // Update request item
                $reqItem = MaterialRequestItem::where('material_request_id', $materialRequest->id)
                    ->where('material_id', $material->id)
                    ->firstOrFail();
                $reqItem->update(['quantity_released' => $qtyReleased]);

                // Decrement store stock and increment kitchen stock
                if ($qtyReleased > 0) {
                    $material->decrement('current_stock', $qtyReleased);
                    $material->increment('kitchen_stock', $qtyReleased);
                }
            }

            // Update status
            $materialRequest->update(['status' => 'released']);

            DB::commit();

            // Notify Kitchen Chefs, GMs, and Admins
            $recipients = \App\Models\User::whereIn('role', ['admin', 'gm', 'kitchen_chef'])->get();
            if ($recipients->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($recipients, new \App\Notifications\MaterialRequestReleased($materialRequest));
            }

            return redirect()->route('material-requests.show', $materialRequest->id)
                ->with('success', "Materials released successfully. Inventory stocks updated.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error releasing materials: ' . $e->getMessage());
        }
    }

    public function reject(MaterialRequest $materialRequest)
    {
        if (!in_array($materialRequest->status, ['pending', 'approved'])) {
            return back()->with('error', 'This request cannot be rejected.');
        }

        $materialRequest->update(['status' => 'rejected']);

        return redirect()->route('material-requests.show', $materialRequest->id)
            ->with('success', 'Material Request rejected.');
    }
}
