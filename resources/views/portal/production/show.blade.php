@extends('layouts.portal')

@section('title', "Production {$run->run_number} — DessertOps Portal")
@section('breadcrumb', 'Production Run Details')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Production Run: {{ $run->run_number }}</div>
    <div class="ph-sub">Prepared by <b>{{ $run->prepared_by }}</b> on {{ $run->prepared_date->format('Y-m-d') }}</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('portal.production.index') }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 14px; height: 14px; margin-right: 4px;"><polyline points="15 18 9 12 15 6"/></svg>
      Back to List
    </a>
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 320px; gap: 16px;">
  <!-- Left: Items & Actions -->
  <div style="display: flex; flex-direction: column; gap: 16px;">

    <!-- Materials Consumed -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Materials Consumed</div>
        <div style="margin-left: auto;">
          @if($run->status === 'pending')
            <span class="badge bn">Pending</span>
          @else
            <span class="badge bg">Completed</span>
          @endif
        </div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="tbl">
          <thead>
            <tr>
              <th style="width: 50%;">Material / Ingredient</th>
              <th style="width: 20%; text-align: right;">Qty Used</th>
              @if($run->status === 'pending' && $isOutletAdmin)
              <th style="width: 30%; text-align: right;">Kitchen Available</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach($run->materials as $mat)
            @php
              $key = ($mat->material_id ? 'mat_' : 'prod_') . ($mat->material_id ?? $mat->product_id);
              $available = $kitchenStocks[$key] ?? 0;
              $sufficient = $available >= (float) $mat->quantity_used;
            @endphp
            <tr>
              <td data-label="Item">
                <div style="font-weight: 600; color: var(--txt);">{{ $mat->item_name }}</div>
                <div style="font-size: 11px; color: var(--txt3);" class="mono">{{ $mat->item_sku }}</div>
              </td>
              <td data-label="Qty Used" class="mono" style="text-align: right; font-weight: 600;">
                {{ number_format($mat->quantity_used, 2) }} {{ $mat->item_unit }}
              </td>
              @if($run->status === 'pending' && $isOutletAdmin)
              <td data-label="Available" class="mono" style="text-align: right;">
                <span style="color: {{ $sufficient ? 'var(--green-tx)' : 'var(--red-tx)' }}; font-weight: 600;">
                  {{ number_format($available, 2) }} {{ $mat->item_unit }}
                </span>
                @if(!$sufficient)
                  <div style="font-size: 10px; color: var(--red-tx);">Insufficient!</div>
                @endif
              </td>
              @endif
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- Complete Action (Admin only, pending only) -->
    @if($run->status === 'pending' && $isOutletAdmin)
    <div class="card" style="border: 1px solid var(--green);">
      <div class="ch" style="background: rgba(34, 197, 94, 0.05);">
        <div class="ch-ic" style="background: var(--green-lt);">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" style="stroke: var(--green-tx)"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="ch-title" style="color: var(--green-tx);">Complete Production</div>
      </div>
      <div class="cb">
        <p style="font-size: 13px; color: var(--txt2); margin-bottom: 16px;">
          Completing this run will consume the listed materials from kitchen stock and add
          <b>{{ number_format($run->quantity_produced, 0) }} units</b> of <b>@if($run->outlet_catalog_item_id && $run->catalogItem){{ $run->catalogItem->name }}@else{{ $run->product->name }}@endif</b>:
          @if((float)$run->qty_to_store > 0)
            <span class="badge ba" style="font-size: 11px;">{{ number_format($run->qty_to_store, 0) }} → Store</span>
          @endif
          @if((float)$run->qty_to_showcase > 0)
            <span class="badge bp" style="font-size: 11px;">{{ number_format($run->qty_to_showcase, 0) }} → Showcase</span>
          @endif
        </p>
        <div style="display: flex; gap: 12px;">
          <form action="{{ route('portal.production.complete', $run) }}" method="POST" onsubmit="return confirm('Complete this production run? Materials will be consumed from kitchen stock.');">
            @csrf
            <button type="submit" class="btn-pri" style="background: var(--green); border-color: var(--green); color: #fff; font-weight: 600;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;"><polyline points="20 6 9 17 4 12"/></svg>
              Complete & Produce
            </button>
          </form>
          <form action="{{ route('portal.production.destroy', $run) }}" method="POST" onsubmit="return confirm('Delete this pending production run?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-ghost" style="color: var(--red-tx);">
              Delete Run
            </button>
          </form>
        </div>
      </div>
    </div>
    @endif

  </div>

  <!-- Right: Summary -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    <div class="card">
      <div class="ch">
        <div class="ch-title">Run Summary</div>
      </div>
      <div class="cb" style="display: flex; flex-direction: column; gap: 14px;">
        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Run Number</label>
          <div class="mono" style="font-size: 14px; font-weight: 700; color: var(--txt); margin-top: 2px;">{{ $run->run_number }}</div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Product</label>
          <div style="font-size: 13px; font-weight: 600; color: var(--txt); margin-top: 2px;">
            @if($run->outlet_catalog_item_id && $run->catalogItem)
              <span class="badge bp" style="font-size: 9px; vertical-align: middle; margin-right: 4px;">RECIPE</span>
              {{ $run->catalogItem->name }}
            @else
              {{ $run->product->name }}
            @endif
          </div>
          <div class="mono" style="font-size: 11px; color: var(--txt3);">@if($run->outlet_catalog_item_id && $run->catalogItem){{ $run->catalogItem->sku }}@else{{ $run->product->sku }}@endif</div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Quantity</label>
          <div class="mono" style="font-size: 15px; font-weight: 700; color: var(--txt); margin-top: 2px;">{{ number_format($run->quantity_produced, 0) }} units</div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Destination Split</label>
          <div style="margin-top: 6px; display: flex; flex-direction: column; gap: 6px;">
            @if((float)$run->qty_to_store > 0)
              <div style="display: flex; align-items: center; gap: 8px;">
                <span class="badge ba" style="min-width: 60px; text-align: center;">Store</span>
                <span class="mono" style="font-weight: 600;">{{ number_format($run->qty_to_store, 0) }} units</span>
              </div>
            @endif
            @if((float)$run->qty_to_showcase > 0)
              <div style="display: flex; align-items: center; gap: 8px;">
                <span class="badge bp" style="min-width: 60px; text-align: center;">Showcase</span>
                <span class="mono" style="font-weight: 600;">{{ number_format($run->qty_to_showcase, 0) }} units</span>
              </div>
            @endif
          </div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Status</label>
          <div style="margin-top: 4px;">
            @if($run->status === 'pending')
              <span class="badge bn">Pending</span>
            @else
              <span class="badge bg">Completed</span>
            @endif
          </div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Prepared By</label>
          <div style="font-size: 13px; font-weight: 500; color: var(--txt2); margin-top: 2px;">{{ $run->prepared_by }}</div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Date</label>
          <div class="mono" style="font-size: 13px; color: var(--txt2); margin-top: 2px;">{{ $run->prepared_date->format('Y-m-d') }}</div>
        </div>

        @if($run->notes)
        <div style="border-top: 1px solid var(--div); padding-top: 12px;">
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Notes</label>
          <div style="font-size: 12.5px; color: var(--txt2); margin-top: 4px; line-height: 1.4; white-space: pre-line;">{{ $run->notes }}</div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
