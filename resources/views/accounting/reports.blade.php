@extends('layouts.accounting')

@section('title', 'Summary Report — DessertOps Accounts')
@section('breadcrumb', 'Summary Report')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Summary Report</div>
    <div class="ph-sub">Income vs expenses breakdown for the selected period</div>
  </div>
</div>

<!-- Date Range Filter -->
<div class="card" style="margin-bottom:20px;">
  <div class="cb" style="padding:14px 16px;">
    <form action="{{ route('accounting.reports') }}" method="GET" class="filter-form">
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

<div class="grid-2-col" style="margin-bottom:14px;">
  <!-- Money Coming In -->
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--green-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--green-tx);width:13px;height:13px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
      </div>
      <div class="ch-title">Money Coming In</div>
    </div>
    <div class="cb" style="padding:0;">
      @forelse($incomes as $income)
      <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 16px; border-bottom:1px solid var(--div);">
        <span style="font-size:13px; color:var(--txt);">{{ $income['name'] }}</span>
        <span class="mono" style="font-size:13px; font-weight:600; color:var(--txt);">₹{{ number_format($income['amount'], 2) }}</span>
      </div>
      @empty
      <div style="padding:16px; text-align:center; color:var(--txt3); font-size:13px;">No income recorded in this period.</div>
      @endforelse

      <!-- Total -->
      <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; background:var(--green-lt); border-top:2px solid var(--green);">
        <span style="font-size:14px; font-weight:700; color:var(--green-tx);">Total Income</span>
        <span class="mono" style="font-size:16px; font-weight:700; color:var(--green-tx);">₹{{ number_format($totalIncome, 2) }}</span>
      </div>
    </div>
  </div>

  <!-- Money Going Out -->
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--red-lt);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--red-tx);width:13px;height:13px;"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
      </div>
      <div class="ch-title">Money Going Out</div>
    </div>
    <div class="cb" style="padding:0;">
      @forelse($expenses as $expense)
      <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 16px; border-bottom:1px solid var(--div);">
        <span style="font-size:13px; color:var(--txt);">{{ $expense['name'] }}</span>
        <span class="mono" style="font-size:13px; font-weight:600; color:var(--txt);">₹{{ number_format($expense['amount'], 2) }}</span>
      </div>
      @empty
      <div style="padding:16px; text-align:center; color:var(--txt3); font-size:13px;">No expenses recorded in this period.</div>
      @endforelse

      <!-- Total -->
      <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; background:var(--red-lt); border-top:2px solid var(--red);">
        <span style="font-size:14px; font-weight:700; color:var(--red-tx);">Total Expenses</span>
        <span class="mono" style="font-size:16px; font-weight:700; color:var(--red-tx);">₹{{ number_format($totalExpenses, 2) }}</span>
      </div>
    </div>
  </div>
</div>

<!-- Bottom Line -->
<div class="card" style="border-top:4px solid {{ $netProfit >= 0 ? 'var(--green)' : 'var(--red)' }};">
  <div class="cb" style="padding:28px; text-align:center;">
    <div style="font-size:12px; font-weight:600; color:var(--txt3); text-transform:uppercase; letter-spacing:1px; margin-bottom:10px;">
      {{ $netProfit >= 0 ? 'Net Profit' : 'Net Loss' }}
    </div>
    <div class="mono" style="font-size:42px; font-weight:800; color:{{ $netProfit >= 0 ? 'var(--green-tx)' : 'var(--red-tx)' }}; letter-spacing:-1px; line-height:1;">
      {{ $netProfit >= 0 ? '' : '-' }}₹{{ number_format(abs($netProfit), 2) }}
    </div>
    <div style="font-size:12px; color:var(--txt3); margin-top:10px;">
      Total Income: ₹{{ number_format($totalIncome, 2) }} — Total Expenses: ₹{{ number_format($totalExpenses, 2) }}
    </div>
    <div style="margin-top:12px;">
      @if($netProfit >= 0)
        <span class="badge bg" style="font-size:12px; padding:4px 12px;">Profitable Period</span>
      @else
        <span class="badge br" style="font-size:12px; padding:4px 12px;">Loss Period</span>
      @endif
    </div>
  </div>
</div>
@endsection
