@extends('layouts.accounting')

@section('title', 'New Fund Transfer — DessertOps Accounts')
@section('breadcrumb', 'New Fund Transfer')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">New Fund Transfer</div>
    <div class="ph-sub">Transfer money between cash in hand and bank accounts</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('accounting.transfers.index') }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:13px;height:13px;"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Back to List
    </a>
  </div>
</div>

<!-- Balance Cards Row -->
<div class="grid-2-col" style="margin-bottom: 24px; max-width: 700px;">
  <!-- Cash Balance Card -->
  <div class="card" style="padding: 16px; border-left: 4px solid var(--acc-green); display: flex; align-items: center; gap: 14px;">
    <div class="ch-ic" style="background:var(--div); margin-bottom: 0;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="stroke:var(--txt2);width:16px;height:16px;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><circle cx="12" cy="12" r="3"/><line x1="1" y1="10" x2="4" y2="10"/><line x1="20" y1="10" x2="23" y2="10"/></svg>
    </div>
    <div>
      <div style="font-size: 11px; color: var(--txt3); font-weight: 600; text-transform: uppercase;">Cash in Hand Balance</div>
      <div style="font-size: 20px; font-weight: 700; color: var(--txt); margin-top: 2px;">₹{{ number_format($cashBalance, 2) }}</div>
    </div>
  </div>

  <!-- Bank Balance Card -->
  <div class="card" style="padding: 16px; border-left: 4px solid #3b82f6; display: flex; align-items: center; gap: 14px;">
    <div class="ch-ic" style="background:var(--div); margin-bottom: 0;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="stroke:var(--txt2);width:16px;height:16px;"><polygon points="12 2 2 7 22 7"/><line x1="4" y1="7" x2="4" y2="17"/><line x1="8" y1="7" x2="8" y2="17"/><line x1="12" y1="7" x2="12" y2="17"/><line x1="16" y1="7" x2="16" y2="17"/><line x1="20" y1="7" x2="20" y2="17"/><rect x="2" y="17" width="20" height="3"/></svg>
    </div>
    <div>
      <div style="font-size: 11px; color: var(--txt3); font-weight: 600; text-transform: uppercase;">Bank Account Balance</div>
      <div style="font-size: 20px; font-weight: 700; color: var(--txt); margin-top: 2px;">₹{{ number_format($bankBalance, 2) }}</div>
    </div>
  </div>
</div>

<div class="card" style="max-width:700px; border-top:3px solid var(--acc-green);">
  <div class="cb" style="padding:24px;">
    <form action="{{ route('accounting.transfers.store') }}" method="POST">
      @csrf

      <div class="grid-2-col" style="margin-bottom:16px;">
        <div class="form-grp">
          <label style="display:block; font-weight:500; margin-bottom:8px; font-size:13px; color:var(--txt2);">Transfer Reference #</label>
          <input type="text" name="reference" value="{{ $reference }}" readonly class="form-input" style="background:var(--div); color:var(--txt2); font-family:'JetBrains Mono',monospace; font-weight:600; width:100%; padding:8px 12px; border:1px solid var(--div2); border-radius:var(--radius);">
          @error('reference')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <div class="form-grp">
          <label style="display:block; font-weight:500; margin-bottom:8px; font-size:13px; color:var(--txt2);">Transfer Date</label>
          <input type="date" name="date" value="{{ now()->toDateString() }}" required max="{{ now()->toDateString() }}" class="form-input" style="width:100%; padding:8px 12px; border:1px solid var(--div2); border-radius:var(--radius); background:var(--bg); color:var(--txt);">
          @error('date')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="grid-2-col" style="margin-bottom:16px;">
        <div class="form-grp">
          <label style="display:block; font-weight:500; margin-bottom:8px; font-size:13px; color:var(--txt2);">Transfer From (Source)</label>
          <select name="from_account" id="from_account" required class="form-input" style="width:100%; padding:8px 12px; border:1px solid var(--div2); border-radius:var(--radius); background:var(--bg); color:var(--txt);">
            <option value="bank" {{ old('from_account') === 'bank' ? 'selected' : '' }}>Bank Account (1020)</option>
            <option value="cash" {{ old('from_account') === 'cash' ? 'selected' : '' }}>Cash in Hand (1010)</option>
          </select>
          @error('from_account')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <div class="form-grp">
          <label style="display:block; font-weight:500; margin-bottom:8px; font-size:13px; color:var(--txt2);">Transfer To (Destination)</label>
          <select name="to_account" id="to_account" required class="form-input" style="width:100%; padding:8px 12px; border:1px solid var(--div2); border-radius:var(--radius); background:var(--bg); color:var(--txt);">
            <option value="cash" {{ old('to_account') === 'cash' ? 'selected' : '' }}>Cash in Hand (1010)</option>
            <option value="bank" {{ old('to_account') === 'bank' ? 'selected' : '' }}>Bank Account (1020)</option>
          </select>
          @error('to_account')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-grp" style="margin-bottom:16px;">
        <label style="display:block; font-weight:500; margin-bottom:8px; font-size:13px; color:var(--txt2);">Transfer Amount (₹)</label>
        <input type="number" step="0.01" name="amount" placeholder="e.g. 5000.00" required value="{{ old('amount') }}" class="form-input" style="width:100%; padding:8px 12px; border:1px solid var(--div2); border-radius:var(--radius); background:var(--bg); color:var(--txt); font-family:var(--mono); font-weight:600;">
        @error('amount')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>

      <div class="form-grp" style="margin-bottom:20px;">
        <label style="display:block; font-weight:500; margin-bottom:8px; font-size:13px; color:var(--txt2);">Notes / Description</label>
        <textarea name="description" placeholder="Specify reason or reference details..." rows="3" class="form-input" style="width:100%; padding:8px 12px; border:1px solid var(--div2); border-radius:var(--radius); background:var(--bg); color:var(--txt); font-family:inherit; resize:vertical;">{{ old('description') }}</textarea>
        @error('description')<div style="color:var(--red-tx);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>

      <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid var(--div2); padding-top:20px;">
        <a href="{{ route('accounting.transfers.index') }}" class="btn-sec">Cancel</a>
        <button type="submit" class="btn-pri" style="background:var(--acc-green); border-color:var(--acc-green);">Confirm Fund Transfer</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromSelect = document.getElementById('from_account');
    const toSelect = document.getElementById('to_account');

    function syncAccounts() {
        if (fromSelect.value === 'bank') {
            toSelect.value = 'cash';
        } else {
            toSelect.value = 'bank';
        }
    }

    fromSelect.addEventListener('change', syncAccounts);
    toSelect.addEventListener('change', function() {
        if (toSelect.value === 'bank') {
            fromSelect.value = 'cash';
        } else {
            fromSelect.value = 'bank';
        }
    });

    // Run initial sync
    syncAccounts();
});
</script>
@endsection
