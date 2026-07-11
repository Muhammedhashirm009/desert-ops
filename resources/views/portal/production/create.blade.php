@extends('layouts.portal')

@section('title', 'New Production Run — DessertOps Portal')
@section('breadcrumb', 'New Production Run')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">New Production Run</div>
    <div class="ph-sub">Prepare a dessert product using materials from <b>{{ $outlet->name }}</b>'s kitchen</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('portal.production.index') }}" class="btn-ghost">Cancel</a>
  </div>
</div>

<form action="{{ route('portal.production.store') }}" method="POST" id="production-form">
  @csrf

  <div class="row r-3-1" style="grid-template-columns: 1fr 320px; gap: 16px;">
    <!-- Left Panel: Materials Consumed -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Materials to Consume</div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="prod-table" id="materials-table">
          <thead>
            <tr>
              <th style="width: 65%;">Material / Ingredient *</th>
              <th style="width: 25%;">Quantity Used *</th>
              <th style="width: 10%;"></th>
            </tr>
          </thead>
          <tbody id="materials-container">
            <!-- Dynamic rows inserted here -->
          </tbody>
        </table>

        <div style="padding: 16px; border-top: 1px solid var(--div);">
          <button type="button" class="btn-ghost" id="add-material-btn" style="padding: 6px 12px; font-size: 12.5px;">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 12px; height: 12px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Material Row
          </button>
        </div>
      </div>
    </div>

    <!-- Right Panel: Production Details -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="ch">
          <div class="ch-title">Production Details</div>
        </div>
        <div class="cb">
          <div class="form-grp">
            <label for="output_select">Product to Produce *</label>
            <select id="output_select" class="form-input" required>
              <option value="">-- Select Product --</option>
              @if(isset($catalogItems) && $catalogItems->count() > 0)
              <optgroup label="📋 Catalog Items (from recipe)">
                @foreach($catalogItems as $catItem)
                  <option value="catalog_{{ $catItem->id }}" data-type="catalog" data-catalog-id="{{ $catItem->id }}">
                    [{{ $catItem->sku }}] {{ $catItem->name }} — ₹{{ number_format($catItem->retail_price, 2) }}
                  </option>
                @endforeach
              </optgroup>
              @endif
              <optgroup label="🏭 Existing Products">
                @foreach($products as $product)
                  <option value="product_{{ $product->id }}" data-type="product" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                    [{{ $product->sku }}] {{ $product->name }}
                  </option>
                @endforeach
              </optgroup>
            </select>
            <input type="hidden" name="product_id" id="product_id_hidden" value="{{ old('product_id') }}">
            <input type="hidden" name="outlet_catalog_item_id" id="catalog_item_id_hidden" value="{{ old('outlet_catalog_item_id') }}">
            @error('product_id')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
            @error('outlet_catalog_item_id')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp" style="margin-top: 16px;">
            <label for="quantity_produced">Quantity to Produce *</label>
            <input type="number" step="0.01" name="quantity_produced" id="quantity_produced" class="form-input" required min="0.01" value="{{ old('quantity_produced', 1) }}" placeholder="Number of units">
            @error('quantity_produced')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp" style="margin-top: 16px;">
            <label for="prepared_date">Preparation Date *</label>
            <input type="date" name="prepared_date" id="prepared_date" class="form-input" required value="{{ old('prepared_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
            @error('prepared_date')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp" style="margin-top: 16px;">
            <label>Send Finished Product To *</label>
            <div style="font-size: 12px; color: var(--txt3); margin-bottom: 8px;">Split the produced quantity between Store and Showcase</div>
            <div style="display: flex; gap: 10px;">
              <div style="flex: 1;">
                <label for="qty_to_store" style="font-size: 11px; color: var(--txt2); font-weight: 600;">To Store</label>
                <input type="number" step="0.01" name="qty_to_store" id="qty_to_store" class="form-input dest-qty" min="0" value="{{ old('qty_to_store', 0) }}" style="margin-top: 4px;" placeholder="0">
              </div>
              <div style="flex: 1;">
                <label for="qty_to_showcase" style="font-size: 11px; color: var(--txt2); font-weight: 600;">To Showcase</label>
                <input type="number" step="0.01" name="qty_to_showcase" id="qty_to_showcase" class="form-input dest-qty" min="0" value="{{ old('qty_to_showcase', 1) }}" style="margin-top: 4px;" placeholder="0">
              </div>
            </div>
            <div id="dest-validation" style="font-size: 11px; margin-top: 6px; color: var(--green-tx);">
              ✓ Total matches quantity to produce
            </div>
            @error('qty_to_store')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
            @error('qty_to_showcase')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp" style="margin-top: 16px;">
            <label for="notes">Notes</label>
            <textarea name="notes" id="notes" class="form-input" rows="3" placeholder="Optional notes about this production...">{{ old('notes') }}</textarea>
          </div>

          <div style="margin-top: 20px;">
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px; font-weight: 600;">
              Create Production Run
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
    const container = document.getElementById('materials-container');
    const addBtn = document.getElementById('add-material-btn');

    // Build combined list of materials and products available in kitchen
    const kitchenItems = [];

    @foreach($outletMaterials as $stock)
      kitchenItems.push({
        type: 'material',
        id: {{ $stock->material_id }},
        name: '{{ addslashes($stock->material->name) }}',
        sku: '{{ $stock->material->sku }}',
        unit: '{{ $stock->material->unit ?? "units" }}',
        available: {{ (float) $stock->kitchen_quantity }}
      });
    @endforeach

    @foreach($outletProducts as $stock)
      kitchenItems.push({
        type: 'product',
        id: {{ $stock->product_id }},
        name: '{{ addslashes($stock->product->name) }}',
        sku: '{{ $stock->product->sku }}',
        unit: 'units',
        available: {{ (float) $stock->kitchen_quantity }}
      });
    @endforeach

    // Catalog item ingredient definitions for auto-populate
    const catalogIngredients = {};
    @if(isset($catalogItems))
    @foreach($catalogItems as $catItem)
      catalogIngredients[{{ $catItem->id }}] = [
        @foreach($catItem->ingredients as $ing)
        {
          type: '{{ $ing->product_id ? 'product' : 'material' }}',
          id: {{ $ing->product_id ?? $ing->material_id }},
          default_quantity: {{ (float) $ing->default_quantity }}
        },
        @endforeach
      ];
    @endforeach
    @endif

    // Destination split validation
    const qtyProducedInput = document.getElementById('quantity_produced');
    const qtyStoreInput = document.getElementById('qty_to_store');
    const qtyShowcaseInput = document.getElementById('qty_to_showcase');
    const destValidation = document.getElementById('dest-validation');

    function validateDestSplit() {
        const total = parseFloat(qtyProducedInput.value) || 0;
        const toStore = parseFloat(qtyStoreInput.value) || 0;
        const toShowcase = parseFloat(qtyShowcaseInput.value) || 0;
        const sum = parseFloat((toStore + toShowcase).toFixed(2));

        if (sum === total && total > 0) {
            destValidation.style.color = 'var(--green-tx)';
            destValidation.textContent = '✓ Total matches quantity to produce (' + sum + ' = ' + total + ')';
        } else if (total > 0) {
            destValidation.style.color = 'var(--red-tx)';
            destValidation.textContent = '✗ Store (' + toStore + ') + Showcase (' + toShowcase + ') = ' + sum + ', but producing ' + total;
        } else {
            destValidation.style.color = 'var(--txt3)';
            destValidation.textContent = 'Enter quantity to produce first';
        }
    }

    qtyProducedInput.addEventListener('input', function() {
        // Auto-fill showcase with total when qty changes and both destinations are 0
        const toStore = parseFloat(qtyStoreInput.value) || 0;
        const toShowcase = parseFloat(qtyShowcaseInput.value) || 0;
        const total = parseFloat(this.value) || 0;
        if (toStore === 0 || (toStore + toShowcase) === 0) {
            qtyShowcaseInput.value = total;
        }
        validateDestSplit();
    });
    qtyStoreInput.addEventListener('input', validateDestSplit);
    qtyShowcaseInput.addEventListener('input', validateDestSplit);
    validateDestSplit();

    let rowIndex = 0;

    function createRow() {
        const row = document.createElement('tr');
        row.id = `mat-row-${rowIndex}`;

        let options = '<option value="" data-type="">-- Choose Material / Ingredient --</option>';
        kitchenItems.forEach(item => {
            options += `<option value="${item.id}" data-type="${item.type}" data-unit="${item.unit}" data-available="${item.available}">[${item.sku}] ${item.name} (Avail: ${parseInt(item.available)} ${item.unit})</option>`;
        });

        row.innerHTML = `
            <td>
              <select name="materials[${rowIndex}][id]" class="form-input material-select searchable-select" required style="height: 36px;">
                ${options}
              </select>
              <input type="hidden" name="materials[${rowIndex}][type]" class="material-type-input" value="">
              <span class="stock-info" style="font-size: 11px; color: var(--txt3); margin-top: 4px; display: block;">Select an item to see availability</span>
            </td>
            <td>
              <div style="display: flex; align-items: center; gap: 6px;">
                <input type="number" step="0.01" name="materials[${rowIndex}][quantity_used]" class="form-input qty-input" required min="0.01" value="1" style="padding: 6px 8px;">
                <span class="unit-label" style="font-size: 12px; color: var(--txt3);">units</span>
              </div>
            </td>
            <td class="text-center">
              <button type="button" class="po-row-btn remove-row-btn" style="padding: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 14px; height: 14px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </td>
        `;

        container.appendChild(row);

        const select = row.querySelector('.material-select');
        const typeInput = row.querySelector('.material-type-input');
        const stockInfo = row.querySelector('.stock-info');
        const unitLabel = row.querySelector('.unit-label');
        const qtyInput = row.querySelector('.qty-input');
        const removeBtn = row.querySelector('.remove-row-btn');

        if (window.initSearchableSelect) {
            window.initSearchableSelect(select);
        }

        select.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const type = opt.dataset.type || '';
            const available = parseFloat(opt.dataset.available || 0);
            const unit = opt.dataset.unit || 'units';

            typeInput.value = type;
            unitLabel.textContent = unit;

            if (type) {
                stockInfo.textContent = `Kitchen Stock: ${parseInt(available)} ${unit}`;
                stockInfo.style.color = available <= 0 ? 'var(--red-tx)' : 'var(--green-tx)';
                qtyInput.max = available;
            } else {
                stockInfo.textContent = 'Select an item to see availability';
                stockInfo.style.color = 'var(--txt3)';
                qtyInput.max = '';
            }
        });

        removeBtn.addEventListener('click', function() {
            row.remove();
        });

        rowIndex++;
    }

    // Add initial row
    createRow();
    addBtn.addEventListener('click', createRow);

    // Handle output select (product vs catalog item)
    const outputSelect = document.getElementById('output_select');
    const productIdHidden = document.getElementById('product_id_hidden');
    const catalogItemIdHidden = document.getElementById('catalog_item_id_hidden');

    outputSelect.addEventListener('change', function() {
        const val = this.value;
        const opt = this.options[this.selectedIndex];
        const type = opt.dataset.type || '';

        if (type === 'catalog') {
            const catalogId = opt.dataset.catalogId;
            catalogItemIdHidden.value = catalogId;
            productIdHidden.value = '';

            // Clear existing material rows and auto-populate from recipe
            container.innerHTML = '';
            rowIndex = 0;
            const ingredients = catalogIngredients[catalogId] || [];
            ingredients.forEach(ing => {
                createRow();
                const lastRow = container.lastElementChild;
                const select = lastRow.querySelector('.material-select');
                const typeInput = lastRow.querySelector('.material-type-input');
                const qtyInput = lastRow.querySelector('.qty-input');

                // Find and select the matching option
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].value == ing.id && select.options[i].dataset.type === ing.type) {
                        select.selectedIndex = i;
                        select.dispatchEvent(new Event('change'));
                        break;
                    }
                }
                typeInput.value = ing.type;
                qtyInput.value = ing.default_quantity;
            });
        } else if (type === 'product') {
            productIdHidden.value = val.replace('product_', '');
            catalogItemIdHidden.value = '';
        } else {
            productIdHidden.value = '';
            catalogItemIdHidden.value = '';
        }
    });

    // Validation on submit
    document.getElementById('production-form').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('#materials-container tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Please add at least one material to consume.');
            return;
        }

        let hasErrors = false;
        const selectedItems = new Set();

        rows.forEach(row => {
            const select = row.querySelector('.material-select');
            const typeInput = row.querySelector('.material-type-input');
            const val = select.value;
            const type = typeInput.value;

            if (!val || !type) {
                alert('Please select a material for all rows.');
                hasErrors = true;
                return;
            }

            const key = `${type}_${val}`;
            if (selectedItems.has(key)) {
                alert('Duplicate materials detected. Please consolidate into a single row.');
                hasErrors = true;
                return;
            }
            selectedItems.add(key);
        });

        if (hasErrors) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
