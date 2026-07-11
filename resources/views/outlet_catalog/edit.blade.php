@extends('layouts.app')

@section('title', 'Edit Catalog Item — DessertOps')
@section('breadcrumb', 'Edit Catalog Item')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Edit: {{ $outlet_catalog->name }}</div>
    <div class="ph-sub">Update catalog item details and recipe ingredients</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('outlet-catalog.index') }}" class="btn-ghost">Cancel</a>
  </div>
</div>

<form action="{{ route('outlet-catalog.update', $outlet_catalog->id) }}" method="POST" id="catalog-form">
  @csrf
  @method('PUT')

  <div class="row r-3-1" style="grid-template-columns: 1fr 340px; gap: 16px;">
    <div class="card">
      <div class="ch"><div class="ch-title">Recipe Ingredients</div></div>
      <div class="cb" style="padding: 0;">
        <table class="prod-table" id="ingredients-table">
          <thead>
            <tr>
              <th style="width: 55%;">Ingredient (Product / Material) *</th>
              <th style="width: 25%;">Default Quantity *</th>
              <th style="width: 10%;">Type</th>
              <th style="width: 10%;"></th>
            </tr>
          </thead>
          <tbody id="ingredients-container"></tbody>
        </table>
        <div style="padding: 16px; border-top: 1px solid var(--div);">
          <button type="button" class="btn-ghost" id="add-ingredient-btn" style="padding: 6px 12px; font-size: 12.5px;">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 12px; height: 12px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Ingredient
          </button>
        </div>
      </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="ch"><div class="ch-title">Catalog Item Details</div></div>
        <div class="cb">
          <div class="form-grp">
            <label for="name">Product Name *</label>
            <input type="text" name="name" id="name" class="form-input" required value="{{ old('name', $outlet_catalog->name) }}">
            @error('name')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>
          <div class="form-grp" style="margin-top: 16px;">
            <label for="sku">SKU Code *</label>
            <input type="text" name="sku" id="sku" class="form-input" required value="{{ old('sku', $outlet_catalog->sku) }}">
            @error('sku')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>
          <div class="form-grp" style="margin-top: 16px;">
            <label for="retail_price">Retail Price (₹) *</label>
            <input type="number" step="0.01" name="retail_price" id="retail_price" class="form-input" required min="0" value="{{ old('retail_price', $outlet_catalog->retail_price) }}">
            @error('retail_price')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>
          <div class="form-grp" style="margin-top: 16px;">
            <label for="description">Description / Preparation Notes</label>
            <textarea name="description" id="description" class="form-input" rows="3">{{ old('description', $outlet_catalog->description) }}</textarea>
          </div>
          <div class="form-grp" style="margin-top: 16px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="checkbox" name="is_active" value="1" {{ old('is_active', $outlet_catalog->is_active) ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--btn);">
              <span>Active</span>
            </label>
          </div>
          <div style="margin-top: 20px;">
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px; font-weight: 600;">Update Catalog Item</button>
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
    const container = document.getElementById('ingredients-container');
    const addBtn = document.getElementById('add-ingredient-btn');

    const availableItems = [];
    @foreach($products as $product)
      availableItems.push({ type: 'product', id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', sku: '{{ $product->sku }}' });
    @endforeach
    @foreach($materials as $material)
      availableItems.push({ type: 'material', id: {{ $material->id }}, name: '{{ addslashes($material->name) }}', sku: '{{ $material->sku }}' });
    @endforeach

    let rowIndex = 0;

    function createRow(preselect) {
        const row = document.createElement('tr');
        row.id = `ing-row-${rowIndex}`;
        let options = '<option value="" data-type="">-- Choose Ingredient --</option>';
        options += '<optgroup label="Products">';
        availableItems.filter(i => i.type === 'product').forEach(item => {
            const sel = preselect && preselect.type === 'product' && preselect.id == item.id ? 'selected' : '';
            options += `<option value="${item.id}" data-type="product" ${sel}>[${item.sku}] ${item.name}</option>`;
        });
        options += '</optgroup><optgroup label="Materials">';
        availableItems.filter(i => i.type === 'material').forEach(item => {
            const sel = preselect && preselect.type === 'material' && preselect.id == item.id ? 'selected' : '';
            options += `<option value="${item.id}" data-type="material" ${sel}>[${item.sku}] ${item.name}</option>`;
        });
        options += '</optgroup>';
        const defQty = preselect ? preselect.default_quantity : 1;
        const defType = preselect ? preselect.type : '';
        row.innerHTML = `
            <td><select name="ingredients[${rowIndex}][id]" class="form-input ing-select" required style="height: 36px;">${options}</select><input type="hidden" name="ingredients[${rowIndex}][type]" class="ing-type-input" value="${defType}"></td>
            <td><input type="number" step="0.01" name="ingredients[${rowIndex}][default_quantity]" class="form-input" required min="0.01" value="${defQty}" style="padding: 6px 8px;"></td>
            <td style="text-align: center;"><span class="ing-type-badge" style="font-size: 11px; padding: 2px 8px; border-radius: 4px; background: var(--bg2); color: ${defType === 'product' ? 'var(--btn)' : defType === 'material' ? 'var(--purple-tx)' : 'var(--txt3)'}">${defType || '—'}</span></td>
            <td class="text-center"><button type="button" class="po-row-btn remove-row-btn" style="padding: 6px;"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 14px; height: 14px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
        `;
        container.appendChild(row);
        const select = row.querySelector('.ing-select');
        const typeInput = row.querySelector('.ing-type-input');
        const typeBadge = row.querySelector('.ing-type-badge');
        select.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const type = opt.dataset.type || '';
            typeInput.value = type;
            typeBadge.textContent = type || '—';
            typeBadge.style.color = type === 'product' ? 'var(--btn)' : type === 'material' ? 'var(--purple-tx)' : 'var(--txt3)';
        });
        row.querySelector('.remove-row-btn').addEventListener('click', () => row.remove());
        rowIndex++;
    }

    // Pre-load existing ingredients
    const existingIngredients = @json($outlet_catalog->ingredients->map(fn($i) => ['type' => $i->product_id ? 'product' : 'material', 'id' => $i->product_id ?? $i->material_id, 'default_quantity' => (float) $i->default_quantity]));
    if (existingIngredients.length > 0) {
        existingIngredients.forEach(ing => createRow(ing));
    } else {
        createRow();
    }
    addBtn.addEventListener('click', () => createRow());

    document.getElementById('catalog-form').addEventListener('submit', function(e) {
        if (container.querySelectorAll('tr').length === 0) { e.preventDefault(); alert('Add at least one ingredient.'); }
    });
});
</script>
@endsection
