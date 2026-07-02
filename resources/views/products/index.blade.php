@extends('layouts.app')

@section('title', 'Products Catalog — DessertOps')
@section('breadcrumb', 'Products Catalog')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Finished Products Catalog</div>
    <div class="ph-sub">Manage sellable dessert items and monitor central kitchen stocks</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('products.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Finished Product
    </a>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <div class="ch-title">Dessert Products & Stock</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>SKU</th>
        <th>Product Name</th>
        <th style="text-align: right;">Retail Price</th>
        <th style="text-align: right;">Kitchen Stock</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($products as $product)
      <tr>
        <td data-label="SKU" class="mono font-semibold">{{ $product->sku }}</td>
        <td data-label="Product Name">
          <div class="td-name">{{ $product->name }}</div>
          <div class="td-meta">SKU Ref: {{ $product->sku }}</div>
        </td>
        <td data-label="Retail Price" class="mono font-semibold" style="text-align: right;">
          ₹{{ number_format($product->retail_price, 2) }}
        </td>
        <td data-label="Kitchen Stock" class="mono font-semibold" style="text-align: right; padding-right: 20px; color: {{ $product->current_kitchen_stock > 0 ? 'var(--green-tx)' : 'var(--red-tx)' }};">
          {{ number_format($product->current_kitchen_stock, 0) }} units
        </td>
        <td data-label="Actions">
          <div style="display: flex; gap: 10px;">
            <a href="{{ route('products.edit', $product->id) }}" class="td-act">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit
            </a>
            <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');" style="display: inline;">
              @csrf
              @method('DELETE')
              <button type="submit" class="td-act po-row-btn" style="padding: 0; font-size: 13px;">
                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Delete
              </button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="text-center td2">No dessert products registered yet. <a href="{{ route('products.create') }}">Register one now</a>.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
