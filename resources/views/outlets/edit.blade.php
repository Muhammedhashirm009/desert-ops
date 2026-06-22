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
