@extends('layouts.app')

@section('title', 'Edit User — DessertOps Central')
@section('breadcrumb', 'Edit User')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Edit User: {{ $user->name }}</div>
    <div class="ph-sub">Modify user details and update role-based access permissions</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('admin.users.index') }}" class="btn-sec">
      Cancel & Go Back
    </a>
  </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto; padding: 24px;">
  <div class="ch" style="margin-bottom: 24px; padding-bottom: 12px; border-bottom: 1px solid var(--div2);">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2);width:13px;height:13px;"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div class="ch-title">Edit User Account Details</div>
  </div>

  <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid-2-col" style="margin-bottom: 16px;">
      <div class="form-grp">
        <label style="display:block; font-weight:500; margin-bottom:8px; font-size:13px;">Full Name</label>
        <input type="text" name="name" placeholder="e.g. John Doe" required value="{{ old('name', $user->name) }}" class="form-input" style="width: 100%; padding: 8px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt);" />
        @error('name')<div style="color:var(--red-tx); font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
      </div>
      <div class="form-grp">
        <label style="display:block; font-weight:500; margin-bottom:8px; font-size:13px;">Email Address</label>
        <input type="email" name="email" placeholder="e.g. john@dessertops.com" required value="{{ old('email', $user->email) }}" class="form-input" style="width: 100%; padding: 8px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt);" />
        @error('email')<div style="color:var(--red-tx); font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
      </div>
    </div>

    <div class="grid-2-col" style="margin-bottom: 20px;">
      <div class="form-grp">
        <label style="display:block; font-weight:500; margin-bottom:8px; font-size:13px;">Password</label>
        <input type="password" name="password" placeholder="Leave blank to keep current" class="form-input" style="width: 100%; padding: 8px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt);" />
        @error('password')<div style="color:var(--red-tx); font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
      </div>
      <div class="form-grp">
        <label style="display:block; font-weight:500; margin-bottom:8px; font-size:13px;">System Role</label>
        <select name="role" required class="form-input" style="width: 100%; padding: 8px 12px; border: 1px solid var(--div2); border-radius: var(--radius); background: var(--bg); color: var(--txt);">
          <option value="">— Select User Role —</option>
          @foreach($roles as $val => $label)
            <option value="{{ $val }}" {{ old('role', $user->role) === $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        @error('role')<div style="color:var(--red-tx); font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
      </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid var(--div2); padding-top: 20px;">
      <a href="{{ route('admin.users.index') }}" class="btn-sec">Cancel</a>
      <button type="submit" class="btn-pri">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:16px; height:16px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Save Changes
      </button>
    </div>
  </form>
</div>
@endsection
