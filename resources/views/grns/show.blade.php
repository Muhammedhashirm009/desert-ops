@extends('layouts.app')

@section('title', 'Goods Received Note ' . $grn->grn_number . ' — DessertOps')
@section('breadcrumb', 'GRN Details')

@section('styles')
<style>
@media print {
  body {
    background: #fff;
    color: #000;
  }
  .sb, .tb, .ph, .alert, .btn-ghost, .btn-pri, form {
    display: none !important;
  }
  .main {
    margin: 0;
    padding: 0;
    width: 100%;
  }
  .content {
    padding: 0;
  }
  .card {
    border: none !important;
    box-shadow: none !important;
  }
  .print-header {
    display: block !important;
    margin-bottom: 30px;
  }
  .print-footer {
    display: block !important;
    margin-top: 50px;
    border-top: 1px solid #ddd;
    padding-top: 20px;
  }
}
.print-header, .print-footer {
  display: none;
}
.grn-details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 25px;
}
.grn-meta-card {
  padding: 15px;
  background: var(--pg-bg);
  border-radius: var(--radius);
  border: 1px solid var(--div2);
}
.grn-meta-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 13px;
}
.grn-meta-item:last-child {
  margin-bottom: 0;
}
.grn-meta-label {
  color: var(--txt3);
  font-weight: 500;
}
.grn-meta-val {
  font-weight: 600;
  color: var(--txt);
}
</style>
@endsection

@section('content')
<!-- Page Header -->
<div class="ph">
  <div>
    <div class="ph-title">Goods Received Note: {{ $grn->grn_number }}</div>
    <div class="ph-sub">
      Processed on {{ $grn->created_at->format('d F Y, h:i A') }}
      <span class="ph-sub-dot"></span>
      Status: <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Processed & Stock Updated</span>
    </div>
  </div>
  <div class="ph-acts">
    <button onclick="window.print();" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      Print GRN
    </button>
    <a href="{{ route('purchase-orders.show', $grn->purchase_order_id) }}" class="btn-ghost">
      View Purchase Order
    </a>
  </div>
</div>

<!-- Print Only Header -->
<div class="print-header">
  <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 15px;">
    <div>
      <h1 style="font-size: 24px; font-weight: 800; color: #111;">DESSERTOPS ERP</h1>
      <p style="color: #666; font-size: 12px; margin-top: 3px;">Central Kitchen Inventory</p>
    </div>
    <div style="text-align: right;">
      <h2 style="font-size: 20px; font-weight: 700;">GOODS RECEIVED NOTE (GRN)</h2>
      <p style="font-family: monospace; font-size: 13px; font-weight: 600; margin-top: 3px;">{{ $grn->grn_number }}</p>
    </div>
  </div>
</div>

<div class="card">
  <div class="cb">
    <!-- Details Grid -->
    <div class="grn-details-grid">
      <!-- Vendor Info -->
      <div>
        <h3 style="font-size: 11px; text-transform: uppercase; color: var(--txt3); letter-spacing: 0.5px; margin-bottom: 8px;">Received From</h3>
        <div style="font-size: 15px; font-weight: 700; color: var(--txt);">{{ $grn->purchaseOrder->supplier->name }}</div>
        @if($grn->purchaseOrder->supplier->contact_person)
          <div style="font-size: 13px; color: var(--txt2); margin-top: 4px;">Contact: {{ $grn->purchaseOrder->supplier->contact_person }}</div>
        @endif
        @if($grn->purchaseOrder->supplier->phone)
          <div style="font-size: 12.5px; color: var(--txt2); margin-top: 2px; font-family: monospace;">Phone: {{ $grn->purchaseOrder->supplier->phone }}</div>
        @endif
      </div>
      
      <!-- Meta Info -->
      <div>
        <h3 style="font-size: 11px; text-transform: uppercase; color: var(--txt3); letter-spacing: 0.5px; margin-bottom: 8px;">GRN Parameters</h3>
        <div class="grn-meta-card">
          <div class="grn-meta-item">
            <span class="grn-meta-label">GRN Reference:</span>
            <span class="grn-meta-val" style="font-family: monospace;">{{ $grn->grn_number }}</span>
          </div>
          <div class="grn-meta-item">
            <span class="grn-meta-label">PO Reference:</span>
            <span class="grn-meta-val" style="font-family: monospace;">
              <a href="{{ route('purchase-orders.show', $grn->purchase_order_id) }}" style="color: inherit; text-decoration: underline;">
                {{ $grn->purchaseOrder->po_number }}
              </a>
            </span>
          </div>
          <div class="grn-meta-item">
            <span class="grn-meta-label">Received Date:</span>
            <span class="grn-meta-val">{{ $grn->received_date->format('d F Y') }}</span>
          </div>
          <div class="grn-meta-item">
            <span class="grn-meta-label">Received By:</span>
            <span class="grn-meta-val">{{ $grn->received_by }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Items Table -->
    <h3 style="font-size: 11px; text-transform: uppercase; color: var(--txt3); letter-spacing: 0.5px; margin-bottom: 8px; margin-top: 20px;">Received Line Items</h3>
    <table style="border: 1px solid var(--div2); border-radius: var(--radius); overflow: hidden;">
      <thead>
        <tr style="background: var(--pg-bg);">
          <th style="width: 15%; padding: 10px 14px;">SKU</th>
          <th style="width: 45%; padding: 10px 14px;">Material Description</th>
          <th style="width: 20%; padding: 10px 14px; text-align: right;">Ordered Qty</th>
          <th style="width: 20%; padding: 10px 14px; text-align: right;">Received Qty</th>
        </tr>
      </thead>
      <tbody>
        @foreach($grn->items as $item)
          @php
            // Find corresponding ordered item
            $orderedItem = $grn->purchaseOrder->items->where('material_id', $item->material_id)->first();
            $orderedQty = $orderedItem ? $orderedItem->quantity : 0;
            $mismatch = $orderedQty != $item->quantity_received;
          @endphp
        <tr style="{{ $mismatch ? 'background: var(--amber-lt);' : '' }}">
          <td class="mono" style="padding: 12px 14px;">{{ $item->material->sku }}</td>
          <td style="padding: 12px 14px;">
            <div style="font-weight: 600; color: var(--txt);">{{ $item->material->name }}</div>
            <div style="font-size: 11px; color: var(--txt3);">Category: {{ ucfirst($item->material->category) }}</div>
          </td>
          <td class="mono td2" style="padding: 12px 14px; text-align: right;">
            {{ number_format($orderedQty, 2) }} {{ $item->material->unit }}
          </td>
          <td class="mono font-semibold" style="padding: 12px 14px; text-align: right; color: {{ $mismatch ? 'var(--amber-tx)' : 'var(--txt)' }};">
            {{ number_format($item->quantity_received, 2) }} {{ $item->material->unit }}
            @if($mismatch)
              <div style="font-size: 10px; font-weight: bold; margin-top: 2px;">
                (Shortfall: {{ number_format($orderedQty - $item->quantity_received, 2) }})
              </div>
            @endif
          </td>
        </tr>
        @endforeach
        @if($grn->notes)
        <tr style="background: var(--pg-bg); border-top: 1px solid var(--div2);">
          <td colspan="4" style="padding: 12px 14px;">
            <div style="font-size: 12px; color: var(--txt2);">
              <strong>GRN Notes / Remarks:</strong> {{ $grn->notes }}
            </div>
          </td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>
</div>

<!-- Print Only Footer -->
<div class="print-footer">
  <div style="display: flex; justify-content: space-between; font-size: 12px; color: #555;">
    <div>
      <p>Received By: <strong>{{ $grn->received_by }}</strong></p>
      <p style="margin-top: 5px;">Signature: ____________________</p>
    </div>
    <div style="text-align: right;">
      <p>Verified By: ____________________</p>
      <p style="margin-top: 5px;">Inventory Manager Signature</p>
    </div>
  </div>
</div>
@endsection
