@extends('layouts.app')

@section('title', 'Purchase Order ' . $purchaseOrder->po_number . ' — DessertOps')
@section('breadcrumb', 'PO Details')

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
.po-details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 25px;
}
.po-meta-card {
  padding: 15px;
  background: var(--pg-bg);
  border-radius: var(--radius);
  border: 1px solid var(--div2);
}
.po-meta-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 13px;
}
.po-meta-item:last-child {
  margin-bottom: 0;
}
.po-meta-label {
  color: var(--txt3);
  font-weight: 500;
}
.po-meta-val {
  font-weight: 600;
  color: var(--txt);
}
</style>
@endsection

@section('content')
<!-- Page Header -->
<div class="ph">
  <div>
    <div class="ph-title">Purchase Order: {{ $purchaseOrder->po_number }}</div>
    <div class="ph-sub">
      Created on {{ $purchaseOrder->created_at->format('d F Y, h:i A') }}
      <span class="ph-sub-dot"></span>
      Status: 
      @if($purchaseOrder->status === 'received')
        <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Received</span>
      @elseif($purchaseOrder->status === 'cancelled')
        <span class="badge br"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Cancelled</span>
      @else
        <span class="badge ba"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Pending</span>
      @endif
    </div>
  </div>
  <div class="ph-acts">
    <button onclick="window.print();" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      Print PO
    </button>
    
    @if($purchaseOrder->status === 'pending')
      <a href="{{ route('purchase-orders.receive', $purchaseOrder->id) }}" class="btn-pri" style="background: var(--green); color: #fff;">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
        Receive Goods (GRN)
      </a>
      
      <form action="{{ route('purchase-orders.destroy', $purchaseOrder->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this Purchase Order?');" style="display: inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-ghost" style="color: var(--red-tx); border-color: var(--red-tx);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          Cancel PO
        </button>
      </form>
    @endif
  </div>
</div>

<!-- Print Only Header -->
<div class="print-header">
  <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 15px;">
    <div>
      <h1 style="font-size: 24px; font-weight: 800; color: #111;">DESSERTOPS ERP</h1>
      <p style="color: #666; font-size: 12px; margin-top: 3px;">Central Procurement Office</p>
    </div>
    <div style="text-align: right;">
      <h2 style="font-size: 20px; font-weight: 700;">PURCHASE ORDER</h2>
      <p style="font-family: monospace; font-size: 13px; font-weight: 600; margin-top: 3px;">{{ $purchaseOrder->po_number }}</p>
    </div>
  </div>
</div>

<div class="card">
  <div class="cb">
    <!-- PO Details Grid -->
    <div class="po-details-grid">
      <!-- Supplier Info -->
      <div>
        <h3 style="font-size: 11px; text-transform: uppercase; color: var(--txt3); letter-spacing: 0.5px; margin-bottom: 8px;">Vendor / Supplier</h3>
        <div style="font-size: 15px; font-weight: 700; color: var(--txt);">{{ $purchaseOrder->supplier->name }}</div>
        @if($purchaseOrder->supplier->contact_person)
          <div style="font-size: 13px; color: var(--txt2); margin-top: 4px;">Attn: {{ $purchaseOrder->supplier->contact_person }}</div>
        @endif
        @if($purchaseOrder->supplier->email || $purchaseOrder->supplier->phone)
          <div style="font-size: 12.5px; color: var(--txt2); margin-top: 2px; font-family: monospace;">
            {{ $purchaseOrder->supplier->email }} {{ $purchaseOrder->supplier->phone ? ' | ' . $purchaseOrder->supplier->phone : '' }}
          </div>
        @endif
        @if($purchaseOrder->supplier->address)
          <div style="font-size: 12.5px; color: var(--txt3); margin-top: 6px; white-space: pre-line;">{{ $purchaseOrder->supplier->address }}</div>
        @endif
      </div>
      
      <!-- Meta Information -->
      <div>
        <h3 style="font-size: 11px; text-transform: uppercase; color: var(--txt3); letter-spacing: 0.5px; margin-bottom: 8px;">Order Parameters</h3>
        <div class="po-meta-card">
          <div class="po-meta-item">
            <span class="po-meta-label">PO Reference:</span>
            <span class="po-meta-val" style="font-family: monospace;">{{ $purchaseOrder->po_number }}</span>
          </div>
          <div class="po-meta-item">
            <span class="po-meta-label">Created Date:</span>
            <span class="po-meta-val">{{ $purchaseOrder->created_at->format('d F Y') }}</span>
          </div>
          <div class="po-meta-item">
            <span class="po-meta-label">Estimated Delivery:</span>
            <span class="po-meta-val" style="color: var(--blue-tx);">{{ $purchaseOrder->eta ? $purchaseOrder->eta->format('d F Y') : 'Immediate' }}</span>
          </div>
          <div class="po-meta-item">
            <span class="po-meta-label">Total Amount:</span>
            <span class="po-meta-val" style="font-family: monospace; color: var(--green-tx);">₹{{ number_format($purchaseOrder->total_amount, 2) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- PO Items Table -->
    <h3 style="font-size: 11px; text-transform: uppercase; color: var(--txt3); letter-spacing: 0.5px; margin-bottom: 8px; margin-top: 20px;">Line Items</h3>
    <table style="border: 1px solid var(--div2); border-radius: var(--radius); overflow: hidden;">
      <thead>
        <tr style="background: var(--pg-bg);">
          <th style="width: 15%; padding: 10px 14px;">SKU</th>
          <th style="width: 45%; padding: 10px 14px;">Material Description</th>
          <th style="width: 12%; padding: 10px 14px; text-align: right;">Quantity</th>
          <th style="width: 13%; padding: 10px 14px; text-align: right;">Unit Price (₹)</th>
          <th style="width: 15%; padding: 10px 14px; text-align: right;">Subtotal (₹)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($purchaseOrder->items as $item)
        <tr>
          <td class="mono" style="padding: 12px 14px;">{{ $item->material->sku }}</td>
          <td style="padding: 12px 14px;">
            <div style="font-weight: 600; color: var(--txt);">{{ $item->material->name }}</div>
            <div style="font-size: 11px; color: var(--txt3);">Category: {{ ucfirst($item->material->category) }}</div>
          </td>
          <td class="mono" style="padding: 12px 14px; text-align: right;">{{ number_format($item->quantity, 2) }} {{ $item->material->unit }}</td>
          <td class="mono" style="padding: 12px 14px; text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
          <td class="mono font-semibold" style="padding: 12px 14px; text-align: right;">₹{{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
        <tr style="background: var(--pg-bg); border-top: 2px solid var(--div2);">
          <td colspan="3" style="padding: 12px 14px;">
            @if($purchaseOrder->notes)
              <div style="font-size: 12px; color: var(--txt2); font-style: italic;">
                <strong>PO Notes:</strong> {{ $purchaseOrder->notes }}
              </div>
            @endif
          </td>
          <td style="padding: 12px 14px; text-align: right; font-weight: 700; font-size: 13px;">GRAND TOTAL:</td>
          <td class="mono font-bold" style="padding: 12px 14px; text-align: right; font-weight: 700; font-size: 14.5px; color: var(--txt);">
            ₹{{ number_format($purchaseOrder->total_amount, 2) }}
          </td>
        </tr>
      </tbody>
    </table>
    
    @if($purchaseOrder->grn)
      <div class="mt-4" style="background: var(--green-lt); border: 1px solid var(--green); border-radius: var(--radius); padding: 12px 16px; display: flex; justify-content: space-between; align-items: center;">
        <div style="color: var(--green-tx); font-size: 13px;">
          <strong>Goods Received Note:</strong> Delivered and processed under <strong>{{ $purchaseOrder->grn->grn_number }}</strong> on {{ $purchaseOrder->grn->received_date->format('d F Y') }}.
        </div>
        <a href="{{ route('grns.show', $purchaseOrder->grn->id) }}" class="btn-ghost" style="padding: 4px 10px; font-size: 12px; border-color: var(--green); color: var(--green-tx); background: #fff;">
          View GRN Detail
        </a>
      </div>
    @endif
  </div>
</div>

<!-- Print Only Footer -->
<div class="print-footer">
  <div style="display: flex; justify-content: space-between; font-size: 12px; color: #555;">
    <div>
      <p>Prepared By: ____________________</p>
      <p style="margin-top: 5px;">Date: {{ now()->format('d-m-Y') }}</p>
    </div>
    <div style="text-align: right;">
      <p>Approved By: ____________________</p>
      <p style="margin-top: 5px;">Signature & Stamp</p>
    </div>
  </div>
</div>
@endsection
