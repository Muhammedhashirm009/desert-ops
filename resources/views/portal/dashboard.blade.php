@extends('layouts.portal')

@section('title', 'Store Dashboard — DessertOps Portal')
@section('breadcrumb', 'Store Dashboard')

@section('content')
@php
  $isOutletAdmin = session('portal_employee_role', 'outlet_admin') === 'outlet_admin';
@endphp
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
    <a href="{{ route('portal.showcase-requests.create') }}" class="btn-ghost">
      Request Showcase
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
    @if($isOutletAdmin)
    <div class="kpi-val">₹{{ number_format($recentSales->sum('net_revenue'), 2) }}</div>
    <div class="kpi-lbl">Net Revenue (Recent Logs)</div>
    @else
    <div class="kpi-val">{{ $recentSales->sum(fn($s) => $s->items->sum('quantity_sold')) }}</div>
    <div class="kpi-lbl">Total Units Sold (Recent)</div>
    @endif
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 340px; gap: 16px;">
  <!-- Left Side: Local Stock Levels -->
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
      </div>
      <div class="ch-title">Current Local Inventory & Stock Locations</div>
    </div>
    <table class="tbl">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Dessert Product</th>
          <th style="text-align: right;">Store Stock</th>
          <th style="text-align: right;">Kitchen Stock</th>
          <th style="text-align: right;">Showcase Stock</th>
          <th style="text-align: right;">Opening Stock</th>
          <th style="text-align: right;">Closing Stock</th>
          @if($isOutletAdmin)
          <th style="text-align: right;">Retail Price</th>
          @endif
          @if($isOutletAdmin)
          <th style="text-align: center;">Actions</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @forelse($outlet->stocks as $stock)
        @php
          $isProduct = (bool)$stock->product_id;
          $isCatalogItem = (bool)$stock->outlet_catalog_item_id;
          if ($isCatalogItem && $stock->catalogItem) {
            $name = $stock->catalogItem->name;
            $sku = $stock->catalogItem->sku;
            $priceText = '₹' . number_format($stock->catalogItem->retail_price, 2);
          } elseif ($isProduct) {
            $name = $stock->product->name;
            $sku = $stock->product->sku;
            $priceText = '₹' . number_format($stock->product->retail_price, 2);
          } else {
            $name = $stock->material ? $stock->material->name : 'Unknown';
            $sku = $stock->material ? $stock->material->sku : 'N/A';
            $priceText = 'N/A';
          }
          $storeQty = $stock->store_quantity;
          $kitchenQty = $stock->kitchen_quantity;
          $showcaseQty = $stock->showcase_quantity;
          $unit = ($isProduct || $isCatalogItem) ? 'Units' : 'Pieces';
          $openingStock = $stock->opening_stock ?? $stock->quantity;
          $closingStock = $stock->closing_stock ?? $stock->quantity;
        @endphp
        <tr>
          <td data-label="SKU" class="mono">{{ $sku }}</td>
          <td data-label="Dessert Product" style="font-weight: 600;">
            {{ $name }}
            @if($isCatalogItem)
              <span class="badge bp" style="font-size: 9px; vertical-align: middle; margin-left: 4px;">RECIPE</span>
            @elseif(!$isProduct)
              <span style="font-size: 11px; font-weight: normal; color: var(--txt3);"> (Packaging)</span>
            @endif
          </td>
          <td data-label="Store Stock" class="mono" style="text-align: right; color: var(--txt2);">
            {{ number_format($storeQty, 0) }} {{ $unit }}
          </td>
          <td data-label="Kitchen Stock" class="mono" style="text-align: right; color: var(--txt2);">
            {{ number_format($kitchenQty, 0) }} {{ $unit }}
          </td>
          <td data-label="Showcase Stock" class="mono font-semibold" style="text-align: right; color: var(--green-tx); font-weight: 600;">
            {{ number_format($showcaseQty, 0) }} {{ $unit }}
          </td>
          <td data-label="Opening Stock" class="mono" style="text-align: right; color: var(--txt3);">
            {{ number_format($openingStock, 0) }} {{ $unit }}
          </td>
          <td data-label="Closing Stock" class="mono font-semibold" style="text-align: right; color: var(--txt); font-weight: 600;">
            {{ number_format($closingStock, 0) }} {{ $unit }}
          </td>
          <td data-label="Retail Price" class="mono" style="text-align: right;">@if($isOutletAdmin){{ $priceText }}@else — @endif</td>
          @if($isOutletAdmin)
          <td data-label="Actions" style="text-align: center;">
            <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
              @if($storeQty > 0)
                <button type="button" class="btn-ghost" style="padding: 4px 8px; font-size: 11px;" onclick="openMoveStockModal({{ $stock->id }}, '{{ addslashes($name) }}', 'store', 'kitchen', {{ $storeQty }}, '{{ $unit }}')">
                  Store ➔ Kitchen
                </button>
                @if($isProduct)
                  <button type="button" class="btn-ghost" style="padding: 4px 8px; font-size: 11px;" onclick="openMoveStockModal({{ $stock->id }}, '{{ addslashes($name) }}', 'store', 'showcase', {{ $storeQty }}, '{{ $unit }}')">
                    Store ➔ Showcase
                  </button>
                @endif
              @endif
              @if($kitchenQty > 0)
                @if($isProduct)
                  <button type="button" class="btn-ghost" style="padding: 4px 8px; font-size: 11px;" onclick="openMoveStockModal({{ $stock->id }}, '{{ addslashes($name) }}', 'kitchen', 'showcase', {{ $kitchenQty }}, '{{ $unit }}')">
                    Kitchen ➔ Showcase
                  </button>
                @else
                  <button type="button" class="btn-ghost" style="padding: 4px 8px; font-size: 11px; color: var(--red);" onclick="openMoveStockModal({{ $stock->id }}, '{{ addslashes($name) }}', 'kitchen', 'consumed', {{ $kitchenQty }}, '{{ $unit }}')">
                    Consume Material
                  </button>
                @endif
              @endif
            </div>
          </td>
          @endif
        </tr>
        @empty
        <tr>
          <td colspan="{{ $isOutletAdmin ? 8 : 6 }}" class="text-center td2" style="padding:30px;">
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
                @if($isOutletAdmin)
                Gross Sales: <b>₹{{ number_format($log->total_revenue, 2) }}</b>
                @else
                Items sold: <b>{{ $log->items->sum('quantity_sold') }} units</b>
                @endif
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

<!-- Move Stock Modal -->
<div id="move-stock-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 16px; box-sizing: border-box;">
  <div class="card" style="max-width: 480px; width: 100%; background: var(--card); border: 1px solid var(--border); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15); border-radius: var(--radius); overflow: hidden;">
    <div class="ch" style="border-bottom: 1px solid var(--div); padding: 16px 20px;">
      <div class="ch-title" id="move-modal-title">Move Stock</div>
      <button type="button" class="btn-ghost" style="padding: 4px; display: inline-flex;" onclick="closeMoveStockModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <form action="{{ route('portal.stock.move') }}" method="POST">
      @csrf
      <input type="hidden" name="stock_id" id="move_stock_id">
      <input type="hidden" name="from_location" id="move_from_location">
      <input type="hidden" name="to_location" id="move_to_location">

      <div class="cb" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
        <div>
          <label class="lbl" style="margin-bottom: 6px;" id="move-quantity-lbl">Quantity to Move</label>
          <div style="display: flex; align-items: center; gap: 8px;">
            <input type="number" name="quantity" id="move_quantity" class="txt" style="flex: 1;" required min="0.01" step="0.01">
            <span id="move-unit" style="font-size: 13px; font-weight: 500; color: var(--txt2);"></span>
          </div>
          <div style="font-size: 12px; color: var(--txt3); margin-top: 6px;" id="move-modal-max-lbl"></div>
        </div>
      </div>
      <div class="cf" style="padding: 14px 20px; background: var(--bg); border-top: 1px solid var(--div); display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn-ghost" onclick="closeMoveStockModal()">Cancel</button>
        <button type="submit" class="btn-pri" id="move-submit-btn">Transfer Stock</button>
      </div>
    </form>
  </div>
</div>

@endsection

@section('scripts')
<script>
function openMoveStockModal(stockId, itemName, fromLoc, toLoc, maxQty, unit) {
    document.getElementById('move_stock_id').value = stockId;
    document.getElementById('move_from_location').value = fromLoc;
    document.getElementById('move_to_location').value = toLoc;
    
    // Update labels
    let title = '';
    let btnText = '';
    let labelText = '';
    if (toLoc === 'consumed') {
        title = `Consume ${itemName} from Kitchen`;
        btnText = 'Confirm Consumption';
        labelText = 'Quantity Consumed';
    } else {
        title = `Move ${itemName}: ${fromLoc.toUpperCase()} ➔ ${toLoc.toUpperCase()}`;
        btnText = 'Transfer Stock';
        labelText = 'Quantity to Transfer';
    }
    
    document.getElementById('move-modal-title').textContent = title;
    document.getElementById('move-submit-btn').textContent = btnText;
    document.getElementById('move-quantity-lbl').textContent = labelText;
    
    const qtyInput = document.getElementById('move_quantity');
    qtyInput.max = maxQty;
    qtyInput.value = maxQty; // Default to max
    qtyInput.min = 0.01;
    qtyInput.step = 0.01;
    
    document.getElementById('move-unit').textContent = unit;
    document.getElementById('move-modal-max-lbl').textContent = `Max available: ${maxQty} ${unit}`;
    
    // Show modal
    document.getElementById('move-stock-modal').style.display = 'flex';
}

function closeMoveStockModal() {
    document.getElementById('move-stock-modal').style.display = 'none';
}

// Close on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeMoveStockModal();
    }
});
</script>
@endsection
