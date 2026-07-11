<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name', 'asc')->get();

        $today = now()->startOfDay();

        // Query today's completed production runs
        $todayProduction = \App\Models\ProductionRun::where('status', 'completed')
            ->where('prepared_date', '>=', $today)
            ->get();

        // Query today's dispatches
        $todayDispatches = \App\Models\DispatchItem::whereHas('dispatch', function($q) use ($today) {
            $q->whereIn('status', ['dispatched', 'received'])->where('dispatch_date', '>=', $today);
        })->get();

        foreach ($products as $product) {
            $additions = $todayProduction->where('product_id', $product->id)->sum('quantity_produced');
            $subtractions = $todayDispatches->where('product_id', $product->id)->sum('quantity');

            $product->opening_stock = $product->current_kitchen_stock - $additions + $subtractions;
            $product->closing_stock = $product->current_kitchen_stock;
        }

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku|max:100',
            'retail_price' => 'required|numeric|min:0',
            'current_kitchen_stock' => 'required|numeric|min:0',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Dessert Product registered successfully.');
    }

    public function show(Product $product)
    {
        return redirect()->route('products.index');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id . '|max:100',
            'retail_price' => 'required|numeric|min:0',
            'current_kitchen_stock' => 'required|numeric|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Dessert Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Dessert Product deleted successfully.');
    }
}
