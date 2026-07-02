@extends('layouts.accounting')

@section('title', 'General Ledger — DessertOps')
@section('breadcrumb', 'General Ledger')

@section('styles')
<style>
  @media (max-width: 768px) {
    .ledger-footer-title {
      padding-left: 0 !important;
      text-align: center !important;
      font-weight: 700 !important;
      font-size: 14px !important;
      background: var(--div2) !important;
    }
  }
</style>
@endsection

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">General Ledger</div>
    <div class="ph-sub">View account transactions, debit/credit audit lines, and running balances</div>
  </div>
</div>

<!-- Filters Card -->
<div class="card" style="padding: 20px; margin-bottom: 24px;">
  <form action="{{ route('accounting.ledger') }}" method="GET" class="filter-form">
    <div>
      <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 13px; color: var(--txt2);">Select Account</label>
      <select name="account_id" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt);">
        <option value="">-- Select GL Account --</option>
        @foreach($accounts as $acc)
          <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
            {{ $acc->code }} — {{ $acc->name }} ({{ strtoupper($acc->type) }})
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 13px; color: var(--txt2);">Start Date</label>
      <input type="date" name="start_date" value="{{ $startDate }}"
             style="width: 100%; padding: 8px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt); font-family: var(--mono);" />
    </div>

    <div>
      <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 13px; color: var(--txt2);">End Date</label>
      <input type="date" name="end_date" value="{{ $endDate }}"
             style="width: 100%; padding: 8px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt); font-family: var(--mono);" />
    </div>

    <button type="submit" class="btn-pri" style="height: 38px; padding: 0 20px; display: flex; align-items: center; gap: 8px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      Generate Statement
    </button>
  </form>
</div>

@if($selectedAccount)
  <div class="card">
    <div class="ch" style="justify-content: space-between;">
      <div style="display:flex; align-items:center; gap:12px;">
        <div class="ch-ic" style="background:var(--div);">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
        <div>
          <div class="ch-title">{{ $selectedAccount->code }} — {{ $selectedAccount->name }}</div>
          <div class="ch-sub">Statement from {{ $startDate }} to {{ $endDate }}</div>
        </div>
      </div>
      <div>
        <span class="badge bp" style="font-size: 11px; text-transform: uppercase;">{{ $selectedAccount->type }} Account</span>
      </div>
    </div>
    <table class="tbl">
      <thead>
        <tr>
          <th>Date</th>
          <th>Reference No.</th>
          <th>Description</th>
          <th style="text-align: right; width: 150px;">Debit</th>
          <th style="text-align: right; width: 150px;">Credit</th>
          <th style="text-align: right; width: 180px;">Running Balance</th>
        </tr>
      </thead>
      <tbody>
        <!-- Opening Balance -->
        <tr style="background: var(--div); border-bottom: 1px solid var(--div2);">
          <td class="mono td2" data-label="Date">{{ $startDate }}</td>
          <td class="mono td2" data-label="Reference No.">—</td>
          <td style="font-weight: 500;" data-label="Description">Opening Balance (Brought Forward)</td>
          <td style="text-align: right;" data-label="Debit">—</td>
          <td style="text-align: right;" data-label="Credit">—</td>
          <td class="mono font-semibold" style="text-align: right; font-size:14px;" data-label="Running Balance">
            ₹{{ number_format($runningBalance, 2) }}
          </td>
        </tr>

        <!-- Transaction Lines -->
        @php
          $currentBalance = $runningBalance;
          $totalDeb = 0;
          $totalCred = 0;
        @endphp
        
        @forelse($entries as $entry)
          @php
            $debit = (float)$entry->debit;
            $credit = (float)$entry->credit;
            $totalDeb += $debit;
            $totalCred += $credit;
            
            // running balance math based on account type
            if ($selectedAccount->type === 'asset' || $selectedAccount->type === 'expense') {
                $currentBalance += ($debit - $credit);
            } else {
                $currentBalance += ($credit - $debit);
            }
          @endphp
          <tr style="border-bottom: 1px solid var(--div2);" onmouseover="this.style.background='var(--div)'" onmouseout="this.style.background=''">
            <td class="mono td2" data-label="Date">{{ $entry->journalTransaction->date->format('Y-m-d') }}</td>
            <td class="mono font-semibold" data-label="Reference No.">{{ $entry->journalTransaction->reference ?? 'JV-' . $entry->journalTransaction->id }}</td>
            <td data-label="Description">{{ $entry->journalTransaction->description }}</td>
            <td class="mono" style="text-align: right; color: {{ $debit > 0 ? 'var(--green-tx)' : 'var(--txt3)' }}; font-weight: {{ $debit > 0 ? '600' : 'normal' }};" data-label="Debit">
              {{ $debit > 0 ? '₹' . number_format($debit, 2) : '—' }}
            </td>
            <td class="mono" style="text-align: right; color: {{ $credit > 0 ? 'var(--purple-tx)' : 'var(--txt3)' }}; font-weight: {{ $credit > 0 ? '600' : 'normal' }};" data-label="Credit">
              {{ $credit > 0 ? '₹' . number_format($credit, 2) : '—' }}
            </td>
            <td class="mono font-semibold" style="text-align: right; font-size:14px; color: {{ $currentBalance < 0 ? 'var(--red-tx)' : 'var(--txt)' }}" data-label="Running Balance">
              ₹{{ number_format($currentBalance, 2) }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center td2" style="padding: 24px;">No transactions logged in this date range.</td>
          </tr>
        @endforelse
      </tbody>
      
      <!-- Totals Footer -->
      <tfoot>
        <tr style="background: var(--div); font-weight: 600; border-top: 2px solid var(--sb-border);">
          <td colspan="3" class="ledger-footer-title" style="text-align: right; padding-right: 20px;">Period Transaction Totals:</td>
          <td class="mono" style="text-align: right; color: var(--green-tx); font-size:14px;" data-label="Total Debit">₹{{ number_format($totalDeb, 2) }}</td>
          <td class="mono" style="text-align: right; color: var(--purple-tx); font-size:14px;" data-label="Total Credit">₹{{ number_format($totalCred, 2) }}</td>
          <td class="mono font-semibold" style="text-align: right; font-size:15px; background: var(--div2);" data-label="Ending Balance">
            ₹{{ number_format($currentBalance, 2) }}
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
@else
  <div class="card" style="padding: 40px; text-align: center; color: var(--txt3);">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 48px; height: 48px; margin: 0 auto 16px; opacity: 0.6;"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
    <div style="font-weight: 500; font-size: 15px;">No Account Selected</div>
    <div style="font-size: 13px; margin-top: 6px;">Please choose an account and date range from the filters above to generate a statement.</div>
  </div>
@endif
@endsection
