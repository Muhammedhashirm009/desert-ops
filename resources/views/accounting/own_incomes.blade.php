@extends('layouts.accounting')

@section('title', 'Own Outlet Incomes — DessertOps')
@section('breadcrumb', 'Own Outlet Incomes')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Own Outlet Incomes</div>
    <div class="ph-sub">Direct retail sales cashflows automatically posted to Bank Current Account</div>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
    </div>
    <div class="ch-title">Direct Sales Cash Records</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Log Ref</th>
        <th>Own Outlet</th>
        <th>Sales Date</th>
        <th>Products Sold Summary</th>
        <th style="text-align: right;">Total Cash Revenue</th>
        <th>Posted Ledger Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse($salesLogs as $log)
      @php
          $totalRev = $log->items->sum('total_revenue');
          $totalQty = $log->items->sum('quantity_sold');
      @endphp
      <tr onmouseover="this.style.background='var(--div)'" onmouseout="this.style.background=''">
        <td class="mono font-semibold" data-label="Log Ref">
          <a href="{{ route('sales-logs.show', $log->id) }}" style="color: inherit; text-decoration: underline;">
            SL-{{ $log->id }}
          </a>
        </td>
        <td data-label="Own Outlet">
          <div class="td-name">{{ $log->outlet->name }}</div>
          <div class="td-meta">Contact: {{ $log->outlet->contact_person }}</div>
        </td>
        <td class="mono td2" data-label="Sales Date">{{ $log->log_date->format('Y-m-d') }}</td>
        <td data-label="Products Summary">
          <div style="font-size: 13.5px; font-weight: 500;">{{ number_format($totalQty, 0) }} total items</div>
          <div style="font-size: 11.5px; color: var(--txt3);">
            @foreach($log->items as $item)
              {{ $item->product->name ?? 'Product' }} ({{ number_format($item->quantity_sold, 0) }}), 
            @endforeach
          </div>
        </td>
        <td class="mono font-semibold" style="text-align: right; color: var(--green-tx); font-size: 14.5px;" data-label="Total Cash Revenue">
          ₹{{ number_format($totalRev, 2) }}
        </td>
        <td data-label="Ledger Status">
          <span class="badge bg" style="font-size: 11.5px; display:inline-flex; align-items:center; gap:4px;">
            <span class="on-dot" style="position:static; display:inline-block; margin-right:4px;"></span>
            Ledger Posted (Code 4010)
          </span>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" class="text-center td2">No direct own outlet sales logs recorded yet.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
