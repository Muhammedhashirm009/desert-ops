@extends('layouts.accounting')

@section('title', 'Account Transfers — DessertOps Accounts')
@section('breadcrumb', 'Account Transfers')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Account Transfers</div>
    <div class="ph-sub">Move funds between cash in hand and bank accounts, and view transfer history</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('accounting.transfers.create') }}" class="btn-pri" style="background: var(--acc-green); border-color: var(--acc-green);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Fund Transfer
    </a>
  </div>
</div>

<div class="card" style="border-top:3px solid var(--acc-green);">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="stroke:var(--txt2);width:13px;height:13px;"><path d="M17 3L21 7L17 11"/><path d="M3 17L7 21L3 17Z"/><path d="M21 7H9"/><path d="M3 17H15"/><path d="M7 21L3 17L7 13"/></svg>
    </div>
    <div class="ch-title">Transfer Records</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Reference #</th>
        <th>Date</th>
        <th>From Account</th>
        <th>To Account</th>
        <th style="text-align:right;">Amount</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      @forelse($transfers as $t)
      <tr onmouseover="this.style.background='var(--div)'" onmouseout="this.style.background=''">
        <td data-label="Reference #" class="mono" style="font-weight:600;">{{ $t->reference }}</td>
        <td data-label="Date" class="mono td2" style="white-space:nowrap;">{{ \Carbon\Carbon::parse($t->date)->format('d M Y') }}</td>
        <td data-label="From Account">
          @if(str_contains(strtolower($t->from_account), 'cash'))
            <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:var(--div);color:var(--txt2);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="10" x2="4" y2="10"/><line x1="20" y1="10" x2="23" y2="10"/></svg>
              {{ $t->from_account }}
            </span>
          @else
            <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:var(--div);color:var(--txt2);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polygon points="12 2 2 7 22 7"/><line x1="4" y1="7" x2="4" y2="17"/><line x1="8" y1="7" x2="8" y2="17"/><line x1="12" y1="7" x2="12" y2="17"/><line x1="16" y1="7" x2="16" y2="17"/><line x1="20" y1="7" x2="20" y2="17"/><rect x="2" y="17" width="20" height="3"/></svg>
              {{ $t->from_account }}
            </span>
          @endif
        </td>
        <td data-label="To Account">
          @if(str_contains(strtolower($t->to_account), 'cash'))
            <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:var(--div);color:var(--txt2);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="10" x2="4" y2="10"/><line x1="20" y1="10" x2="23" y2="10"/></svg>
              {{ $t->to_account }}
            </span>
          @else
            <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:var(--div);color:var(--txt2);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polygon points="12 2 2 7 22 7"/><line x1="4" y1="7" x2="4" y2="17"/><line x1="8" y1="7" x2="8" y2="17"/><line x1="12" y1="7" x2="12" y2="17"/><line x1="16" y1="7" x2="16" y2="17"/><line x1="20" y1="7" x2="20" y2="17"/><rect x="2" y="17" width="20" height="3"/></svg>
              {{ $t->to_account }}
            </span>
          @endif
        </td>
        <td data-label="Amount" class="mono font-semibold" style="text-align:right; color: var(--txt);">₹{{ number_format($t->amount, 2) }}</td>
        <td data-label="Description" class="td2">{{ $t->description }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="6" style="text-align:center; padding: 40px 20px; color: var(--txt3);">
          <div style="margin-bottom:12px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;opacity:0.5;"><path d="M17 3L21 7L17 11"/><path d="M3 17L7 21L3 13"/><path d="M21 7H9"/><path d="M3 17H15"/></svg>
          </div>
          No fund transfers recorded yet.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
