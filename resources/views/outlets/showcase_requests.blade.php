@extends('layouts.app')

@section('title', 'Showcase Requests — DessertOps')
@section('breadcrumb', 'Showcase Requests')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Showcase Requests Monitoring</div>
    <div class="ph-sub">Monitor all showcase stock requests initiated by retail outlets</div>
  </div>
</div>

<div class="card">
  <div class="ch" style="display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div class="ch-ic" style="background: var(--purple-lt); color: var(--purple-tx);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div class="ch-title">Showcase Request Logs</div>
    </div>
    <div style="font-size: 11.5px; color: var(--txt3); font-weight: 500;">
      All Outlet Requests
    </div>
  </div>
  <div class="cb" style="padding: 0; overflow-x: auto;">
    <table class="tbl">
      <thead>
        <tr>
          <th>Request Number</th>
          <th>Outlet</th>
          <th>Requested Date</th>
          <th>Requested By</th>
          <th>Items Breakdown</th>
          <th>Status</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $req)
        <tr>
          <td data-label="Request Number" class="mono font-semibold" style="font-size: 13px;">
            {{ $req->request_number }}
          </td>
          <td data-label="Outlet">
            @if($req->outlet)
              <a href="{{ route('outlets.show', $req->outlet->id) }}" style="color: var(--txt); text-decoration: none; font-weight: 600;">
                {{ $req->outlet->name }}
              </a>
            @else
              <span class="td3">N/A</span>
            @endif
          </td>
          <td data-label="Requested Date" class="mono text-nowrap" style="font-size: 12px; color: var(--txt2);">
            {{ $req->requested_date ? $req->requested_date->format('Y-m-d') : ($req->created_at ? $req->created_at->format('Y-m-d') : 'N/A') }}
          </td>
          <td data-label="Requested By" style="font-weight: 500;">
            {{ $req->requested_by }}
          </td>
          <td data-label="Items Breakdown">
            <div style="display: flex; flex-direction: column; gap: 4px;">
              @forelse($req->items as $item)
                <div style="font-size: 12px; color: var(--txt2); display: flex; align-items: center; gap: 6px;">
                  <span style="font-weight: 600;">{{ $item->product ? $item->product->name : 'Unknown Product' }}</span>
                  <span style="color: var(--txt3); font-family: 'JetBrains Mono', monospace; font-size: 11px;">
                    (Req: {{ number_format($item->quantity_requested, 0) }} / Rel: {{ number_format($item->quantity_released, 0) }})
                  </span>
                  @if($item->quantity_released > 0 && $item->release_source)
                    <span class="badge bn" style="font-size: 9px; padding: 1px 4px;">from {{ ucfirst($item->release_source) }}</span>
                  @endif
                </div>
              @empty
                <span class="td3" style="font-size: 12px;">No items requested</span>
              @endforelse
            </div>
          </td>
          <td data-label="Status">
            @if($req->status === 'pending')
              <span class="badge ba">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Pending
              </span>
            @elseif($req->status === 'approved')
              <span class="badge bb">
                <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Approved
              </span>
            @elseif($req->status === 'released')
              <span class="badge bg">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Released
              </span>
            @elseif($req->status === 'rejected')
              <span class="badge br">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Rejected
              </span>
            @else
              <span class="badge ba">{{ ucfirst($req->status) }}</span>
            @endif
          </td>
          <td data-label="Notes" style="font-size: 12px; color: var(--txt2); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $req->notes }}">
            {{ $req->notes ?? '—' }}
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center td2" style="padding: 30px; color: var(--txt3);">
            No showcase requests found in history.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
