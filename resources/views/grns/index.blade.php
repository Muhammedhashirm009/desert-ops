@extends('layouts.app')

@section('title', 'Goods Received Notes — DessertOps')
@section('breadcrumb', 'Goods Received Notes (GRN)')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Goods Received Notes (GRN)</div>
    <div class="ph-sub">Verify and record raw material deliveries into inventory</div>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
    </div>
    <div class="ch-title">Goods Received Log</div>
  </div>
  <table>
    <thead>
      <tr>
        <th>GRN No.</th>
        <th>PO Ref</th>
        <th>Supplier</th>
        <th>Received By</th>
        <th>Received Date</th>
        <th>Created Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($grns as $grn)
      <tr>
        <td class="mono font-semibold">{{ $grn->grn_number }}</td>
        <td class="mono td2">
          <a href="{{ route('purchase-orders.show', $grn->purchase_order_id) }}" style="color: inherit; text-decoration: underline;">
            {{ $grn->purchaseOrder->po_number }}
          </a>
        </td>
        <td class="td-name">{{ $grn->purchaseOrder->supplier->name }}</td>
        <td>{{ $grn->received_by }}</td>
        <td class="mono">{{ $grn->received_date->format('d M Y') }}</td>
        <td class="td3 mono">{{ $grn->created_at->format('d M Y h:i A') }}</td>
        <td>
          <a href="{{ route('grns.show', $grn->id) }}" class="td-act">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            View GRN
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="text-center td2">No Goods Received Notes recorded yet. Complete a pending Purchase Order to generate a GRN.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
