@extends('layouts.accounting')

@section('title', 'Supplier Bill Details — DessertOps Accounts')
@section('breadcrumb', 'Bill Details')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Supplier Bill: {{ $bill->bill_number }}</div>
    <div class="ph-sub">View bill details and payment history</div>
  </div>
  <div class="ph-acts">
    @if($bill->status !== 'paid')
      <a href="{{ route('accounting.payment-vouchers.create', ['bill_id' => $bill->id]) }}" class="btn-pri">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Create Payment Voucher
      </a>
    @endif
    <a href="{{ route('accounting.bills.index') }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:13px;height:13px;"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Back to Bills List
    </a>
  </div>
</div>

<div class="row r-2">
  <!-- Left Column: Bill Details -->
  <div style="display:flex; flex-direction:column; gap:14px;">
    <div class="card" style="padding:24px;">
      <div class="ch" style="margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--div2);">
        <div class="ch-ic" style="background:var(--div);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div style="flex:1;">
          <div class="ch-title">Invoice & PO Summary</div>
          <div class="ch-sub">Linked procurement references</div>
        </div>
        <div>
          @if($bill->remaining_amount <= 0)
            <span class="badge bg" style="display:inline-flex; align-items:center; gap:4px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px;"><polyline points="20 6 9 17 4 12"/></svg> Paid</span>
          @elseif($bill->remaining_amount < $bill->amount)
            <span class="badge bp" style="display:inline-flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:currentColor; display:inline-block;"></span> Partially Paid</span>
          @else
            <span class="badge br" style="display:inline-flex; align-items:center; gap:4px;"><span style="width:6px; height:6px; border-radius:50%; background:currentColor; display:inline-block;"></span> Unpaid</span>
          @endif
        </div>
      </div>

      <div class="grid-2-col" style="font-size:14px;">
        <div>
          <div style="color:var(--txt3); font-size:12px; margin-bottom:4px;">Supplier</div>
          <div style="font-weight:600;">{{ $bill->supplier->name }}</div>
          <div style="font-size:12.5px; color:var(--txt2); margin-top:4px;">
            {{ $bill->supplier->contact_person }}<br>
            {{ $bill->supplier->phone }} | {{ $bill->supplier->email }}
          </div>
        </div>

        <div>
          <div style="color:var(--txt3); font-size:12px; margin-bottom:4px;">PO / GRN References</div>
          <div>
            PO: <a href="{{ route('purchase-orders.show', $bill->purchase_order_id) }}" style="color:var(--blue-tx); font-weight:500; text-decoration:underline;">
              {{ $bill->purchaseOrder->po_number }}
            </a>
          </div>
          <div style="margin-top:4px;">
            GRN: @if($bill->purchaseOrder->grn)
              <span class="mono" style="font-weight:500;">{{ $bill->purchaseOrder->grn->grn_number }}</span>
            @else
              <span class="td2">N/A</span>
            @endif
          </div>
        </div>

        <div>
          <div style="color:var(--txt3); font-size:12px; margin-bottom:4px;">Bill Date</div>
          <div class="mono" style="font-weight:600;">{{ $bill->created_at->format('Y-m-d') }}</div>
        </div>

        <div>
          <div style="color:var(--txt3); font-size:12px; margin-bottom:4px;">Due Date</div>
          <div class="mono" style="font-weight:600; {{ $bill->status !== 'paid' && $bill->due_date->isPast() ? 'color:var(--red-tx);' : '' }}">
            {{ $bill->due_date->format('Y-m-d') }}
            @if($bill->status !== 'paid' && $bill->due_date->isPast())
              <span style="font-size:10px; font-weight:700; margin-left:6px;">[OVERDUE]</span>
            @endif
          </div>
        </div>
      </div>

      <!-- Financial Totals Box -->
      <div class="grid-3-col" style="margin-top:24px; background:var(--div); padding:16px; border-radius:var(--radius); text-align:center;">
        <div style="border-right:1px solid var(--div2);">
          <div style="font-size:11px; color:var(--txt3); text-transform:uppercase;">Total Invoice</div>
          <div class="mono" style="font-size:18px; font-weight:600; margin-top:4px;">₹{{ number_format($bill->amount, 2) }}</div>
        </div>
        <div style="border-right:1px solid var(--div2);">
          <div style="font-size:11px; color:var(--txt3); text-transform:uppercase;">Amount Paid</div>
          <div class="mono" style="font-size:18px; font-weight:600; margin-top:4px; color:var(--green-tx);">₹{{ number_format($bill->paid_amount, 2) }}</div>
        </div>
        <div>
          <div style="font-size:11px; color:var(--txt3); text-transform:uppercase;">Remaining Balance</div>
          <div class="mono" style="font-size:18px; font-weight:600; margin-top:4px; color:{{ $bill->remaining_amount > 0 ? 'var(--red-tx)' : 'var(--txt2)' }}">
            ₹{{ number_format($bill->remaining_amount, 2) }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Column: Payment History + Action -->
  <div style="display:flex; flex-direction:column; gap:14px;">
    <!-- Payments List Card -->
    <div class="card">
      <div class="ch">
        <div class="ch-ic" style="background:var(--div);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="ch-title">Payment History</div>
      </div>
      <table class="tbl">
        <thead>
          <tr>
            <th>Payment No.</th>
            <th>Date</th>
            <th>Method</th>
            <th>Reference</th>
            <th style="text-align:right;">Amount</th>
          </tr>
        </thead>
        <tbody>
          @forelse($bill->payments as $pay)
          <tr>
            <td class="mono" style="font-weight:600;" data-label="Payment No.">
              <a href="{{ route('accounting.payment-vouchers.show', ['type' => 'supplier', 'id' => $pay->id]) }}" style="color:var(--blue-tx); text-decoration:underline;">
                {{ $pay->payment_number }}
              </a>
            </td>
            <td class="mono td2" data-label="Date">{{ $pay->payment_date->format('Y-m-d') }}</td>
            <td data-label="Method"><span class="badge bb">{{ strtoupper($pay->payment_method) }}</span></td>
            <td class="mono td2" data-label="Reference">{{ $pay->reference ?? '—' }}</td>
            <td class="mono" style="text-align:right; font-weight:600; color:var(--green-tx);" data-label="Amount">₹{{ number_format($pay->amount, 2) }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center td2">No payments processed for this bill yet.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($bill->status === 'paid')
      <div class="card" style="padding:24px; text-align:center; border-left:4px solid var(--green);">
        <div style="font-size:40px; color:var(--green-tx); margin-bottom:12px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div style="font-weight:600; font-size:16px;">Invoice Fully Settled</div>
        <div class="td2" style="margin-top:6px; font-size:13px;">This supplier bill has been paid in full. No outstanding balance remains.</div>
      </div>
    @elseif($bill->status === 'partially_paid')
      <div class="card" style="padding:24px; text-align:center; border-left:4px solid var(--amber);">
        <div style="font-size:40px; color:var(--amber-tx); margin-bottom:12px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg></div>
        <div style="font-weight:600; font-size:16px;">Invoice Partially Settled</div>
        <div class="td2" style="margin-top:6px; font-size:13px;">This supplier bill has been partially paid. An outstanding balance of <span class="mono font-semibold" style="color:var(--red-tx);">₹{{ number_format($bill->remaining_amount, 2) }}</span> remains.</div>
      </div>
    @endif

  </div>
</div>
@endsection
