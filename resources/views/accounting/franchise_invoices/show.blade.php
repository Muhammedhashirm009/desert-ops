@extends('layouts.accounting')

@section('title', 'Franchise Invoice Details — DessertOps Accounts')
@section('breadcrumb', 'Franchise Invoice Details')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Franchise Invoice: {{ $invoice->invoice_number }}</div>
    <div class="ph-sub">View franchise billing details and collection history</div>
  </div>
  <div class="ph-acts">
    @if($invoice->status !== 'paid')
      <a href="{{ route('accounting.receipt-vouchers.create', ['invoice_id' => $invoice->id]) }}" class="btn-pri">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Create Receipt Voucher
      </a>
    @endif
    <a href="{{ route('accounting.franchise-invoices.index') }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:13px;height:13px;"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Back to Invoices List
    </a>
  </div>
</div>

<div class="row r-2">
  <!-- Left Column: Invoice Details -->
  <div style="display:flex; flex-direction:column; gap:14px;">
    <div class="card" style="padding:24px;">
      <div class="ch" style="margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--div2);">
        <div class="ch-ic" style="background:var(--div);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
        </div>
        <div style="flex:1;">
          <div class="ch-title">Commission & Invoice Summary</div>
          <div class="ch-sub">Linked sales logs reference</div>
        </div>
        <div>
          @if($invoice->remaining_amount <= 0)
            <span class="badge bg" style="display:inline-flex; align-items:center; gap:4px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px;"><polyline points="20 6 9 17 4 12"/></svg> Collected</span>
          @elseif($invoice->remaining_amount < $invoice->amount)
            <span class="badge bp" style="display:inline-flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:currentColor; display:inline-block;"></span> Partially Collected</span>
          @else
            <span class="badge br" style="display:inline-flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:currentColor; display:inline-block;"></span> Uncollected</span>
          @endif
        </div>
      </div>

      <div class="grid-2-col" style="font-size:14px;">
        <div>
          <div style="color:var(--txt3); font-size:12px; margin-bottom:4px;">Franchise Outlet</div>
          <div style="font-weight:600;">{{ $invoice->outlet->name }}</div>
          <div style="font-size:12.5px; color:var(--txt2); margin-top:4px;">
            Type: Franchise Store<br>
            Commission Cut: {{ number_format($invoice->outlet->commission_rate, 1) }}%
          </div>
        </div>

        <div>
          <div style="color:var(--txt3); font-size:12px; margin-bottom:4px;">Sales Log Reference</div>
          <div>
            Sales Log: <a href="{{ route('sales-logs.show', $invoice->sales_log_id) }}" style="color:var(--blue-tx); font-weight:500; text-decoration:underline;">
              SL-{{ $invoice->sales_log_id }}
            </a>
          </div>
          <div style="margin-top:4px;">
            Log Date: <span class="mono" style="font-weight:500;">{{ $invoice->salesLog->log_date->format('Y-m-d') }}</span>
          </div>
        </div>

        <div>
          <div style="color:var(--txt3); font-size:12px; margin-bottom:4px;">Invoice Date</div>
          <div class="mono" style="font-weight:600;">{{ $invoice->created_at->format('Y-m-d') }}</div>
        </div>

        <div>
          <div style="color:var(--txt3); font-size:12px; margin-bottom:4px;">Due Date</div>
          <div class="mono" style="font-weight:600; {{ $invoice->status !== 'paid' && $invoice->due_date->isPast() ? 'color:var(--red-tx);' : '' }}">
            {{ $invoice->due_date->format('Y-m-d') }}
            @if($invoice->status !== 'paid' && $invoice->due_date->isPast())
              <span style="font-size:10px; font-weight:700; margin-left:6px;">[OVERDUE]</span>
            @endif
          </div>
        </div>
      </div>

      <!-- Financial Totals Box -->
      <div class="grid-3-col" style="margin-top:24px; background:var(--div); padding:16px; border-radius:var(--radius); text-align:center;">
        <div style="border-right:1px solid var(--div2);">
          <div style="font-size:11px; color:var(--txt3); text-transform:uppercase;">Total Commission Owed</div>
          <div class="mono" style="font-size:18px; font-weight:600; margin-top:4px;">₹{{ number_format($invoice->amount, 2) }}</div>
        </div>
        <div style="border-right:1px solid var(--div2);">
          <div style="font-size:11px; color:var(--txt3); text-transform:uppercase;">Collected</div>
          <div class="mono" style="font-size:18px; font-weight:600; margin-top:4px; color:var(--green-tx);">₹{{ number_format($invoice->paid_amount, 2) }}</div>
        </div>
        <div>
          <div style="font-size:11px; color:var(--txt3); text-transform:uppercase;">Receivable Balance</div>
          <div class="mono" style="font-size:18px; font-weight:600; margin-top:4px; color:{{ $invoice->remaining_amount > 0 ? 'var(--red-tx)' : 'var(--txt2)' }}">
            ₹{{ number_format($invoice->remaining_amount, 2) }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Column: Receipt History + Action -->
  <div style="display:flex; flex-direction:column; gap:14px;">
    <!-- Receipts List Card -->
    <div class="card">
      <div class="ch">
        <div class="ch-ic" style="background:var(--div);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="ch-title">Collection History</div>
      </div>
      <table class="tbl">
        <thead>
          <tr>
            <th>Receipt No.</th>
            <th>Date Collected</th>
            <th>Method</th>
            <th>Reference</th>
            <th style="text-align:right;">Amount</th>
          </tr>
        </thead>
        <tbody>
          @forelse($invoice->receipts as $rec)
          <tr>
            <td class="mono" style="font-weight:600;" data-label="Receipt No.">
              <a href="{{ route('accounting.receipt-vouchers.show', ['type' => 'franchise', 'id' => $rec->id]) }}" style="color:var(--blue-tx); text-decoration:underline;">
                {{ $rec->receipt_number }}
              </a>
            </td>
            <td class="mono td2" data-label="Date Collected">{{ $rec->receipt_date->format('Y-m-d') }}</td>
            <td data-label="Method"><span class="badge bb">{{ strtoupper($rec->payment_method) }}</span></td>
            <td class="mono td2" data-label="Reference">{{ $rec->reference ?? '—' }}</td>
            <td class="mono" style="text-align:right; font-weight:600; color:var(--green-tx);" data-label="Amount">₹{{ number_format($rec->amount, 2) }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center td2">No collection receipts logged for this invoice yet.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($invoice->status === 'paid')
      <div class="card" style="padding:24px; text-align:center; border-left:4px solid var(--green);">
        <div style="font-size:40px; color:var(--green-tx); margin-bottom:12px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div style="font-weight:600; font-size:16px;">Invoice Fully Collected</div>
        <div class="td2" style="margin-top:6px; font-size:13px;">This franchise commission invoice has been collected in full.</div>
      </div>
    @elseif($invoice->status === 'partially_paid')
      <div class="card" style="padding:24px; text-align:center; border-left:4px solid var(--amber);">
        <div style="font-size:40px; color:var(--amber-tx); margin-bottom:12px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg></div>
        <div style="font-weight:600; font-size:16px;">Invoice Partially Collected</div>
        <div class="td2" style="margin-top:6px; font-size:13px;">This franchise commission invoice has been partially collected. A receivable balance of <span class="mono font-semibold" style="color:var(--red-tx);">₹{{ number_format($invoice->remaining_amount, 2) }}</span> remains.</div>
      </div>
    @endif
  </div>
</div>
@endsection
