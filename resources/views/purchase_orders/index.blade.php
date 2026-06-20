@extends('layouts.app')

@section('title', 'Purchase Orders — DessertOps')
@section('breadcrumb', 'Purchase Orders')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Purchase Orders (POs)</div>
    <div class="ph-sub">Generate and track raw material purchases</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('purchase-orders.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Generate New PO
    </a>
  </div>
</div>

<!-- Filters Bar -->
<div class="card mb-4">
  <div class="cb" style="padding: 10px 16px; display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; gap: 8px;">
      <a href="{{ route('purchase-orders.index', ['status' => 'all']) }}" class="btn-ghost {{ request('status', 'all') === 'all' ? 'on' : '' }}" style="padding: 4px 10px; font-size: 12px; font-weight: 600;">
        All POs
      </a>
      <a href="{{ route('purchase-orders.index', ['status' => 'pending']) }}" class="btn-ghost {{ request('status') === 'pending' ? 'on' : '' }}" style="padding: 4px 10px; font-size: 12px; font-weight: 600;">
        Pending
      </a>
      <a href="{{ route('purchase-orders.index', ['status' => 'received']) }}" class="btn-ghost {{ request('status') === 'received' ? 'on' : '' }}" style="padding: 4px 10px; font-size: 12px; font-weight: 600;">
        Received (GRN)
      </a>
      <a href="{{ route('purchase-orders.index', ['status' => 'cancelled']) }}" class="btn-ghost {{ request('status') === 'cancelled' ? 'on' : '' }}" style="padding: 4px 10px; font-size: 12px; font-weight: 600;">
        Cancelled
      </a>
    </div>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
    </div>
    <div class="ch-title">Procurement History</div>
  </div>
  <table>
    <thead>
      <tr>
        <th>PO No.</th>
        <th>Supplier</th>
        <th>Total Amount</th>
        <th>ETA</th>
        <th>Status</th>
        <th>Created Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($purchaseOrders as $po)
      <tr>
        <td class="mono font-semibold">{{ $po->po_number }}</td>
        <td>
          <div class="td-name">{{ $po->supplier->name }}</div>
          <div class="td-meta">{{ $po->items->count() }} items</div>
        </td>
        <td class="mono font-semibold">₹{{ number_format($po->total_amount, 2) }}</td>
        <td class="mono td2">{{ $po->eta ? $po->eta->format('d M Y') : '—' }}</td>
        <td>
          @if($po->status === 'received')
            <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Received</span>
          @elseif($po->status === 'cancelled')
            <span class="badge br"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Cancelled</span>
          @else
            <span class="badge ba"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Pending</span>
          @endif
        </td>
        <td class="td3 mono">{{ $po->created_at->format('d M Y h:i A') }}</td>
        <td>
          <a href="{{ route('purchase-orders.show', $po->id) }}" class="td-act">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            View Details
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="text-center td2">No Purchase Orders found. <a href="{{ route('purchase-orders.create') }}">Generate a new PO now</a>.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
