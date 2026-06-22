@extends('layouts.app')

@section('title', 'Create Dispatch Shipment — DessertOps')
@section('breadcrumb', 'Create Dispatch')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Create Dispatch Shipment</div>
    <div class="ph-sub">Ship prepared finished dessert boxes from Central Kitchen stock to outlets</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('dispatches.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<form action="{{ route('dispatches.store') }}" method="POST" id="dispatch-form">
  @csrf

  <div class="row r-3-1" style="grid-template-columns: 1fr 320px; gap: 16px;">
    <!-- Left Panel: Products List -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Shipment Items & Quantities</div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="po-table" id="items-table">
          <thead>
            <tr>
              <th style="width: 65%;">Dessert Product *</th>
              <th style="width: 25%;">Quantity (Units) *</th>
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
            Add Dessert Item
          </button>
        </div>
      </div>
    </div>

    <!-- Right Panel: Meta Info -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="ch">
          <div class="ch-title">Shipment Info</div>
        </div>
        <div class="cb">
          <div class="form-grp">
            <label for="outlet_id">Destination Outlet *</label>
            <select name="outlet_id" id="outlet_id" class="form-input searchable-select" required style="height: 38px;">
              <option value="">-- Choose Destination --</option>
              @foreach($outlets as $outlet)
                <option value="{{ $outlet->id }}" {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                  {{ $outlet->name }} ({{ ucfirst($outlet->type) }})
                </option>
              @endforeach
            </select>
            @error('outlet_id')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp" style="margin-top: 10px;">
            <label for="dispatch_date">Dispatch Date *</label>
            <input type="date" name="dispatch_date" id="dispatch_date" class="form-input" required 
                   value="{{ old('dispatch_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
            @error('dispatch_date')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp" style="margin-top: 10px;">
            <label for="notes">Delivery Remarks</label>
            <textarea name="notes" id="notes" class="form-input" rows="4" 
                      placeholder="e.g. Courier contact, packaging instructions, temperature control notes...">{{ old('notes') }}</textarea>
            @error('notes')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="mt-4">
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px;">
              Save & Ship Desserts
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
    
    // Array of finished dessert products
    const products = @json($products);
    
    let rowIndex = 0;

    function createRow() {
        const row = document.createElement('tr');
        row.id = `row-${rowIndex}`;
        
        let options = '<option value="">-- Choose Dessert --</option>';
        products.forEach(p => {
            options += `<option value="${p.id}">[${p.sku}] ${p.name} (Kitchen stock: ${parseInt(p.current_kitchen_stock)} units)</option>`;
        });

        row.innerHTML = `
            <td>
              <select name="items[${rowIndex}][product_id]" class="form-input product-select" required style="height: 36px;">
                ${options}
              </select>
            </td>
            <td>
              <input type="number" step="1" name="items[${rowIndex}][quantity]" class="form-input" required min="1" value="10" style="padding: 6px 8px;">
            </td>
            <td class="text-center">
              <button type="button" class="po-row-btn remove-row-btn" style="padding: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 14px; height: 14px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </td>
        `;

        container.appendChild(row);

        // Initialize searchable select
        const select = row.querySelector('.product-select');
        window.initSearchableSelect(select);

        const removeBtn = row.querySelector('.remove-row-btn');
        removeBtn.addEventListener('click', function() {
            row.remove();
        });

        rowIndex++;
    }

    // Add initial row
    createRow();
    
    addBtn.addEventListener('click', createRow);

    document.getElementById('dispatch-form').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('#items-container tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Please add at least one product item to ship.');
        }
    });
});
</script>
@endsection
