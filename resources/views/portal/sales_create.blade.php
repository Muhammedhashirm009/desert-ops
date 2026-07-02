@extends('layouts.portal')

@section('title', 'Log Daily Sales — DessertOps Portal')
@section('breadcrumb', 'Log Daily Sales')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Log Daily Sales Report</div>
    <div class="ph-sub">Record quantities of desserts sold today at <b>{{ $outlet->name }}</b></div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('portal.sales.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<form action="{{ route('portal.sales.store') }}" method="POST" id="sales-form">
  @csrf

  <div class="row r-3-1" style="grid-template-columns: 1fr 320px; gap: 16px;">
    <!-- Left Panel: Products Sold -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Sold Items & Quantities</div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="po-table" id="items-table">
          <thead>
            <tr>
              <th style="width: 60%;">Dessert Product *</th>
              <th style="width: 30%;">Quantity Sold *</th>
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
            Add Dessert Sold Row
          </button>
        </div>
      </div>
    </div>

    <!-- Right Panel: Meta Info -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="ch">
          <div class="ch-title">Sales Metadata</div>
        </div>
        <div class="cb">
          <div class="form-grp">
            <label style="opacity: 0.8; font-size: 12px;">Active Outlet</label>
            <div style="font-weight: 700; font-size: 15px; color: var(--txt); margin-top: 2px;">
              {{ $outlet->name }}
            </div>
            <span class="badge {{ $outlet->type === 'own' ? 'bg' : 'bp' }}" style="font-size: 10px; margin-top: 4px;">
              {{ $outlet->type === 'own' ? 'Company Owned Store' : 'Franchise Partner' }}
            </span>
          </div>

          <div class="form-grp" style="margin-top: 16px;">
            <label for="log_date">Report Date *</label>
            <input type="date" name="log_date" id="log_date" class="form-input" required 
                   value="{{ old('log_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
            @error('log_date')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="mt-4" style="margin-top: 20px;">
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px; font-weight: 600;">
              Save Sales Report
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
    const stocks = @json($stocks); // Plucked: { product_id: quantity }
    
    let rowIndex = 0;

    function getProductStock(productId) {
        if (!stocks || stocks[productId] === undefined) {
            return 0.00;
        }
        return parseFloat(stocks[productId]);
    }

    function createRow() {
        const row = document.createElement('tr');
        row.id = `row-${rowIndex}`;
        
        let options = '<option value="">-- Choose Dessert --</option>';
        products.forEach(p => {
            const available = getProductStock(p.id);
            options += `<option value="${p.id}">[${p.sku}] ${p.name} (Retail: ₹${parseFloat(p.retail_price).toFixed(2)} | In Stock: ${parseInt(available)})</option>`;
        });

        row.innerHTML = `
            <td>
              <select name="items[${rowIndex}][product_id]" class="form-input product-select searchable-select" required style="height: 36px;">
                ${options}
              </select>
            </td>
            <td>
              <div style="display: flex; flex-direction: column; gap: 4px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                  <input type="number" step="1" name="items[${rowIndex}][quantity_sold]" class="form-input qty-input" required min="1" value="1" style="padding: 6px 8px;">
                  <span style="font-size:12px; color:var(--txt3);">Units</span>
                </div>
                <span class="stock-lbl" style="font-size: 11px; color: var(--txt3);">Select product to view stock</span>
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

        const qtyInput = row.querySelector('.qty-input');
        const stockLbl = row.querySelector('.stock-lbl');
        const removeBtn = row.querySelector('.remove-row-btn');

        function updateRowStock() {
            const productId = select.value;

            if (!productId) {
                stockLbl.textContent = 'Select a product';
                stockLbl.style.color = 'var(--txt3)';
                qtyInput.max = '';
                return;
            }

            const stock = getProductStock(productId);
            stockLbl.textContent = `Available Stock: ${parseInt(stock)} units`;
            qtyInput.max = parseInt(stock);

            if (stock <= 0) {
                stockLbl.style.color = 'var(--red-tx)';
                stockLbl.textContent += ' (Out of Stock)';
            } else if (stock <= 5) {
                stockLbl.style.color = 'var(--amber-tx)';
                stockLbl.textContent += ' (Low Stock)';
            } else {
                stockLbl.style.color = 'var(--green-tx)';
            }
        }

        // Listeners
        select.addEventListener('change', updateRowStock);

        removeBtn.addEventListener('click', function() {
            row.remove();
        });

        // Trigger once to init labels
        updateRowStock();

        rowIndex++;
    }

    // Add initial row
    createRow();
    
    addBtn.addEventListener('click', createRow);

    document.getElementById('sales-form').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('#items-container tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Please add at least one product sale.');
            return;
        }

        // Validate quantities vs available stocks before posting
        let hasErrors = false;
        const selectedProducts = new Set();

        rows.forEach(row => {
            const select = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.qty-input');
            const productId = select.value;

            if (!productId) {
                alert('Please choose a product for all rows.');
                hasErrors = true;
                return;
            }

            if (selectedProducts.has(productId)) {
                alert('Duplicate dessert products detected. Please consolidate duplicate products into a single row.');
                hasErrors = true;
                return;
            }
            selectedProducts.add(productId);

            const stock = getProductStock(productId);
            const qtySold = parseInt(qtyInput.value) || 0;
            if (qtySold > stock) {
                alert(`Cannot record sale. Quantity sold (${qtySold}) exceeds available stock (${parseInt(stock)}) for the selected product.`);
                hasErrors = true;
            }
        });

        if (hasErrors) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
