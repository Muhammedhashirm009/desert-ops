@extends('layouts.app')

@section('title', 'Sales Report Sheet — DessertOps')
@section('breadcrumb', 'Sales Report Detail')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Sales Report: {{ $salesLog->log_date->format('F d, Y') }}</div>
    <div class="ph-sub">Outlet: <a href="{{ route('outlets.show', $salesLog->outlet_id) }}" style="color:inherit; font-weight:600;">{{ $salesLog->outlet->name }}</a></div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('sales-logs.index') }}" class="btn-ghost">
      Back to Sales Reports
    </a>
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 340px; gap: 16px;">
  <!-- Left Panel: Sold Items Details -->
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </div>
      <div class="ch-title">Sold Products Breakdowns</div>
    </div>
    <table>
      <thead>
        <tr>
          <th>SKU</th>
          <th>Dessert Product</th>
          <th style="text-align: right;">Qty Sold</th>
          <th style="text-align: right;">Unit Price</th>
          <th style="text-align: right;">Gross Revenue</th>
          <th style="text-align: right;">Franchise Comm.</th>
          <th style="text-align: right;">Net Kitchen Revenue</th>
        </tr>
      </thead>
      <tbody>
        @foreach($salesLog->items as $item)
        <tr>
          <td class="mono">{{ $item->product->sku }}</td>
          <td style="font-weight:600;">{{ $item->product->name }}</td>
          <td class="mono" style="text-align: right;">{{ number_format($item->quantity_sold, 0) }} Units</td>
          <td class="mono" style="text-align: right;">₹{{ number_format($item->unit_price, 2) }}</td>
          <td class="mono" style="text-align: right; font-weight: 600;">₹{{ number_format($item->total_revenue, 2) }}</td>
          <td class="mono" style="text-align: right; color: var(--purple-tx);">
            @if($salesLog->outlet->type === 'franchise')
              ₹{{ number_format($item->commission_amount, 2) }}
            @else
              <span class="td3">—</span>
            @endif
          </td>
          <td class="mono font-semibold" style="text-align: right; color: var(--green-tx); font-weight: 600;">
            ₹{{ number_format($item->net_revenue, 2) }}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- Right Panel: Financial Summary and Info -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    <!-- Summary Cards -->
    <div class="card" style="background: var(--btn); color: #fff;">
      <div class="ch" style="border-bottom-color: rgba(255,255,255,0.12);">
        <div class="ch-title" style="color: #fff;">Revenue Summary</div>
      </div>
      <div class="cb" style="font-size: 13.5px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
          <span style="color: rgba(255,255,255,0.6)">Gross Revenue:</span>
          <span class="mono" style="font-weight:600;">₹{{ number_format($salesLog->total_revenue, 2) }}</span>
        </div>
        
        @if($salesLog->outlet->type === 'franchise')
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px dashed rgba(255,255,255,0.15); padding-bottom: 10px;">
          <span style="color: rgba(255,255,255,0.6)">Franchise Comm. ({{ number_format($salesLog->outlet->commission_rate, 1) }}%):</span>
          <span class="mono" style="font-weight:600; color: #E5E7EB;">- ₹{{ number_format($salesLog->commission_amount, 2) }}</span>
        </div>
        @endif

        <div style="display: flex; justify-content: space-between; font-size: 15px; font-weight: 700; margin-top: 10px;">
          <span>Net Earning:</span>
          <span class="mono" style="color: #34D399;">₹{{ number_format($salesLog->net_revenue, 2) }}</span>
        </div>
      </div>
    </div>

    <!-- Metadata Logistics -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Report Metadata</div>
      </div>
      <div class="cb" style="font-size: 13px;">
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Store</div>
          <div>{{ $salesLog->outlet->name }}</div>
        </div>
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Report Date</div>
          <div class="mono">{{ $salesLog->log_date->format('Y-m-d') }}</div>
        </div>
        <div>
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Logged At</div>
          <div class="mono">{{ $salesLog->created_at->format('Y-m-d H:i') }}</div>
        </div>
      </div>
    </div>

    <!-- Delete sales report action -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Manage Report</div>
      </div>
      <div class="cb">
        <form action="{{ route('sales-logs.destroy', $salesLog->id) }}" method="POST" onsubmit="return confirm('Deleting this report will remove the financial totals and restore the sold quantities back to the outlet stock. Proceed?');">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px; background:var(--red); color:#fff;">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            Delete & Restore Stocks
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
