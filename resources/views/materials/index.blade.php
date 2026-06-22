@extends('layouts.app')

@section('title', 'Raw Materials — DessertOps')
@section('breadcrumb', 'Inventory Materials')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Raw Materials Inventory</div>
    <div class="ph-sub">Manage raw dessert ingredients and packaging items</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('materials.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Material Item
    </a>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
    </div>
    <div class="ch-title">Ingredients & Packaging Items</div>
  </div>
  <table>
    <thead>
      <tr>
        <th>SKU</th>
        <th>Material Name</th>
        <th>Category</th>
        <th>Store Stock</th>
        <th>Kitchen Stock</th>
        <th>Min Limit</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($materials as $material)
      <tr class="{{ $material->is_low_stock ? 'row-low-stock' : '' }}">
        <td class="mono">{{ $material->sku }}</td>
        <td>
          <div class="td-name">{{ $material->name }}</div>
          <div class="td-meta">Unit: {{ $material->unit }}</div>
        </td>
        <td>
          @if($material->category === 'ingredient')
            <span class="badge bp">Ingredient</span>
          @else
            <span class="badge bb">Packaging</span>
          @endif
        </td>
        <td class="mono font-semibold" style="font-weight: 600;">
          {{ number_format($material->current_stock, 2) }} {{ $material->unit }}
        </td>
        <td class="mono font-semibold" style="font-weight: 600; color: var(--blue-tx);">
          {{ number_format($material->kitchen_stock, 2) }} {{ $material->unit }}
        </td>
        <td class="mono td2">
          {{ number_format($material->min_stock_alert, 2) }} {{ $material->unit }}
        </td>
        <td>
          @if($material->is_low_stock)
            <span class="badge br"><svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>Low Stock</span>
          @else
            <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Safe</span>
          @endif
        </td>
        <td>
          <div style="display: flex; gap: 10px;">
            <a href="{{ route('materials.edit', $material->id) }}" class="td-act">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit
            </a>
            <form action="{{ route('materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this material?');" style="display: inline;">
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
        <td colspan="8" class="text-center td2">No materials added yet. <a href="{{ route('materials.create') }}">Add one now</a>.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
