@extends('layouts.app')

@section('title', 'Product Dispatches — DessertOps')
@section('breadcrumb', 'Product Dispatches')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Dessert Shipments & Dispatches</div>
    <div class="ph-sub">Manage transfers of prepared desserts from the Central Kitchen to retail outlets and franchises</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('dispatches.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Create Dispatch Shipment
    </a>
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
        <th>Destination Outlet</th>
        <th>Shipment Date</th>
        <th style="text-align: center;">Total Items</th>
        <th>Status</th>
        <th style="text-align: right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($dispatches as $disp)
      <tr>
        <td data-label="Dispatch Number" class="mono">
          <a href="{{ route('dispatches.show', $disp->id) }}" style="font-weight:600; color:var(--txt); text-decoration:none;">
            {{ $disp->dispatch_number }}
          </a>
        </td>
        <td data-label="Destination Outlet">
          <div class="td-name">{{ $disp->outlet->name }}</div>
          <div class="td-meta">Type: {{ ucfirst($disp->outlet->type) }}</div>
        </td>
        <td data-label="Shipment Date" class="mono">{{ $disp->dispatch_date->format('Y-m-d') }}</td>
        <td data-label="Total Items" class="mono" style="text-align: center; font-weight: 600;">
          {{ $disp->items->count() }} ({{ number_format($disp->items->sum('quantity'), 0) }} Units)
        </td>
        <td data-label="Status">
          @if($disp->status === 'pending')
            <span class="badge bn"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Pending (In Kitchen)</span>
          @elseif($disp->status === 'dispatched')
            <span class="badge ba"><svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/></svg>In Transit (Dispatched)</span>
          @elseif($disp->status === 'cancelled')
            <span class="badge br"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>Cancelled</span>
          @else
            <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Received (At Outlet)</span>
          @endif
        </td>
        <td data-label="Actions">
          <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <a href="{{ route('dispatches.show', $disp->id) }}" class="td-act">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              Manage Shipment
            </a>
            @if($disp->status === 'pending')
              <form action="{{ route('dispatches.destroy', $disp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this pending shipment?');" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="td-act po-row-btn" style="padding:0; font-size:13px;">
                  <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  Cancel
                </button>
              </form>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" class="text-center td2">No shipments created yet. <a href="{{ route('dispatches.create') }}">Create one now</a>.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
