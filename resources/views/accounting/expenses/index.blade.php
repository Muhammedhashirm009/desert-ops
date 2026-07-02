@extends('layouts.accounting')

@section('title', 'Expense Vouchers — DessertOps')
@section('breadcrumb', 'Operational Expenses')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Expense Vouchers</div>
    <div class="ph-sub">Log and track manual operational expenditures (Rent, Utilities, Wages, Petty Cash)</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('accounting.expenses.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Log Expense Voucher
    </a>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
    </div>
    <div class="ch-title">Recorded Vouchers</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Voucher No.</th>
        <th>Date</th>
        <th>Payee</th>
        <th>Expense Category</th>
        <th>Paid From</th>
        <th>Amount</th>
        <th>Notes</th>
        <th>Logged By</th>
      </tr>
    </thead>
    <tbody>
      @forelse($expenses as $exp)
      <tr onmouseover="this.style.background='var(--div)'" onmouseout="this.style.background=''">
        <td class="mono font-semibold" data-label="Voucher No.">{{ $exp->voucher_number }}</td>
        <td class="mono td2" data-label="Date">{{ $exp->date->format('Y-m-d') }}</td>
        <td data-label="Payee"><div class="td-name">{{ $exp->payee }}</div></td>
        <td data-label="Expense Category">
          <span style="font-weight: 500;">{{ $exp->expenseAccount->name }}</span>
          <div style="font-size: 11px; color: var(--txt3);">Code: {{ $exp->expenseAccount->code }}</div>
        </td>
        <td data-label="Paid From">
          <span style="font-weight: 500;">{{ $exp->paymentAccount->name }}</span>
          <div style="font-size: 11px; color: var(--txt3);">Code: {{ $exp->paymentAccount->code }}</div>
        </td>
        <td class="mono font-semibold" style="color: var(--red-tx);" data-label="Amount">
          ₹{{ number_format($exp->amount, 2) }}
        </td>
        <td class="td2" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" data-label="Notes">
          {{ $exp->notes ?? '—' }}
        </td>
        <td data-label="Logged By">
          <span class="badge bb" style="font-size: 11px;">{{ $exp->user->name ?? 'System' }}</span>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="8" class="text-center td2">No expense vouchers logged yet. <a href="{{ route('accounting.expenses.create') }}">Create one now</a>.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
