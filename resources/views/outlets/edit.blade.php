@extends('layouts.app')

@section('title', 'Edit Retail Outlet — DessertOps')
@section('breadcrumb', 'Edit Retail Outlet')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Edit Retail Outlet</div>
    <div class="ph-sub">Modify details for {{ $outlet->name }}</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('outlets.show', $outlet->id) }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
  <div class="ch">
    <div class="ch-title">Outlet Configuration</div>
  </div>
  <div class="cb">
    <form action="{{ route('outlets.update', $outlet->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="form-grp">
        <label for="name">Outlet / Store Name *</label>
        <input type="text" name="name" id="name" class="form-input" required value="{{ old('name', $outlet->name) }}">
        @error('name')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="grid-2">
        <div class="form-grp">
          <label for="type">Outlet Type *</label>
          <select name="type" id="type" class="form-input" required style="height: 38px;">
            <option value="own" {{ old('type', $outlet->type) === 'own' ? 'selected' : '' }}>Company Owned</option>
            <option value="franchise" {{ old('type', $outlet->type) === 'franchise' ? 'selected' : '' }}>Franchise Partner</option>
          </select>
          @error('type')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp" id="commission-container" style="display: none;">
          <label for="commission_rate">Franchise Commission (%) *</label>
          <div style="display: flex; align-items: center; gap: 8px;">
            <input type="number" step="0.1" name="commission_rate" id="commission_rate" class="form-input" 
                   value="{{ old('commission_rate', $outlet->commission_rate) }}" min="0" max="100">
            <span style="font-weight: 600; color: var(--txt3);">%</span>
          </div>
          @error('commission_rate')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="grid-2" style="margin-top: 10px;">
        <div class="form-grp">
          <label for="contact_person">Contact Manager</label>
          <input type="text" name="contact_person" id="contact_person" class="form-input" value="{{ old('contact_person', $outlet->contact_person) }}">
          @error('contact_person')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp">
          <label for="phone">Phone Number</label>
          <input type="text" name="phone" id="phone" class="form-input" value="{{ old('phone', $outlet->phone) }}">
          @error('phone')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="form-grp" style="margin-top: 10px;">
        <label for="address">Address Location</label>
        <textarea name="address" id="address" class="form-input" rows="3">{{ old('address', $outlet->address) }}</textarea>
        @error('address')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div style="margin: 20px 0 10px; padding: 16px; background: var(--bg2); border-radius: 8px; border: 1px solid var(--border);">
        <div style="font-weight: 600; font-size: 14px; color: var(--txt1); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Portal Authentication Credentials
        </div>
        <div class="grid-2">
          <div class="form-grp" style="margin-bottom: 0;">
            <label for="email">Portal Email *</label>
            <input type="email" name="email" id="email" class="form-input" required value="{{ old('email', $outlet->email) }}" placeholder="e.g. store@dessertops.com">
            @error('email')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-grp" style="margin-bottom: 0;">
            <label for="password">Password (Optional)</label>
            <input type="password" name="password" id="password" class="form-input" placeholder="Leave blank to keep current">
            @error('password')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
          </div>
        </div>
      </div>

      <div class="mt-4">
        <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; padding: 10px;">
          Update Retail Outlet
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const commissionContainer = document.getElementById('commission-container');
    const commissionInput = document.getElementById('commission_rate');

    function toggleCommission() {
        if (typeSelect.value === 'franchise') {
            commissionContainer.style.display = 'block';
            commissionInput.setAttribute('required', 'required');
        } else {
            commissionContainer.style.display = 'none';
            commissionInput.removeAttribute('required');
        }
    }

    typeSelect.addEventListener('change', toggleCommission);
    toggleCommission(); // run on load
});
</script>
@endsection
