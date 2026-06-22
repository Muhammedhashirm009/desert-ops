@extends('layouts.app')

@section('title', 'Register Product — DessertOps')
@section('breadcrumb', 'New Product')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Register Dessert Product</div>
    <div class="ph-sub">Define a new finished dessert item that can be prepared in the kitchen</div>
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
    <form action="{{ route('products.store') }}" method="POST">
      @csrf

      <div class="form-grp">
        <label for="name">Product Name *</label>
        <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}" required placeholder="e.g. Gulab Jamun Box">
        @error('name')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="form-grp">
        <label for="sku">SKU Code *</label>
        <input type="text" name="sku" id="sku" class="form-input" value="{{ old('sku') }}" required placeholder="e.g. DSR-GJB-001">
        @error('sku')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="grid-2">
        <div class="form-grp">
          <label for="retail_price">Retail Price (₹) *</label>
          <input type="number" step="0.01" name="retail_price" id="retail_price" class="form-input" value="{{ old('retail_price', 0) }}" required>
          @error('retail_price')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp">
          <label for="current_kitchen_stock">Initial Kitchen Stock *</label>
          <input type="number" step="1" name="current_kitchen_stock" id="current_kitchen_stock" class="form-input" value="{{ old('current_kitchen_stock', 0) }}" required>
          @error('current_kitchen_stock')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="mt-4" style="display: flex; justify-content: flex-end; gap: 10px;">
        <a href="{{ route('products.index') }}" class="btn-ghost">Cancel</a>
        <button type="submit" class="btn-pri">Save Product</button>
      </div>
    </form>
  </div>
</div>
@endsection
