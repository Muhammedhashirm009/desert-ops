@extends('layouts.app')

@section('title', 'Operations Dashboard — DessertOps')
@section('breadcrumb', 'Operations Overview')

@section('content')
<!-- Page Header -->
<div class="ph">
  <div>
    <div class="ph-title">Operations Dashboard</div>
    <div class="ph-sub">
      {{ now()->format('l, d F Y') }}
      <span class="ph-sub-dot"></span>
      Central Kitchen
      <span class="ph-sub-dot"></span>
      Last updated just now
    </div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('purchase-orders.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Purchase Order
    </a>
  </div>
</div>

<!-- Summary Strip -->
<div class="sum-strip">
  <div class="sum-item">
    <div class="sum-val"><sup>₹</sup>6,42,800</div>
    <div class="sum-lbl">Month-to-Date Revenue</div>
    <div class="sum-delta">
      <svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>8.1% vs last month
    </div>
  </div>
  <div class="sum-item">
    <div class="sum-val">87.9%</div>
    <div class="sum-lbl">Fulfillment Rate</div>
    <div class="sum-delta">
      <svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>+3.2% this week
    </div>
  </div>
  <div class="sum-item">
    <div class="sum-val">{{ $activeOutletsCount }}</div>
    <div class="sum-lbl">Active Outlets</div>
    <div class="sum-delta">3 own · 2 franchise</div>
  </div>
  <div class="sum-item">
    <div class="sum-val">94.2%</div>
    <div class="sum-lbl">On-Time Dispatch</div>
    <div class="sum-delta">
      <svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>+1.8% vs target
    </div>
  </div>
</div>

<!-- KPI Grid -->
<div class="kpi-grid">
  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--purple-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--purple-tx)"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
      </div>
      <div class="kpi-trend t-up">
        <svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>12.4%
      </div>
    </div>
    <div class="kpi-val"><sup>₹</sup>84,200</div>
    <div class="kpi-lbl">Today's Production Value</div>
    <div class="kpi-foot">
      <span class="kpi-foot-lbl">Yesterday</span>
      <span class="kpi-foot-val">₹74,920</span>
    </div>
  </div>

  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--amber-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--amber-tx)"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      </div>
      <div class="kpi-trend t-neu">{{ $openPoCount }} open</div>
    </div>
    <div class="kpi-val">₹{{ number_format($openPoValue / 1000, 1) }}K</div>
    <div class="kpi-lbl">Open PO Value</div>
    <div class="kpi-foot">
      <span class="kpi-foot-lbl">Pending approval</span>
      <span class="kpi-foot-val">{{ $openPoCount }} POs</span>
    </div>
  </div>

  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--green-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--green-tx)"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      </div>
      <div class="kpi-trend t-up">
        <svg viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg>4 done
      </div>
    </div>
    <div class="kpi-val">12</div>
    <div class="kpi-lbl">Dispatches Today</div>
    <div class="kpi-foot">
      <span class="kpi-foot-lbl">Pending dispatch</span>
      <span class="kpi-foot-val">3 routes</span>
    </div>
  </div>

  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--red-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--red-tx)"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div class="kpi-trend {{ $lowStockCount > 0 ? 't-dn' : 't-up' }}">
        {{ $lowStockCount > 0 ? 'Critical' : 'Normal' }}
      </div>
    </div>
    <div class="kpi-val">{{ $lowStockCount }}</div>
    <div class="kpi-lbl">Low Stock Alerts</div>
    <div class="kpi-foot">
      <span class="kpi-foot-lbl">Materials affected</span>
      <span class="kpi-foot-val">{{ $lowStockCount }} items</span>
    </div>
  </div>
</div>

<!-- Chart + Stock -->
<div class="row r-3-1">
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </div>
      <div style="flex:1;">
        <div class="ch-title">Production vs Dispatch</div>
        <div class="ch-sub">Last 7 days · Central Kitchen</div>
      </div>
      <a class="ch-link" href="#">Full Report <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></a>
    </div>
    <div class="chart-wrap">
      <div class="bar-chart" id="chart"></div>
    </div>
    <div class="chart-legend">
      <div class="leg"><div class="leg-dot" style="background:var(--btn);"></div>Production (₹)</div>
      <div class="leg"><div class="leg-dot" style="background:var(--green);"></div>Dispatched (₹)</div>
      <div class="leg"><div class="leg-dot" style="background:var(--blue);opacity:.5;"></div>Franchise Orders (₹)</div>
    </div>
    <div class="chart-stats">
      <div class="cs-cell">
        <div class="cs-val">₹5.8L</div>
        <div class="cs-lbl">Week Production</div>
      </div>
      <div class="cs-cell">
        <div class="cs-val">₹5.1L</div>
        <div class="cs-lbl">Week Dispatched</div>
      </div>
      <div class="cs-cell">
        <div class="cs-val">87.9%</div>
        <div class="cs-lbl">Fulfillment Rate</div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--amber-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--amber-tx)"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      </div>
      <div style="flex:1;">
        <div class="ch-title">Outlet Stock Levels</div>
        <div class="ch-sub">Live status</div>
      </div>
      <a class="ch-link" href="#">Details <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></a>
    </div>
    <div class="cb">
      <div class="srows">
        <div class="srow">
          <div class="srow-top">
            <span class="srow-name"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>MG Road</span>
            <div class="srow-right"><span class="badge bg" style="font-size:10px;padding:1.5px 6px;">Own</span><span class="srow-pct">82%</span></div>
          </div>
          <div class="track"><div class="fill" style="width:82%;background:var(--green);"></div></div>
        </div>
        <div class="srow">
          <div class="srow-top">
            <span class="srow-name"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>Calicut Beach</span>
            <div class="srow-right"><span class="badge bg" style="font-size:10px;padding:1.5px 6px;">Own</span><span class="srow-pct">67%</span></div>
          </div>
          <div class="track"><div class="fill" style="width:67%;background:var(--btn);"></div></div>
        </div>
        <div class="srow">
          <div class="srow-top">
            <span class="srow-name"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>Palayam</span>
            <div class="srow-right"><span class="badge bg" style="font-size:10px;padding:1.5px 6px;">Own</span><span class="srow-pct">41%</span></div>
          </div>
          <div class="track"><div class="fill" style="width:41%;background:var(--amber);"></div></div>
        </div>
        <div class="srow">
          <div class="srow-top">
            <span class="srow-name"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>Thrissur</span>
            <div class="srow-right"><span class="badge ba" style="font-size:10px;padding:1.5px 6px;">Franchise</span><span class="srow-pct" style="color:var(--red-tx);">23%</span></div>
          </div>
          <div class="track"><div class="fill" style="width:23%;background:var(--red);"></div></div>
        </div>
        <div class="srow">
          <div class="srow-top">
            <span class="srow-name"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>Kochi</span>
            <div class="srow-right"><span class="badge ba" style="font-size:10px;padding:1.5px 6px;">Franchise</span><span class="srow-pct">58%</span></div>
          </div>
          <div class="track"><div class="fill" style="width:58%;background:var(--blue);"></div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- PO Table + Alerts Feed -->
<div class="row r-2">
  <!-- Purchase Orders -->
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      </div>
      <div style="flex:1;">
        <div class="ch-title">Recent Purchase Orders</div>
        <div class="ch-sub">Live activity from procurement</div>
      </div>
      <a href="{{ route('purchase-orders.index') }}" class="ch-link">All POs <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg></a>
    </div>
    <table>
      <thead>
        <tr>
          <th>PO No.</th>
          <th>Supplier</th>
          <th>Amount</th>
          <th>Status</th>
          <th>ETA</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentPos as $po)
        <tr>
          <td class="mono">{{ $po->po_number }}</td>
          <td>
            <div class="td-name">{{ $po->supplier->name }}</div>
            <div class="td-meta">{{ $po->items->count() }} items</div>
          </td>
          <td class="mono">₹{{ number_format($po->total_amount, 2) }}</td>
          <td>
            @if($po->status === 'received')
              <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Received</span>
            @elseif($po->status === 'cancelled')
              <span class="badge br"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Cancelled</span>
            @else
              <span class="badge ba"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Pending</span>
            @endif
          </td>
          <td class="td3 mono">{{ $po->eta ? $po->eta->format('d M') : '—' }}</td>
          <td>
            <a href="{{ route('purchase-orders.show', $po->id) }}" class="td-act">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              View
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="text-center td2">No Purchase Orders created yet. <a href="{{ route('purchase-orders.create') }}">Create one now</a>.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="display:flex;flex-direction:column;gap:14px;">
    <!-- Alerts Feed -->
    <div class="card">
      <div class="ch">
        <div class="ch-ic" style="background:var(--red-lt);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--red-tx)"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>
        <div style="flex:1;">
          <div class="ch-title">System Alerts</div>
          <div class="ch-sub">{{ count($alerts) }} notification(s)</div>
        </div>
      </div>
      <div class="act-feed">
        @foreach($alerts as $alert)
        <div class="act-item">
          <div class="act-ic" style="background: {{ $alert['type'] === 'critical' ? 'var(--red-lt)' : 'var(--green-lt)' }};">
            @if($alert['type'] === 'critical')
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--red-tx)"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            @else
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--green-tx)"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
            @endif
          </div>
          <div style="flex:1;min-width:0;">
            <div class="act-ti">{{ $alert['title'] }}</div>
            <div class="act-de">{{ $alert['details'] }}</div>
            <div class="act-tm">{{ $alert['time'] }}</div>
          </div>
          <div class="act-r">
            <span class="badge {{ $alert['type'] === 'critical' ? 'br' : 'bg' }}">
              {{ $alert['type'] === 'critical' ? 'Critical' : 'Info' }}
            </span>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    <!-- Top SKUs (Visual only) -->
    <div class="card">
      <div class="ch">
        <div class="ch-ic" style="background:var(--div);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
        </div>
        <div style="flex:1;">
          <div class="ch-title">Top Dessert Products — MTD</div>
          <div class="ch-sub">By revenue</div>
        </div>
      </div>
      <div>
        <div class="sku-item">
          <div class="sku-rank">01</div>
          <div class="sku-name">
            <div class="sku-n">Gulab Jamun Box</div>
            <div class="sku-s">248 units · All outlets</div>
          </div>
          <div class="sku-r">
            <div class="sku-val">₹62,000</div>
            <div class="sku-bar"><div class="sku-fill" style="width:100%;"></div></div>
          </div>
        </div>
        <div class="sku-item">
          <div class="sku-rank">02</div>
          <div class="sku-name">
            <div class="sku-n">Mango Custard</div>
            <div class="sku-s">186 units · Franchise</div>
          </div>
          <div class="sku-r">
            <div class="sku-val">₹46,500</div>
            <div class="sku-bar"><div class="sku-fill" style="width:75%;"></div></div>
          </div>
        </div>
        <div class="sku-item">
          <div class="sku-rank">03</div>
          <div class="sku-name">
            <div class="sku-n">Chocolate Truffle</div>
            <div class="sku-s">142 units · Own outlets</div>
          </div>
          <div class="sku-r">
            <div class="sku-val">₹42,600</div>
            <div class="sku-bar"><div class="sku-fill" style="width:68%;"></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
// Bar chart matching design
const cdata = [
  {d:'Mon',p:72,dp:60,f:20},{d:'Tue',p:88,dp:78,f:35},
  {d:'Wed',p:65,dp:55,f:28},{d:'Thu',p:95,dp:85,f:40},
  {d:'Fri',p:78,dp:70,f:30},{d:'Sat',p:100,dp:90,f:45},
  {d:'Sun',p:84,dp:72,f:38}
];
const wrap = document.getElementById('chart');
cdata.forEach(d => {
  const col = document.createElement('div');
  col.className = 'bc-col';
  col.innerHTML = `
    <div class="bc-bars">
      <div class="bc-b" style="height:${d.p}%;background:#111827;opacity:.85;"></div>
      <div class="bc-b" style="height:${d.dp}%;background:#16A34A;opacity:.8;"></div>
      <div class="bc-b" style="height:${d.f}%;background:#2563EB;opacity:.6;"></div>
    </div>
    <div class="bc-lbl">${d.d}</div>
  `;
  wrap.appendChild(col);
});
</script>
@endsection
