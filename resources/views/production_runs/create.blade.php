@extends('layouts.app')

@section('title', 'Log Production Run — DessertOps')
@section('breadcrumb', 'Log Production Run')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Log Dessert Preparation Batch</div>
    <div class="ph-sub">Record finished dessert products prepared and log raw materials consumed</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('production-runs.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<form action="{{ route('production-runs.store') }}" method="POST" id="production-form">
  @csrf

  <div class="row r-3-1" style="grid-template-columns: 1fr 320px; gap: 16px;">
    <!-- Left: Raw Materials Consumed -->
    <div class="card">
      <div class="ch">
        <div class="ch-ic" style="background:var(--div);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
        </div>
        <div class="ch-title">Raw Materials Consumed in Kitchen</div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="po-table" id="consumption-table">
          <thead>
            <tr>
              <th style="width: 60%;">Raw Material (Kitchen Stock) *</th>
              <th style="width: 30%;">Quantity Used *</th>
              <th style="width: 10%;"></th>
            </tr>
          </thead>
          <tbody id="consumption-container">
            <!-- Dynamic rows inserted here -->
          </tbody>
        </table>
        
        <div style="padding: 16px; border-top: 1px solid var(--div);">
          <button type="button" class="btn-ghost" id="add-material-btn" style="padding: 6px 12px; font-size: 12.5px;">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 12px; height: 12px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Raw Material Row
          </button>
        </div>
      </div>
    </div>

    <!-- Right: Production Run Info -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="ch">
          <div class="ch-title">Batch Info</div>
        </div>
        <div class="cb">
          <div class="form-grp">
            <label for="product_id">Finished Dessert Product *</label>
            <select name="product_id" id="product_id" class="form-input" required style="height: 38px;">
              <option value="">-- Choose Dessert --</option>
              @foreach($products as $product)
                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                  {{ $product->name }} ({{ $product->sku }})
                </option>
              @endforeach
            </select>
            @error('product_id')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="grid-2" style="grid-template-columns: 1fr; gap: 0;">
            <div class="form-grp">
              <label for="quantity_produced">Quantity Prepared (Units) *</label>
              <input type="number" step="1" name="quantity_produced" id="quantity_produced" class="form-input" 
                     value="{{ old('quantity_produced', 10) }}" required min="1">
              @error('quantity_produced')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
            </div>

            <div class="form-grp" style="margin-top: 10px;">
              <label for="prepared_date">Prepared Date *</label>
              <input type="date" name="prepared_date" id="prepared_date" class="form-input" required 
                     value="{{ old('prepared_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
              @error('prepared_date')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
            </div>
          </div>

          <div class="form-grp" style="margin-top: 10px;">
            <label for="notes">Production Remarks</label>
            <textarea name="notes" id="notes" class="form-input" rows="4" 
                      placeholder="e.g. Morning batch prepared by Suresh, sensory profiling perfect...">{{ old('notes') }}</textarea>
            @error('notes')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="mt-4">
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px;">
              Save Production Batch
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
    const container = document.getElementById('consumption-container');
    const addBtn = document.getElementById('add-material-btn');
    
    // Array of raw materials
    const materials = @json($materials);
    
    let rowIndex = 0;
    
    function createRow() {
        const row = document.createElement('tr');
        row.id = `row-${rowIndex}`;
        
        let options = '<option value="">-- Choose Raw Material --</option>';
        materials.forEach(m => {
            options += `<option value="${m.id}" data-unit="${m.unit}">[${m.sku}] ${m.name} (Kitchen stock: ${parseFloat(m.kitchen_stock).toFixed(2)} ${m.unit})</option>`;
        });
        
        row.innerHTML = `
            <td>
              <select name="items[${rowIndex}][material_id]" class="form-input material-select" required style="height: 36px;">
                ${options}
              </select>
            </td>
            <td>
              <div style="display: flex; align-items: center; gap: 6px;">
                <input type="number" step="0.01" name="items[${rowIndex}][quantity_used]" class="form-input" required min="0.01" value="1.00" style="padding: 6px 8px;">
                <span class="unit-lbl text-xs" style="color: var(--txt3); font-size: 11.5px; min-width: 28px;">unit</span>
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
        const remove = row.querySelector('.remove-row-btn');
        const label = row.querySelector('.unit-lbl');
        
        select.addEventListener('change', function() {
            const opt = select.options[select.selectedIndex];
            label.textContent = opt.dataset.unit || 'unit';
        });
        
        remove.addEventListener('click', function() {
            row.remove();
        });
        
        rowIndex++;
    }
    
    // Add initial row
    createRow();
    
    addBtn.addEventListener('click', createRow);
    
    document.getElementById('production-form').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('#consumption-container tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Please add at least one raw material consumed for this production.');
        }
    });
});
</script>
@endsection
