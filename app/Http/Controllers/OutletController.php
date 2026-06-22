<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\Product;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function index()
    {
        $outlets = Outlet::withCount('stocks')
            ->orderBy('name', 'asc')
            ->get();
        return view('outlets.index', compact('outlets'));
    }

    public function create()
    {
        return view('outlets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:own,franchise',
            'commission_rate' => 'required_if:type,franchise|numeric|min:0|max:100',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'address' => 'nullable|string',
        ]);

        // Force commission rate to 0 for own outlets
        if ($validated['type'] === 'own') {
            $validated['commission_rate'] = 0.00;
        }

        Outlet::create($validated);

        return redirect()->route('outlets.index')->with('success', 'Outlet added successfully.');
    }

    public function show(Outlet $outlet)
    {
        $outlet->load(['stocks.product', 'dispatches' => function($q) {
            $q->orderBy('created_at', 'desc')->take(10);
        }, 'salesLogs' => function($q) {
            $q->orderBy('created_at', 'desc')->take(10);
        }]);

        return view('outlets.show', compact('outlet'));
    }

    public function edit(Outlet $outlet)
    {
        return view('outlets.edit', compact('outlet'));
    }

    public function update(Request $request, Outlet $outlet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:own,franchise',
            'commission_rate' => 'required_if:type,franchise|numeric|min:0|max:100',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'address' => 'nullable|string',
        ]);

        if ($validated['type'] === 'own') {
            $validated['commission_rate'] = 0.00;
        }

        $outlet->update($validated);

        return redirect()->route('outlets.index')->with('success', 'Outlet updated successfully.');
    }

    public function destroy(Outlet $outlet)
    {
        $outlet->delete();
        return redirect()->route('outlets.index')->with('success', 'Outlet deleted successfully.');
    }
}
