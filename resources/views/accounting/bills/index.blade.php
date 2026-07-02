@extends('layouts.accounting')

@section('title', 'Supplier Bills (Accounts Payable) — DessertOps')
@section('breadcrumb', 'Accounts Payable')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Supplier Bills (Accounts Payable)</div>
    <div class="ph-sub">Manage raw material purchase invoices and process supplier payments</div>
  </div>
</div>

<!-- Filters -->
<div style="display: flex; gap: 10px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 6px; -webkit-overflow-scrolling: touch;">
  <a href="{{ route('accounting.bills.index') }}?status=all" class="btn-sec {{ !request()->has('status') || request()->status === 'all' ? 'on' : '' }}" style="border-radius:20px; padding:6px 16px;">
    All Bills ({{ \App\Models\SupplierBill::count() }})
  </a>
  <a href="{{ route('accounting.bills.index') }}?status=unpaid" class="btn-sec {{ request()->status === 'unpaid' ? 'on' : '' }}" style="border-radius:20px; padding:6px 16px; display:flex; align-items:center; gap:6px;">
    <span class="dot red" style="width:8px; height:8px; border-radius:50%; display:inline-block; background:var(--red-tx);"></span>
    Unpaid ({{ \App\Models\SupplierBill::where('status', 'unpaid')->count() }})
  </a>
  <a href="{{ route('accounting.bills.index') }}?status=partially_paid" class="btn-sec {{ request()->status === 'partially_paid' ? 'on' : '' }}" style="border-radius:20px; padding:6px 16px; display:flex; align-items:center; gap:6px;">
    <span class="dot amber" style="width:8px; height:8px; border-radius:50%; display:inline-block; background:var(--amber-tx);"></span>
    Partially Paid ({{ \App\Models\SupplierBill::where('status', 'partially_paid')->count() }})
  </a>
  <a href="{{ route('accounting.bills.index') }}?status=paid" class="btn-sec {{ request()->status === 'paid' ? 'on' : '' }}" style="border-radius:20px; padding:6px 16px; display:flex; align-items:center; gap:6px;">
    <span class="dot green" style="width:8px; height:8px; border-radius:50%; display:inline-block; background:var(--green-tx);"></span>
    Paid ({{ \App\Models\SupplierBill::where('status', 'paid')->count() }})
  </a>
</div>

<div class="card" style="border-top:3px solid var(--btn);">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
    </div>
    <div class="ch-title">Supplier Invoices</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Bill No.</th>
        <th>PO Ref</th>
        <th>Supplier</th>
        <th>Bill Date</th>
        <th>Due Date</th>
        <th style="text-align: right;">Bill Amount</th>
        <th style="text-align: right;">Paid Amount</th>
        <th style="text-align: right;">Balance Due</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($bills as $bill)
      <tr class="{{ $bill->status !== 'paid' && $bill->due_date->isPast() ? 'row-low-stock' : '' }}" onmouseover="this.style.background='var(--div)'" onmouseout="this.style.background=''">
        <td data-label="Bill No." class="mono font-semibold">{{ $bill->bill_number }}</td>
        <td data-label="PO Ref" class="mono td2">
          <a href="{{ route('purchase-orders.show', $bill->purchase_order_id) }}" style="color: inherit; text-decoration: underline;">
            {{ $bill->purchaseOrder->po_number }}
          </a>
        </td>
        <td data-label="Supplier">
          <div class="td-name">{{ $bill->supplier->name }}</div>
          <div class="td-meta">Contact: {{ $bill->supplier->contact_person }}</div>
        </td>
        <td data-label="Bill Date" class="mono td2">{{ $bill->created_at->format('Y-m-d') }}</td>
        <td data-label="Due Date" class="mono td2 {{ $bill->status !== 'paid' && $bill->due_date->isPast() ? 'font-semibold' : '' }}" style="{{ $bill->status !== 'paid' && $bill->due_date->isPast() ? 'color: var(--red-tx);' : '' }}">
          {{ $bill->due_date->format('Y-m-d') }}
          @if($bill->status !== 'paid' && $bill->due_date->isPast())
            <div style="font-size:10px; font-weight:600;">OVERDUE</div>
          @endif
        </td>
        <td data-label="Bill Amount" class="mono font-semibold" style="text-align: right;">₹{{ number_format($bill->amount, 2) }}</td>
        <td data-label="Paid Amount" class="mono td2" style="text-align: right; color: var(--green-tx);">₹{{ number_format($bill->paid_amount, 2) }}</td>
        <td data-label="Balance Due" class="mono font-semibold" style="text-align: right; color: {{ $bill->remaining_amount > 0 ? 'var(--red-tx)' : 'var(--txt2)' }};">
          ₹{{ number_format($bill->remaining_amount, 2) }}
        </td>
        <td data-label="Status">
          @if($bill->status === 'paid')
            <span class="badge bg" style="display:inline-flex; align-items:center; gap:4px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px;"><polyline points="20 6 9 17 4 12"/></svg> Paid</span>
          @elseif($bill->status === 'partially_paid')
            <span class="badge bp" style="display:inline-flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:currentColor; display:inline-block;"></span> Partially Paid</span>
          @else
            <span class="badge br" style="display:inline-flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:currentColor; display:inline-block;"></span> Unpaid</span>
          @endif
        </td>
        <td data-label="Action">
          <a href="{{ route('accounting.bills.show', $bill->id) }}" class="btn-sec" style="font-size: 12px; padding: 4px 10px;">
            @if($bill->status === 'paid')
              View Details
            @else
              Process Payment
            @endif
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="10" style="padding:48px 24px; text-align:center;">
          <div style="margin-bottom:16px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--txt3)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;opacity:0.5;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
          </div>
          <div style="font-size:15px;font-weight:600;color:var(--txt2);margin-bottom:6px;">No supplier bills found</div>
          <div style="font-size:13px;color:var(--txt3);">All raw material purchase bills will be logged here.</div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
