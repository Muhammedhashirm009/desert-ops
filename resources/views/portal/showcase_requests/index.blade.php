@extends('layouts.portal')

@section('title', 'Showcase Requests — DessertOps Portal')
@section('breadcrumb', 'Showcase Requests')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Showcase Requests</div>
    <div class="ph-sub">Manage and track display showcase replenishment requests for this outlet</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('portal.showcase-requests.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Showcase Request
    </a>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
    </div>
    <div class="ch-title">Showcase Request History</div>
  </div>
  <table class="tbl">
    <thead>
      <tr>
        <th style="width: 20%;">Request Number</th>
        <th style="width: 15%;">Date Requested</th>
        <th style="width: 20%;">Requested By</th>
        <th style="width: 15%;">Status</th>
        <th style="width: 15%; text-align: right;">Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($showcaseRequests as $req)
      <tr>
        <td data-label="Request Number" class="mono font-semibold" style="font-weight: 600;">
          {{ $req->request_number }}
        </td>
        <td data-label="Date Requested" class="mono">
          {{ $req->requested_date ? $req->requested_date->format('Y-m-d') : $req->created_at->format('Y-m-d') }}
        </td>
        <td data-label="Requested By">
          {{ $req->requested_by }}
        </td>
        <td data-label="Status">
          @if($req->status === 'pending')
            <span class="badge bn">Pending</span>
          @elseif($req->status === 'approved')
            <span class="badge ba">Approved</span>
          @elseif($req->status === 'rejected')
            <span class="badge br">Rejected</span>
          @elseif($req->status === 'released')
            <span class="badge bg">Released</span>
          @else
            <span class="badge bn">{{ ucfirst($req->status) }}</span>
          @endif
        </td>
        <td data-label="Action" style="text-align: right;">
          <a href="{{ route('portal.showcase-requests.show', $req->id) }}" class="btn-ghost" style="padding: 4px 10px; font-size: 12px;">
            View Details
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="text-center td2" style="padding: 30px;">
          No showcase requests found. <a href="{{ route('portal.showcase-requests.create') }}">Create one now</a>.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
