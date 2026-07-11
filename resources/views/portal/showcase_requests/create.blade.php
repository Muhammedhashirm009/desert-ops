@extends('layouts.portal')

@section('title', 'New Showcase Request — DessertOps Portal')
@section('breadcrumb', 'New Showcase Request')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">New Showcase Request</div>
    <div class="ph-sub">Request dessert products to be moved or replenished in the outlet's showcase display</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('portal.showcase-requests.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<form action="{{ route('portal.showcase-requests.store') }}" method="POST" id="showcase-request-form">
  @csrf

  <div class="row r-3-1" style="grid-template-columns: 1fr 320px; gap: 16px;">
    <!-- Left Panel: Products Requested -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Requested Dessert Products</div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="po-table" id="items-table">
          <thead>
            <tr>
              <th style="width: 70%;">Dessert Product *</th>
              <th style="width: 20%;">Quantity Requested *</th>
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
            Add Product Row
          </button>
        </div>
      </div>
    </div>

    <!-- Right Panel: Meta Info & Notes -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="ch">
          <div class="ch-title">Request Details</div>
        </div>
        <div class="cb">
          <div class="form-grp">
            <label style="opacity: 0.8; font-size: 12px;">Active Outlet</label>
            <div style="font-weight: 700; font-size: 15px; color: var(--txt); margin-top: 2px;">
              {{ $outlet->name }}
            </div>
          </div>

          <div class="form-grp" style="margin-top: 16px;">
            <label for="requested_by">Requested By *</label>
            @if(session('portal_employee_name'))
              <input type="text" name="requested_by" id="requested_by" class="form-input" required value="{{ session('portal_employee_name') }}" readonly style="font-size: 13px; background: var(--bg); cursor: not-allowed;">
              <div style="font-size: 11px; color: var(--txt3); margin-top: 4px;">Auto-filled from your logged-in account</div>
            @else
              <input type="text" name="requested_by" id="requested_by" class="form-input" required value="{{ old('requested_by', $outlet->contact_person ?? '') }}" placeholder="Your Name" style="font-size: 13px;">
            @endif
            @error('requested_by')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp" style="margin-top: 16px;">
            <label for="notes">Notes / Comments</label>
            <textarea name="notes" id="notes" class="form-input" rows="4" placeholder="Any specific reasons or comments..." style="font-size: 13px;"></textarea>
            @error('notes')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="mt-4" style="margin-top: 20px;">
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px; font-weight: 600;">
              Submit Showcase Request
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
    let rowIndex = 0;

    function createRow() {
        const row = document.createElement('tr');
        row.id = `row-${rowIndex}`;
        
        let options = '<option value="">-- Choose Product --</option>';
        products.forEach(p => {
            options += `<option value="${p.id}">[${p.sku}] ${p.name}</option>`;
        });

        row.innerHTML = `
            <td>
              <select name="items[${rowIndex}][product_id]" class="form-input product-select searchable-select" required style="height: 36px;">
                ${options}
              </select>
            </td>
            <td>
              <input type="number" step="0.01" name="items[${rowIndex}][quantity_requested]" class="form-input qty-input" required min="0.01" value="1" style="padding: 6px 8px; width: 100%;">
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

        const removeBtn = row.querySelector('.remove-row-btn');
        removeBtn.addEventListener('click', function() {
            row.remove();
        });

        rowIndex++;
    }

    // Add initial row
    createRow();
    
    addBtn.addEventListener('click', createRow);

    document.getElementById('showcase-request-form').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('#items-container tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Please add at least one product to the request.');
            return;
        }

        let hasErrors = false;
        const selectedProducts = new Set();

        rows.forEach(row => {
            const select = row.querySelector('.product-select');
            const productId = select.value;

            if (!productId) {
                alert('Please choose a product for all rows.');
                hasErrors = true;
                return;
            }

            if (selectedProducts.has(productId)) {
                alert('Duplicate products detected. Please consolidate duplicate products into a single row.');
                hasErrors = true;
                return;
            }
            selectedProducts.add(productId);
        });

        if (hasErrors) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
