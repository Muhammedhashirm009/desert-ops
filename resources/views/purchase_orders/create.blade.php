@extends('layouts.app')

@section('title', 'Generate PO — DessertOps')
@section('breadcrumb', 'Generate Purchase Order')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Generate Purchase Order</div>
    <div class="ph-sub">Order ingredients or packaging materials from suppliers</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('purchase-orders.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<form action="{{ route('purchase-orders.store') }}" method="POST" id="po-form">
  @csrf

  <div class="row r-3-1" style="grid-template-columns: 1fr 300px; gap: 16px;">
    <!-- Main Form Area (Materials Selection) -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Ordered Materials & Quantity</div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="po-table" id="items-table">
          <thead>
            <tr>
              <th style="width: 45%;">Material / SKU *</th>
              <th style="width: 20%;">Quantity *</th>
              <th style="width: 20%;">Unit Price (₹) *</th>
              <th style="width: 15%;">Subtotal (₹)</th>
              <th style="width: 50px;"></th>
            </tr>
          </thead>
          <tbody id="items-container">
            <!-- Dynamic rows will be inserted here -->
          </tbody>
        </table>
        
        <div style="padding: 16px; border-top: 1px solid var(--div); display: flex; justify-content: space-between; align-items: center;">
          <button type="button" class="btn-ghost" id="add-row-btn" style="padding: 6px 12px; font-size: 12.5px;">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 12px; height: 12px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Material Row
          </button>
          
          <div style="font-size: 14px; font-weight: 700; color: var(--txt);">
            Grand Total: <span style="font-family: 'JetBrains Mono', monospace;" id="grand-total">₹0.00</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Metadata Panel -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="ch">
          <div class="ch-title">PO Meta & Settings</div>
        </div>
        <div class="cb">
          <div class="form-grp">
            <label for="supplier_id">Select Supplier *</label>
            <select name="supplier_id" id="supplier_id" class="form-input searchable-select" required style="height: 38px;">
              <option value="">-- Choose Supplier --</option>
              @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                  {{ $supplier->name }}
                </option>
              @endforeach
            </select>
            @error('supplier_id')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp">
            <label for="eta">Estimated Delivery (ETA)</label>
            <input type="date" name="eta" id="eta" class="form-input" min="{{ date('Y-m-d') }}" value="{{ old('eta') }}">
            @error('eta')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp">
            <label for="notes">Internal PO Notes</label>
            <textarea name="notes" id="notes" class="form-input" rows="4" placeholder="Terms, shipping method, packaging instructions...">{{ old('notes') }}</textarea>
            @error('notes')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="mt-4">
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px;">
              Save & Generate PO
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('items-container');
    const addRowBtn = document.getElementById('add-row-btn');
    const grandTotalEl = document.getElementById('grand-total');
    
    // Array of materials to populate option lists
    const materials = @json($materials);
    
    let rowIndex = 0;
    
    function createRow() {
        const row = document.createElement('tr');
        row.id = `row-${rowIndex}`;
        
        let materialOptions = '<option value="">-- Select Material --</option>';
        materials.forEach(m => {
            materialOptions += `<option value="${m.id}" data-unit="${m.unit}">${m.name} (${m.sku})</option>`;
        });
        
        row.innerHTML = `
            <td>
              <select name="items[${rowIndex}][material_id]" class="form-input material-select" required style="height: 36px;">
                ${materialOptions}
              </select>
            </td>
            <td>
              <div style="display: flex; align-items: center; gap: 6px;">
                <input type="number" step="0.01" name="items[${rowIndex}][quantity]" class="form-input qty-input" required min="0.01" value="1.00" style="padding: 6px 8px;">
                <span class="unit-lbl text-xs text-slate-400" style="color: var(--txt3); font-size: 11.5px; min-width: 28px;">unit</span>
              </div>
            </td>
            <td>
              <input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="form-input price-input" required min="0.00" value="0.00" style="padding: 6px 8px;">
            </td>
            <td class="mono font-semibold text-sm subtotal-lbl" style="padding: 10px 12px;">
              ₹0.00
            </td>
            <td class="text-center">
              <button type="button" class="po-row-btn remove-row-btn" style="padding: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 14px; height: 14px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </td>
        `;
        
        itemsContainer.appendChild(row);
        
        const select = row.querySelector('.material-select');
        window.initSearchableSelect(select);
        const qty = row.querySelector('.qty-input');
        const price = row.querySelector('.price-input');
        const removeBtn = row.querySelector('.remove-row-btn');
        const unitLbl = row.querySelector('.unit-lbl');
        
        // Listeners for updates
        select.addEventListener('change', function() {
            const selectedOpt = select.options[select.selectedIndex];
            const unit = selectedOpt.dataset.unit || 'unit';
            unitLbl.textContent = unit;
        });
        
        qty.addEventListener('input', calculateSubtotal);
        price.addEventListener('input', calculateSubtotal);
        
        removeBtn.addEventListener('click', function() {
            row.remove();
            calculateGrandTotal();
        });
        
        rowIndex++;
    }
    
    function calculateSubtotal(e) {
        const row = e.target.closest('tr');
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const subtotal = qty * price;
        
        row.querySelector('.subtotal-lbl').textContent = `₹${subtotal.toFixed(2)}`;
        calculateGrandTotal();
    }
    
    function calculateGrandTotal() {
        let total = 0;
        document.querySelectorAll('#items-container tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            total += qty * price;
        });
        
        grandTotalEl.textContent = `₹${total.toFixed(2)}`;
    }
    
    // Add initial row
    createRow();
    
    // Add row button handler
    addRowBtn.addEventListener('click', function() {
        createRow();
    });
    
    // Prevent empty items list on submit
    document.getElementById('po-form').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('#items-container tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Please add at least one material item to the Purchase Order.');
        }
    });
});
</script>
@endsection
