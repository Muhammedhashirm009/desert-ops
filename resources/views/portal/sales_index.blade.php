@extends('layouts.portal')

@section('title', 'Daily Sales Logs — DessertOps Portal')
@section('breadcrumb', 'Daily Sales Logs')

@section('content')
@php
  $isOutletAdmin = session('portal_employee_role', 'outlet_admin') === 'outlet_admin';
@endphp

<div class="ph">
  <div>
    <div class="ph-title">Daily Sales Reports</div>
    <div class="ph-sub">View and track daily sales reports logged for this outlet{{ $isOutletAdmin ? ' and commission details' : '' }}</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('portal.sales.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Log Daily Sales
    </a>
  </div>
</div>

@if($isOutletAdmin)
<!-- Financial Summary Strip (Admin Only) -->
<div class="sum-strip">
  <div class="sum-item">
    <div class="sum-val">₹{{ number_format($salesLogs->sum('total_revenue'), 2) }}</div>
    <div class="sum-lbl">Gross Store Sales</div>
  </div>
  <div class="sum-item">
    <div class="sum-val" style="color:var(--purple-tx);">
      @php
        $currentOutlet = \App\Models\Outlet::find(session('portal_outlet_id'));
      @endphp
      @if($currentOutlet && $currentOutlet->type === 'franchise')
        ₹{{ number_format($salesLogs->sum('commission_amount'), 2) }}
      @else
        ₹0.00
      @endif
    </div>
    <div class="sum-lbl">Franchise Commissions</div>
  </div>
  <div class="sum-item">
    <div class="sum-val" style="color:var(--green-tx);">₹{{ number_format($salesLogs->sum('net_revenue'), 2) }}</div>
    <div class="sum-lbl">Net Kitchen Earnings</div>
  </div>
</div>
@endif

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    </div>
    <div class="ch-title">Sales History Logs</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th style="width: {{ $isOutletAdmin ? '15%' : '20%' }};">Report Date</th>
        <th style="width: {{ $isOutletAdmin ? '40%' : '60%' }};">Items Sold</th>
        @if($isOutletAdmin)
        <th style="width: 15%; text-align: right;">Gross Sales</th>
        <th style="width: 15%; text-align: right;">Commission</th>
        <th style="width: 15%; text-align: right;">Net Revenue</th>
        @else
        <th style="width: 20%; text-align: right;">Total Qty Sold</th>
        @endif
      </tr>
    </thead>
    <tbody>
      @forelse($salesLogs as $log)
      <tr>
        <td data-label="Report Date" class="mono font-semibold" style="font-weight: 600;">
          {{ $log->log_date->format('Y-m-d') }}
        </td>
        <td data-label="Items Sold">
          <ul style="margin: 0; padding: 0 0 0 16px; font-size: 12px; color: var(--txt2); line-height: 1.5;">
            @foreach($log->items as $item)
              <li>
                {{ $item->product->name }}: 
                <b>{{ number_format($item->quantity_sold, 0) }} Units</b> 
                @if($isOutletAdmin)
                <span class="td3">@ ₹{{ number_format($item->unit_price, 2) }}</span>
                @endif
              </li>
            @endforeach
          </ul>
        </td>
        @if($isOutletAdmin)
        <td data-label="Gross Sales" class="mono font-semibold" style="text-align: right; font-weight: 600;">₹{{ number_format($log->total_revenue, 2) }}</td>
        <td data-label="Commission" class="mono" style="text-align: right; color: var(--purple-tx);">
          @if($log->outlet->type === 'franchise')
            ₹{{ number_format($log->commission_amount, 2) }}
          @else
            <span class="td3">—</span>
          @endif
        </td>
        <td data-label="Net Revenue" class="mono font-semibold" style="text-align: right; color: var(--green-tx); font-weight: 600;">₹{{ number_format($log->net_revenue, 2) }}</td>
        @else
        <td data-label="Total Qty" class="mono font-semibold" style="text-align: right; font-weight: 600;">
          {{ number_format($log->items->sum('quantity_sold'), 0) }} Units
        </td>
        @endif
      </tr>
      @empty
      <tr>
        <td colspan="{{ $isOutletAdmin ? 5 : 3 }}" class="text-center td2" style="padding: 30px;">
          No sales logged yet for this outlet. <a href="{{ route('portal.sales.create') }}">Log your first sales report now</a>.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
