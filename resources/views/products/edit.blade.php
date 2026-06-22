@extends('layouts.app')

@section('title', 'Edit Product — DessertOps')
@section('breadcrumb', 'Modify Product')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Edit Dessert Product: {{ $product->name }}</div>
    <div class="ph-sub">Update description, stock details and pricing rules</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('products.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
  <div class="ch">
    <div class="ch-title">Product Description & Pricing</div>
  </div>
  <div class="cb">
    <form action="{{ route('products.update', $product->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="form-grp">
        <label for="name">Product Name *</label>
        <input type="text" name="name" id="name" class="form-input" value="{{ old('name', $product->name) }}" required placeholder="e.g. Gulab Jamun Box">
        @error('name')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="form-grp">
        <label for="sku">SKU Code *</label>
        <input type="text" name="sku" id="sku" class="form-input" value="{{ old('sku', $product->sku) }}" required placeholder="e.g. DSR-GJB-001">
        @error('sku')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="grid-2">
        <div class="form-grp">
          <label for="retail_price">Retail Price (₹) *</label>
          <input type="number" step="0.01" name="retail_price" id="retail_price" class="form-input" value="{{ old('retail_price', $product->retail_price) }}" required>
          @error('retail_price')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp">
          <label for="current_kitchen_stock">Current Kitchen Stock *</label>
          <input type="number" step="1" name="current_kitchen_stock" id="current_kitchen_stock" class="form-input" value="{{ old('current_kitchen_stock', $product->current_kitchen_stock) }}" required>
          @error('current_kitchen_stock')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="mt-4" style="display: flex; justify-content: flex-end; gap: 10px;">
        <a href="{{ route('products.index') }}" class="btn-ghost">Cancel</a>
        <button type="submit" class="btn-pri">Update Product</button>
      </div>
    </form>
  </div>
</div>
@endsection
