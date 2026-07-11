@extends('layouts.app')

@section('title', 'Outlet Product Catalog — DessertOps')
@section('breadcrumb', 'Outlet Catalog')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Outlet Product Catalog</div>
    <div class="ph-sub">Define finished products that outlets produce by cooking half-prepared items</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('outlet-catalog.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Catalog Item
    </a>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    </div>
    <div class="ch-title">Catalog Items</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>SKU</th>
        <th>Product Name</th>
        <th style="text-align: right;">Retail Price</th>
        <th style="text-align: center;">Ingredients</th>
        <th style="text-align: center;">Assigned Outlets</th>
        <th style="text-align: center;">Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($catalogItems as $item)
      <tr>
        <td data-label="SKU" class="mono font-semibold">{{ $item->sku }}</td>
        <td data-label="Product Name">
          <div class="td-name">{{ $item->name }}</div>
          <div class="td-meta">{{ $item->description ? Str::limit($item->description, 40) : 'No description' }}</div>
        </td>
        <td data-label="Retail Price" class="mono font-semibold" style="text-align: right;">₹{{ number_format($item->retail_price, 2) }}</td>
        <td data-label="Ingredients" class="mono" style="text-align: center;">{{ $item->ingredients_count }}</td>
        <td data-label="Outlets" class="mono" style="text-align: center;">{{ $item->outlets_count }}</td>
        <td data-label="Status" style="text-align: center;">
          @if($item->is_active)
            <span class="badge bg">Active</span>
          @else
            <span class="badge bn">Inactive</span>
          @endif
        </td>
        <td data-label="Actions">
          <div style="display: flex; gap: 10px;">
            <a href="{{ route('outlet-catalog.show', $item->id) }}" class="td-act">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              View
            </a>
            <a href="{{ route('outlet-catalog.edit', $item->id) }}" class="td-act">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit
            </a>
            <form action="{{ route('outlet-catalog.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this catalog item?');" style="display: inline;">
              @csrf @method('DELETE')
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
        <td colspan="7" class="text-center td2">No catalog items defined yet. <a href="{{ route('outlet-catalog.create') }}">Create one now</a>.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
