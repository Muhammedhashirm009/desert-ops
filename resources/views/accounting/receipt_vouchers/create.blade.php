@extends('layouts.accounting')

@section('title', 'New Receipt Voucher — DessertOps Accounts')
@section('breadcrumb', 'New Receipt Voucher')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">New Receipt Voucher</div>
    <div class="ph-sub">Record money coming in — own branch income or franchise collection</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('accounting.receipt-vouchers.index') }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:13px;height:13px;"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Back to List
    </a>
  </div>
</div>

<div class="card" style="max-width:700px;">
  <!-- Tab Toggle -->
  <div style="display:flex; border-bottom:2px solid var(--div);">
    <button type="button" id="tab-own" onclick="switchTab('own')"
            style="flex:1; padding:13px 16px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; border-bottom:3px solid var(--btn); color:var(--txt); font-family:inherit;">
      Record Own Income
    </button>
    <button type="button" id="tab-franchise" onclick="switchTab('franchise')"
            style="flex:1; padding:13px 16px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; border-bottom:3px solid transparent; color:var(--txt3); font-family:inherit;">
      Collect from Franchise
    </button>
  </div>

  <!-- Own Income Form -->
  <div id="form-own" class="cb" style="padding:24px;">
    <form action="{{ route('accounting.receipt-vouchers.store') }}" method="POST">
      @csrf
      <input type="hidden" name="receipt_type" value="own_income">

      <div class="grid-2-col" style="margin-bottom:16px;">
        <div class="form-grp">
          <label>Voucher Number</label>
          <input type="text" name="voucher_number" value="{{ $voucherNumber }}" readonly class="form-input" style="background:var(--div); color:var(--txt2); font-family:'JetBrains Mono',monospace; font-weight:600;">
        </div>
        <div class="form-grp">
          <label>Date</label>
          <input type="date" name="date" value="{{ now()->toDateString() }}" required max="{{ now()->toDateString() }}" class="form-input">
          @error('date')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-grp" style="margin-bottom:16px;">
        <label>Received From</label>
        <input type="text" name="received_from" placeholder="e.g. Main Branch — Daily Sales" required value="{{ old('received_from') }}" class="form-input">
        @error('received_from')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>

      <div class="grid-2-col" style="margin-bottom:16px;">
        <div class="form-grp">
          <label>Amount (₹)</label>
          <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required value="{{ old('amount') }}" class="form-input" style="font-family:'JetBrains Mono',monospace; font-weight:600;">
          @error('amount')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <div class="form-grp">
          <label>Received Via</label>
          <div style="display:flex; gap:16px; margin-top:8px;">
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:500;">
              <input type="radio" name="received_via" value="cash" {{ old('received_via', 'cash') === 'cash' ? 'checked' : '' }} style="accent-color:var(--btn);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="10" x2="4" y2="10"/><line x1="20" y1="10" x2="23" y2="10"/></svg> Cash
            </label>
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:500;">
              <input type="radio" name="received_via" value="bank" {{ old('received_via') === 'bank' ? 'checked' : '' }} style="accent-color:var(--btn);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><polygon points="12 2 2 7 22 7"/><line x1="4" y1="7" x2="4" y2="17"/><line x1="8" y1="7" x2="8" y2="17"/><line x1="12" y1="7" x2="12" y2="17"/><line x1="16" y1="7" x2="16" y2="17"/><line x1="20" y1="7" x2="20" y2="17"/><rect x="2" y="17" width="20" height="3"/></svg> Bank
            </label>
          </div>
          @error('received_via')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-grp" style="margin-bottom:20px;">
        <label>Notes</label>
        <textarea name="notes" rows="3" placeholder="Optional description or details" class="form-input" style="resize:vertical;">{{ old('notes') }}</textarea>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--div); padding-top:16px;">
        <a href="{{ route('accounting.receipt-vouchers.index') }}" class="btn-ghost">Cancel</a>
        <button type="submit" class="btn-pri">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px;height:14px;"><polyline points="20 6 9 17 4 12"/></svg>
          Save Receipt Voucher
        </button>
      </div>
    </form>
  </div>

  <!-- Franchise Collection Form -->
  <div id="form-franchise" class="cb" style="padding:24px; display:none;">
    <form action="{{ route('accounting.receipt-vouchers.store') }}" method="POST">
      @csrf
      <input type="hidden" name="receipt_type" value="franchise">

      <div class="form-grp" style="margin-bottom:16px;">
        <label>Voucher Number</label>
        <input type="text" name="voucher_number" value="{{ $voucherNumber }}" readonly class="form-input" style="background:var(--div); color:var(--txt2); font-family:'JetBrains Mono',monospace; font-weight:600;">
      </div>

      <div class="form-grp" style="margin-bottom:16px;">
        <label>Select Invoice</label>
        <select name="franchise_invoice_id" id="invoice-select" required class="form-input" onchange="updateInvoiceMax()">
          <option value="" data-remaining="0">— Select Unpaid Invoice —</option>
          @foreach($unpaidInvoices as $inv)
            <option value="{{ $inv->id }}" data-remaining="{{ $inv->remaining_amount }}" {{ old('franchise_invoice_id') == $inv->id ? 'selected' : '' }}>
              {{ $inv->invoice_number }} — {{ $inv->outlet->name }} (₹{{ number_format($inv->remaining_amount, 2) }} remaining)
            </option>
          @endforeach
        </select>
        @error('franchise_invoice_id')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>

      <div class="grid-2-col" style="margin-bottom:16px;">
        <div class="form-grp">
          <label>Amount (₹)</label>
          <input type="number" name="amount" id="invoice-amount" step="0.01" min="0.01" placeholder="0.00" required value="{{ old('amount') }}" class="form-input" style="font-family:'JetBrains Mono',monospace; font-weight:600;">
          <div id="invoice-max-hint" style="font-size:11.5px; color:var(--txt3); margin-top:4px;"></div>
          @error('amount')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <div class="form-grp">
          <label>Date</label>
          <input type="date" name="date" value="{{ now()->toDateString() }}" required max="{{ now()->toDateString() }}" class="form-input">
          @error('date')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="grid-2-col" style="margin-bottom:16px;">
        <div class="form-grp">
          <label>Received Via</label>
          <div style="display:flex; gap:16px; margin-top:8px;">
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:500;">
              <input type="radio" name="received_via" value="cash" {{ old('received_via', 'cash') === 'cash' ? 'checked' : '' }} style="accent-color:var(--btn);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="10" x2="4" y2="10"/><line x1="20" y1="10" x2="23" y2="10"/></svg> Cash
            </label>
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:500;">
              <input type="radio" name="received_via" value="bank" {{ old('received_via') === 'bank' ? 'checked' : '' }} style="accent-color:var(--btn);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><polygon points="12 2 2 7 22 7"/><line x1="4" y1="7" x2="4" y2="17"/><line x1="8" y1="7" x2="8" y2="17"/><line x1="12" y1="7" x2="12" y2="17"/><line x1="16" y1="7" x2="16" y2="17"/><line x1="20" y1="7" x2="20" y2="17"/><rect x="2" y="17" width="20" height="3"/></svg> Bank
            </label>
          </div>
        </div>
        <div class="form-grp">
          <label>Reference</label>
          <input type="text" name="reference" placeholder="e.g. TXN-123456" value="{{ old('reference') }}" class="form-input">
        </div>
      </div>

      <div class="form-grp" style="margin-bottom:20px;">
        <label>Notes</label>
        <textarea name="notes" rows="3" placeholder="Optional receipt notes" class="form-input" style="resize:vertical;">{{ old('notes') }}</textarea>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--div); padding-top:16px;">
        <a href="{{ route('accounting.receipt-vouchers.index') }}" class="btn-ghost">Cancel</a>
        <button type="submit" class="btn-pri">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px;height:14px;"><polyline points="20 6 9 17 4 12"/></svg>
          Save Receipt Voucher
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
function switchTab(tab) {
  const ownBtn = document.getElementById('tab-own');
  const fraBtn = document.getElementById('tab-franchise');
  const ownForm = document.getElementById('form-own');
  const fraForm = document.getElementById('form-franchise');

  if (tab === 'own') {
    ownBtn.style.borderBottomColor = 'var(--btn)';
    ownBtn.style.color = 'var(--txt)';
    fraBtn.style.borderBottomColor = 'transparent';
    fraBtn.style.color = 'var(--txt3)';
    ownForm.style.display = 'block';
    fraForm.style.display = 'none';
  } else {
    fraBtn.style.borderBottomColor = 'var(--btn)';
    fraBtn.style.color = 'var(--txt)';
    ownBtn.style.borderBottomColor = 'transparent';
    ownBtn.style.color = 'var(--txt3)';
    fraForm.style.display = 'block';
    ownForm.style.display = 'none';
  }
}

function updateInvoiceMax() {
  const sel = document.getElementById('invoice-select');
  const opt = sel.options[sel.selectedIndex];
  const remaining = parseFloat(opt.dataset.remaining || 0);
  const amtInput = document.getElementById('invoice-amount');
  const hint = document.getElementById('invoice-max-hint');

  if (remaining > 0) {
    amtInput.max = remaining;
    amtInput.value = remaining;
    hint.textContent = 'Max receivable: ₹' + remaining.toLocaleString('en-IN', {minimumFractionDigits: 2});
  } else {
    amtInput.max = '';
    amtInput.value = '';
    hint.textContent = '';
  }
}

// Auto-select invoice if coming from franchise invoice detail page
const urlParams = new URLSearchParams(window.location.search);
const preselectedInvoice = urlParams.get('invoice_id');
if (preselectedInvoice) {
    switchTab('franchise');
    const invoiceSelect = document.getElementById('invoice-select');
    if (invoiceSelect) {
        invoiceSelect.value = preselectedInvoice;
        updateInvoiceMax();
    }
}
</script>
@endsection
