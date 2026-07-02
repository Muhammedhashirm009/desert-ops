@extends('layouts.app')

@section('title', 'Outlet Details — DessertOps')
@section('breadcrumb', 'Outlet Details')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">{{ $outlet->name }}</div>
    <div class="ph-sub">
      Type: @if($outlet->type === 'own') Own Outlet @else Franchise ({{ number_format($outlet->commission_rate, 1) }}% commission) @endif
      <span class="ph-sub-dot"></span>
      Manager: {{ $outlet->contact_person ?? 'N/A' }}
    </div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('outlets.edit', $outlet->id) }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit Profile
    </a>
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 340px; gap: 16px;">
  <!-- Left Column -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    <!-- Current Stock -->
    <div class="card">
      <div class="ch">
        <div class="ch-ic" style="background:var(--div);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
        </div>
        <div class="ch-title">Current Dessert Stock at Location</div>
      </div>
      <table class="tbl">
        <thead>
          <tr>
            <th>SKU</th>
            <th>Dessert Product</th>
            <th style="text-align: right;">Store Stock</th>
            <th style="text-align: right;">Kitchen Stock</th>
            <th style="text-align: right;">Showcase Stock</th>
            <th style="text-align: right;">Total Stock</th>
            <th style="text-align: right;">Unit Price (₹)</th>
          </tr>
        </thead>
        <tbody>
          @forelse($outlet->stocks as $stock)
          <tr>
            <td data-label="SKU" class="mono">{{ $stock->product ? $stock->product->sku : ($stock->material ? $stock->material->sku : 'N/A') }}</td>
            <td data-label="Product" style="font-weight: 600;">{{ $stock->product ? $stock->product->name : ($stock->material ? $stock->material->name : 'Unknown') }}</td>
            <td data-label="Store Stock" class="mono" style="text-align: right;">
              {{ number_format($stock->store_quantity, 0) }}
            </td>
            <td data-label="Kitchen Stock" class="mono" style="text-align: right;">
              {{ number_format($stock->kitchen_quantity, 0) }}
            </td>
            <td data-label="Showcase Stock" class="mono" style="text-align: right;">
              {{ number_format($stock->showcase_quantity, 0) }}
            </td>
            <td data-label="Total Stock" class="mono font-semibold" style="text-align: right; color: var(--green-tx); font-weight: 600;">
              {{ number_format($stock->quantity, 0) }} {{ $stock->product ? 'Units' : 'Pcs' }}
            </td>
            <td data-label="Price" class="mono" style="text-align: right;">
              @if($stock->product)
                ₹{{ number_format($stock->product->retail_price, 2) }}
              @elseif($stock->material && $stock->material->retail_price)
                ₹{{ number_format($stock->material->retail_price, 2) }}
              @else
                —
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center td2" style="padding: 30px 10px;">
              No stock dispatched to this location yet. 
              <div style="margin-top:10px;">
                <a href="{{ route('dispatches.create', ['outlet_id' => $outlet->id]) }}" class="btn-pri" style="display:inline-flex;">
                  Ship Desserts
                </a>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Product & Material Assignment -->
    <div class="card">
      <div class="ch">
        <div class="ch-ic" style="background:var(--green-bg);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--green-tx)"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div class="ch-title">Product & Packaging Assignment</div>
      </div>
      <div class="cb">
        <form action="{{ route('outlets.update-assignments', $outlet->id) }}" method="POST">
          @csrf

          <div style="margin-bottom: 20px;">
            <div style="font-weight: 600; font-size: 13px; color: var(--txt); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px; height:14px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
              Dessert Products
            </div>
            <div style="font-size: 12px; color: var(--txt3); margin-bottom: 10px;">
              Select which dessert products this outlet can receive via dispatch. Only checked products will appear in dispatch creation.
            </div>
            <div class="omd-assign-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 8px;">
              @foreach($allProducts as $product)
              <label class="omd-assign-item" style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer; transition: all 0.15s ease; font-size: 13px;"
                     onmouseover="this.style.borderColor='var(--btn)'; this.style.background='var(--bg2)'"
                     onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='var(--border)'; this.style.background=''}">
                <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                       {{ $outlet->assignedProducts->contains('id', $product->id) ? 'checked' : '' }}
                       style="width: 16px; height: 16px; accent-color: var(--btn);"
                       onchange="var l=this.closest('.omd-assign-item'); if(this.checked){l.style.borderColor='var(--btn)';l.style.background='var(--bg2)'}else{l.style.borderColor='var(--border)';l.style.background=''}">
                <div style="flex:1; min-width:0;">
                  <div style="font-weight: 600; color: var(--txt); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $product->name }}</div>
                  <div style="font-size: 11px; color: var(--txt3); font-family: 'JetBrains Mono', monospace;">{{ $product->sku }} · ₹{{ number_format($product->retail_price, 2) }}</div>
                </div>
              </label>
              @endforeach
            </div>
            @if($allProducts->isEmpty())
            <div style="text-align:center; padding: 16px; color:var(--txt3); font-size:12px;">No products created yet.</div>
            @endif
          </div>

          @if($allPackagingMaterials->isNotEmpty())
          <div style="border-top: 1px solid var(--div); padding-top: 16px;">
            <div style="font-weight: 600; font-size: 13px; color: var(--txt); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px; height:14px;"><path d="M12.89 1.45l8 4A2 2 0 0 1 22 7.24v9.53a2 2 0 0 1-1.11 1.79l-8 4a2 2 0 0 1-1.79 0l-8-4a2 2 0 0 1-1.1-1.8V7.24a2 2 0 0 1 1.11-1.79l8-4a2 2 0 0 1 1.78 0z"/><polyline points="2.32 6.16 12 11 21.68 6.16"/><line x1="12" y1="22.76" x2="12" y2="11"/></svg>
              Packaging Materials
            </div>
            <div style="font-size: 12px; color: var(--txt3); margin-bottom: 10px;">
              Select which packaging items can be dispatched to this outlet.
            </div>
            <div class="omd-assign-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 8px;">
              @foreach($allPackagingMaterials as $material)
              <label class="omd-assign-item" style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; cursor: pointer; transition: all 0.15s ease; font-size: 13px;"
                     onmouseover="this.style.borderColor='var(--btn)'; this.style.background='var(--bg2)'"
                     onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='var(--border)'; this.style.background=''}">
                <input type="checkbox" name="material_ids[]" value="{{ $material->id }}"
                       {{ $outlet->assignedMaterials->contains('id', $material->id) ? 'checked' : '' }}
                       style="width: 16px; height: 16px; accent-color: var(--btn);"
                       onchange="var l=this.closest('.omd-assign-item'); if(this.checked){l.style.borderColor='var(--btn)';l.style.background='var(--bg2)'}else{l.style.borderColor='var(--border)';l.style.background=''}">
                <div style="flex:1; min-width:0;">
                  <div style="font-weight: 600; color: var(--txt); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $material->name }}</div>
                  <div style="font-size: 11px; color: var(--txt3); font-family: 'JetBrains Mono', monospace;">{{ $material->sku }}@if($material->retail_price) · ₹{{ number_format($material->retail_price, 2) }}@endif</div>
                </div>
              </label>
              @endforeach
            </div>
          </div>
          @endif

          <div style="margin-top: 16px; display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid var(--div);">
            <div style="flex:1; font-size: 12px; color: var(--txt3); display: flex; align-items: center; gap: 6px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px; height:14px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
              Checked items will be available for dispatch and sales at this outlet.
            </div>
            <button type="submit" class="btn-pri" style="padding: 8px 20px;">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px; height:14px;"><polyline points="20 6 9 17 4 12"/></svg>
              Save Assignments
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Right: Metadata & History Panels -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    <!-- Outlet Contact Info Card -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Store Information</div>
      </div>
      <div class="cb" style="font-size: 13px;">
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Type</div>
          <div style="font-weight:600; color:var(--txt);">
            @if($outlet->type === 'own') Company-Owned Retailer @else Franchise Partner @endif
          </div>
        </div>
        @if($outlet->type === 'franchise')
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Franchise Commission</div>
          <div style="font-weight:600; color:var(--purple-tx);">{{ number_format($outlet->commission_rate, 1) }}% per Sale</div>
        </div>
        @endif
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Contact Person</div>
          <div style="color:var(--txt2);">{{ $outlet->contact_person ?? 'None specified' }}</div>
        </div>
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Phone</div>
          <div style="color:var(--txt2); font-family: 'JetBrains Mono', monospace;">{{ $outlet->phone ?? 'None specified' }}</div>
        </div>
        <div>
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Location Address</div>
          <div style="color:var(--txt2); white-space: pre-line;">{{ $outlet->address ?? 'No address provided' }}</div>
        </div>
      </div>
    </div>

    <!-- Assignment Summary Card -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Assignment Summary</div>
      </div>
      <div class="cb" style="padding: 14px;">
        <div style="display: flex; gap: 12px;">
          <div style="flex:1; text-align:center; padding: 12px; background: var(--bg2); border-radius: 8px;">
            <div style="font-size: 22px; font-weight: 700; color: var(--btn);">{{ $outlet->assignedProducts->count() }}</div>
            <div style="font-size: 11px; color: var(--txt3); font-weight: 500;">Products</div>
          </div>
          <div style="flex:1; text-align:center; padding: 12px; background: var(--bg2); border-radius: 8px;">
            <div style="font-size: 22px; font-weight: 700; color: var(--purple-tx);">{{ $outlet->assignedMaterials->count() }}</div>
            <div style="font-size: 11px; color: var(--txt3); font-weight: 500;">Packaging</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Dispatches -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Recent Shipments</div>
        <a href="{{ route('dispatches.create', ['outlet_id' => $outlet->id]) }}" class="ch-link">
          Ship
        </a>
      </div>
      <div class="cb" style="padding:0;">
        <div class="act-feed">
          @forelse($outlet->dispatches as $disp)
          <div class="act-item" style="padding: 10px 14px;">
            <div style="flex:1;">
              <div class="act-ti" style="font-size:12.5px;">
                <a href="{{ route('dispatches.show', $disp->id) }}" style="color:inherit; text-decoration:none;">
                  {{ $disp->dispatch_number }}
                </a>
              </div>
              <div class="act-de" style="font-size: 11.5px;">
                Date: {{ $disp->dispatch_date->format('Y-m-d') }}
              </div>
              <div style="margin-top: 4px;">
                @if($disp->status === 'pending')
                  <span class="badge bn">Pending</span>
                @elseif($disp->status === 'dispatched')
                  <span class="badge ba">In Transit</span>
                @else
                  <span class="badge bg">Delivered</span>
                @endif
              </div>
            </div>
          </div>
          @empty
          <div style="padding: 16px; text-align:center; color:var(--txt3); font-size:12px;">No shipments logged.</div>
          @endforelse
        </div>
      </div>
    </div>

    <!-- Recent Sales Logs -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Recent Sales Reports</div>
        <a href="{{ route('sales-logs.create', ['outlet_id' => $outlet->id]) }}" class="ch-link">
          Log Sales
        </a>
      </div>
      <div class="cb" style="padding:0;">
        <div class="act-feed">
          @forelse($outlet->salesLogs as $log)
          <div class="act-item" style="padding: 10px 14px;">
            <div style="flex:1;">
              <div class="act-ti" style="font-size:12.5px;">
                <a href="{{ route('sales-logs.show', $log->id) }}" style="color:inherit; text-decoration:none;">
                  Sales Report — {{ $log->log_date->format('M d, Y') }}
                </a>
              </div>
              <div class="act-tm">Logged at {{ $log->created_at->format('M d, H:i') }}</div>
            </div>
          </div>
          @empty
          <div style="padding: 16px; text-align:center; color:var(--txt3); font-size:12px;">No sales logged.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Highlight pre-checked assignment items on page load
    document.querySelectorAll('.omd-assign-item input[type="checkbox"]:checked').forEach(function(cb) {
        var item = cb.closest('.omd-assign-item');
        item.style.borderColor = 'var(--btn)';
        item.style.background = 'var(--bg2)';
    });
});
</script>
@endsection
