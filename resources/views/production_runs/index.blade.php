@extends('layouts.app')

@section('title', 'Production Runs — DessertOps')
@section('breadcrumb', 'Central Kitchen Production')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Central Kitchen Production Runs</div>
    <div class="ph-sub">Log dessert preparation batches and increment finished product stocks</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('production-runs.create') }}" class="btn-pri">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Log Production Run
    </a>
  </div>
</div>

<div class="card">
  <div class="ch">
    <div class="ch-ic" style="background:var(--div);">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
    </div>
    <div class="ch-title">Production Runs History</div>
  </div>
  <table>
    <thead>
      <tr>
        <th>Run No.</th>
        <th>Product Prepared</th>
        <th style="text-align: right;">Quantity Produced</th>
        <th>Status</th>
        <th>Prepared Date</th>
        <th>Created Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($productionRuns as $run)
      <tr>
        <td class="mono font-semibold">{{ $run->run_number }}</td>
        <td>
          <div class="td-name">{{ $run->product->name }}</div>
          <div class="td-meta">SKU: {{ $run->product->sku }}</div>
        </td>
        <td class="mono font-semibold" style="text-align: right; padding-right: 20px;">
          {{ number_format($run->quantity_produced, 0) }} units
        </td>
        <td>
          @if($run->status === 'completed')
            <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Completed</span>
          @else
            <span class="badge ba"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Pending Completion</span>
          @endif
        </td>
        <td class="mono td3">{{ $run->prepared_date->format('d M Y') }}</td>
        <td class="mono td3">{{ $run->created_at->format('d M Y h:i A') }}</td>
        <td>
          <a href="{{ route('production-runs.show', $run->id) }}" class="td-act">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            View Details
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="text-center td2">No production runs recorded yet. <a href="{{ route('production-runs.create') }}">Log a run now</a>.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
