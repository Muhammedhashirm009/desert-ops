@extends('layouts.portal')

@section('title', 'Store Dashboard — DessertOps Portal')
@section('breadcrumb', 'Store Dashboard')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">{{ $outlet->name }} Portal</div>
    <div class="ph-sub">Manage local dessert stocks, receive kitchen shipments, and record store sales</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('portal.sales.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Log Daily Sales
    </a>
  </div>
</div>

<!-- KPI Grid -->
<div class="kpi-grid">
  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--green-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="color:var(--green-tx);"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      </div>
    </div>
    <div class="kpi-val">{{ number_format($outlet->stocks->sum('quantity'), 0) }}</div>
    <div class="kpi-lbl">Total Dessert Units in Stock</div>
  </div>

  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--blue-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="color:var(--blue-tx);"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      </div>
      @if($incomingCount > 0)
        <div class="kpi-trend t-up">{{ $incomingCount }} active</div>
      @endif
    </div>
    <div class="kpi-val">{{ $incomingCount }}</div>
    <div class="kpi-lbl">Incoming Cargo Shipments</div>
  </div>

  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--purple-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="color:var(--purple-tx);"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </div>
    </div>
    <div class="kpi-val">{{ $recentSales->count() }}</div>
    <div class="kpi-lbl">Sales Reports Logged</div>
  </div>

  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--amber-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="color:var(--amber-tx);"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
    </div>
    <div class="kpi-val">₹{{ number_format($recentSales->sum('net_revenue'), 2) }}</div>
    <div class="kpi-lbl">Net Revenue (Recent Logs)</div>
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 340px; gap: 16px;">
  <!-- Left Side: Local Stock Levels -->
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
      </div>
      <div class="ch-title">Current In-Store Inventory</div>
    </div>
    <table class="tbl">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Dessert Product</th>
          <th style="text-align: right;">Current Stock</th>
          <th style="text-align: right;">Retail Price</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($outlet->stocks as $stock)
        @php
          $isProduct = (bool)$stock->product_id;
          $name = $isProduct ? $stock->product->name : $stock->material->name;
          $sku = $isProduct ? $stock->product->sku : $stock->material->sku;
          $qty = $stock->quantity;
          $unit = $isProduct ? 'Units' : 'Pieces';
          $priceText = $isProduct ? '₹' . number_format($stock->product->retail_price, 2) : 'N/A';
        @endphp
        <tr>
          <td data-label="SKU" class="mono">{{ $sku }}</td>
          <td data-label="Dessert Product" style="font-weight: 600;">
            {{ $name }}
            @if(!$isProduct)
              <span style="font-size: 11px; font-weight: normal; color: var(--txt3);"> (Packaging)</span>
            @endif
          </td>
          <td data-label="Current Stock" class="mono font-semibold" style="text-align: right; color: var(--green-tx); font-weight: 600;">
            {{ number_format($qty, 0) }} {{ $unit }}
          </td>
          <td data-label="Retail Price" class="mono" style="text-align: right;">{{ $priceText }}</td>
          <td data-label="Status">
            @if($qty > 20)
              <span class="badge bg">Good Stock</span>
            @elseif($qty > 0)
              <span class="badge ba">Low Stock</span>
            @else
              <span class="badge br">Out of Stock</span>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-center td2" style="padding:30px;">
            No inventory stocked at this outlet yet. Confirm delivery of incoming shipments to populate.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Right Side: Alerts & History -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    <!-- Incoming Shipments Alerts -->
    @if($incomingCount > 0)
    <div class="card" style="border-color: var(--blue);">
      <div class="ch" style="background: var(--blue-lt); border-bottom-color: var(--blue);">
        <div class="ch-ic" style="background:var(--blue-lt);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" style="stroke:var(--blue-tx);"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        </div>
        <div class="ch-title" style="color: var(--blue-tx);">Incoming Shipments</div>
      </div>
      <div class="cb" style="padding: 14px;">
        <div style="font-size: 13px; line-height: 1.4; color: var(--txt2);">
          There are <b>{{ $incomingCount }}</b> shipments in transit to your outlet.
          Please verify the quantities and receive them.
        </div>
        <div class="mt-4">
          <a href="{{ route('portal.dispatches') }}" class="btn-pri" style="width: 100%; justify-content: center; background: var(--blue); color: #fff;">
            Receive Cargo Shipments
          </a>
        </div>
      </div>
    </div>
    @endif

    <!-- Recent Sales Logs -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Recent Sales Reports</div>
        <a href="{{ route('portal.sales.index') }}" class="ch-link">
          All Logs
        </a>
      </div>
      <div class="cb" style="padding: 0;">
        <div class="act-feed">
          @forelse($recentSales as $log)
          <div class="act-item" style="padding: 10px 14px;">
            <div style="flex:1;">
              <div class="act-ti" style="font-size:12.5px; font-weight:600;">
                Report: {{ $log->log_date->format('Y-m-d') }}
              </div>
              <div class="act-de" style="font-size: 11.5px; color: var(--txt2); margin-top: 2px;">
                Gross Sales: <b>₹{{ number_format($log->total_revenue, 2) }}</b>
              </div>
              <div class="act-tm">Logged {{ $log->created_at->diffForHumans() }}</div>
            </div>
          </div>
          @empty
          <div style="padding: 20px; text-align: center; color: var(--txt3); font-size:12.5px;">
            No sales reports logged yet.
          </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
