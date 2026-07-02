@extends('layouts.accounting')

@section('title', 'New Payment Voucher — DessertOps Accounts')
@section('breadcrumb', 'New Payment Voucher')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">New Payment Voucher</div>
    <div class="ph-sub">Record money going out — either a direct expense or a supplier bill payment</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:13px;height:13px;"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Back to List
    </a>
  </div>
</div>

<div class="card" style="max-width:700px;">
  <!-- Tab Toggle -->
  <div style="display:flex; border-bottom:2px solid var(--div);">
    <button type="button" id="tab-expense" onclick="switchTab('expense')"
            style="flex:1; padding:13px 16px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; border-bottom:3px solid var(--btn); color:var(--txt); font-family:inherit;">
      Pay an Expense
    </button>
    <button type="button" id="tab-supplier" onclick="switchTab('supplier')"
            style="flex:1; padding:13px 16px; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; border-bottom:3px solid transparent; color:var(--txt3); font-family:inherit;">
      Pay a Supplier Bill
    </button>
  </div>

  <!-- Expense Form -->
  <div id="form-expense" class="cb" style="padding:24px;">
    <form action="{{ route('accounting.payment-vouchers.store') }}" method="POST">
      @csrf
      <input type="hidden" name="payment_type" value="expense">

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

      <div class="grid-2-col" style="margin-bottom:16px;">
        <div class="form-grp">
          <label>Paid To / Payee</label>
          <input type="text" name="payee" placeholder="e.g. KSEB, Staff Name, Vendor" required value="{{ old('payee') }}" class="form-input">
          @error('payee')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <div class="form-grp">
          <label>Category</label>
          <select name="expense_account_id" required class="form-input">
            <option value="">— Select Category —</option>
            @foreach($categories as $cat)
              <option value="{{ $cat['id'] }}" {{ old('expense_account_id') == $cat['id'] ? 'selected' : '' }}>{{ $cat['label'] }}</option>
            @endforeach
          </select>
          @error('expense_account_id')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="grid-2-col" style="margin-bottom:16px;">
        <div class="form-grp">
          <label>Amount (₹)</label>
          <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required value="{{ old('amount') }}" class="form-input" style="font-family:'JetBrains Mono',monospace; font-weight:600;">
          @error('amount')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <div class="form-grp">
          <label>Paid Via</label>
          <div style="display:flex; gap:16px; margin-top:8px;">
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:500;">
              <input type="radio" name="paid_via" value="cash" {{ old('paid_via', 'cash') === 'cash' ? 'checked' : '' }} style="accent-color:var(--btn);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="10" x2="4" y2="10"/><line x1="20" y1="10" x2="23" y2="10"/></svg> Cash
            </label>
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:500;">
              <input type="radio" name="paid_via" value="bank" {{ old('paid_via') === 'bank' ? 'checked' : '' }} style="accent-color:var(--btn);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><polygon points="12 2 2 7 22 7"/><line x1="4" y1="7" x2="4" y2="17"/><line x1="8" y1="7" x2="8" y2="17"/><line x1="12" y1="7" x2="12" y2="17"/><line x1="16" y1="7" x2="16" y2="17"/><line x1="20" y1="7" x2="20" y2="17"/><rect x="2" y="17" width="20" height="3"/></svg> Bank
            </label>
          </div>
          @error('paid_via')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-grp" style="margin-bottom:20px;">
        <label>Notes</label>
        <textarea name="notes" rows="3" placeholder="Optional description or details" class="form-input" style="resize:vertical;">{{ old('notes') }}</textarea>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--div); padding-top:16px;">
        <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn-ghost">Cancel</a>
        <button type="submit" class="btn-pri">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px;height:14px;"><polyline points="20 6 9 17 4 12"/></svg>
          Save Payment Voucher
        </button>
      </div>
    </form>
  </div>

  <!-- Supplier Bill Form -->
  <div id="form-supplier" class="cb" style="padding:24px; display:none;">
    <form action="{{ route('accounting.payment-vouchers.store') }}" method="POST">
      @csrf
      <input type="hidden" name="payment_type" value="supplier">

      <div class="form-grp" style="margin-bottom:16px;">
        <label>Voucher Number</label>
        <input type="text" name="voucher_number" value="{{ $voucherNumber }}" readonly class="form-input" style="background:var(--div); color:var(--txt2); font-family:'JetBrains Mono',monospace; font-weight:600;">
      </div>

      <div class="form-grp" style="margin-bottom:16px;">
        <label>Select Bill</label>
        <select name="supplier_bill_id" id="bill-select" required class="form-input" onchange="updateBillMax()">
          <option value="" data-remaining="0">— Select Unpaid Bill —</option>
          @foreach($unpaidBills as $bill)
            <option value="{{ $bill->id }}" data-remaining="{{ $bill->remaining_amount }}" {{ old('supplier_bill_id') == $bill->id ? 'selected' : '' }}>
              {{ $bill->bill_number }} — {{ $bill->supplier->name }} (₹{{ number_format($bill->remaining_amount, 2) }} remaining)
            </option>
          @endforeach
        </select>
        @error('supplier_bill_id')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>

      <div class="grid-2-col" style="margin-bottom:16px;">
        <div class="form-grp">
          <label>Amount (₹)</label>
          <input type="number" name="amount" id="bill-amount" step="0.01" min="0.01" placeholder="0.00" required value="{{ old('amount') }}" class="form-input" style="font-family:'JetBrains Mono',monospace; font-weight:600;">
          <div id="bill-max-hint" style="font-size:11.5px; color:var(--txt3); margin-top:4px;"></div>
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
          <label>Paid Via</label>
          <div style="display:flex; gap:16px; margin-top:8px;">
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:500;">
              <input type="radio" name="paid_via" value="cash" {{ old('paid_via', 'cash') === 'cash' ? 'checked' : '' }} style="accent-color:var(--btn);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="10" x2="4" y2="10"/><line x1="20" y1="10" x2="23" y2="10"/></svg> Cash
            </label>
            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-weight:500;">
              <input type="radio" name="paid_via" value="bank" {{ old('paid_via') === 'bank' ? 'checked' : '' }} style="accent-color:var(--btn);">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;"><polygon points="12 2 2 7 22 7"/><line x1="4" y1="7" x2="4" y2="17"/><line x1="8" y1="7" x2="8" y2="17"/><line x1="12" y1="7" x2="12" y2="17"/><line x1="16" y1="7" x2="16" y2="17"/><line x1="20" y1="7" x2="20" y2="17"/><rect x="2" y="17" width="20" height="3"/></svg> Bank
            </label>
          </div>
        </div>
        <div class="form-grp">
          <label>Reference</label>
          <input type="text" name="reference" placeholder="e.g. TXN-123456, CHQ-789" value="{{ old('reference') }}" class="form-input">
        </div>
      </div>

      <div class="form-grp" style="margin-bottom:20px;">
        <label>Notes</label>
        <textarea name="notes" rows="3" placeholder="Optional payment notes" class="form-input" style="resize:vertical;">{{ old('notes') }}</textarea>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--div); padding-top:16px;">
        <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn-ghost">Cancel</a>
        <button type="submit" class="btn-pri">
          <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px;height:14px;"><polyline points="20 6 9 17 4 12"/></svg>
          Save Payment Voucher
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
function switchTab(tab) {
  const expBtn = document.getElementById('tab-expense');
  const supBtn = document.getElementById('tab-supplier');
  const expForm = document.getElementById('form-expense');
  const supForm = document.getElementById('form-supplier');

  if (tab === 'expense') {
    expBtn.style.borderBottomColor = 'var(--btn)';
    expBtn.style.color = 'var(--txt)';
    supBtn.style.borderBottomColor = 'transparent';
    supBtn.style.color = 'var(--txt3)';
    expForm.style.display = 'block';
    supForm.style.display = 'none';
  } else {
    supBtn.style.borderBottomColor = 'var(--btn)';
    supBtn.style.color = 'var(--txt)';
    expBtn.style.borderBottomColor = 'transparent';
    expBtn.style.color = 'var(--txt3)';
    supForm.style.display = 'block';
    expForm.style.display = 'none';
  }
}

function updateBillMax() {
  const sel = document.getElementById('bill-select');
  const opt = sel.options[sel.selectedIndex];
  const remaining = parseFloat(opt.dataset.remaining || 0);
  const amtInput = document.getElementById('bill-amount');
  const hint = document.getElementById('bill-max-hint');

  if (remaining > 0) {
    amtInput.max = remaining;
    amtInput.value = remaining;
    hint.textContent = 'Max payable: ₹' + remaining.toLocaleString('en-IN', {minimumFractionDigits: 2});
  } else {
    amtInput.max = '';
    amtInput.value = '';
    hint.textContent = '';
  }
}

// Auto-select bill if coming from bill detail page
const urlParams = new URLSearchParams(window.location.search);
const preselectedBill = urlParams.get('bill_id');
if (preselectedBill) {
    switchTab('supplier');
    const billSelect = document.getElementById('bill-select');
    if (billSelect) {
        billSelect.value = preselectedBill;
        updateBillMax();
    }
}
</script>
@endsection
