@extends('layouts.app')

@section('title', 'Edit Material — DessertOps')
@section('breadcrumb', 'Modify Material')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Edit Material: {{ $material->name }}</div>
    <div class="ph-sub">Update registered details and stock parameters</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('materials.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
  <div class="ch">
    <div class="ch-title">Material Properties</div>
  </div>
  <div class="cb">
    <form action="{{ route('materials.update', $material->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="grid-2">
        <div class="form-grp">
          <label for="name">Material Name *</label>
          <input type="text" name="name" id="name" class="form-input" value="{{ old('name', $material->name) }}" required placeholder="e.g. White Sugar">
          @error('name')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp">
          <label for="sku">SKU Code *</label>
          <input type="text" name="sku" id="sku" class="form-input" value="{{ old('sku', $material->sku) }}" required placeholder="e.g. RAW-SUG-001">
          @error('sku')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="grid-2">
        <div class="form-grp">
          <label for="category">Category *</label>
          <select name="category" id="category" class="form-input" required style="height: 38px;">
            <option value="ingredient" {{ old('category', $material->category) === 'ingredient' ? 'selected' : '' }}>Ingredient</option>
            <option value="packaging" {{ old('category', $material->category) === 'packaging' ? 'selected' : '' }}>Packaging (Wrapper/Box)</option>
          </select>
          @error('category')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp">
          <label for="unit">Stock Unit *</label>
          <input type="text" name="unit" id="unit" class="form-input" value="{{ old('unit', $material->unit) }}" required placeholder="e.g. kg, L, pcs, box">
          @error('unit')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="grid-2" id="per-box-qty-row" style="display: none;">
        <div class="form-grp">
          <label for="per_box_qty">Per Box Qty (Pieces per Box) *</label>
          <input type="number" step="1" name="per_box_qty" id="per_box_qty" class="form-input" value="{{ old('per_box_qty', $material->per_box_qty) }}" placeholder="e.g. 100">
          @error('per_box_qty')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
        <div class="form-grp">
          <label for="retail_price">Retail Price (₹ per piece)</label>
          <input type="number" step="0.01" name="retail_price" id="retail_price" class="form-input" value="{{ old('retail_price', $material->retail_price) }}" placeholder="e.g. 5.00" min="0">
          @error('retail_price')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="form-grp">
        <label for="cost_price">Cost Price / WAC (₹)</label>
        <input type="number" step="0.01" name="cost_price" id="cost_price" class="form-input" value="{{ old('cost_price', $material->cost_price ?? 0) }}" min="0" placeholder="0.00">
        <span style="font-size: 11px; color: var(--txt3);">Weighted average cost — auto-updated when GRN is received</span>
        @error('cost_price')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="grid-2">
        <div class="form-grp">
          <label for="current_stock">Current Stock Level *</label>
          <input type="number" step="0.01" name="current_stock" id="current_stock" class="form-input" value="{{ old('current_stock', $material->current_stock) }}" required>
          @error('current_stock')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp">
          <label for="min_stock_alert">Minimum Alert Level *</label>
          <input type="number" step="0.01" name="min_stock_alert" id="min_stock_alert" class="form-input" value="{{ old('min_stock_alert', $material->min_stock_alert) }}" required>
          @error('min_stock_alert')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="mt-4" style="display: flex; justify-content: flex-end; gap: 10px;">
        <a href="{{ route('materials.index') }}" class="btn-ghost">Cancel</a>
        <button type="submit" class="btn-pri">Update Material</button>
      </div>
    </form>
  </div>
</div>

<!-- Price History Card -->
<div class="card" style="max-width: 600px; margin: 20px auto 0 auto;">
  <div class="ch">
    <div class="ch-title">WAC Price History Log</div>
  </div>
  <div class="cb" style="padding: 0;">
    @if($priceHistories && $priceHistories->count() > 0)
    <table class="tbl">
      <thead>
        <tr>
          <th>Date</th>
          <th style="text-align: right;">Old WAC</th>
          <th style="text-align: right;">New WAC</th>
          <th style="text-align: right;">Qty Recv</th>
          <th style="text-align: right;">GRN Cost</th>
          <th>GRN Ref</th>
        </tr>
      </thead>
      <tbody>
        @foreach($priceHistories as $history)
        <tr>
          <td style="font-size: 12px;">{{ $history->created_at->format('Y-m-d H:i') }}</td>
          <td class="mono" style="text-align: right;">₹{{ number_format($history->old_cost_price, 2) }}</td>
          <td class="mono font-semibold" style="text-align: right; color: var(--green-tx);">₹{{ number_format($history->new_cost_price, 2) }}</td>
          <td class="mono" style="text-align: right;">{{ number_format($history->quantity_received, 2) }}</td>
          <td class="mono" style="text-align: right;">₹{{ number_format($history->unit_cost, 2) }}</td>
          <td class="mono" style="font-size: 12px;">
            @if($history->grn)
              <a href="{{ route('grns.show', $history->grn_id) }}" style="text-decoration: underline; color: inherit;">
                {{ $history->grn->grn_number }}
              </a>
            @else
              Manual
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div style="padding: 20px; text-align: center; color: var(--txt3); font-style: italic;">
      No price history logged yet. Costs will update dynamically from GRNs.
    </div>
    @endif
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const perBoxRow = document.getElementById('per-box-qty-row');
    const perBoxInput = document.getElementById('per_box_qty');
    const stockLabel = document.querySelector('label[for="current_stock"]');
    const alertLabel = document.querySelector('label[for="min_stock_alert"]');

    function togglePerBox() {
        if (categorySelect.value === 'packaging') {
            perBoxRow.style.display = 'grid';
            perBoxInput.setAttribute('required', 'required');
            stockLabel.textContent = 'Current Stock Level (Boxes) *';
            alertLabel.textContent = 'Minimum Alert Level (Boxes) *';
        } else {
            perBoxRow.style.display = 'none';
            perBoxInput.removeAttribute('required');
            perBoxInput.value = '';
            stockLabel.textContent = 'Current Stock Level *';
            alertLabel.textContent = 'Minimum Alert Level *';
        }
    }

    if (categorySelect && perBoxRow && perBoxInput) {
        categorySelect.addEventListener('change', togglePerBox);
        togglePerBox();
    }
});
</script>
@endsection
