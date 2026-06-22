@extends('layouts.app')

@section('title', 'Outlets Management — DessertOps')
@section('breadcrumb', 'Outlets Management')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Outlets Management</div>
    <div class="ph-sub">Add and manage company-owned retail outlets and franchise partners</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('outlets.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Retail Outlet
    </a>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
    </div>
    <div class="ch-title">Outlets & Franchise Locations</div>
  </div>
  <table>
    <thead>
      <tr>
        <th>Outlet Name</th>
        <th>Type</th>
        <th style="text-align: right;">Franchise Commission</th>
        <th>Contact Person</th>
        <th>Phone</th>
        <th style="text-align: center;">Stocked Items</th>
        <th style="text-align: right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($outlets as $outlet)
      <tr>
        <td>
          <a href="{{ route('outlets.show', $outlet->id) }}" class="td-name" style="color:var(--txt); text-decoration:none; font-weight:600;">
            {{ $outlet->name }}
          </a>
          <div class="td-meta">{{ Str::limit($outlet->address, 40) }}</div>
        </td>
        <td>
          @if($outlet->type === 'own')
            <span class="badge bg">Company Owned</span>
          @else
            <span class="badge bp">Franchise</span>
          @endif
        </td>
        <td class="mono" style="text-align: right; font-weight: 600;">
          @if($outlet->type === 'franchise')
            {{ number_format($outlet->commission_rate, 1) }}%
          @else
            <span class="td3">—</span>
          @endif
        </td>
        <td>{{ $outlet->contact_person ?? 'N/A' }}</td>
        <td class="mono">{{ $outlet->phone ?? 'N/A' }}</td>
        <td class="mono" style="text-align: center; font-weight: 600;">
          {{ $outlet->stocks_count }}
        </td>
        <td>
          <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('outlets.show', $outlet->id) }}" class="td-act">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              View
            </a>
            <a href="{{ route('outlets.edit', $outlet->id) }}" class="td-act">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit
            </a>
            <form action="{{ route('outlets.destroy', $outlet->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this outlet? All stock history will be lost.');" style="display: inline;">
              @csrf
              @method('DELETE')
              <button type="submit" class="td-act po-row-btn" style="padding:0; font-size:13px;">
                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Delete
              </button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="text-center td2">No outlets registered yet. <a href="{{ route('outlets.create') }}">Create your first outlet</a>.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
