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
@media (max-width: 768px) {
  .po-details-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}
</style>
@endsection

@section('content')
@php
  $poMsg = "*PURCHASE ORDER: " . $purchaseOrder->po_number . "*\n";
  $poMsg .= "------------------------------\n";
  $poMsg .= "*Supplier:* " . $purchaseOrder->supplier->name . "\n";
  $poMsg .= "*Date:* " . $purchaseOrder->created_at->format('d F Y') . "\n";
  $poMsg .= "*Estimated Delivery:* " . ($purchaseOrder->eta ? $purchaseOrder->eta->format('d F Y') : 'Immediate') . "\n";
  $poMsg .= "*Total Amount:* ₹" . number_format($purchaseOrder->total_amount, 2) . "\n\n";

  $poMsg .= "*Line Items:*\n";
  foreach($purchaseOrder->items as $item) {
      $poMsg .= "• " . $item->material->name . " (" . $item->material->sku . ")\n";
      $poMsg .= "  Qty: " . number_format($item->quantity, 2) . " " . $item->material->unit . " @ ₹" . number_format($item->unit_price, 2) . " (Subtotal: ₹" . number_format($item->subtotal, 2) . ")\n";
  }

  if($purchaseOrder->notes) {
      $poMsg .= "\n*Notes:* " . $purchaseOrder->notes . "\n";
  }

  $poMsg .= "\nGenerated via DessertOps ERP.";
  
  $phone = $purchaseOrder->supplier->phone ?? '';
  $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
  $whatsappUrl = "https://api.whatsapp.com/send?" . ($cleanPhone ? "phone=" . urlencode($cleanPhone) . "&" : "") . "text=" . rawurlencode($poMsg);
@endphp

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
    <button onclick="copyToClipboard({{ json_encode($poMsg) }});" class="btn-pri" style="background: var(--blue-tx); border-color: var(--blue-tx); color: #fff; display: inline-flex; align-items: center; cursor: pointer; border: none; margin-right: 8px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px; margin-right: 6px; vertical-align: middle;"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
      Copy PO Text
    </button>

    <a href="{{ $whatsappUrl }}" target="_blank" class="btn-pri" style="background: #25D366; border-color: #25D366; color: #fff; display: inline-flex; align-items: center; text-decoration: none;">
      <svg viewBox="0 0 24 24" fill="currentColor" style="width: 16px; height: 16px; margin-right: 6px; vertical-align: middle;">
        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.513 2.262 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.717-1.458L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.863-9.864.001-2.63-1.019-5.105-2.875-6.964-1.856-1.854-4.321-2.877-6.953-2.878-5.438 0-9.863 4.42-9.867 9.867-.001 1.986.518 3.926 1.502 5.642l-.99 3.616 3.701-.97c1.602.874 3.19 1.348 4.799 1.348zm8.686-7.02c-.276-.138-1.637-.808-1.89-.9-.253-.093-.437-.138-.62.138-.184.276-.713.9-.873 1.085-.16.184-.32.207-.597.069-.276-.138-1.168-.43-2.227-1.374-.823-.734-1.378-1.643-1.54-1.92-.162-.276-.017-.425.121-.562.124-.124.276-.322.414-.483.138-.161.184-.276.276-.46.092-.184.046-.345-.023-.483-.069-.138-.62-1.493-.849-2.046-.224-.537-.45-.463-.62-.472-.16-.008-.344-.01-.528-.01-.184 0-.483.069-.736.345-.253.276-.966.943-.966 2.3 0 1.357.988 2.668 1.126 2.852.138.184 1.944 2.97 4.71 4.16.659.283 1.173.452 1.575.58.662.21 1.265.18 1.741.11.53-.08 1.637-.67 1.867-1.318.23-.647.23-1.2.16-1.317-.07-.12-.25-.18-.53-.318z"/>
      </svg>
      Send via WhatsApp
    </a>

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

@section('scripts')
<script>
function copyToClipboard(text) {
    if (!navigator.clipboard) {
        // Fallback for older browsers or non-https contexts
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed'; // Avoid scrolling to bottom
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            document.execCommand('copy');
            showCopyAlert();
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }
        document.body.removeChild(textarea);
        return;
    }
    navigator.clipboard.writeText(text).then(function() {
        showCopyAlert();
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}

function showCopyAlert() {
    const alertDiv = document.createElement('div');
    alertDiv.style.position = 'fixed';
    alertDiv.style.bottom = '24px';
    alertDiv.style.right = '24px';
    alertDiv.style.background = '#25D366';
    alertDiv.style.color = '#fff';
    alertDiv.style.padding = '12px 24px';
    alertDiv.style.borderRadius = 'var(--radius)';
    alertDiv.style.boxShadow = '0 10px 15px -3px rgba(0,0,0,0.1)';
    alertDiv.style.zIndex = '99999';
    alertDiv.style.fontSize = '14px';
    alertDiv.style.fontWeight = '600';
    alertDiv.style.display = 'flex';
    alertDiv.style.alignItems = 'center';
    alertDiv.style.gap = '8px';
    alertDiv.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><polyline points="20 6 9 17 4 12"/></svg>
        PO Copied for WhatsApp!
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        alertDiv.style.transition = 'opacity 0.5s ease';
        setTimeout(() => alertDiv.remove(), 500);
    }, 2500);
}
</script>
@endsection
