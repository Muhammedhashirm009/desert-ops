@extends('layouts.app')

@section('title', 'User Management — DessertOps Central')
@section('breadcrumb', 'User Management')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">User Management</div>
    <div class="ph-sub">Manage central kitchen office users, roles, and system access levels</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('admin.users.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add New User
    </a>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2);width:13px;height:13px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div class="ch-title">System Users</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Created At</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $user)
      <tr onmouseover="this.style.background='var(--div)'" onmouseout="this.style.background=''">
        <td data-label="Name">
          <div class="td-name">{{ $user->name }}</div>
          @if(auth()->id() === $user->id)
            <span class="badge bg" style="font-size:9.5px; padding:1px 5px; margin-top:2px; display:inline-block;">You</span>
          @endif
        </td>
        <td data-label="Email" class="mono td2">{{ $user->email }}</td>
        <td data-label="Role">
          @if($user->role === 'admin')
            <span class="badge br">Administrator</span>
          @elseif($user->role === 'gm')
            <span class="badge bp">General Manager</span>
          @elseif($user->role === 'store_manager')
            <span class="badge ba">Store Manager</span>
          @elseif($user->role === 'kitchen_chef')
            <span class="badge bg">Kitchen Chef</span>
          @elseif($user->role === 'accountant')
            <span class="badge bb">Accountant</span>
          @else
            <span class="badge bg-sec">{{ ucfirst($user->role) }}</span>
          @endif
        </td>
        <td data-label="Created At" class="mono td2">{{ $user->created_at->format('Y-m-d') }}</td>
        <td data-label="Actions" style="text-align:right;">
          <div style="display:inline-flex; gap:8px; justify-content:flex-end; align-items:center;">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="td-act">
              Edit <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            @if(auth()->id() !== $user->id)
              <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" style="background:none; border:none; color:var(--red-tx); font-size:12px; font-weight:600; cursor:pointer; padding:0; display:inline-flex; align-items:center; gap:2px;">
                  Delete
                </button>
              </form>
            @endif
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
