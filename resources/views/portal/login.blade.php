@extends('layouts.app')

@section('title', 'Outlet Portal Select — DessertOps')
@section('breadcrumb', 'Outlet Select')

@section('content')
<div style="max-width: 900px; margin: 40px auto;">
  <div style="text-align: center; margin-bottom: 30px;">
    <h1 style="font-size: 24px; font-weight: 700; color: var(--txt); letter-spacing: -0.5px;">Outlet Portal Access</h1>
    <p style="color: var(--txt3); font-size: 13.5px; margin-top: 6px;">Select a retail or franchise location to manage local stock and daily sales logs</p>
  </div>

  @if(session('error'))
      <div class="alert alert-danger" style="max-width: 500px; margin: 0 auto 20px;">
          {{ session('error') }}
      </div>
  @endif

  <div class="row r-3" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
    @forelse($outlets as $outlet)
      <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s; cursor: default;">
        <div class="cb" style="padding: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <span class="badge {{ $outlet->type === 'own' ? 'bg' : 'bp' }}">
              {{ $outlet->type === 'own' ? 'Company Owned' : 'Franchise Partner' }}
            </span>
            @if($outlet->type === 'franchise')
              <span class="mono" style="font-size: 11px; font-weight: 700; color: var(--purple-tx);">
                {{ number_format($outlet->commission_rate, 0) }}% Comm.
              </span>
            @endif
          </div>
          
          <h3 style="font-size: 16px; font-weight: 700; color: var(--txt); margin-bottom: 8px;">{{ $outlet->name }}</h3>
          
          <div style="font-size: 12.5px; color: var(--txt2); display: flex; flex-direction: column; gap: 4px; margin-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 6px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="var(--txt3)" stroke-width="2" style="width: 13px; height: 13px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              <span>Manager: {{ $outlet->contact_person ?? 'N/A' }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="var(--txt3)" stroke-width="2" style="width: 13px; height: 13px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              <span class="mono">{{ $outlet->phone ?? 'N/A' }}</span>
            </div>
          </div>
        </div>

        <div style="padding: 14px 20px; background: var(--acc-lt); border-top: 1px solid var(--div); display: flex; justify-content: flex-end;">
          <form action="{{ route('portal.login.post') }}" method="POST">
            @csrf
            <input type="hidden" name="outlet_id" value="{{ $outlet->id }}">
            <button type="submit" class="btn-pri" style="font-size: 12px; font-weight: 600; padding: 6px 12px; display: flex; align-items: center; gap: 4px;">
              Enter Portal
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 12px; height: 12px;"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
          </form>
        </div>
      </div>
    @empty
      <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--txt3);">
        No retail outlets registered in the system yet. Please create an outlet in the main admin dashboard first.
      </div>
    @endforelse
  </div>
</div>
@endsection
