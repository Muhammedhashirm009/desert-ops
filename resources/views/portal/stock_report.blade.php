@extends('layouts.portal')

@section('title', 'Daily Stock Report — DessertOps Portal')
@section('breadcrumb', 'Daily Stock Report')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Daily Stock Audit Report</div>
    <div class="ph-sub">View daily opening, additions, subtractions, and closing stock levels for {{ $outlet->name }}</div>
  </div>
  <div class="ph-acts">
    <form action="{{ route('portal.stock-report') }}" method="GET" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
      <label for="report-date" style="font-size: 13px; font-weight: 600; color: var(--txt2);">Select Date:</label>
      <input type="date" id="report-date" name="date" value="{{ $dateStr }}" max="{{ now()->toDateString() }}" style="padding: 8px 12px; border: 1px solid var(--div2); border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; outline: none; background: var(--card); color: var(--txt); width: 150px;" onchange="this.form.submit()">
    </form>
  </div>
</div>

<div class="card">
  <div class="ch" style="display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; align-items: center;">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div>
        <div class="ch-title">Stock Ledger on {{ \Carbon\Carbon::parse($dateStr)->format('d M Y') }}</div>
        <div class="ch-sub">Opening is calculated at 12:00 AM; Closing is the final balance for the day</div>
      </div>
    </div>
  </div>

  <table class="tbl">
    <thead>
      <tr>
        <th>SKU</th>
        <th>Item Name</th>
        <th>Category</th>
        <th style="text-align: right;">Opening Stock</th>
        <th style="text-align: right; color: var(--green-tx);">Additions (+)</th>
        <th style="text-align: right; color: var(--red-tx);">Subtractions (-)</th>
        <th style="text-align: right; font-weight: bold;">Closing Stock</th>
      </tr>
    </thead>
    <tbody>
      @forelse($reportData as $row)
      <tr>
        <td data-label="SKU" class="mono">{{ $row->sku }}</td>
        <td data-label="Item Name" style="font-weight: 600;">
          {{ $row->name }}
        </td>
        <td data-label="Category">
          <span class="badge {{ $row->category === 'Product' ? 'bp' : 'bb' }}">{{ $row->category }}</span>
        </td>
        <td data-label="Opening Stock" class="mono" style="text-align: right; color: var(--txt2);">
          {{ number_format($row->opening_stock, 0) }} {{ $row->unit }}
        </td>
        <td data-label="Additions (+)" class="mono font-semibold" style="text-align: right; color: var(--green-tx);">
          @if($row->additions > 0)
            +{{ number_format($row->additions, 0) }}
          @else
            0
          @endif
        </td>
        <td data-label="Subtractions (-)" class="mono font-semibold" style="text-align: right; color: var(--red-tx);">
          @if($row->subtractions > 0)
            -{{ number_format($row->subtractions, 0) }}
          @else
            0
          @endif
        </td>
        <td data-label="Closing Stock" class="mono font-semibold" style="text-align: right; font-weight: 700; color: var(--txt);">
          {{ number_format($row->closing_stock, 0) }} {{ $row->unit }}
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="text-center td2">No inventory items assigned to this store.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
