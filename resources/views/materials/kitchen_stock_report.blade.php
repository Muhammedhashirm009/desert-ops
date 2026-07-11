@extends('layouts.app')

@section('title', 'Daily Stock Report — Central Kitchen')
@section('breadcrumb', 'Central Kitchen Daily Stock Report')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Central Kitchen Daily Stock Report</div>
    <div class="ph-sub">View daily opening and closing stock levels for raw materials and finished products in the Central Kitchen</div>
  </div>
  <div class="ph-acts">
    <form action="{{ route('kitchen.stock-report') }}" method="GET" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
      <label for="report-date" style="font-size: 13px; font-weight: 600; color: var(--txt2);">Select Date:</label>
      <input type="date" id="report-date" name="date" value="{{ $dateStr }}" max="{{ now()->toDateString() }}" style="padding: 8px 12px; border: 1px solid var(--div2); border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; outline: none; background: var(--card); color: var(--txt); width: 150px;" onchange="this.form.submit()">
    </form>
  </div>
</div>

<!-- Tab Switcher -->
<div class="tabs" style="display: flex; gap: 8px; border-bottom: 1px solid var(--div2); padding-bottom: 8px; margin-bottom: 20px;">
  <button type="button" class="btn-tab active" id="tab-products-btn" onclick="switchReportTab('products')" style="padding: 8px 16px; border: none; border-radius: var(--radius); font-size: 13.5px; font-weight: 600; cursor: pointer; background: var(--purple-lt); color: var(--purple-tx); transition: all 0.2s;">
    Finished Desserts ({{ count($productData) }})
  </button>
  <button type="button" class="btn-tab" id="tab-materials-btn" onclick="switchReportTab('materials')" style="padding: 8px 16px; border: none; border-radius: var(--radius); font-size: 13.5px; font-weight: 600; cursor: pointer; background: transparent; color: var(--txt3); transition: all 0.2s;">
    Raw Materials ({{ count($materialData) }})
  </button>
</div>

<!-- Section 1: Finished Products Stock -->
<div id="products-report-container">
  <div class="card mb-4">
    <div class="ch">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div>
        <div class="ch-title">Finished Desserts Stock Ledger on {{ \Carbon\Carbon::parse($dateStr)->format('d M Y') }}</div>
        <div class="ch-sub">Opening is calculated at 12:00 AM; Closing is the final balance for the day</div>
      </div>
    </div>

    <table class="tbl">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Product Name</th>
          <th style="text-align: right;">Opening Stock</th>
          <th style="text-align: right; color: var(--green-tx);">Produced (+)</th>
          <th style="text-align: right; color: var(--red-tx);">Dispatched (-)</th>
          <th style="text-align: right; font-weight: bold;">Closing Stock</th>
        </tr>
      </thead>
      <tbody>
        @forelse($productData as $row)
        <tr>
          <td data-label="SKU" class="mono">{{ $row->sku }}</td>
          <td data-label="Product Name" style="font-weight: 600;">
            {{ $row->name }}
          </td>
          <td data-label="Opening Stock" class="mono" style="text-align: right; color: var(--txt2);">
            {{ number_format($row->opening_stock, 0) }} units
          </td>
          <td data-label="Produced (+)" class="mono font-semibold" style="text-align: right; color: var(--green-tx);">
            @if($row->additions > 0)
              +{{ number_format($row->additions, 0) }}
            @else
              0
            @endif
          </td>
          <td data-label="Dispatched (-)" class="mono font-semibold" style="text-align: right; color: var(--red-tx);">
            @if($row->subtractions > 0)
              -{{ number_format($row->subtractions, 0) }}
            @else
              0
            @endif
          </td>
          <td data-label="Closing Stock" class="mono font-semibold" style="text-align: right; font-weight: 700; color: var(--txt);">
            {{ number_format($row->closing_stock, 0) }} units
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="text-center td2">No dessert products registered in the catalog.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Section 2: Raw Materials Stock -->
<div id="materials-report-container" style="display: none;">
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
      </div>
      <div>
        <div class="ch-title">Raw Materials Kitchen Stock Ledger on {{ \Carbon\Carbon::parse($dateStr)->format('d M Y') }}</div>
        <div class="ch-sub">Opening is calculated at 12:00 AM; Closing is the final balance for the day</div>
      </div>
    </div>

    <table class="tbl">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Material Name</th>
          <th style="text-align: right;">Opening Stock</th>
          <th style="text-align: right; color: var(--green-tx);">Received (+)</th>
          <th style="text-align: right; color: var(--red-tx);">Consumed (-)</th>
          <th style="text-align: right; font-weight: bold;">Closing Stock</th>
        </tr>
      </thead>
      <tbody>
        @forelse($materialData as $row)
        <tr>
          <td data-label="SKU" class="mono">{{ $row->sku }}</td>
          <td data-label="Material Name" style="font-weight: 600;">
            {{ $row->name }}
          </td>
          <td data-label="Opening Stock" class="mono" style="text-align: right; color: var(--txt2);">
            {{ number_format($row->opening_stock, 2) }} {{ $row->unit }}
          </td>
          <td data-label="Received (+)" class="mono font-semibold" style="text-align: right; color: var(--green-tx);">
            @if($row->additions > 0)
              +{{ number_format($row->additions, 2) }}
            @else
              0
            @endif
          </td>
          <td data-label="Consumed (-)" class="mono font-semibold" style="text-align: right; color: var(--red-tx);">
            @if($row->subtractions > 0)
              -{{ number_format($row->subtractions, 2) }}
            @else
              0
            @endif
          </td>
          <td data-label="Closing Stock" class="mono font-semibold" style="text-align: right; font-weight: 700; color: var(--txt);">
            {{ number_format($row->closing_stock, 2) }} {{ $row->unit }}
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="text-center td2">No raw materials registered in the catalog.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<script>
function switchReportTab(tab) {
    const productsBtn = document.getElementById('tab-products-btn');
    const materialsBtn = document.getElementById('tab-materials-btn');
    const productsCont = document.getElementById('products-report-container');
    const materialsCont = document.getElementById('materials-report-container');

    if (tab === 'products') {
        productsBtn.classList.add('active');
        productsBtn.style.background = 'var(--purple-lt)';
        productsBtn.style.color = 'var(--purple-tx)';

        materialsBtn.classList.remove('active');
        materialsBtn.style.background = 'transparent';
        materialsBtn.style.color = 'var(--txt3)';

        productsCont.style.display = 'block';
        materialsCont.style.display = 'none';
    } else {
        materialsBtn.classList.add('active');
        materialsBtn.style.background = 'var(--purple-lt)';
        materialsBtn.style.color = 'var(--purple-tx)';

        productsBtn.classList.remove('active');
        productsBtn.style.background = 'transparent';
        productsBtn.style.color = 'var(--txt3)';

        productsCont.style.display = 'none';
        materialsCont.style.display = 'block';
    }
}
</script>
@endsection
