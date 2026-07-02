@extends('layouts.app')

@section('title', 'Outlet Stock Monitor — DessertOps')
@section('breadcrumb', 'Outlet Stock Monitor')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Outlet Stock Monitor</div>
    <div class="ph-sub">Real-time internal stock levels and movement audit logs across all outlets</div>
  </div>
</div>

<!-- Tabs selector -->
<div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--div2); padding-bottom: 12px; align-items: center;">
  <button id="tab-btn-store" class="btn-tab active" onclick="switchStockTab('store')" style="padding: 8px 16px; border: none; background: var(--purple-lt); color: var(--purple-tx); border-radius: var(--radius); font-weight: 600; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 8px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
    Store Stock
  </button>
  <button id="tab-btn-kitchen" class="btn-tab" onclick="switchStockTab('kitchen')" style="padding: 8px 16px; border: none; background: transparent; color: var(--txt2); border-radius: var(--radius); font-weight: 500; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 8px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
    Kitchen Stock
  </button>
  <button id="tab-btn-showcase" class="btn-tab" onclick="switchStockTab('showcase')" style="padding: 8px 16px; border: none; background: transparent; color: var(--txt2); border-radius: var(--radius); font-weight: 500; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 8px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;"><circle cx="12" cy="12" r="10"/><polygon points="12 8 8 12 12 16 16 12 12 8"/></svg>
    Showcase Stock
  </button>
</div>

<!-- Stock Grid Views -->
<div style="margin-bottom: 30px;">
  <!-- Store Stock Tab -->
  <div id="stock-grid-store" class="stock-grid-view">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 16px;">
      @forelse($outlets as $outlet)
      <div class="card outlet-stock-card" style="transition: all 0.2s ease;">
        <div class="ch" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--div2); padding: 12px 16px;">
          <div>
            <div class="ch-title" style="font-size: 14.5px; font-weight: 700;">
              <a href="{{ route('outlets.show', $outlet->id) }}" style="color: var(--txt); text-decoration: none;">
                {{ $outlet->name }}
              </a>
            </div>
            <div style="font-size: 11px; color: var(--txt3); margin-top: 2px;">
              {{ $outlet->contact_person ?? 'No Manager' }} &bull; {{ $outlet->phone ?? 'N/A' }}
            </div>
          </div>
          @if($outlet->type === 'own')
            <span class="badge bg">Own Store</span>
          @else
            <span class="badge bp">Franchise</span>
          @endif
        </div>
        <div class="cb" style="padding: 0;">
          <table class="tbl" style="font-size: 12.5px;">
            <thead>
              <tr style="background: var(--pg-bg);">
                <th style="padding: 8px 16px;">Item Name</th>
                <th style="padding: 8px 16px;">SKU</th>
                <th style="text-align: right; padding: 8px 16px;">Qty</th>
              </tr>
            </thead>
            <tbody>
              @php
                $storeStocks = $outlet->stocks->filter(function($s) { return $s->store_quantity > 0; });
              @endphp
              @forelse($storeStocks as $stock)
              <tr>
                <td style="font-weight: 600; padding: 8px 16px;">
                  {{ $stock->product ? $stock->product->name : ($stock->material ? $stock->material->name : 'Unknown') }}
                </td>
                <td class="mono" style="font-size: 11px; color: var(--txt2); padding: 8px 16px;">
                  {{ $stock->product ? $stock->product->sku : ($stock->material ? $stock->material->sku : 'N/A') }}
                </td>
                <td style="text-align: right; font-weight: 600; color: var(--green-tx); padding: 8px 16px;" class="mono">
                  {{ number_format($stock->store_quantity, 0) }} {{ $stock->product ? 'Units' : 'Pcs' }}
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="3" style="text-align: center; color: var(--txt3); padding: 20px 10px;">
                  No stock in Store
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      @empty
      <div class="card" style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--txt2);">
        No outlets registered.
      </div>
      @endforelse
    </div>
  </div>

  <!-- Kitchen Stock Tab -->
  <div id="stock-grid-kitchen" class="stock-grid-view" style="display: none;">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 16px;">
      @forelse($outlets as $outlet)
      <div class="card outlet-stock-card" style="transition: all 0.2s ease;">
        <div class="ch" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--div2); padding: 12px 16px;">
          <div>
            <div class="ch-title" style="font-size: 14.5px; font-weight: 700;">
              <a href="{{ route('outlets.show', $outlet->id) }}" style="color: var(--txt); text-decoration: none;">
                {{ $outlet->name }}
              </a>
            </div>
            <div style="font-size: 11px; color: var(--txt3); margin-top: 2px;">
              {{ $outlet->contact_person ?? 'No Manager' }} &bull; {{ $outlet->phone ?? 'N/A' }}
            </div>
          </div>
          @if($outlet->type === 'own')
            <span class="badge bg">Own Store</span>
          @else
            <span class="badge bp">Franchise</span>
          @endif
        </div>
        <div class="cb" style="padding: 0;">
          <table class="tbl" style="font-size: 12.5px;">
            <thead>
              <tr style="background: var(--pg-bg);">
                <th style="padding: 8px 16px;">Item Name</th>
                <th style="padding: 8px 16px;">SKU</th>
                <th style="text-align: right; padding: 8px 16px;">Qty</th>
              </tr>
            </thead>
            <tbody>
              @php
                $kitchenStocks = $outlet->stocks->filter(function($s) { return $s->kitchen_quantity > 0; });
              @endphp
              @forelse($kitchenStocks as $stock)
              <tr>
                <td style="font-weight: 600; padding: 8px 16px;">
                  {{ $stock->product ? $stock->product->name : ($stock->material ? $stock->material->name : 'Unknown') }}
                </td>
                <td class="mono" style="font-size: 11px; color: var(--txt2); padding: 8px 16px;">
                  {{ $stock->product ? $stock->product->sku : ($stock->material ? $stock->material->sku : 'N/A') }}
                </td>
                <td style="text-align: right; font-weight: 600; color: var(--amber-tx); padding: 8px 16px;" class="mono">
                  {{ number_format($stock->kitchen_quantity, 0) }} {{ $stock->product ? 'Units' : 'Pcs' }}
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="3" style="text-align: center; color: var(--txt3); padding: 20px 10px;">
                  No stock in Kitchen
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      @empty
      <div class="card" style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--txt2);">
        No outlets registered.
      </div>
      @endforelse
    </div>
  </div>

  <!-- Showcase Stock Tab -->
  <div id="stock-grid-showcase" class="stock-grid-view" style="display: none;">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 16px;">
      @forelse($outlets as $outlet)
      <div class="card outlet-stock-card" style="transition: all 0.2s ease;">
        <div class="ch" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--div2); padding: 12px 16px;">
          <div>
            <div class="ch-title" style="font-size: 14.5px; font-weight: 700;">
              <a href="{{ route('outlets.show', $outlet->id) }}" style="color: var(--txt); text-decoration: none;">
                {{ $outlet->name }}
              </a>
            </div>
            <div style="font-size: 11px; color: var(--txt3); margin-top: 2px;">
              {{ $outlet->contact_person ?? 'No Manager' }} &bull; {{ $outlet->phone ?? 'N/A' }}
            </div>
          </div>
          @if($outlet->type === 'own')
            <span class="badge bg">Own Store</span>
          @else
            <span class="badge bp">Franchise</span>
          @endif
        </div>
        <div class="cb" style="padding: 0;">
          <table class="tbl" style="font-size: 12.5px;">
            <thead>
              <tr style="background: var(--pg-bg);">
                <th style="padding: 8px 16px;">Item Name</th>
                <th style="padding: 8px 16px;">SKU</th>
                <th style="text-align: right; padding: 8px 16px;">Qty</th>
              </tr>
            </thead>
            <tbody>
              @php
                $showcaseStocks = $outlet->stocks->filter(function($s) { return $s->showcase_quantity > 0; });
              @endphp
              @forelse($showcaseStocks as $stock)
              <tr>
                <td style="font-weight: 600; padding: 8px 16px;">
                  {{ $stock->product ? $stock->product->name : ($stock->material ? $stock->material->name : 'Unknown') }}
                </td>
                <td class="mono" style="font-size: 11px; color: var(--txt2); padding: 8px 16px;">
                  {{ $stock->product ? $stock->product->sku : ($stock->material ? $stock->material->sku : 'N/A') }}
                </td>
                <td style="text-align: right; font-weight: 600; color: var(--purple-tx); padding: 8px 16px;" class="mono">
                  {{ number_format($stock->showcase_quantity, 0) }} {{ $stock->product ? 'Units' : 'Pcs' }}
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="3" style="text-align: center; color: var(--txt3); padding: 20px 10px;">
                  No stock in Showcase
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      @empty
      <div class="card" style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--txt2);">
        No outlets registered.
      </div>
      @endforelse
    </div>
  </div>
</div>

<!-- Stock Movement Audit Log -->
<div class="card">
  <div class="ch" style="display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div class="ch-ic" style="background: var(--purple-lt); color: var(--purple-tx);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
      </div>
      <div class="ch-title">Internal Stock Movements Audit Log</div>
    </div>
    <div style="font-size: 11.5px; color: var(--txt3); font-weight: 500;">
      Real-Time Operations Ledger
    </div>
  </div>
  <div class="cb" style="padding: 0; overflow-x: auto;">
    <table class="tbl">
      <thead>
        <tr>
          <th>Date & Time</th>
          <th>Outlet</th>
          <th>Stock Item</th>
          <th>Movement Pathway</th>
          <th style="text-align: right;">Quantity</th>
          <th>Logged By</th>
          <th>Reference / Notes</th>
        </tr>
      </thead>
      <tbody>
        @php
          $locationBadges = [
              'store' => 'badge bn', // grey
              'kitchen' => 'badge ba', // amber
              'showcase' => 'badge bp', // purple
          ];
        @endphp
        @forelse($movements as $mov)
        <tr>
          <td data-label="Date & Time" class="mono text-nowrap" style="font-size: 12px; color: var(--txt2);">
            {{ $mov->created_at ? $mov->created_at->format('Y-m-d H:i') : 'N/A' }}
          </td>
          <td data-label="Outlet" style="font-weight: 600;">
            @if($mov->outlet)
              <a href="{{ route('outlets.show', $mov->outlet->id) }}" style="color: var(--txt); text-decoration: none; font-weight: 600;">
                {{ $mov->outlet->name }}
              </a>
            @else
              <span class="td3">N/A</span>
            @endif
          </td>
          <td data-label="Stock Item">
            @if($mov->product)
              <span style="font-weight: 600;">{{ $mov->product->name }}</span>
              <div class="mono" style="font-size: 10.5px; color: var(--txt3);">{{ $mov->product->sku }} &bull; Product</div>
            @elseif($mov->material)
              <span style="font-weight: 600;">{{ $mov->material->name }}</span>
              <div class="mono" style="font-size: 10.5px; color: var(--txt3);">{{ $mov->material->sku }} &bull; Material</div>
            @else
              <span class="td3">Unknown</span>
            @endif
          </td>
          <td data-label="Movement Pathway" style="vertical-align: middle;">
            <div style="display: flex; align-items: center; gap: 4px;">
              <span class="{{ $locationBadges[$mov->from_location] ?? 'badge bn' }}">{{ ucfirst($mov->from_location) }}</span>
              <span style="color: var(--txt3); font-weight: bold;">&rarr;</span>
              <span class="{{ $locationBadges[$mov->to_location] ?? 'badge bn' }}">{{ ucfirst($mov->to_location) }}</span>
            </div>
          </td>
          <td data-label="Quantity" style="text-align: right; font-weight: 600;" class="mono text-nowrap">
            {{ number_format($mov->quantity, 0) }} {{ $mov->product ? 'Units' : ($mov->material ? $mov->material->unit : 'Pcs') }}
          </td>
          <td data-label="Logged By" class="mono" style="font-size: 12px; color: var(--txt2);">
            {{ $mov->logged_by ?? 'System' }}
          </td>
          <td data-label="Reference / Notes" style="font-size: 12px; color: var(--txt2); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $mov->reference }}">
            {{ $mov->reference ?? '—' }}
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center td2" style="padding: 30px; color: var(--txt3);">
            No internal stock movements logged yet.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<style>
  .outlet-stock-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--card-sh-md);
    border-color: var(--div2);
  }
  .btn-tab {
    font-family: inherit;
    font-size: 13px;
  }
  .btn-tab:hover {
    background: var(--div);
    color: var(--txt);
  }
  .btn-tab.active {
    background: var(--purple-lt) !important;
    color: var(--purple-tx) !important;
  }
</style>
@endsection

@section('scripts')
<script>
function switchStockTab(tabName) {
  // Hide all view grids
  document.querySelectorAll('.stock-grid-view').forEach(function(el) {
    el.style.display = 'none';
  });
  
  // Show target view grid
  document.getElementById('stock-grid-' + tabName).style.display = 'block';
  
  // Reset all buttons style
  document.querySelectorAll('.btn-tab').forEach(function(btn) {
    btn.classList.remove('active');
    btn.style.background = 'transparent';
    btn.style.color = 'var(--txt2)';
    btn.style.fontWeight = '500';
  });
  
  // Apply active style to selected button
  var activeBtn = document.getElementById('tab-btn-' + tabName);
  activeBtn.classList.add('active');
  activeBtn.style.background = 'var(--purple-lt)';
  activeBtn.style.color = 'var(--purple-tx)';
  activeBtn.style.fontWeight = '600';
}
</script>
@endsection
