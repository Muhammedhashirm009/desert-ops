@extends('layouts.app')

@section('title', 'System Initialization — DessertOps')
@section('breadcrumb', 'Inventory Init')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">System Inventory & Cost Initialization</div>
    <div class="ph-sub">Set up bulk starting stock levels and cost prices (WAC) before launching live operations</div>
  </div>
</div>

<form action="{{ route('inventory.initialize.store') }}" method="POST">
  @csrf

  <div style="margin-bottom: 20px;">
    <!-- Tab Controls -->
    <div style="display: flex; gap: 8px; border-bottom: 2px solid var(--div); padding-bottom: 1px; overflow-x: auto; -webkit-overflow-scrolling: touch;">
      <button type="button" class="tab-btn active" onclick="switchTab('raw-materials')" id="tab-raw-materials" style="padding: 10px 20px; font-weight: 600; border: none; background: none; color: var(--txt3); cursor: pointer; border-bottom: 2px solid transparent; font-size: 14px; white-space: nowrap;">
        Raw Materials
      </button>
      <button type="button" class="tab-btn" onclick="switchTab('finished-products')" id="tab-finished-products" style="padding: 10px 20px; font-weight: 600; border: none; background: none; color: var(--txt3); cursor: pointer; border-bottom: 2px solid transparent; font-size: 14px; white-space: nowrap;">
        Finished Products
      </button>
      <button type="button" class="tab-btn" onclick="switchTab('outlet-stocks')" id="tab-outlet-stocks" style="padding: 10px 20px; font-weight: 600; border: none; background: none; color: var(--txt3); cursor: pointer; border-bottom: 2px solid transparent; font-size: 14px; white-space: nowrap;">
        Outlet Stocks
      </button>
    </div>
  </div>

  <!-- Tab 1: Raw Materials -->
  <div class="tab-content" id="content-raw-materials">
    <div class="card">
      <div class="ch">
        <div class="ch-title">Raw Materials Opening Stock</div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="tbl">
          <thead>
            <tr>
              <th style="width: 15%;">SKU</th>
              <th style="width: 35%;">Raw Material Name</th>
              <th style="width: 15%; text-align: right;">Store Stock</th>
              <th style="width: 15%; text-align: right;">Kitchen Stock</th>
              <th style="width: 20%; text-align: right;">Cost Price / WAC (₹)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($materials as $index => $material)
            <tr>
              <td data-label="SKU" class="mono font-semibold">{{ $material->sku }}</td>
              <td data-label="Item Name">
                <div style="font-weight: 600; color: var(--txt);">{{ $material->name }}</div>
                <div style="font-size: 11px; color: var(--txt3);">Category: {{ ucfirst($material->category) }} ({{ $material->unit }})</div>
                <input type="hidden" name="materials[{{ $index }}][id]" value="{{ $material->id }}">
              </td>
              <td data-label="Store Stock">
                <input type="number" step="0.01" name="materials[{{ $index }}][current_stock]" class="form-input text-right mono" value="{{ old('materials.'.$index.'.current_stock', number_format($material->current_stock, 2, '.', '')) }}" required style="width: 120px; float: right;">
              </td>
              <td data-label="Kitchen Stock">
                <input type="number" step="0.01" name="materials[{{ $index }}][kitchen_stock]" class="form-input text-right mono" value="{{ old('materials.'.$index.'.kitchen_stock', number_format($material->kitchen_stock, 2, '.', '')) }}" required style="width: 120px; float: right;">
              </td>
              <td data-label="Cost / WAC">
                <input type="number" step="0.01" name="materials[{{ $index }}][cost_price]" class="form-input text-right mono" value="{{ old('materials.'.$index.'.cost_price', number_format($material->cost_price, 2, '.', '')) }}" required style="width: 140px; float: right;" placeholder="0.00">
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Tab 2: Finished Products -->
  <div class="tab-content" id="content-finished-products" style="display: none;">
    <div class="card">
      <div class="ch">
        <div class="ch-title">Finished Products opening Stock (Central Kitchen)</div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="tbl">
          <thead>
            <tr>
              <th style="width: 20%;">SKU</th>
              <th style="width: 40%;">Product Name</th>
              <th style="width: 20%; text-align: right;">Kitchen Stock (Units)</th>
              <th style="width: 20%; text-align: right;">Cost Price (₹)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($products as $index => $product)
            <tr>
              <td data-label="SKU" class="mono font-semibold">{{ $product->sku }}</td>
              <td data-label="Product">
                <div style="font-weight: 600; color: var(--txt);">{{ $product->name }}</div>
                <input type="hidden" name="products[{{ $index }}][id]" value="{{ $product->id }}">
              </td>
              <td data-label="Kitchen Stock">
                <input type="number" step="0.01" name="products[{{ $index }}][current_kitchen_stock]" class="form-input text-right mono" value="{{ old('products.'.$index.'.current_kitchen_stock', number_format($product->current_kitchen_stock, 2, '.', '')) }}" required style="width: 140px; float: right;">
              </td>
              <td data-label="Cost Price">
                <input type="number" step="0.01" name="products[{{ $index }}][cost_price]" class="form-input text-right mono" value="{{ old('products.'.$index.'.cost_price', number_format($product->cost_price, 2, '.', '')) }}" required style="width: 140px; float: right;" placeholder="0.00">
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Tab 3: Outlet Stocks -->
  <div class="tab-content" id="content-outlet-stocks" style="display: none;">
    @php
      $outletStockIndex = 0;
    @endphp
    @foreach($outlets as $outlet)
    <div class="card" style="margin-bottom: 20px;">
      <div class="ch">
        <div class="ch-title">{{ $outlet->name }} Opening Stock <span class="badge {{ $outlet->type === 'own' ? 'bg' : 'ba' }}" style="margin-left: 8px;">{{ ucfirst($outlet->type) }}</span></div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="tbl">
          <thead>
            <tr>
              <th style="width: 15%;">SKU</th>
              <th style="width: 40%;">Item Name (Product / Recipe)</th>
              <th style="width: 15%; text-align: right;">Store Qty</th>
              <th style="width: 15%; text-align: right;">Kitchen Qty</th>
              <th style="width: 15%; text-align: right;">Showcase Qty</th>
            </tr>
          </thead>
          <tbody>
            <!-- Assigned Products -->
            @foreach($outlet->assignedProducts as $prod)
              @php
                $existing = $outletStocks->get($outlet->id)?->where('product_id', $prod->id)->first();
                $storeQty = $existing ? $existing->store_quantity : 0;
                $kitchenQty = $existing ? $existing->kitchen_quantity : 0;
                $showcaseQty = $existing ? $existing->showcase_quantity : 0;
              @endphp
              <tr>
                <td data-label="SKU" class="mono">{{ $prod->sku }}</td>
                <td data-label="Item" style="font-weight: 600;">
                  {{ $prod->name }} <span style="font-size: 11px; font-weight: normal; color: var(--purple-tx);"> (Product)</span>
                  <input type="hidden" name="outlet_stocks[{{ $outletStockIndex }}][outlet_id]" value="{{ $outlet->id }}">
                  <input type="hidden" name="outlet_stocks[{{ $outletStockIndex }}][product_id]" value="{{ $prod->id }}">
                  <input type="hidden" name="outlet_stocks[{{ $outletStockIndex }}][outlet_catalog_item_id]" value="">
                </td>
                <td data-label="Store Qty">
                  <input type="number" step="0.01" name="outlet_stocks[{{ $outletStockIndex }}][store_quantity]" class="form-input text-right mono" value="{{ $storeQty }}" required style="width: 100px; float: right;">
                </td>
                <td data-label="Kitchen Qty">
                  <input type="number" step="0.01" name="outlet_stocks[{{ $outletStockIndex }}][kitchen_quantity]" class="form-input text-right mono" value="{{ $kitchenQty }}" required style="width: 100px; float: right;">
                </td>
                <td data-label="Showcase Qty">
                  <input type="number" step="0.01" name="outlet_stocks[{{ $outletStockIndex }}][showcase_quantity]" class="form-input text-right mono" value="{{ $showcaseQty }}" required style="width: 100px; float: right;">
                </td>
              </tr>
              @php $outletStockIndex++; @endphp
            @endforeach

            <!-- Assigned Catalog Items -->
            @foreach($outlet->assignedCatalogItems as $catItem)
              @php
                $existing = $outletStocks->get($outlet->id)?->where('outlet_catalog_item_id', $catItem->id)->first();
                $storeQty = $existing ? $existing->store_quantity : 0;
                $kitchenQty = $existing ? $existing->kitchen_quantity : 0;
                $showcaseQty = $existing ? $existing->showcase_quantity : 0;
              @endphp
              <tr>
                <td data-label="SKU" class="mono">{{ $catItem->sku }}</td>
                <td data-label="Item" style="font-weight: 600;">
                  {{ $catItem->name }} <span style="font-size: 11px; font-weight: normal; color: var(--btn);"> (Recipe)</span>
                  <input type="hidden" name="outlet_stocks[{{ $outletStockIndex }}][outlet_id]" value="{{ $outlet->id }}">
                  <input type="hidden" name="outlet_stocks[{{ $outletStockIndex }}][product_id]" value="">
                  <input type="hidden" name="outlet_stocks[{{ $outletStockIndex }}][outlet_catalog_item_id]" value="{{ $catItem->id }}">
                </td>
                <td data-label="Store Qty">
                  <input type="number" step="0.01" name="outlet_stocks[{{ $outletStockIndex }}][store_quantity]" class="form-input text-right mono" value="{{ $storeQty }}" required style="width: 100px; float: right;">
                </td>
                <td data-label="Kitchen Qty">
                  <input type="number" step="0.01" name="outlet_stocks[{{ $outletStockIndex }}][kitchen_quantity]" class="form-input text-right mono" value="{{ $kitchenQty }}" required style="width: 100px; float: right;">
                </td>
                <td data-label="Showcase Qty">
                  <input type="number" step="0.01" name="outlet_stocks[{{ $outletStockIndex }}][showcase_quantity]" class="form-input text-right mono" value="{{ $showcaseQty }}" required style="width: 100px; float: right;">
                </td>
              </tr>
              @php $outletStockIndex++; @endphp
            @endforeach

            @if($outlet->assignedProducts->isEmpty() && $outlet->assignedCatalogItems->isEmpty())
              <tr>
                <td colspan="5" class="text-center td2" style="padding: 14px;">No products or catalog recipes assigned to this outlet yet.</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
    @endforeach
  </div>

  <!-- Submit Buttons -->
  <div class="init-actions" style="display: flex; gap: 12px; margin-top: 25px; justify-content: flex-end;">
    <a href="{{ route('dashboard') }}" class="btn-ghost" style="padding: 10px 20px;">Cancel</a>
    <button type="submit" class="btn-pri" style="padding: 10px 30px; font-weight: 600;">
      Initialize opening Inventory & Balances
    </button>
  </div>
</form>

<style>
.tab-btn {
  transition: all 0.2s ease;
}
.tab-btn.active {
  color: var(--btn) !important;
  border-bottom-color: var(--btn) !important;
}

@media (max-width: 767px) {
  /* Hide standard headers */
  .tbl thead {
    display: none;
  }
  /* Stacking table items */
  .tbl, .tbl tbody, .tbl tr, .tbl td {
    display: block;
    width: 100% !important;
    box-sizing: border-box;
  }
  .tbl tr {
    margin-bottom: 20px;
    padding: 16px;
    border: 1px solid var(--div);
    border-radius: 12px;
    background: var(--bg2);
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
  }
  .tbl td {
    padding: 10px 0;
    border-bottom: 1px solid var(--div) !important;
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-align: left !important;
  }
  .tbl td:last-child {
    border-bottom: none !important;
  }
  /* Prefix content with corresponding column label */
  .tbl td::before {
    content: attr(data-label);
    font-weight: 600;
    font-size: 13px;
    color: var(--txt3);
    margin-right: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  /* Style the form inputs for responsive cards */
  .tbl td input.form-input {
    width: 60% !important;
    max-width: 200px;
    text-align: right !important;
    float: none !important;
    margin: 0 !important;
    height: 38px;
    padding: 6px 10px;
  }
  /* Make item name stand out on top of its card group */
  .tbl td[data-label="Item Name"],
  .tbl td[data-label="Product"],
  .tbl td[data-label="Item"] {
    flex-direction: column;
    align-items: flex-start;
    border-bottom: 2px solid var(--div) !important;
    padding-bottom: 12px;
  }
  .tbl td[data-label="Item Name"]::before,
  .tbl td[data-label="Product"]::before,
  .tbl td[data-label="Item"]::before {
    margin-bottom: 6px;
  }
  /* Bottom button layout stacking for mobile screens */
  .init-actions {
    flex-direction: column-reverse !important;
    gap: 12px !important;
    margin-top: 20px !important;
  }
  .init-actions a,
  .init-actions button {
    width: 100% !important;
    text-align: center;
    justify-content: center;
    padding: 12px !important;
    font-size: 14px !important;
  }
}
</style>

<script>
function switchTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.style.display = 'none';
    });
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab content
    document.getElementById('content-' + tabId).style.display = 'block';
    // Add active class to selected button
    document.getElementById('tab-' + tabId).classList.add('active');
}
</script>
@endsection
