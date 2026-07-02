@extends('layouts.app')

@section('title', 'Suppliers — DessertOps')
@section('breadcrumb', 'Suppliers List')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Suppliers Directory</div>
    <div class="ph-sub">Manage ingredient and packaging suppliers</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('suppliers.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add New Supplier
    </a>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div class="ch-title">All Registered Suppliers</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th>Name</th>
        <th>Materials</th>
        <th>Contact Person</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Address</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($suppliers as $supplier)
      <tr>
        <td data-label="Name" class="td-name"><a href="{{ route('suppliers.show', $supplier->id) }}" style="color:var(--txt); text-decoration:none; font-weight:600;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">{{ $supplier->name }}</a></td>
        <td data-label="Materials">@if($supplier->materials_count > 0)<span class="badge bb">{{ $supplier->materials_count }} linked</span>@else<span style="color:var(--txt3); font-size:12px;">None</span>@endif</td>
        <td data-label="Contact Person">{{ $supplier->contact_person ?? '—' }}</td>
        <td data-label="Email" class="mono">{{ $supplier->email ?? '—' }}</td>
        <td data-label="Phone" class="mono">{{ $supplier->phone ?? '—' }}</td>
        <td data-label="Address">{{ Str::limit($supplier->address, 50) ?? '—' }}</td>
        <td data-label="Actions">
          <div style="display: flex; gap: 10px;">
            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="td-act">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit
            </a>
            <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this supplier?');" style="display: inline;">
              @csrf
              @method('DELETE')
              <button type="submit" class="td-act po-row-btn" style="padding: 0; font-size: 13px;">
                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Delete
              </button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="text-center td2">No suppliers registered yet. <a href="{{ route('suppliers.create') }}">Create one now</a>.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
