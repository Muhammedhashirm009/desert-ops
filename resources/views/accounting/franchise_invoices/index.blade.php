@extends('layouts.accounting')

@section('title', 'Franchise Invoices (Accounts Receivable) — DessertOps')
@section('breadcrumb', 'Franchise Accounts Receivable')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Franchise Invoices (Accounts Receivable)</div>
    <div class="ph-sub">Manage franchise sales commission shares and record payment receipts</div>
  </div>
</div>

<!-- Filters -->
<div style="display: flex; gap: 10px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 6px; -webkit-overflow-scrolling: touch;">
  <a href="{{ route('accounting.franchise-invoices.index') }}?status=all" class="btn-sec {{ !request()->has('status') || request()->status === 'all' ? 'on' : '' }}" style="border-radius:20px; padding:6px 16px;">
    All Invoices ({{ \App\Models\FranchiseInvoice::count() }})
  </a>
  <a href="{{ route('accounting.franchise-invoices.index') }}?status=unpaid" class="btn-sec {{ request()->status === 'unpaid' ? 'on' : '' }}" style="border-radius:20px; padding:6px 16px; display:flex; align-items:center; gap:6px;">
    <span class="dot red" style="width:8px; height:8px; border-radius:50%; display:inline-block; background:var(--red-tx);"></span>
    Unpaid ({{ \App\Models\FranchiseInvoice::where('status', 'unpaid')->count() }})
  </a>
  <a href="{{ route('accounting.franchise-invoices.index') }}?status=partially_paid" class="btn-sec {{ request()->status === 'partially_paid' ? 'on' : '' }}" style="border-radius:20px; padding:6px 16px; display:flex; align-items:center; gap:6px;">
    <span class="dot amber" style="width:8px; height:8px; border-radius:50%; display:inline-block; background:var(--amber-tx);"></span>
    Partially Paid ({{ \App\Models\FranchiseInvoice::where('status', 'partially_paid')->count() }})
  </a>
  <a href="{{ route('accounting.franchise-invoices.index') }}?status=paid" class="btn-sec {{ request()->status === 'paid' ? 'on' : '' }}" style="border-radius:20px; padding:6px 16px; display:flex; align-items:center; gap:6px;">
    <span class="dot green" style="width:8px; height:8px; border-radius:50%; display:inline-block; background:var(--green-tx);"></span>
    Paid ({{ \App\Models\FranchiseInvoice::where('status', 'paid')->count() }})
  </a>
</div>

<div class="card" style="border-top:3px solid var(--btn);">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
    </div>
    <div class="ch-title">Franchise Share Receivables</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Invoice No.</th>
        <th>Sales Log Ref</th>
        <th>Franchise Outlet</th>
        <th>Log Date</th>
        <th>Due Date</th>
        <th style="text-align: right;">Owed Amount (Net)</th>
        <th style="text-align: right;">Collected</th>
        <th style="text-align: right;">Balance Receivable</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($invoices as $inv)
      <tr class="{{ $inv->status !== 'paid' && $inv->due_date->isPast() ? 'row-low-stock' : '' }}" onmouseover="this.style.background='var(--div)'" onmouseout="this.style.background=''">
        <td data-label="Invoice No." class="mono font-semibold">{{ $inv->invoice_number }}</td>
        <td data-label="Sales Log Ref" class="mono td2">
          <a href="{{ route('sales-logs.show', $inv->sales_log_id) }}" style="color: inherit; text-decoration: underline;">
            SL-{{ $inv->sales_log_id }}
          </a>
        </td>
        <td data-label="Franchise Outlet">
          <div class="td-name">{{ $inv->outlet->name }}</div>
          <div class="td-meta">Commission rate: {{ number_format($inv->outlet->commission_rate, 1) }}%</div>
        </td>
        <td data-label="Log Date" class="mono td2">{{ $inv->created_at->format('Y-m-d') }}</td>
        <td data-label="Due Date" class="mono td2 {{ $inv->status !== 'paid' && $inv->due_date->isPast() ? 'font-semibold' : '' }}" style="{{ $inv->status !== 'paid' && $inv->due_date->isPast() ? 'color: var(--red-tx);' : '' }}">
          {{ $inv->due_date->format('Y-m-d') }}
          @if($inv->status !== 'paid' && $inv->due_date->isPast())
            <div style="font-size:10px; font-weight:600;">OVERDUE</div>
          @endif
        </td>
        <td data-label="Owed Amount" class="mono font-semibold" style="text-align: right;">₹{{ number_format($inv->amount, 2) }}</td>
        <td data-label="Collected" class="mono td2" style="text-align: right; color: var(--green-tx);">₹{{ number_format($inv->paid_amount, 2) }}</td>
        <td data-label="Balance Receivable" class="mono font-semibold" style="text-align: right; color: {{ $inv->remaining_amount > 0 ? 'var(--red-tx)' : 'var(--txt2)' }};">
          ₹{{ number_format($inv->remaining_amount, 2) }}
        </td>
        <td data-label="Status">
          @if($inv->status === 'paid')
            <span class="badge bg" style="display:inline-flex; align-items:center; gap:4px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px;"><polyline points="20 6 9 17 4 12"/></svg> Collected</span>
          @elseif($inv->status === 'partially_paid')
            <span class="badge bp" style="display:inline-flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:currentColor; display:inline-block;"></span> Partially Collected</span>
          @else
            <span class="badge br" style="display:inline-flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:currentColor; display:inline-block;"></span> Uncollected</span>
          @endif
        </td>
        <td data-label="Action">
          <a href="{{ route('accounting.franchise-invoices.show', $inv->id) }}" class="btn-sec" style="font-size: 12px; padding: 4px 10px;">
            @if($inv->status === 'paid')
              View Details
            @else
              Record Receipt
            @endif
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="10" style="padding:48px 24px; text-align:center;">
          <div style="margin-bottom:16px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--txt3)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;opacity:0.5;"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
          </div>
          <div style="font-size:15px;font-weight:600;color:var(--txt2);margin-bottom:6px;">No franchise invoices found</div>
          <div style="font-size:13px;color:var(--txt3);">All generated franchise sales log commission invoices will be logged here.</div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
