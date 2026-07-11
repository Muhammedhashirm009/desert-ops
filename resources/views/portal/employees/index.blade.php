@extends('layouts.portal')

@section('title', 'Manage Employees — DessertOps Portal')
@section('breadcrumb', 'Manage Employees')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Team Members</div>
    <div class="ph-sub">Manage outlet staff accounts — assign roles and control portal access</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('portal.employees.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Employee
    </a>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="ch-title">Outlet Employees</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th style="width: 25%;">Name</th>
        <th style="width: 25%;">Email</th>
        <th style="width: 15%; text-align: center;">Role</th>
        <th style="width: 15%; text-align: center;">Status</th>
        <th style="width: 20%; text-align: center;">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($employees as $employee)
      <tr>
        <td data-label="Name" style="font-weight: 600;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <div style="width: 30px; height: 30px; border-radius: 50%; background: {{ $employee->role === 'outlet_admin' ? 'var(--green-lt)' : 'var(--purple-lt)' }}; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: {{ $employee->role === 'outlet_admin' ? 'var(--green-tx)' : 'var(--purple-tx)' }}; flex-shrink: 0;">
              {{ strtoupper(substr($employee->name, 0, 2)) }}
            </div>
            {{ $employee->name }}
          </div>
        </td>
        <td data-label="Email" class="mono" style="font-size: 12.5px; color: var(--txt2);">{{ $employee->email }}</td>
        <td data-label="Role" style="text-align: center;">
          @if($employee->role === 'outlet_admin')
            <span class="badge bg">Admin</span>
          @else
            <span class="badge bp">Salesperson</span>
          @endif
        </td>
        <td data-label="Status" style="text-align: center;">
          @if($employee->is_active)
            <span class="badge bg" style="font-size: 10px;">Active</span>
          @else
            <span class="badge br" style="font-size: 10px;">Inactive</span>
          @endif
        </td>
        <td data-label="Actions" style="text-align: center;">
          <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
            <a href="{{ route('portal.employees.edit', $employee) }}" class="btn-ghost" style="padding: 4px 10px; font-size: 11.5px;">
              Edit
            </a>
            @if(session('portal_employee_id') != $employee->id)
            <form action="{{ route('portal.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this employee?');" style="display: inline;">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-ghost" style="padding: 4px 10px; font-size: 11.5px; color: var(--red-tx);">
                Delete
              </button>
            </form>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="text-center td2" style="padding: 30px;">
          No employees added yet. <a href="{{ route('portal.employees.create') }}">Add your first team member</a>.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
