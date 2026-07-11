@extends('layouts.app')

@section('title', "{$outlet_catalog->name} — DessertOps")
@section('breadcrumb', 'Catalog Item Details')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">{{ $outlet_catalog->name }}</div>
    <div class="ph-sub">Catalog item details and recipe ingredients</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('outlet-catalog.index') }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 14px; height: 14px; margin-right: 4px;"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Catalog
    </a>
    <a href="{{ route('outlet-catalog.edit', $outlet_catalog->id) }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </a>
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 320px; gap: 16px;">
  <!-- Left: Details & Ingredients -->
  <div style="display: flex; flex-direction: column; gap: 16px;">

    <!-- Ingredients Table -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Recipe Ingredients</div>
        <div style="margin-left: auto;">
          <span class="badge ba">{{ $outlet_catalog->ingredients->count() }} items</span>
        </div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="tbl">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Name</th>
              <th>Type</th>
              <th style="text-align: right;">Default Qty</th>
            </tr>
          </thead>
          <tbody>
            @forelse($outlet_catalog->ingredients as $ing)
            <tr>
              <td data-label="SKU" class="mono font-semibold">{{ $ing->item_sku }}</td>
              <td data-label="Name">
                <div class="td-name">{{ $ing->item_name }}</div>
              </td>
              <td data-label="Type">
                @if($ing->material_id)
                  <span class="badge bn">Material</span>
                @else
                  <span class="badge bp">Product</span>
                @endif
              </td>
              <td data-label="Default Qty" class="mono font-semibold" style="text-align: right;">
                {{ number_format($ing->default_quantity, 2) }}
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center td2">No ingredients defined.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Assigned Outlets -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Assigned Outlets</div>
        <div style="margin-left: auto;">
          <span class="badge ba">{{ $outlet_catalog->outlets->count() }} outlets</span>
        </div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="tbl">
          <thead>
            <tr>
              <th>Outlet Name</th>
              <th>Type</th>
              <th>Assigned On</th>
            </tr>
          </thead>
          <tbody>
            @forelse($outlet_catalog->outlets as $outlet)
            <tr>
              <td data-label="Outlet Name">
                <div class="td-name">{{ $outlet->name }}</div>
              </td>
              <td data-label="Type">
                <span class="badge ba">{{ ucfirst($outlet->type ?? 'outlet') }}</span>
              </td>
              <td data-label="Assigned On" class="mono" style="color: var(--txt3);">
                {{ $outlet->pivot->created_at ? $outlet->pivot->created_at->format('Y-m-d') : '—' }}
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="3" class="text-center td2">Not assigned to any outlets yet.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Right: Summary -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    <div class="card">
      <div class="ch">
        <div class="ch-title">Item Summary</div>
      </div>
      <div class="cb" style="display: flex; flex-direction: column; gap: 14px;">
        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Product Name</label>
          <div style="font-size: 14px; font-weight: 700; color: var(--txt); margin-top: 2px;">{{ $outlet_catalog->name }}</div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">SKU Code</label>
          <div class="mono" style="font-size: 14px; font-weight: 700; color: var(--txt); margin-top: 2px;">{{ $outlet_catalog->sku }}</div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Retail Price</label>
          <div class="mono" style="font-size: 15px; font-weight: 700; color: var(--txt); margin-top: 2px;">₹{{ number_format($outlet_catalog->retail_price, 2) }}</div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Status</label>
          <div style="margin-top: 4px;">
            @if($outlet_catalog->is_active)
              <span class="badge bg">Active</span>
            @else
              <span class="badge bn">Inactive</span>
            @endif
          </div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Created By</label>
          <div style="font-size: 13px; font-weight: 500; color: var(--txt2); margin-top: 2px;">{{ $outlet_catalog->creator->name ?? '—' }}</div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Date Created</label>
          <div class="mono" style="font-size: 13px; color: var(--txt2); margin-top: 2px;">{{ $outlet_catalog->created_at->format('Y-m-d') }}</div>
        </div>

        @if($outlet_catalog->description)
        <div style="border-top: 1px solid var(--div); padding-top: 12px;">
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Description</label>
          <div style="font-size: 12.5px; color: var(--txt2); margin-top: 4px; line-height: 1.4; white-space: pre-line;">{{ $outlet_catalog->description }}</div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
