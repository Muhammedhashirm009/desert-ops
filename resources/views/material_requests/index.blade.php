@extends('layouts.app')

@section('title', 'Material Requests — DessertOps')
@section('breadcrumb', 'Material Requests')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Material Requests (MRs)</div>
    <div class="ph-sub">Manage kitchen requests for raw materials from main inventory</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('material-requests.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Material Request
    </a>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
    </div>
    <div class="ch-title">Request Logs</div>
  </div>
  <table>
    <thead>
      <tr>
        <th>Request No.</th>
        <th>Requested By</th>
        <th>Items Count</th>
        <th>Status</th>
        <th>Requested Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($materialRequests as $mr)
      <tr>
        <td class="mono font-semibold">{{ $mr->request_number }}</td>
        <td class="td-name">{{ $mr->requested_by }}</td>
        <td>{{ $mr->items->count() }} items</td>
        <td>
          @if($mr->status === 'released')
            <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Released</span>
          @elseif($mr->status === 'approved')
            <span class="badge bb"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Approved</span>
          @elseif($mr->status === 'rejected')
            <span class="badge br"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Rejected</span>
          @else
            <span class="badge ba"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Pending</span>
          @endif
        </td>
        <td class="mono td3">{{ $mr->requested_date->format('d M Y') }}</td>
        <td>
          <a href="{{ route('material-requests.show', $mr->id) }}" class="td-act">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            View Details
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" class="text-center td2">No material requests recorded yet. <a href="{{ route('material-requests.create') }}">Submit one now</a>.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
