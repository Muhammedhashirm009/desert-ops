@extends('layouts.portal')

@section('title', 'Incoming Shipments — DessertOps Portal')
@section('breadcrumb', 'Incoming Shipments')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Incoming Shipments</div>
    <div class="ph-sub">Verify and confirm receipt of finished dessert shipments sent from the Central Kitchen</div>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
    </div>
    <div class="ch-title">Shipment Records</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Dispatch Number</th>
        <th>Date Shipped</th>
        <th>Products Shipped</th>
        <th>Status</th>
        <th style="text-align: right;">Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($dispatches as $disp)
      <tr>
        <td data-label="Dispatch Number" class="mono font-semibold" style="font-weight: 600;">{{ $disp->dispatch_number }}</td>
        <td data-label="Date Shipped" class="mono">{{ $disp->dispatch_date->format('Y-m-d') }}</td>
        <td data-label="Products Shipped">
          <ul style="margin: 0; padding: 0 0 0 14px; font-size:12px; color: var(--txt2);">
            @foreach($disp->items as $item)
              @if($item->product_id)
                <li>{{ $item->product->name }}: <b>{{ number_format($item->quantity, 0) }} Units</b></li>
              @else
                <li>{{ $item->material->name }}: <b>{{ number_format($item->quantity, 0) }} Pieces</b></li>
              @endif
            @endforeach
          </ul>
        </td>
        <td data-label="Status">
          @if($disp->status === 'pending')
            <span class="badge bn">Pending (In Kitchen)</span>
          @elseif($disp->status === 'dispatched')
            <span class="badge ba"><svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/></svg>In Transit</span>
          @elseif($disp->status === 'cancelled')
            <span class="badge br"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>Cancelled</span>
          @else
            <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Received</span>
          @endif
        </td>
        <td data-label="Action" style="text-align: right;">
          @if($disp->status === 'dispatched')
            <form action="{{ route('portal.dispatches.receive', $disp->id) }}" method="POST" onsubmit="return confirm('Do you confirm that you have received this shipment and all item quantities match?');" style="display:inline;">
              @csrf
              <button type="submit" class="btn-pri" style="font-size:12px; font-weight:600; padding:6px 12px; background:var(--green); color:#fff; display:inline-flex; align-items:center; gap:4px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:12px; height:12px;"><polyline points="20 6 9 17 4 12"/></svg>
                Confirm Receipt
              </button>
            </form>
          @elseif($disp->status === 'received')
            <span class="badge bg" style="font-size:11.5px;"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Stocked</span>
          @elseif($disp->status === 'cancelled')
            <span style="font-size: 11.5px; color: var(--red-tx); font-weight: 600;">Cancelled/Rejected</span>
          @else
            <span class="td3">Awaiting dispatch</span>
          @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="text-center td2" style="padding: 24px;">No shipments routed to this outlet yet.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
