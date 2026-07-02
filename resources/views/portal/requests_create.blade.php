@extends('layouts.portal')

@section('title', 'Request Products — DessertOps Portal')
@section('breadcrumb', 'Request Products')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Request Dessert Products</div>
    <div class="ph-sub">Submit a new request for desserts from the Central Kitchen to <b>{{ $outlet->name }}</b></div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('portal.dispatches') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<form action="{{ route('portal.requests.store') }}" method="POST" id="request-form">
  @csrf

  <div class="row r-3-1" style="grid-template-columns: 1fr 320px; gap: 16px;">
    <!-- Left Panel: Products Requested -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Requested Items & Quantities</div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="po-table" id="items-table">
          <thead>
            <tr>
              <th style="width: 70%;">Requested Item (Dessert / Packaging) *</th>
              <th style="width: 20%;">Quantity *</th>
              <th style="width: 10%;"></th>
            </tr>
          </thead>
          <tbody id="items-container">
            <!-- Dynamic rows inserted here -->
          </tbody>
        </table>

        <div style="padding: 16px; border-top: 1px solid var(--div);">
          <button type="button" class="btn-ghost" id="add-row-btn" style="padding: 6px 12px; font-size: 12.5px;">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 12px; height: 12px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Item Row
          </button>
        </div>
      </div>
    </div>

    <!-- Right Panel: Meta Info & Notes -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="ch">
          <div class="ch-title">Request Summary</div>
        </div>
        <div class="cb">
          <div class="form-grp">
            <label style="opacity: 0.8; font-size: 12px;">Active Outlet</label>
            <div style="font-weight: 700; font-size: 15px; color: var(--txt); margin-top: 2px;">
              {{ $outlet->name }}
            </div>
            <span class="badge {{ $outlet->type === 'own' ? 'bg' : 'bp' }}" style="font-size: 10px; margin-top: 4px;">
              {{ $outlet->type === 'own' ? 'Company Owned' : 'Franchise' }}
            </span>
          </div>

          <div class="form-grp" style="margin-top: 16px;">
            <label for="notes">Request Notes / Comments</label>
            <textarea name="notes" id="notes" class="form-input" rows="4" placeholder="Any specific instructions for this order..." style="font-size: 13px;"></textarea>
            @error('notes')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="mt-4" style="margin-top: 20px;">
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px; font-weight: 600;">
              Submit Product Request
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
    const container = document.getElementById('items-container');
    const addBtn = document.getElementById('add-row-btn');
    
    const products = @json($products);
    const packagingMaterials = @json($packagingMaterials);
    let rowIndex = 0;

    function createRow() {
        const row = document.createElement('tr');
        row.id = `row-${rowIndex}`;
        
        let options = '<option value="">-- Choose Item --</option>';
        options += '<optgroup label="Dessert Products">';
        products.forEach(p => {
            options += `<option value="product:${p.id}">[${p.sku}] ${p.name} (Kitchen Stock: ${parseInt(p.current_kitchen_stock)})</option>`;
        });
        options += '</optgroup>';
        options += '<optgroup label="Packaging Materials (Request by Piece)">';
        packagingMaterials.forEach(m => {
            const totalPieces = parseInt(m.kitchen_stock) * (parseInt(m.per_box_qty) || 1);
            options += `<option value="material:${m.id}">[${m.sku}] ${m.name} (Kitchen Stock: ${totalPieces} pcs, Per Box: ${m.per_box_qty} pcs)</option>`;
        });
        options += '</optgroup>';

        row.innerHTML = `
            <td>
              <select name="items[${rowIndex}][item_id]" class="form-input product-select searchable-select" required style="height: 36px;">
                ${options}
              </select>
            </td>
            <td>
              <div style="display: flex; align-items: center; gap: 6px;">
                <input type="number" step="1" name="items[${rowIndex}][quantity]" class="form-input qty-input" required min="1" value="1" style="padding: 6px 8px;">
                <span class="qty-unit-label" style="font-size:12px; color:var(--txt3);">Units</span>
              </div>
            </td>
            <td class="text-center">
              <button type="button" class="po-row-btn remove-row-btn" style="padding: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 14px; height: 14px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </td>
        `;

        container.appendChild(row);

        const select = row.querySelector('.product-select');
        window.initSearchableSelect(select);

        // Dynamic units label based on selection
        select.addEventListener('change', function() {
            const val = this.value;
            const label = row.querySelector('.qty-unit-label');
            if (val.startsWith('material:')) {
                label.textContent = 'Pieces';
            } else {
                label.textContent = 'Units';
            }
        });

        const removeBtn = row.querySelector('.remove-row-btn');
        removeBtn.addEventListener('click', function() {
            row.remove();
        });

        rowIndex++;
    }

    // Add initial row
    createRow();
    
    addBtn.addEventListener('click', createRow);

    document.getElementById('request-form').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('#items-container tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the request.');
            return;
        }

        let hasErrors = false;
        const selectedItems = new Set();

        rows.forEach(row => {
            const select = row.querySelector('.product-select');
            const itemId = select.value;

            if (!itemId) {
                alert('Please choose an item for all rows.');
                hasErrors = true;
                return;
            }

            if (selectedItems.has(itemId)) {
                alert('Duplicate items detected. Please consolidate duplicate items into a single row.');
                hasErrors = true;
                return;
            }
            selectedItems.add(itemId);
        });

        if (hasErrors) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
