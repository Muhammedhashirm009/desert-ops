@extends('layouts.accounting')

@section('title', 'Log Expense Voucher — DessertOps')
@section('breadcrumb', 'New Expense Voucher')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Log Expense Voucher</div>
    <div class="ph-sub">Record operational cash outflows and post to ledger</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('accounting.expenses.index') }}" class="btn-sec">
      Cancel & Go Back
    </a>
  </div>
</div>

@if(session('error'))
  <div class="toast-notif toast-err" style="margin-bottom: 20px; animation: none;">
    <div style="color: var(--red-tx); font-weight: 500;">{{ session('error') }}</div>
  </div>
@endif

<div class="card" style="max-width: 700px; margin: 0 auto; padding: 24px;">
  <div class="ch" style="margin-bottom: 24px; padding-bottom: 12px; border-bottom: 1px solid var(--div2);">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    </div>
    <div class="ch-title">Voucher Details</div>
  </div>

  <form action="{{ route('accounting.expenses.store') }}" method="POST">
    @csrf

    <div class="grid-2-col" style="margin-bottom: 20px;">
      <div>
        <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 13.5px;">Voucher Number</label>
        <input type="text" name="voucher_number" value="{{ $voucherNumber }}" readonly 
               style="width: 100%; padding: 10px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--div); color: var(--txt2); font-family: var(--mono); font-weight: 600;" />
      </div>
      <div>
        <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 13.5px;">Voucher Date</label>
        <input type="date" name="date" value="{{ now()->toDateString() }}" required max="{{ now()->toDateString() }}"
               style="width: 100%; padding: 10px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt); font-family: var(--mono);" />
        @error('date')
          <div style="color: var(--red-tx); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <div class="grid-2-col" style="margin-bottom: 20px;">
      <div>
        <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 13.5px;">Payee / Recipient</label>
        <input type="text" name="payee" placeholder="e.g. Calicut Realty, KSEB, Manager name" required value="{{ old('payee') }}"
               style="width: 100%; padding: 10px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt);" />
        @error('payee')
          <div style="color: var(--red-tx); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
        @enderror
      </div>
      <div>
        <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 13.5px;">Amount (₹)</label>
        <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required value="{{ old('amount') }}"
               style="width: 100%; padding: 10px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt); font-family: var(--mono); font-weight: 600;" />
        @error('amount')
          <div style="color: var(--red-tx); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <div class="grid-2-col" style="margin-bottom: 20px;">
      <div>
        <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 13.5px;">Expense Category (Debit Account)</label>
        <select name="expense_account_id" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt);">
          <option value="">-- Select Expense Category --</option>
          @foreach($expenseAccounts as $acc)
            <option value="{{ $acc->id }}" {{ old('expense_account_id') == $acc->id ? 'selected' : '' }}>
              {{ $acc->code }} — {{ $acc->name }}
            </option>
          @endforeach
        </select>
        @error('expense_account_id')
          <div style="color: var(--red-tx); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
        @enderror
      </div>
      <div>
        <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 13.5px;">Paid From (Credit Account)</label>
        <select name="payment_account_id" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt);">
          @foreach($paymentAccounts as $acc)
            <option value="{{ $acc->id }}" {{ old('payment_account_id') == $acc->id ? 'selected' : '' }}>
              {{ $acc->code }} — {{ $acc->name }}
            </option>
          @endforeach
        </select>
        @error('payment_account_id')
          <div style="color: var(--red-tx); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <div style="margin-bottom: 24px;">
      <label style="display: block; font-weight: 500; margin-bottom: 8px; font-size: 13.5px;">Notes / Description</label>
      <textarea name="notes" placeholder="Write description or details of this expense voucher" rows="3" 
                style="width: 100%; padding: 10px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt); font-family: inherit; resize: vertical;">{{ old('notes') }}</textarea>
      @error('notes')
        <div style="color: var(--red-tx); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
      @enderror
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid var(--div2); padding-top: 20px;">
      <a href="{{ route('accounting.expenses.index') }}" class="btn-sec">Cancel</a>
      <button type="submit" class="btn-pri">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:16px; height:16px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save & Post Voucher
      </button>
    </div>
  </form>
</div>
@endsection
