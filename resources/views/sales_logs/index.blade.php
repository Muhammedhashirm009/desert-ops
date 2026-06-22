@extends('layouts.app')

@section('title', 'Outlet Sales Logs — DessertOps')
@section('breadcrumb', 'Outlet Sales Logs')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Outlet Sales Reports</div>
    <div class="ph-sub">View daily sales reports logged by retail outlets and calculated franchise commissions</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('sales-logs.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Log Daily Sales
    </a>
  </div>
</div>

<!-- Financial Summary Strip -->
<div class="sum-strip">
  <div class="sum-item">
    <div class="sum-val">₹{{ number_format($salesLogs->sum('total_revenue'), 2) }}</div>
    <div class="sum-lbl">Gross Sales Revenue</div>
  </div>
  <div class="sum-item">
    <div class="sum-val" style="color:var(--purple-tx);">₹{{ number_format($salesLogs->sum('commission_amount'), 2) }}</div>
    <div class="sum-lbl">Franchise Commissions</div>
  </div>
  <div class="sum-item">
    <div class="sum-val" style="color:var(--green-tx);">₹{{ number_format($salesLogs->sum('net_revenue'), 2) }}</div>
    <div class="sum-lbl">Net Kitchen Earnings</div>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    </div>
    <div class="ch-title">Sales Reports Log</div>
  </div>
  <table>
    <thead>
      <tr>
        <th>Report Date</th>
        <th>Outlet Name</th>
        <th>Type</th>
        <th style="text-align: right;">Gross Sales</th>
        <th style="text-align: right;">Commission Cut</th>
        <th style="text-align: right;">Net Kitchen Revenue</th>
        <th style="text-align: right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($salesLogs as $log)
      <tr>
        <td class="mono font-semibold" style="font-weight: 600;">
          <a href="{{ route('sales-logs.show', $log->id) }}" style="color:inherit; text-decoration:none;">
            {{ $log->log_date->format('Y-m-d') }}
          </a>
        </td>
        <td>
          <a href="{{ route('outlets.show', $log->outlet_id) }}" style="color:inherit; text-decoration:none; font-weight:600;">
            {{ $log->outlet->name }}
          </a>
        </td>
        <td>
          @if($log->outlet->type === 'own')
            <span class="badge bg">Own Outlet</span>
          @else
            <span class="badge bp">Franchise</span>
          @endif
        </td>
        <td class="mono" style="text-align: right; font-weight:600;">₹{{ number_format($log->total_revenue, 2) }}</td>
        <td class="mono" style="text-align: right; color: var(--purple-tx);">
          @if($log->outlet->type === 'franchise')
            ₹{{ number_format($log->commission_amount, 2) }}
          @else
            <span class="td3">—</span>
          @endif
        </td>
        <td class="mono" style="text-align: right; color: var(--green-tx); font-weight: 600;">₹{{ number_format($log->net_revenue, 2) }}</td>
        <td>
          <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <a href="{{ route('sales-logs.show', $log->id) }}" class="td-act">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              View Sheet
            </a>
            <form action="{{ route('sales-logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this sales log? Stock levels will be restored to the outlet.');" style="display: inline;">
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
        <td colspan="7" class="text-center td2">No sales logged yet. <a href="{{ route('sales-logs.create') }}">Log sales now</a>.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
