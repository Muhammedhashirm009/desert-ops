@extends('layouts.app')

@section('title', 'Central Kitchen Stocks — DessertOps')
@section('breadcrumb', 'Central Kitchen Stocks')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Central Kitchen Stock</div>
    <div class="ph-sub">Monitor raw materials released from the main store and available for dessert production</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('material-requests.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
      Request Raw Materials
    </a>
  </div>
</div>

<!-- KPI Grid -->
<div class="kpi-grid">
  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--blue-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="color:var(--blue-tx);"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
      </div>
    </div>
    <div class="kpi-val">{{ $materials->where('kitchen_stock', '>', 0)->count() }}</div>
    <div class="kpi-lbl">Active Materials in Kitchen</div>
  </div>

  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--red-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="color:var(--red-tx);"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
    </div>
    <div class="kpi-val">{{ $materials->where('kitchen_stock', '<=', 0)->count() }}</div>
    <div class="kpi-lbl">Out of Stock in Kitchen</div>
  </div>

  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--green-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="color:var(--green-tx);"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
    </div>
    <div class="kpi-val">{{ $recentReleases->count() }}</div>
    <div class="kpi-lbl">Recent Transfers Completed</div>
  </div>

  <div class="kpi">
    <div class="kpi-row1">
      <div class="kpi-icon" style="background:var(--purple-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="color:var(--purple-tx);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      </div>
    </div>
    <div class="kpi-val">{{ $materials->count() }}</div>
    <div class="kpi-lbl">Total Catalog Materials</div>
  </div>
</div>

<div class="row r-3-1">
  <!-- Left Side: Table of Kitchen Stocks -->
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
      </div>
      <div class="ch-title">Kitchen Raw Inventory Status</div>
      <div class="tb-search" style="width: 220px; border-radius: var(--radius-sm);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="kitchenSearch" placeholder="Search kitchen items..." onkeyup="filterKitchenTable()">
      </div>
    </div>

    <table id="kitchenStockTable" class="tbl">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Material Name</th>
          <th>Category</th>
          <th style="text-align: right;">Kitchen Stock</th>
          <th style="text-align: right;">Store Stock (Ref)</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($materials as $material)
        <tr>
          <td data-label="SKU" class="mono">{{ $material->sku }}</td>
          <td data-label="Material Name">
            <div class="td-name">{{ $material->name }}</div>
            <div class="td-meta">Unit of Measure: {{ $material->unit }}</div>
          </td>
          <td data-label="Category">
            @if($material->category === 'ingredient')
              <span class="badge bp">Ingredient</span>
            @else
              <span class="badge bb">Packaging</span>
            @endif
          </td>
          <td data-label="Kitchen Stock" class="mono" style="text-align: right; font-weight: 600; color: var(--blue-tx);">
            {{ number_format($material->kitchen_stock, 2) }} {{ $material->unit }}
          </td>
          <td data-label="Store Stock (Ref)" class="mono td2" style="text-align: right;">
            {{ number_format($material->current_stock, 2) }} {{ $material->unit }}
          </td>
          <td data-label="Status">
            @if($material->kitchen_stock > 5)
              <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>In Stock</span>
            @elseif($material->kitchen_stock > 0)
              <span class="badge ba"><svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg>Low Stock</span>
            @else
              <span class="badge br"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Out of Stock</span>
            @endif
          </td>
          <td data-label="Actions">
            <a href="{{ route('material-requests.create', ['material_id' => $material->id]) }}" class="td-act">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
              Request
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center td2">No materials found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Right Side: Recent Release History Feed -->
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--green-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--green-tx)"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <div class="ch-title">Recent Releases</div>
    </div>
    <div class="cb" style="padding:0;">
      <div class="act-feed">
        @forelse($recentReleases as $mr)
        <div class="act-item">
          <div class="act-ic" style="background:var(--blue-lt);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--blue-tx)"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
          </div>
          <div style="flex:1;">
            <div class="act-ti">
              <a href="{{ route('material-requests.show', $mr->id) }}" style="color:inherit; text-decoration:none; font-weight:600;">
                {{ $mr->request_number }}
              </a>
            </div>
            <div class="act-de">
              <ul style="margin: 4px 0 0 12px; padding: 0; font-size: 11.5px; color: var(--txt2);">
                @foreach($mr->items as $item)
                  @if($item->quantity_released > 0)
                    <li>{{ $item->material->name ?? 'Material' }}: <b>{{ number_format($item->quantity_released, 2) }} {{ $item->material->unit }}</b></li>
                  @endif
                @endforeach
              </ul>
            </div>
            <div class="act-tm">Released on {{ $mr->updated_at->format('M d, H:i') }}</div>
          </div>
        </div>
        @empty
        <div style="padding:20px; text-align:center; color:var(--txt3);">
          No recent releases to kitchen.
        </div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
function filterKitchenTable() {
  const input = document.getElementById("kitchenSearch");
  const filter = input.value.toUpperCase();
  const table = document.getElementById("kitchenStockTable");
  const tr = table.getElementsByTagName("tr");

  for (let i = 1; i < tr.length; i++) {
    let match = false;
    const tds = tr[i].getElementsByTagName("td");
    
    // Search across SKU (td[0]) and Name (td[1])
    if (tds.length > 1) {
      const skuText = tds[0].textContent || tds[0].innerText;
      const nameText = tds[1].textContent || tds[1].innerText;
      
      if (skuText.toUpperCase().indexOf(filter) > -1 || nameText.toUpperCase().indexOf(filter) > -1) {
        match = true;
      }
    }
    
    if (match) {
      tr[i].style.display = "";
    } else {
      tr[i].style.display = "none";
    }
  }
}
</script>
@endsection
