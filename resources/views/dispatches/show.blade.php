@extends('layouts.app')

@section('title', 'Shipment Sheet — DessertOps')
@section('breadcrumb', 'Shipment Sheet')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Shipment: {{ $dispatch->dispatch_number }}</div>
    <div class="ph-sub">Destination: <a href="{{ route('outlets.show', $dispatch->outlet_id) }}" style="color:inherit; font-weight:600;">{{ $dispatch->outlet->name }}</a></div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('dispatches.index') }}" class="btn-ghost">
      Back to Shipments
    </a>
  </div>
</div>

@if($dispatch->status === 'cancelled')
  <div class="alert alert-danger mb-4" style="display:flex; align-items:center; gap:8px;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" style="width:16px; height:16px; color:var(--red-tx)"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    <div style="font-size: 13px; font-weight: 600; color: var(--red-tx);">
      This dispatch request has been CANCELLED and will not be processed.
    </div>
  </div>
@else
<!-- Step Tracker -->
<div class="card mb-4" style="padding: 20px;">
  <div class="steps">
    <!-- Step 1: Created (Pending) -->
    <div class="step">
      <div class="step-col">
        <div class="step-dot s-done">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="step-lbl s-done-lbl">1. Prepared</div>
      </div>
      <div class="step-line {{ $dispatch->status !== 'pending' ? 'done' : '' }}"></div>
    </div>

    <!-- Step 2: Dispatched -->
    <div class="step">
      <div class="step-col">
        <div class="step-dot {{ $dispatch->status === 'dispatched' || $dispatch->status === 'received' ? 's-done' : 's-todo' }}">
          @if($dispatch->status === 'dispatched' || $dispatch->status === 'received')
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          @else
            <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/></svg>
          @endif
        </div>
        <div class="step-lbl {{ $dispatch->status === 'dispatched' || $dispatch->status === 'received' ? 's-done-lbl' : '' }}">2. In Transit</div>
      </div>
      <div class="step-line {{ $dispatch->status === 'received' ? 'done' : '' }}"></div>
    </div>

    <!-- Step 3: Received -->
    <div class="step">
      <div class="step-col">
        <div class="step-dot {{ $dispatch->status === 'received' ? 's-done' : 's-todo' }}">
          @if($dispatch->status === 'received')
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          @else
            <svg viewBox="0 0 24 24"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
          @endif
        </div>
        <div class="step-lbl {{ $dispatch->status === 'received' ? 's-done-lbl' : '' }}">3. Delivered</div>
      </div>
    </div>
  </div>
</div>
@endif

<div class="row r-3-1" style="grid-template-columns: 1fr 340px; gap: 16px;">
  <!-- Left Panel: Product Details -->
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
      </div>
      <div class="ch-title">Shipment Items Sheet</div>
    </div>
    <table>
      <thead>
        <tr>
          <th>SKU</th>
          <th>Dessert Product</th>
          <th style="text-align: right;">Quantity Sent</th>
          <th style="text-align: right;">Unit Cost (₹)</th>
          <th style="text-align: right;">Line Cost (₹)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($dispatch->items as $item)
        @php
          $isProduct = (bool)$item->product_id;
          $sku = $isProduct ? $item->product->sku : $item->material->sku;
          $name = $isProduct ? $item->product->name : $item->material->name;
          $qty = $item->quantity;
          $unit = $isProduct ? 'Units' : 'Pieces';
        @endphp
        <tr>
          <td class="mono">{{ $sku }}</td>
          <td style="font-weight:600;">
            {{ $name }}
            @if(!$isProduct)
              <span style="font-size:11px; font-weight:normal; color:var(--txt3);"> (Packaging)</span>
            @endif
          </td>
          <td class="mono font-semibold" style="text-align: right; color: var(--blue-tx); font-weight: 600;">
            {{ number_format($qty, 0) }} {{ $unit }}
          </td>
          <td class="mono" style="text-align: right;">
            ₹{{ number_format($item->unit_cost, 2) }}
          </td>
          <td class="mono font-semibold" style="text-align: right;">
            ₹{{ number_format($item->line_cost, 2) }}
          </td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr style="font-weight: 700; border-top: 1px solid var(--div);">
          <td colspan="4" style="text-align: right; padding: 12px;">Total Dispatch Cost:</td>
          <td class="mono font-bold" style="text-align: right; padding: 12px; color: var(--blue-tx);">
            ₹{{ number_format($dispatch->total_cost, 2) }}
          </td>
        </tr>
      </tfoot>
    </table>
  </div>

  <!-- Right Panel: Meta Info & Ship Actions -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    <!-- Meta Info Card -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Shipment Logistics</div>
      </div>
      <div class="cb" style="font-size: 13px;">
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Dispatch ID</div>
          <div style="font-family:'JetBrains Mono',monospace; font-weight:600;">{{ $dispatch->dispatch_number }}</div>
        </div>
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Destination Outlet</div>
          <div>{{ $dispatch->outlet->name }} ({{ ucfirst($dispatch->outlet->type) }})</div>
        </div>
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Shipment Date</div>
          <div class="mono">{{ $dispatch->dispatch_date->format('Y-m-d') }}</div>
        </div>
        @if($dispatch->notes)
        <div>
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Remarks</div>
          <div style="color:var(--txt2);">{{ $dispatch->notes }}</div>
        </div>
        @endif
      </div>
    </div>

    <!-- Actions Panel -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Status Actions</div>
      </div>
      <div class="cb">
        @if($dispatch->status === 'pending')
          <form action="{{ route('dispatches.dispatch', $dispatch->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px;">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/></svg>
              Mark as Dispatched
            </button>
          </form>
          <div class="alert alert-danger mt-4" style="font-size: 11.5px; padding: 8px 10px; margin-bottom:0; font-weight:normal; line-height:1.4;">
            <b>Attention:</b> Marking as dispatched will immediately verify and deduct stock from the **Central Kitchen Finished Goods** inventory.
          </div>
          <div style="margin-top: 12px; text-align: center;">
            <form action="{{ route('dispatches.destroy', $dispatch->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-ghost" style="color: var(--red-tx); font-weight: 600; font-size: 12px;">Cancel Order</button>
            </form>
          </div>

        @elseif($dispatch->status === 'cancelled')
          <div class="alert alert-danger" style="margin-bottom:0; display:flex; align-items:center; gap:8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" style="width:16px; height:16px; color:var(--red-tx)"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <div style="font-size: 12.5px; line-height:1.3;">
              <b style="color:var(--red-tx)">Order Cancelled</b>
              <div style="font-size:11px; font-weight:normal; color:var(--txt2); margin-top:2px;">This product request was cancelled and rejected by the admin.</div>
            </div>
          </div>

        @elseif($dispatch->status === 'dispatched')
          <div class="alert alert-success" style="margin-bottom:0; display:flex; align-items:center; gap:8px; border-color: var(--blue);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" style="width:16px; height:16px; color:var(--blue-tx)"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <div style="font-size: 12.5px; line-height:1.3;">
              <b style="color:var(--blue-tx)">In Transit</b>
              <div style="font-size:11px; font-weight:normal; color:var(--txt2); margin-top:2px;">Confirm receipt via the dedicated <b>Outlet Portal</b>.</div>
            </div>
          </div>

        @else
          <div class="alert alert-success" style="margin-bottom:0; display:flex; align-items:center; gap:8px; border-color: var(--green);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" style="width:16px; height:16px; color:var(--green-tx)"><polyline points="20 6 9 17 4 12"/></svg>
            <div style="font-size: 12.5px; line-height:1.3;">
              <b style="color:var(--green-tx)">Delivery Confirmed</b>
              <div style="font-size:11px; font-weight:normal; color:var(--green-tx); margin-top:2px;">Stock levels populated at target outlet.</div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    const dispatchId = {{ $dispatch->id }};
    const currentStatus = '{{ $dispatch->status }}';
    
    // Only poll if the dispatch is in a transitional state
    if (currentStatus === 'received' || currentStatus === 'cancelled') return;

    let pollInterval = setInterval(function() {
        fetch('/api/dispatches/' + dispatchId + '/status', {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status !== currentStatus) {
                // Status changed! Reload the page to update tracking bar & actions
                clearInterval(pollInterval);
                window.location.reload();
            }
        })
        .catch(() => {}); // Silently ignore network errors
    }, 5000); // Poll every 5 seconds
})();
</script>
@endsection
