@extends('layouts.portal')

@section('title', 'Edit Employee — DessertOps Portal')
@section('breadcrumb', 'Edit Employee')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Edit Employee</div>
    <div class="ph-sub">Update account details for <b>{{ $employee->name }}</b></div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('portal.employees.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 320px; gap: 16px;">
  <div class="card">
    <div class="ch">
      <div class="ch-title">Employee Details</div>
    </div>
    <div class="cb">
      <form action="{{ route('portal.employees.update', $employee) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-grp">
          <label for="name">Full Name *</label>
          <input type="text" name="name" id="name" class="form-input" required value="{{ old('name', $employee->name) }}" placeholder="Enter employee name">
          @error('name')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp" style="margin-top: 16px;">
          <label for="email">Email Address *</label>
          <input type="email" name="email" id="email" class="form-input" required value="{{ old('email', $employee->email) }}" placeholder="employee@example.com">
          @error('email')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp" style="margin-top: 16px;">
          <label for="password">Password</label>
          <input type="password" name="password" id="password" class="form-input" minlength="6" placeholder="Leave blank to keep current password">
          @error('password')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp" style="margin-top: 16px;">
          <label for="role">Role *</label>
          <select name="role" id="role" class="form-input" required>
            @foreach($roles as $value => $label)
              <option value="{{ $value }}" {{ old('role', $employee->role) == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          @error('role')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp" style="margin-top: 16px;">
          <label for="is_active">Account Status *</label>
          <select name="is_active" id="is_active" class="form-input" required>
            <option value="1" {{ old('is_active', $employee->is_active) == 1 ? 'selected' : '' }}>Active — Can log in</option>
            <option value="0" {{ old('is_active', $employee->is_active) == 0 ? 'selected' : '' }}>Inactive — Login blocked</option>
          </select>
          @error('is_active')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
          <a href="{{ route('portal.employees.index') }}" class="btn-ghost">Cancel</a>
          <button type="submit" class="btn-pri" style="font-weight: 600;">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 14px; height: 14px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <div style="display: flex; flex-direction: column; gap: 16px;">
    <div class="card">
      <div class="ch">
        <div class="ch-title">Account Info</div>
      </div>
      <div class="cb" style="display: flex; flex-direction: column; gap: 12px;">
        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Created</label>
          <div class="mono" style="font-size: 13px; color: var(--txt2); margin-top: 2px;">{{ $employee->created_at->format('Y-m-d H:i') }}</div>
        </div>
        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Last Updated</label>
          <div class="mono" style="font-size: 13px; color: var(--txt2); margin-top: 2px;">{{ $employee->updated_at->format('Y-m-d H:i') }}</div>
        </div>
        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Current Status</label>
          <div style="margin-top: 4px;">
            @if($employee->is_active)
              <span class="badge bg" style="font-size: 10px;">Active</span>
            @else
              <span class="badge br" style="font-size: 10px;">Inactive</span>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
