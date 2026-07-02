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
        <table class="po-table po-create-table" id="items-table">
          <thead>
            <tr>
              <th style="width: 30%;">Material / SKU *</th>
              <th style="width: 30%;">Supplier *</th>
              <th style="width: 15%;">Quantity *</th>
              <th style="width: 15%;">Unit Price (₹) *</th>
              <th style="width: 10%;">Subtotal (₹)</th>
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
          <div class="form-grp" style="display: none;">
            <label for="supplier_id">Default Supplier (Optional)</label>
            <select name="supplier_id" id="supplier_id" class="form-input searchable-select" style="height: 38px;">
              <option value="">-- Choose Default Supplier --</option>
              @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                  {{ $supplier->name }}
                </option>
              @endforeach
            </select>
            <div style="font-size: 11px; color: var(--txt3); margin-top: 4px;">
              Sets the supplier for all rows where possible. If empty, you can set separate suppliers per item.
            </div>
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
    const defaultSupplierSelect = document.getElementById('supplier_id');
    
    // Arrays of materials & suppliers from backend
    const materials = @json($materials);
    const allSuppliers = @json($suppliers);
    
    let rowIndex = 0;
    
    function createRow() {
        const row = document.createElement('tr');
        row.id = `row-${rowIndex}`;
        const currentRowIndex = rowIndex;
        
        let materialOptions = '<option value="">-- Select Material --</option>';
        materials.forEach(m => {
            materialOptions += `<option value="${m.id}" data-unit="${m.unit}">${m.name} (${m.sku})</option>`;
        });
        
        row.innerHTML = `
            <td>
              <select name="items[${currentRowIndex}][material_id]" class="form-input material-select" required style="height: 36px;">
                ${materialOptions}
              </select>
            </td>
            <td>
              <select name="items[${currentRowIndex}][supplier_id]" class="form-input supplier-select" required style="height: 36px;">
                <option value="">-- Choose Material First --</option>
              </select>
            </td>
            <td>
              <div style="display: flex; align-items: center; gap: 6px;">
                <input type="number" step="0.01" name="items[${currentRowIndex}][quantity]" class="form-input qty-input" required min="0.01" value="1.00" style="padding: 6px 8px;">
                <span class="unit-lbl text-xs text-slate-400" style="color: var(--txt3); font-size: 11.5px; min-width: 28px;">unit</span>
              </div>
            </td>
            <td>
              <input type="number" step="0.01" name="items[${currentRowIndex}][unit_price]" class="form-input price-input" required min="0.00" value="0.00" style="padding: 6px 8px;">
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
        
        const matSelect = row.querySelector('.material-select');
        const supplierSelect = row.querySelector('.supplier-select');
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const removeBtn = row.querySelector('.remove-row-btn');
        const unitLbl = row.querySelector('.unit-lbl');
        
        window.initSearchableSelect(matSelect);
        
        // Populate suppliers dropdown when material changes
        matSelect.addEventListener('change', function() {
            const materialId = matSelect.value;
            const selectedOpt = matSelect.options[matSelect.selectedIndex];
            const unit = selectedOpt.dataset.unit || 'unit';
            unitLbl.textContent = unit;
            
            populateSuppliersForRow(row, materialId);
        });
        
        supplierSelect.addEventListener('change', function() {
            const selectedOpt = supplierSelect.options[supplierSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                priceInput.value = parseFloat(selectedOpt.dataset.price || 0).toFixed(2);
                calculateRowSubtotal(row);
            }
        });
        
        qtyInput.addEventListener('input', () => calculateRowSubtotal(row));
        priceInput.addEventListener('input', () => calculateRowSubtotal(row));
        
        removeBtn.addEventListener('click', function() {
            row.remove();
            calculateGrandTotal();
        });
        
        rowIndex++;
    }
    
    function populateSuppliersForRow(row, materialId) {
        const supplierSelect = row.querySelector('.supplier-select');
        const priceInput = row.querySelector('.price-input');
        
        supplierSelect.innerHTML = '';
        
        if (!materialId) {
            supplierSelect.innerHTML = '<option value="">-- Choose Material First --</option>';
            priceInput.value = '0.00';
            calculateRowSubtotal(row);
            return;
        }
        
        const material = materials.find(m => m.id == materialId);
        if (!material) return;
        
        let preferredSupplierId = null;
        let preferredPrice = 0.00;
        
        // Add linked suppliers first
        if (material.suppliers && material.suppliers.length > 0) {
            material.suppliers.forEach(sup => {
                const isPref = sup.pivot.is_preferred ? ' (Preferred)' : '';
                const optionText = `${sup.name}${isPref} - ₹${parseFloat(sup.pivot.unit_price).toFixed(2)}`;
                const option = document.createElement('option');
                option.value = sup.id;
                option.textContent = optionText;
                option.dataset.price = sup.pivot.unit_price;
                option.dataset.linked = 'true';
                
                if (sup.pivot.is_preferred) {
                    preferredSupplierId = sup.id;
                    preferredPrice = sup.pivot.unit_price;
                    option.selected = true;
                }
                supplierSelect.appendChild(option);
            });
            
            // Default to first if none preferred
            if (!preferredSupplierId && material.suppliers.length > 0) {
                preferredSupplierId = material.suppliers[0].id;
                preferredPrice = material.suppliers[0].pivot.unit_price;
                supplierSelect.options[0].selected = true;
            }
        }
        
        // Add divider if there are linked suppliers
        if (material.suppliers && material.suppliers.length > 0) {
            const separator = document.createElement('option');
            separator.disabled = true;
            separator.textContent = '------------------';
            supplierSelect.appendChild(separator);
        }
        
        // Add all remaining suppliers as fallbacks
        allSuppliers.forEach(sup => {
            const isAlreadyLinked = material.suppliers && material.suppliers.some(s => s.id == sup.id);
            if (isAlreadyLinked) return;
            
            const option = document.createElement('option');
            option.value = sup.id;
            option.textContent = `${sup.name} (Unlinked)`;
            option.dataset.price = '0.00';
            option.dataset.linked = 'false';
            supplierSelect.appendChild(option);
        });
        
        // If a default supplier is selected in the sidebar, let's try to match it
        const defaultSupplierId = defaultSupplierSelect.value;
        if (defaultSupplierId) {
            for (let i = 0; i < supplierSelect.options.length; i++) {
                if (supplierSelect.options[i].value == defaultSupplierId) {
                    supplierSelect.selectedIndex = i;
                    break;
                }
            }
        }
        
        // Set unit price from selected option
        const selectedOpt = supplierSelect.options[supplierSelect.selectedIndex];
        if (selectedOpt) {
            priceInput.value = parseFloat(selectedOpt.dataset.price || 0).toFixed(2);
        }
        
        calculateRowSubtotal(row);
    }
    
    function calculateRowSubtotal(row) {
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
    
    // Sync default supplier selection to all rows
    defaultSupplierSelect.addEventListener('change', function() {
        const defaultSupplierId = defaultSupplierSelect.value;
        if (!defaultSupplierId) return;
        
        document.querySelectorAll('#items-container tr').forEach(row => {
            const supplierSelect = row.querySelector('.supplier-select');
            const priceInput = row.querySelector('.price-input');
            
            let found = false;
            for (let i = 0; i < supplierSelect.options.length; i++) {
                if (supplierSelect.options[i].value == defaultSupplierId) {
                    supplierSelect.selectedIndex = i;
                    priceInput.value = parseFloat(supplierSelect.options[i].dataset.price || 0).toFixed(2);
                    found = true;
                    break;
                }
            }
            
            if (found) {
                calculateRowSubtotal(row);
            }
        });
    });
    
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
            return;
        }
        
        // Validate each row has a supplier selected
        let missingSupplier = false;
        rows.forEach(row => {
            const supplierSelect = row.querySelector('.supplier-select');
            if (!supplierSelect.value) {
                missingSupplier = true;
            }
        });
        
        if (missingSupplier) {
            e.preventDefault();
            alert('Please ensure you select a supplier for each item.');
        }
    });
});
</script>
@endsection
