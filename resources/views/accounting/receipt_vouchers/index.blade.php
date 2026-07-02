@extends('layouts.accounting')

@section('title', 'Receipt Vouchers — DessertOps Accounts')
@section('breadcrumb', 'Receipt Vouchers')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Receipt Vouchers</div>
    <div class="ph-sub">All recorded receipts — own income and franchise collections</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('accounting.receipt-vouchers.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Receipt Voucher
    </a>
  </div>
</div>

<div class="card" style="border-top:3px solid var(--btn);">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2);width:13px;height:13px;"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
    </div>
    <div class="ch-title">Receipt Records</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Voucher #</th>
        <th>Date</th>
        <th>Received From</th>
        <th>Type</th>
        <th style="text-align:right;">Amount</th>
        <th>Received Via</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($vouchers as $v)
      <tr onmouseover="this.style.background='var(--div)'" onmouseout="this.style.background=''">
        <td class="mono" style="font-weight:600;" data-label="Voucher #">{{ $v->voucher_number }}</td>
        <td class="mono td2" style="white-space:nowrap;" data-label="Date">{{ \Carbon\Carbon::parse($v->date)->format('d M Y') }}</td>
        <td data-label="Received From">
          <div class="td-name">{{ $v->received_from }}</div>
        </td>
        <td data-label="Type">
          @if($v->type === 'franchise')
            <span class="badge bp">Franchise</span>
          @else
            <span class="badge bg">Own Income</span>
          @endif
        </td>
        <td class="mono" style="text-align:right; font-weight:600;" data-label="Amount">₹{{ number_format($v->amount, 2) }}</td>
        <td data-label="Received Via">
          @if($v->received_via === 'cash')
            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:var(--div);color:var(--txt2);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="10" x2="4" y2="10"/><line x1="20" y1="10" x2="23" y2="10"/></svg> Cash</span>
          @else
            <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:var(--div);color:var(--txt2);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><polygon points="12 2 2 7 22 7"/><line x1="4" y1="7" x2="4" y2="17"/><line x1="8" y1="7" x2="8" y2="17"/><line x1="12" y1="7" x2="12" y2="17"/><line x1="16" y1="7" x2="16" y2="17"/><line x1="20" y1="7" x2="20" y2="17"/><rect x="2" y="17" width="20" height="3"/></svg> Bank</span>
          @endif
        </td>
        <td data-label="Action">
          <a href="{{ route('accounting.receipt-vouchers.show', ['type' => $v->source_type, 'id' => $v->source_id]) }}" class="td-act">
            View <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" style="padding:48px 24px; text-align:center;">
          <div style="margin-bottom:16px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--txt3)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;opacity:0.5;"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
          </div>
          <div style="font-size:15px;font-weight:600;color:var(--txt2);margin-bottom:6px;">No receipt vouchers yet</div>
          <div style="font-size:13px;color:var(--txt3);margin-bottom:16px;">Record your first incoming receipt to get started.</div>
          <a href="{{ route('accounting.receipt-vouchers.create') }}" class="btn-pri">Create your first receipt voucher</a>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
