@extends('layouts.app')

@section('title', 'Kitchen P&L Report — DessertOps')
@section('breadcrumb', 'Kitchen P&L Report')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Central Kitchen P&L Report</div>
    <div class="ph-sub">Profitability analysis: Dispatch Cost vs Sales Revenue</div>
  </div>
</div>

<!-- Date Range Filter -->
<div class="card" style="margin-bottom: 16px;">
  <div class="cb" style="padding: 14px;">
    <form method="GET" action="{{ route('reports.central-kitchen') }}" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
      <div class="form-grp" style="margin: 0;">
        <label style="font-size: 11px; font-weight: 600; color: var(--txt3);">From</label>
        <input type="date" name="date_from" class="form-input" value="{{ $dateFrom }}" style="padding: 6px 10px;">
      </div>
      <div class="form-grp" style="margin: 0;">
        <label style="font-size: 11px; font-weight: 600; color: var(--txt3);">To</label>
        <input type="date" name="date_to" class="form-input" value="{{ $dateTo }}" style="padding: 6px 10px;">
      </div>
      <button type="submit" class="btn-pri" style="padding: 7px 16px; font-size: 12.5px;">Generate Report</button>
    </form>
  </div>
</div>

<!-- KPI Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 16px;">
  <div class="card">
    <div class="cb" style="padding: 16px; text-align: center;">
      <div style="font-size: 11px; color: var(--txt3); font-weight: 600; text-transform: uppercase;">Dispatch Cost (COGS)</div>
      <div style="font-size: 24px; font-weight: 700; color: var(--red-tx); margin-top: 4px;">₹{{ number_format($totalDispatchCost, 2) }}</div>
    </div>
  </div>
  <div class="card">
    <div class="cb" style="padding: 16px; text-align: center;">
      <div style="font-size: 11px; color: var(--txt3); font-weight: 600; text-transform: uppercase;">Sales Revenue</div>
      <div style="font-size: 24px; font-weight: 700; color: var(--green-tx); margin-top: 4px;">₹{{ number_format($totalRevenue, 2) }}</div>
    </div>
  </div>
  <div class="card">
    <div class="cb" style="padding: 16px; text-align: center;">
      <div style="font-size: 11px; color: var(--txt3); font-weight: 600; text-transform: uppercase;">Gross Profit</div>
      <div style="font-size: 24px; font-weight: 700; color: {{ $grossProfit >= 0 ? 'var(--green-tx)' : 'var(--red-tx)' }}; margin-top: 4px;">₹{{ number_format($grossProfit, 2) }}</div>
    </div>
  </div>
  <div class="card">
    <div class="cb" style="padding: 16px; text-align: center;">
      <div style="font-size: 11px; color: var(--txt3); font-weight: 600; text-transform: uppercase;">Profit Margin</div>
      <div style="font-size: 24px; font-weight: 700; color: {{ $profitMargin >= 0 ? 'var(--btn)' : 'var(--red-tx)' }}; margin-top: 4px;">{{ $profitMargin }}%</div>
    </div>
  </div>
  <div class="card">
    <div class="cb" style="padding: 16px; text-align: center;">
      <div style="font-size: 11px; color: var(--txt3); font-weight: 600; text-transform: uppercase;">Units Produced</div>
      <div style="font-size: 24px; font-weight: 700; color: var(--txt); margin-top: 4px;">{{ number_format($totalProduced, 0) }}</div>
    </div>
  </div>
</div>

<!-- Outlet Breakdown -->
<div class="card" style="margin-bottom: 16px;">
  <div class="ch">
    <div class="ch-title">Outlet Breakdown</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Outlet</th>
        <th>Type</th>
        <th style="text-align: right;">Dispatched (₹)</th>
        <th style="text-align: right;">Revenue (₹)</th>
        <th style="text-align: right;">Profit (₹)</th>
        <th style="text-align: right;">Margin %</th>
        <th style="text-align: right;">Dispatched</th>
        <th style="text-align: right;">Sold</th>
        <th style="text-align: right;">Sell-through %</th>
      </tr>
    </thead>
    <tbody>
      @forelse($outletBreakdown as $outlet)
      <tr>
        <td style="font-weight: 600;">{{ $outlet['name'] }}</td>
        <td><span class="badge {{ $outlet['type'] === 'own' ? 'bg' : 'ba' }}">{{ ucfirst($outlet['type']) }}</span></td>
        <td class="mono" style="text-align: right;">₹{{ number_format($outlet['dispatch_cost'], 2) }}</td>
        <td class="mono" style="text-align: right; color: var(--green-tx);">₹{{ number_format($outlet['revenue'], 2) }}</td>
        <td class="mono font-semibold" style="text-align: right; color: {{ $outlet['profit'] >= 0 ? 'var(--green-tx)' : 'var(--red-tx)' }};">₹{{ number_format($outlet['profit'], 2) }}</td>
        <td class="mono" style="text-align: right;">{{ $outlet['margin'] }}%</td>
        <td class="mono" style="text-align: right;">{{ number_format($outlet['units_dispatched'], 0) }}</td>
        <td class="mono" style="text-align: right;">{{ number_format($outlet['units_sold'], 0) }}</td>
        <td class="mono" style="text-align: right;">{{ $outlet['sell_through'] }}%</td>
      </tr>
      @empty
      <tr><td colspan="9" class="text-center td2">No data for the selected period.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Product Breakdown -->
<div class="card">
  <div class="ch">
    <div class="ch-title">Product Breakdown</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>SKU</th>
        <th>Product</th>
        <th style="text-align: right;">WAC (₹)</th>
        <th style="text-align: right;">Retail (₹)</th>
        <th style="text-align: right;">Margin/Unit</th>
        <th style="text-align: right;">Dispatched</th>
        <th style="text-align: right;">Cost (₹)</th>
        <th style="text-align: right;">Sold</th>
        <th style="text-align: right;">Revenue (₹)</th>
        <th style="text-align: right;">Profit (₹)</th>
      </tr>
    </thead>
    <tbody>
      @forelse($productBreakdown as $product)
      <tr>
        <td class="mono">{{ $product['sku'] }}</td>
        <td style="font-weight: 600;">{{ $product['name'] }}</td>
        <td class="mono" style="text-align: right;">₹{{ number_format($product['cost_price'], 2) }}</td>
        <td class="mono" style="text-align: right;">₹{{ number_format($product['retail_price'], 2) }}</td>
        <td class="mono" style="text-align: right; color: {{ $product['margin_per_unit'] >= 0 ? 'var(--green-tx)' : 'var(--red-tx)' }};">₹{{ number_format($product['margin_per_unit'], 2) }}</td>
        <td class="mono" style="text-align: right;">{{ number_format($product['dispatched_qty'], 0) }}</td>
        <td class="mono" style="text-align: right;">₹{{ number_format($product['dispatch_cost'], 2) }}</td>
        <td class="mono" style="text-align: right;">{{ number_format($product['sold_qty'], 0) }}</td>
        <td class="mono" style="text-align: right; color: var(--green-tx);">₹{{ number_format($product['revenue'], 2) }}</td>
        <td class="mono font-semibold" style="text-align: right; color: {{ $product['profit'] >= 0 ? 'var(--green-tx)' : 'var(--red-tx)' }};">₹{{ number_format($product['profit'], 2) }}</td>
      </tr>
      @empty
      <tr><td colspan="10" class="text-center td2">No product data for the selected period.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
