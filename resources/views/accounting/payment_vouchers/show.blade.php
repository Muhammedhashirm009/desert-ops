@extends('layouts.accounting')

@section('title', 'Payment Voucher — DessertOps Accounts')
@section('breadcrumb', 'Payment Voucher')

@section('styles')
<style>
  @media print {
    .sb, .tb, .ph, .no-print { display: none !important; }
    .main { margin: 0; padding: 0; }
    .content { padding: 0; }
    .voucher-card { box-shadow: none !important; border: 1px solid #ccc !important; max-width: 100% !important; }
  }
</style>
@endsection

@section('content')
<div class="ph no-print">
  <div>
    <div class="ph-title">Payment Voucher: {{ $voucher->voucher_number }}</div>
    <div class="ph-sub">Printable payment record</div>
  </div>
</div>

<div class="card voucher-card" style="max-width:600px; margin:0 auto; border-top:4px solid var(--btn); position:relative; overflow:hidden;">
  <!-- Watermark -->
  <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-30deg);font-size:72px;font-weight:900;color:var(--red-lt);opacity:0.35;pointer-events:none;letter-spacing:8px;white-space:nowrap;z-index:0;">PAID</div>
  <!-- Voucher Header -->
  <div style="padding:24px 24px 16px; text-align:center; border-bottom:2px solid var(--div2);">
    <div style="font-size:20px; font-weight:800; color:var(--txt); letter-spacing:1px;">DESSERTOPS</div>
    <div style="font-size:13px; font-weight:600; color:var(--txt3); text-transform:uppercase; letter-spacing:2px; margin-top:4px;">Payment Voucher</div>
  </div>

  <!-- Voucher Details -->
  <div style="padding:20px 24px;">
    <table style="width:100%; border-collapse:collapse;">
      <tbody>
        <tr>
          <td style="padding:8px 0; font-weight:600; color:var(--txt2); width:140px; border-bottom:1px solid var(--div); font-size:13px;">Voucher No:</td>
          <td style="padding:8px 0; font-weight:600; color:var(--txt); border-bottom:1px solid var(--div); font-family:'JetBrains Mono',monospace; font-size:13px;">{{ $voucher->voucher_number }}</td>
        </tr>
        <tr>
          <td style="padding:8px 0; font-weight:600; color:var(--txt2); border-bottom:1px solid var(--div); font-size:13px;">Date:</td>
          <td style="padding:8px 0; color:var(--txt); border-bottom:1px solid var(--div); font-size:13px;">{{ \Carbon\Carbon::parse($voucher->date)->format('d M Y') }}</td>
        </tr>
        <tr>
          <td style="padding:8px 0; font-weight:600; color:var(--txt2); border-bottom:1px solid var(--div); font-size:13px;">Paid To:</td>
          <td style="padding:8px 0; font-weight:600; color:var(--txt); border-bottom:1px solid var(--div); font-size:13px;">{{ $voucher->payee }}</td>
        </tr>
        @if(!empty($voucher->category))
        <tr>
          <td style="padding:8px 0; font-weight:600; color:var(--txt2); border-bottom:1px solid var(--div); font-size:13px;">Category:</td>
          <td style="padding:8px 0; color:var(--txt); border-bottom:1px solid var(--div); font-size:13px;">{{ $voucher->category }}</td>
        </tr>
        @endif
        <tr>
          <td style="padding:8px 0; font-weight:600; color:var(--txt2); border-bottom:1px solid var(--div); font-size:13px;">Amount:</td>
          <td style="padding:8px 0; font-weight:800; color:var(--red-tx); border-bottom:1px solid var(--div); font-size:22px; font-family:'JetBrains Mono',monospace;">₹{{ number_format($voucher->amount, 2) }}</td>
        </tr>
        <tr>
          <td style="padding:8px 0; font-weight:600; color:var(--txt2); border-bottom:1px solid var(--div); font-size:13px;">Paid Via:</td>
          <td style="padding:8px 0; color:var(--txt); border-bottom:1px solid var(--div); font-size:13px;">
            @if($voucher->paid_via === 'cash') <span style="display:inline-flex;align-items:center;gap:5px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="10" x2="4" y2="10"/><line x1="20" y1="10" x2="23" y2="10"/></svg> Cash</span> @else <span style="display:inline-flex;align-items:center;gap:5px;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><polygon points="12 2 2 7 22 7"/><line x1="4" y1="7" x2="4" y2="17"/><line x1="8" y1="7" x2="8" y2="17"/><line x1="12" y1="7" x2="12" y2="17"/><line x1="16" y1="7" x2="16" y2="17"/><line x1="20" y1="7" x2="20" y2="17"/><rect x="2" y="17" width="20" height="3"/></svg> Bank</span> @endif
          </td>
        </tr>
        @if(!empty($voucher->reference))
        <tr>
          <td style="padding:8px 0; font-weight:600; color:var(--txt2); border-bottom:1px solid var(--div); font-size:13px;">Reference:</td>
          <td style="padding:8px 0; color:var(--txt); border-bottom:1px solid var(--div); font-family:'JetBrains Mono',monospace; font-size:13px;">{{ $voucher->reference }}</td>
        </tr>
        @endif
      </tbody>
    </table>

    @if(!empty($voucher->notes))
    <div style="margin-top:16px; padding:12px; background:var(--div); border-radius:var(--radius-sm);">
      <div style="font-size:11px; font-weight:600; color:var(--txt3); text-transform:uppercase; margin-bottom:4px;">Notes</div>
      <div style="font-size:13px; color:var(--txt); line-height:1.5;">{{ $voucher->notes }}</div>
    </div>
    @endif
  </div>

  <!-- Footer -->
  <div style="padding:12px 24px 20px; border-top:2px solid var(--div2);">
    <div style="font-size:11.5px; color:var(--txt3); text-align:center;">
      Prepared on: {{ \Carbon\Carbon::parse($voucher->created_at)->format('d M Y, h:i A') }}
    </div>
  </div>
</div>

<!-- Action Buttons -->
<div class="no-print" style="max-width:600px; margin:16px auto 0; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
  <button onclick="window.print()" class="btn-pri">
    <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px;height:14px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
    Print Voucher
  </button>
  <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn-ghost">
    <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:13px;height:13px;"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Back to List
  </a>
</div>
@endsection
