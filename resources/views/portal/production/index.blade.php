@extends('layouts.portal')

@section('title', 'Kitchen Production — DessertOps Portal')
@section('breadcrumb', 'Kitchen Production')

@section('content')
@php
  $isOutletAdmin = session('portal_employee_role', 'outlet_admin') === 'outlet_admin';
@endphp

<div class="ph">
  <div>
    <div class="ph-title">Kitchen Production</div>
    <div class="ph-sub">Prepare desserts — consume raw materials and produce finished products at <b>{{ $outlet->name }}</b></div>
  </div>
  @if($isOutletAdmin)
  <div class="ph-acts">
    <a href="{{ route('portal.production.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Production Run
    </a>
  </div>
  @endif
</div>

<!-- KPI Row -->
<div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px;">
  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--amber-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="color:var(--amber-tx);"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
    </div>
    <div class="kpi-val">{{ $runs->where('status', 'pending')->count() }}</div>
    <div class="kpi-lbl">Pending Runs</div>
  </div>
  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--green-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="color:var(--green-tx);"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
    </div>
    <div class="kpi-val">{{ $runs->where('status', 'completed')->count() }}</div>
    <div class="kpi-lbl">Completed Runs</div>
  </div>
  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--purple-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="color:var(--purple-tx);"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </div>
    </div>
    <div class="kpi-val">{{ $runs->where('status', 'completed')->sum('quantity_produced') }}</div>
    <div class="kpi-lbl">Total Units Produced</div>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
    </div>
    <div class="ch-title">Production Runs</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th style="width: 15%;">Run #</th>
        <th style="width: 25%;">Product</th>
        <th style="width: 12%; text-align: right;">Qty</th>
        <th style="width: 13%; text-align: center;">Destination</th>
        <th style="width: 12%;">Date</th>
        <th style="width: 10%; text-align: center;">Status</th>
        <th style="width: 13%;">Prepared By</th>
      </tr>
    </thead>
    <tbody>
      @forelse($runs as $run)
      <tr style="cursor: pointer;" onclick="window.location='{{ route('portal.production.show', $run) }}'">
        <td data-label="Run #" class="mono" style="font-weight: 600;">{{ $run->run_number }}</td>
        <td data-label="Product" style="font-weight: 600;">
          @if($run->outlet_catalog_item_id && $run->catalogItem)
            <span class="badge bp" style="font-size: 9px; vertical-align: middle; margin-right: 4px;">RECIPE</span>
            {{ $run->catalogItem->name }}
          @else
            {{ $run->product->name }}
          @endif
        </td>
        <td data-label="Qty Produced" class="mono" style="text-align: right; font-weight: 600;">{{ number_format($run->quantity_produced, 0) }}</td>
        <td data-label="Destination" style="text-align: center;">
          @if((float)$run->qty_to_store > 0)
            <span class="badge ba" style="font-size: 10px;">{{ number_format($run->qty_to_store, 0) }} Store</span>
          @endif
          @if((float)$run->qty_to_showcase > 0)
            <span class="badge bp" style="font-size: 10px;">{{ number_format($run->qty_to_showcase, 0) }} Showcase</span>
          @endif
        </td>
        <td data-label="Date" class="mono" style="font-size: 12.5px;">{{ $run->prepared_date->format('Y-m-d') }}</td>
        <td data-label="Status" style="text-align: center;">
          @if($run->status === 'pending')
            <span class="badge bn">Pending</span>
          @else
            <span class="badge bg">Completed</span>
          @endif
        </td>
        <td data-label="Prepared By" style="font-size: 12.5px; color: var(--txt2);">{{ $run->prepared_by }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="text-center td2" style="padding: 30px;">
          No production runs yet.
          @if($isOutletAdmin)
            <a href="{{ route('portal.production.create') }}">Create your first production run</a>.
          @endif
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
