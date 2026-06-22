@extends('layouts.app')

@section('title', 'Request Materials — DessertOps')
@section('breadcrumb', 'New Material Request')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Create Material Request</div>
    <div class="ph-sub">Request raw ingredients or packaging items from the main inventory store</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('material-requests.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<form action="{{ route('material-requests.store') }}" method="POST" id="mr-form">
  @csrf

  <div class="row r-3-1" style="grid-template-columns: 1fr 300px; gap: 16px;">
    <!-- Items Selection Table -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Requested Raw Materials</div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="po-table" id="items-table">
          <thead>
            <tr>
              <th style="width: 60%;">Raw Material *</th>
              <th style="width: 30%;">Quantity Requested *</th>
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
            Add Material Row
          </button>
        </div>
      </div>
    </div>

    <!-- Metadata Panel -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="ch">
          <div class="ch-title">Request Info</div>
        </div>
        <div class="cb">
          <div class="form-grp">
            <label for="requested_by">Requested By *</label>
            <input type="text" name="requested_by" id="requested_by" class="form-input" required 
                   value="{{ old('requested_by', 'Kitchen Chef') }}" placeholder="Chef/Staff name">
            @error('requested_by')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp">
            <label for="requested_date">Request Date *</label>
            <input type="date" name="requested_date" id="requested_date" class="form-input" required 
                   value="{{ old('requested_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
            @error('requested_date')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp">
            <label for="notes">Purpose / Remarks</label>
            <textarea name="notes" id="notes" class="form-input" rows="4" 
                      placeholder="e.g. Production of 100 boxes of Gulab Jamun for Thrissur dispatch...">{{ old('notes') }}</textarea>
            @error('notes')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="mt-4">
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px;">
              Submit Material Request
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
    
    // Array of raw materials
    const materials = @json($materials);
    
    let rowIndex = 0;
    
    function createRow() {
        const row = document.createElement('tr');
        row.id = `row-${rowIndex}`;
        
        let materialOptions = '<option value="">-- Select Raw Material --</option>';
        materials.forEach(m => {
            materialOptions += `<option value="${m.id}" data-unit="${m.unit}">[${m.sku}] ${m.name} (Stock: ${parseFloat(m.current_stock).toFixed(2)} ${m.unit})</option>`;
        });
        
        row.innerHTML = `
            <td>
              <select name="items[${rowIndex}][material_id]" class="form-input material-select" required style="height: 36px;">
                ${materialOptions}
              </select>
            </td>
            <td>
              <div style="display: flex; align-items: center; gap: 6px;">
                <input type="number" step="0.01" name="items[${rowIndex}][quantity_requested]" class="form-input" required min="0.01" value="1.00" style="padding: 6px 8px;">
                <span class="unit-lbl text-xs" style="color: var(--txt3); font-size: 11.5px; min-width: 28px;">unit</span>
              </div>
            </td>
            <td class="text-center">
              <button type="button" class="po-row-btn remove-row-btn" style="padding: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 14px; height: 14px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </td>
        `;
        
        itemsContainer.appendChild(row);
        
        const select = row.querySelector('.material-select');
        const removeBtn = row.querySelector('.remove-row-btn');
        const unitLbl = row.querySelector('.unit-lbl');
        
        select.addEventListener('change', function() {
            const selectedOpt = select.options[select.selectedIndex];
            const unit = selectedOpt.dataset.unit || 'unit';
            unitLbl.textContent = unit;
        });
        
        removeBtn.addEventListener('click', function() {
            row.remove();
        });
        
        rowIndex++;
    }
    
    // Add initial row
    createRow();
    
    addRowBtn.addEventListener('click', function() {
        createRow();
    });
    
    document.getElementById('mr-form').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('#items-container tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Please add at least one raw material to the request.');
        }
    });
});
</script>
@endsection
