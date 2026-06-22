@extends('layouts.app')

@section('title', 'Production Run ' . $productionRun->run_number . ' — DessertOps')
@section('breadcrumb', 'Production Details')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Production Run: {{ $productionRun->run_number }}</div>
    <div class="ph-sub">
      Processed on {{ $productionRun->prepared_date->format('d F Y') }}
      <span class="ph-sub-dot"></span>
      Status: 
      @if($productionRun->status === 'completed')
        <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Completed & Stock Updated</span>
      @else
        <span class="badge ba"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Pending Completion</span>
      @endif
    </div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('production-runs.index') }}" class="btn-ghost">
      Back to List
    </a>
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 300px; gap: 16px;">
  <!-- Details Card -->
  <div class="card">
    <div class="ch">
      <div class="ch-title">Production Run details</div>
    </div>
    <div class="cb">
      <table style="border: none;">
        <tbody>
          <tr style="background: none;">
            <td style="width: 25%; font-weight: 600; color: var(--txt3); border: none; padding: 10px 0;">Finished Product:</td>
            <td style="font-weight: 700; color: var(--txt); border: none; padding: 10px 0;">
              {{ $productionRun->product->name }} (SKU: {{ $productionRun->product->sku }})
            </td>
          </tr>
          <tr style="background: none;">
            <td style="font-weight: 600; color: var(--txt3); border: none; padding: 10px 0;">Quantity Prepared:</td>
            <td style="font-family: 'JetBrains Mono', monospace; font-weight: 700; color: var(--txt); border: none; padding: 10px 0;">
              {{ number_format($productionRun->quantity_produced, 0) }} units
            </td>
          </tr>
          <tr style="background: none;">
            <td style="font-weight: 600; color: var(--txt3); border: none; padding: 10px 0;">Prepared Date:</td>
            <td style="font-weight: 700; color: var(--txt); border: none; padding: 10px 0;">
              {{ $productionRun->prepared_date->format('d F Y') }}
            </td>
          </tr>
          <tr style="background: none;">
            <td style="font-weight: 600; color: var(--txt3); border: none; padding: 10px 0;">Internal Notes:</td>
            <td style="color: var(--txt2); font-style: italic; border: none; padding: 10px 0; white-space: pre-line;">
              {{ $productionRun->notes ?? 'No batch comments entered.' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Sidebar Actions -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    @if($productionRun->status === 'pending')
    <div class="card">
      <div class="ch">
        <div class="ch-title">Production Status</div>
      </div>
      <div class="cb">
        <p style="font-size: 12.5px; color: var(--txt2); margin-bottom: 15px;">
          Confirm completion of this batch to update finished dessert stocks in the central kitchen inventory.
        </p>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <form action="{{ route('production-runs.complete', $productionRun->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; background: var(--green);">
              Complete Production Run
            </button>
          </form>
          
          <form action="{{ route('production-runs.destroy', $productionRun->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this pending run?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-ghost" style="width: 100%; justify-content: center; color: var(--red-tx); border-color: var(--red-tx);">
              Delete Pending Run
            </button>
          </form>
        </div>
      </div>
    </div>
    @else
    <div class="card">
      <div class="ch">
        <div class="ch-title">Inventory Updated</div>
      </div>
      <div class="cb" style="background: var(--green-lt); color: var(--green-tx); border-radius: 0 0 var(--radius) var(--radius); padding: 12px 16px; font-size: 13px;">
        This production run is finalized. The stock of <strong>{{ $productionRun->product->name }}</strong> in the Central Kitchen has been incremented by <strong>{{ number_format($productionRun->quantity_produced, 0) }} units</strong> (Current total stock: {{ number_format($productionRun->product->current_kitchen_stock, 0) }} units).
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
