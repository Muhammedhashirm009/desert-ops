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
          <label for="cost_price">Cost Price (₹)</label>
          <input type="number" step="0.01" name="cost_price" id="cost_price" class="form-input" value="{{ old('cost_price', $product->cost_price ?? 0) }}">
          @error('cost_price')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="form-grp">
        <label for="current_kitchen_stock">Current Kitchen Stock *</label>
        <input type="number" step="1" name="current_kitchen_stock" id="current_kitchen_stock" class="form-input" value="{{ old('current_kitchen_stock', $product->current_kitchen_stock) }}" required>
        @error('current_kitchen_stock')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="mt-4" style="display: flex; justify-content: flex-end; gap: 10px;">
        <a href="{{ route('products.index') }}" class="btn-ghost">Cancel</a>
        <button type="submit" class="btn-pri">Update Product</button>
      </div>
    </form>
  </div>
</div>

<!-- Price History Card -->
<div class="card" style="max-width: 600px; margin: 20px auto 0 auto;">
  <div class="ch">
    <div class="ch-title">Product Cost Price History Log</div>
  </div>
  <div class="cb" style="padding: 0;">
    @if($priceHistories && $priceHistories->count() > 0)
    <table class="tbl">
      <thead>
        <tr>
          <th>Date</th>
          <th style="text-align: right;">Old Cost</th>
          <th style="text-align: right;">New Cost</th>
          <th>Change Trigger</th>
        </tr>
      </thead>
      <tbody>
        @foreach($priceHistories as $history)
        <tr>
          <td style="font-size: 12px;">{{ $history->created_at->format('Y-m-d H:i') }}</td>
          <td class="mono" style="text-align: right;">₹{{ number_format($history->old_cost_price, 2) }}</td>
          <td class="mono font-semibold" style="text-align: right; color: var(--green-tx);">₹{{ number_format($history->new_cost_price, 2) }}</td>
          <td>{{ $history->notes ?? 'Manual update / recipe adjustment' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div style="padding: 20px; text-align: center; color: var(--txt3); font-style: italic;">
      No price history logged yet.
    </div>
    @endif
  </div>
</div>
@endsection
