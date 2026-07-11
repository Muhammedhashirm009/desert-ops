@extends('layouts.portal')

@section('title', 'Add Employee — DessertOps Portal')
@section('breadcrumb', 'Add Employee')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Add New Employee</div>
    <div class="ph-sub">Create a new staff account for <b>{{ $outlet->name }}</b></div>
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
      <form action="{{ route('portal.employees.store') }}" method="POST">
        @csrf

        <div class="form-grp">
          <label for="name">Full Name *</label>
          <input type="text" name="name" id="name" class="form-input" required value="{{ old('name') }}" placeholder="Enter employee name">
          @error('name')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp" style="margin-top: 16px;">
          <label for="email">Email Address *</label>
          <input type="email" name="email" id="email" class="form-input" required value="{{ old('email') }}" placeholder="employee@example.com">
          @error('email')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp" style="margin-top: 16px;">
          <label for="password">Password *</label>
          <input type="password" name="password" id="password" class="form-input" required minlength="6" placeholder="Minimum 6 characters">
          @error('password')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp" style="margin-top: 16px;">
          <label for="role">Role *</label>
          <select name="role" id="role" class="form-input" required>
            @foreach($roles as $value => $label)
              <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          @error('role')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
          <a href="{{ route('portal.employees.index') }}" class="btn-ghost">Cancel</a>
          <button type="submit" class="btn-pri" style="font-weight: 600;">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 14px; height: 14px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Create Employee
          </button>
        </div>
      </form>
    </div>
  </div>

  <div style="display: flex; flex-direction: column; gap: 16px;">
    <div class="card" style="background: rgba(255,255,255,0.01); border: 1px dashed var(--div2);">
      <div class="cb" style="font-size: 12.5px; line-height: 1.5; color: var(--txt2);">
        <div style="font-weight: 700; color: var(--txt); margin-bottom: 8px;">Role Descriptions</div>
        <div style="margin-bottom: 10px;">
          <span class="badge bg" style="font-size: 10px;">Admin</span>
          <div style="margin-top: 4px;">Full portal access — manage employees, approve showcase requests, receive shipments, request products, log sales, and move stock.</div>
        </div>
        <div>
          <span class="badge bp" style="font-size: 10px;">Salesperson</span>
          <div style="margin-top: 4px;">Limited access — log daily sales and create showcase requests only. Cannot see any pricing or financial data.</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
