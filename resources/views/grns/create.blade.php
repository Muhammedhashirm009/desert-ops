@extends('layouts.app')

@section('title', 'Receive Goods for PO ' . $purchaseOrder->po_number . ' — DessertOps')
@section('breadcrumb', 'Process GRN')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Receive Goods: {{ $purchaseOrder->po_number }}</div>
    <div class="ph-sub">Verify delivered items and record them into inventory stock</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('purchase-orders.show', $purchaseOrder->id) }}" class="btn-ghost">
      Back to PO
    </a>
  </div>
</div>

<form action="{{ route('purchase-orders.receive.store', $purchaseOrder->id) }}" method="POST">
  @csrf

  <div class="row r-3-1" style="grid-template-columns: 1fr 300px; gap: 16px;">
    <!-- Items list verification -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Verify Received Quantities</div>
      </div>
      <div class="cb" style="padding: 0;">
        <table>
          <thead>
            <tr>
              <th style="width: 12%;">SKU</th>
              <th style="width: 33%;">Material</th>
              <th style="width: 15%; text-align: right;">Ordered Qty</th>
              <th style="width: 15%;">Received Qty *</th>
              <th style="width: 15%;">Unit Cost (₹) *</th>
              <th style="width: 10%; text-align: right;">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($purchaseOrder->items as $index => $item)
            <tr class="item-row">
              <td class="mono">{{ $item->material->sku }}</td>
              <td>
                <div style="font-weight: 600; color: var(--txt);">{{ $item->material->name }}</div>
                <div style="font-size: 11px; color: var(--txt3);">Unit: {{ $item->material->unit }}</div>
                
                <!-- Hidden input for material ID -->
                <input type="hidden" name="items[{{ $index }}][material_id]" value="{{ $item->material_id }}">
              </td>
              <td class="mono font-semibold" style="text-align: right; padding-right: 15px;">
                {{ number_format($item->quantity, 2) }} {{ $item->material->unit }}
              </td>
              <td>
                <div style="display: flex; align-items: center; gap: 6px;">
                  <input type="number" step="0.01" name="items[{{ $index }}][quantity_received]" 
                         class="form-input qty-input" required min="0" max="{{ $item->quantity * 1.5 }}" 
                         value="{{ old('items.'.$index.'.quantity_received', $item->quantity) }}" 
                         style="padding: 6px 8px; width: 90px;">
                  <span style="color: var(--txt3); font-size: 11px;">{{ $item->material->unit }}</span>
                </div>
              </td>
              <td>
                <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" 
                       class="form-input cost-input" required min="0.01" 
                       value="{{ old('items.'.$index.'.unit_cost', $item->unit_price) }}" 
                       style="padding: 6px 8px; width: 100px;" placeholder="0.00">
              </td>
              <td class="mono font-semibold line-total" style="text-align: right; padding-right: 10px;">
                ₹0.00
              </td>
            </tr>
            @endforeach
            <tr>
              <td colspan="5" style="text-align: right; font-weight: 700; padding: 12px;">Grand Total Cost:</td>
              <td id="grand-total-display" class="mono font-bold" style="text-align: right; font-weight: 700; padding: 12px; color: var(--green-tx);">₹0.00</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Metadata Panel -->
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <div class="card">
        <div class="ch">
          <div class="ch-title">Receiving details</div>
        </div>
        <div class="cb">
          <div class="form-grp">
            <label for="received_by">Received By *</label>
            <input type="text" name="received_by" id="received_by" class="form-input" required 
                   value="{{ old('received_by', 'Hashir') }}" placeholder="Staff name">
            @error('received_by')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp">
            <label for="received_date">Received Date *</label>
            <input type="date" name="received_date" id="received_date" class="form-input" required 
                   value="{{ old('received_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
            @error('received_date')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp">
            <label for="notes">GRN Notes</label>
            <textarea name="notes" id="notes" class="form-input" rows="4" 
                      placeholder="e.g. delivered in good condition, batch numbers, temperature logs...">{{ old('notes') }}</textarea>
            @error('notes')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="mt-4">
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px; background: var(--green);">
              Save GRN & Update Stock
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
    const rows = document.querySelectorAll('.item-row');
    const grandTotalDisplay = document.getElementById('grand-total-display');

    function calculate() {
        let grandTotal = 0;
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const total = qty * cost;
            row.querySelector('.line-total').textContent = '₹' + total.toFixed(2);
            grandTotal += total;
        });
        grandTotalDisplay.textContent = '₹' + grandTotal.toFixed(2);
    }

    rows.forEach(row => {
        row.querySelector('.qty-input').addEventListener('input', calculate);
        row.querySelector('.cost-input').addEventListener('input', calculate);
    });

    calculate();
});
</script>
@endsection
