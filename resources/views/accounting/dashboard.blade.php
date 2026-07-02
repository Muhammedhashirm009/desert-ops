@extends('layouts.accounting')

@section('title', 'Money Overview — DessertOps Accounts')
@section('breadcrumb', 'Money Overview')

@section('styles')
<style>
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 20px;
  }
  @media (max-width: 768px) {
    .kpi-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
@endsection

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Money Overview</div>
    <div class="ph-sub">Real-time snapshot of cash, bank, and outstanding balances</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('accounting.payment-vouchers.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Payment
    </a>
    <a href="{{ route('accounting.receipt-vouchers.create') }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Receipt
    </a>
  </div>
</div>

<!-- KPI Cards Grid: 2 rows × 3 columns -->
<div class="kpi-grid">

  <!-- 1. Cash in Hand -->
  <div class="card" style="border-left:3px solid var(--green); transition:transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
    <div class="cb" style="padding:18px 20px;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div style="font-size:12px; font-weight:600; color:var(--txt3); text-transform:uppercase; letter-spacing:.4px;">Cash in Hand</div>
        <div class="kpi-icon" style="background:var(--green-lt);"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--green-tx);width:15px;height:15px;"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
      </div>
      <div style="font-size:28px; font-weight:700; color:var(--green-tx); letter-spacing:-.8px; line-height:1;">
        <sup style="font-size:14px; font-weight:700;">₹</sup>{{ number_format($cashBalance, 2) }}
      </div>
      <div style="font-size:11.5px; color:var(--txt3); margin-top:6px;">Available cash balance</div>
    </div>
  </div>

  <!-- 2. Bank Balance -->
  <div class="card" style="border-left:3px solid var(--blue); transition:transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
    <div class="cb" style="padding:18px 20px;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div style="font-size:12px; font-weight:600; color:var(--txt3); text-transform:uppercase; letter-spacing:.4px;">Bank Balance</div>
        <div class="kpi-icon" style="background:var(--blue-lt);"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--blue-tx);width:15px;height:15px;"><rect x="2" y="2" width="20" height="20" rx="5"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="12" y1="2" x2="12" y2="22"/></svg></div>
      </div>
      <div style="font-size:28px; font-weight:700; color:var(--blue-tx); letter-spacing:-.8px; line-height:1;">
        <sup style="font-size:14px; font-weight:700;">₹</sup>{{ number_format($bankBalance, 2) }}
      </div>
      <div style="font-size:11.5px; color:var(--txt3); margin-top:6px;">Current bank balance</div>
    </div>
  </div>

  <div class="card" style="border-left:3px solid var(--green); transition:transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
    <div class="cb" style="padding:18px 20px;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div style="font-size:12px; font-weight:600; color:var(--txt3); text-transform:uppercase; letter-spacing:.4px;">This Month's Income</div>
        <div class="kpi-icon" style="background:var(--green-lt);"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--green-tx);width:15px;height:15px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
      </div>
      <div style="font-size:28px; font-weight:700; color:var(--green-tx); letter-spacing:-.8px; line-height:1;">
        <sup style="font-size:14px; font-weight:700;">₹</sup>{{ number_format($mtdRevenue, 2) }}
      </div>
      <div style="font-size:11.5px; color:var(--txt3); margin-top:6px;">Month-to-date revenue</div>
    </div>
  </div>

  <!-- 4. This Month's Expenses -->
  <div class="card" style="border-left:3px solid var(--red); transition:transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
    <div class="cb" style="padding:18px 20px;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div style="font-size:12px; font-weight:600; color:var(--txt3); text-transform:uppercase; letter-spacing:.4px;">This Month's Expenses</div>
        <div class="kpi-icon" style="background:var(--red-lt);"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--red-tx);width:15px;height:15px;"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg></div>
      </div>
      <div style="font-size:28px; font-weight:700; color:var(--red-tx); letter-spacing:-.8px; line-height:1;">
        <sup style="font-size:14px; font-weight:700;">₹</sup>{{ number_format($mtdExpenses, 2) }}
      </div>
      <div style="font-size:11.5px; color:var(--txt3); margin-top:6px;">Month-to-date spending</div>
    </div>
  </div>

  <!-- 5. Owed to Suppliers -->
  <div class="card" style="border-left:3px solid var(--amber); transition:transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
    <div class="cb" style="padding:18px 20px;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div style="font-size:12px; font-weight:600; color:var(--txt3); text-transform:uppercase; letter-spacing:.4px;">Owed to Suppliers</div>
        <div class="kpi-icon" style="background:var(--amber-lt);"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--amber-tx);width:15px;height:15px;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></div>
      </div>
      <div style="font-size:28px; font-weight:700; color:var(--amber-tx); letter-spacing:-.8px; line-height:1;">
        <sup style="font-size:14px; font-weight:700;">₹</sup>{{ number_format($outstandingAP, 2) }}
      </div>
      <div style="font-size:11.5px; color:var(--txt3); margin-top:6px;">{{ $unpaidBillsCount }} unpaid bill{{ $unpaidBillsCount != 1 ? 's' : '' }}</div>
    </div>
  </div>

  <!-- 6. Due from Franchises -->
  <div class="card" style="border-left:3px solid var(--purple); transition:transform 0.15s, box-shadow 0.15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
    <div class="cb" style="padding:18px 20px;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div style="font-size:12px; font-weight:600; color:var(--txt3); text-transform:uppercase; letter-spacing:.4px;">Due from Franchises</div>
        <div class="kpi-icon" style="background:var(--purple-lt);"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--purple-tx);width:15px;height:15px;"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg></div>
      </div>
      <div style="font-size:28px; font-weight:700; color:var(--purple-tx); letter-spacing:-.8px; line-height:1;">
        <sup style="font-size:14px; font-weight:700;">₹</sup>{{ number_format($outstandingAR, 2) }}
      </div>
      <div style="font-size:11.5px; color:var(--txt3); margin-top:6px;">{{ $unpaidInvoicesCount }} pending invoice{{ $unpaidInvoicesCount != 1 ? 's' : '' }}</div>
    </div>
  </div>

</div>

<!-- Recent Activity -->
<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2);width:13px;height:13px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <div class="ch-title">Recent Activity</div>
    <a href="{{ route('accounting.transaction-history') }}" class="ch-link">View All <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></a>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Date</th>
        <th>Description</th>
        <th style="text-align:right;">Cash</th>
        <th style="text-align:right;">Bank</th>
      </tr>
    </thead>
    <tbody>
      @forelse($recentActivity as $activity)
      <tr onmouseover="this.style.background='var(--div)'" onmouseout="this.style.background=''">
        <td class="mono td2" style="white-space:nowrap;" data-label="Date">{{ $activity['date'] }}</td>
        <td data-label="Description">
          <div style="font-weight:500;">{{ $activity['description'] }}</div>
          @if(!empty($activity['reference']))
            <div class="td-meta">Ref: {{ $activity['reference'] }}</div>
          @endif
        </td>
        <td class="mono" style="text-align:right; font-weight:600;" data-label="Cash">
          @if($activity['cash_amount'] > 0)
            <span style="color:var(--green-tx);">+₹{{ number_format($activity['cash_amount'], 2) }}</span>
          @elseif($activity['cash_amount'] < 0)
            <span style="color:var(--red-tx);">-₹{{ number_format(abs($activity['cash_amount']), 2) }}</span>
          @else
            <span style="color:var(--txt3);">—</span>
          @endif
        </td>
        <td class="mono" style="text-align:right; font-weight:600;" data-label="Bank">
          @if($activity['bank_amount'] > 0)
            <span style="color:var(--green-tx);">+₹{{ number_format($activity['bank_amount'], 2) }}</span>
          @elseif($activity['bank_amount'] < 0)
            <span style="color:var(--red-tx);">-₹{{ number_format(abs($activity['bank_amount']), 2) }}</span>
          @else
            <span style="color:var(--txt3);">—</span>
          @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="4" class="text-center td2" style="padding:24px;">No recent activity to display.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
