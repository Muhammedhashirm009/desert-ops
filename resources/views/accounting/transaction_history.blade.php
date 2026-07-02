@extends('layouts.accounting')

@section('title', 'Transaction History — DessertOps Accounts')
@section('breadcrumb', 'Transaction History')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Transaction History</div>
    <div class="ph-sub">Complete log of all cash and bank movements</div>
  </div>
</div>

<!-- Date Range Filter -->
<div class="card" style="margin-bottom:20px;">
  <div class="cb" style="padding:14px 16px;">
    <form action="{{ route('accounting.transaction-history') }}" method="GET" class="filter-form">
      <div>
        <label style="display:block; font-weight:500; margin-bottom:6px; font-size:12px; color:var(--txt2);">Start Date</label>
        <input type="date" name="start_date" value="{{ $startDate }}" class="form-input" style="padding:7px 12px;">
      </div>
      <div>
        <label style="display:block; font-weight:500; margin-bottom:6px; font-size:12px; color:var(--txt2);">End Date</label>
        <input type="date" name="end_date" value="{{ $endDate }}" class="form-input" style="padding:7px 12px;">
      </div>
      <button type="submit" class="btn-pri" style="height:36px; padding:0 16px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Apply
      </button>
    </form>
  </div>
</div>

<!-- Transaction Table -->
<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2);width:13px;height:13px;"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    </div>
    <div class="ch-title">Transactions</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Date</th>
        <th>Description</th>
        <th style="text-align:right;">Cash</th>
        <th style="text-align:right;">Bank</th>
        <th style="text-align:right;">Balance</th>
      </tr>
    </thead>
    <tbody>
      @forelse($transactions as $tx)
      <tr onmouseover="this.style.background='var(--div)'" onmouseout="this.style.background=''">
        <td class="mono td2" style="white-space:nowrap;" data-label="Date">{{ \Carbon\Carbon::parse($tx['date'])->format('d M Y') }}</td>
        <td data-label="Description">
          <div style="font-weight:500;">{{ $tx['description'] }}</div>
          @if(!empty($tx['reference']))
            <div class="td-meta" style="font-size:11px;color:var(--txt3);">Ref: {{ $tx['reference'] }}</div>
          @endif
        </td>
        <td class="mono" style="text-align:right; font-weight:600;" data-label="Cash">
          @if($tx['cash_amount'] > 0)
            <span style="color:var(--green-tx);">+₹{{ number_format($tx['cash_amount'], 2) }}</span>
          @elseif($tx['cash_amount'] < 0)
            <span style="color:var(--red-tx);">-₹{{ number_format(abs($tx['cash_amount']), 2) }}</span>
          @else
            <span style="color:var(--txt3);">—</span>
          @endif
        </td>
        <td class="mono" style="text-align:right; font-weight:600;" data-label="Bank">
          @if($tx['bank_amount'] > 0)
            <span style="color:var(--green-tx);">+₹{{ number_format($tx['bank_amount'], 2) }}</span>
          @elseif($tx['bank_amount'] < 0)
            <span style="color:var(--red-tx);">-₹{{ number_format(abs($tx['bank_amount']), 2) }}</span>
          @else
            <span style="color:var(--txt3);">—</span>
          @endif
        </td>
        <td class="mono" style="text-align:right; font-weight:600; color:{{ $tx['running_balance'] < 0 ? 'var(--red-tx)' : 'var(--txt)' }};" data-label="Balance">
          ₹{{ number_format($tx['running_balance'], 2) }}
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="text-center td2" style="padding:24px;">No transactions found for this period.</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  <!-- Summary Footer -->
  <div class="grid-3-col" style="padding:14px 16px; background:var(--div); border-top:2px solid var(--div2);">
    <div>
      <span style="font-size:11px; color:var(--txt3); text-transform:uppercase; font-weight:600;">Cash in Hand:</span>
      <span class="mono" style="font-size:14px; font-weight:700; color:var(--green-tx); margin-left:6px;">₹{{ number_format($cashBalance, 2) }}</span>
    </div>
    <div>
      <span style="font-size:11px; color:var(--txt3); text-transform:uppercase; font-weight:600;">Bank Balance:</span>
      <span class="mono" style="font-size:14px; font-weight:700; color:var(--blue-tx); margin-left:6px;">₹{{ number_format($bankBalance, 2) }}</span>
    </div>
    <div>
      <span style="font-size:11px; color:var(--txt3); text-transform:uppercase; font-weight:600;">Total:</span>
      <span class="mono" style="font-size:14px; font-weight:700; color:var(--txt); margin-left:6px;">₹{{ number_format($cashBalance + $bankBalance, 2) }}</span>
    </div>
  </div>
</div>
@endsection
