@extends('layouts.app')

@section('title', 'Material Request ' . $materialRequest->request_number . ' — DessertOps')
@section('breadcrumb', 'Material Request Details')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Material Request: {{ $materialRequest->request_number }}</div>
    <div class="ph-sub">
      Submitted by {{ $materialRequest->requested_by }} on {{ $materialRequest->requested_date->format('d F Y') }}
      <span class="ph-sub-dot"></span>
      Status: 
      @if($materialRequest->status === 'released')
        <span class="badge bg"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Released</span>
      @elseif($materialRequest->status === 'approved')
        <span class="badge bb"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>Approved</span>
      @elseif($materialRequest->status === 'rejected')
        <span class="badge br"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>Rejected</span>
      @else
        <span class="badge ba"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Pending</span>
      @endif
    </div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('material-requests.index') }}" class="btn-ghost">
      Back to List
    </a>
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 300px; gap: 16px;">
  <!-- Details & Items -->
  <div class="card">
    <div class="ch">
      <div class="ch-title">Requested Line Items</div>
    </div>
    
    @if($materialRequest->status === 'approved' && in_array(auth()->user()->role, ['admin', 'gm', 'store_manager']))
      <!-- Store Manager Release Form -->
      <form action="{{ route('material-requests.release', $materialRequest->id) }}" method="POST">
        @csrf
        <div class="cb" style="padding: 0;">
          <table>
            <thead>
              <tr>
                <th style="width: 15%;">SKU</th>
                <th style="width: 35%;">Raw Material</th>
                <th style="width: 25%; text-align: right;">Requested Qty</th>
                <th style="width: 25%;">Released Qty *</th>
              </tr>
            </thead>
            <tbody>
              @foreach($materialRequest->items as $index => $item)
              <tr>
                <td class="mono">{{ $item->material->sku }}</td>
                <td>
                  <div style="font-weight: 600; color: var(--txt);">{{ $item->material->name }}</div>
                  <div style="font-size: 11px; color: {{ $item->material->current_stock > $item->quantity_requested ? 'var(--green-tx)' : 'var(--red-tx)' }};">
                    Available Stock: {{ number_format($item->material->current_stock, 2) }} {{ $item->material->unit }}
                  </div>
                  <input type="hidden" name="items[{{ $index }}][material_id]" value="{{ $item->material_id }}">
                </td>
                <td class="mono font-semibold" style="text-align: right; padding-right: 20px;">
                  {{ number_format($item->quantity_requested, 2) }} {{ $item->material->unit }}
                </td>
                <td>
                  <div style="display: flex; align-items: center; gap: 6px;">
                    <input type="number" step="0.01" name="items[{{ $index }}][quantity_released]" 
                           class="form-input" required min="0" max="{{ $item->material->current_stock }}" 
                           value="{{ old('items.'.$index.'.quantity_released', $item->quantity_requested) }}" 
                           style="padding: 6px 8px; width: 100px;">
                    <span style="color: var(--txt3); font-size: 12.5px;">{{ $item->material->unit }}</span>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          
          <div style="padding: 16px; border-top: 1px solid var(--div); display: flex; justify-content: flex-end; gap: 10px;">
            <button type="submit" class="btn-pri" style="background: var(--green);">
              <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 13px; height: 13px;"><polyline points="20 6 9 17 4 12"/></svg>
              Confirm Release (Update Inventory)
            </button>
          </div>
        </div>
      </form>
    @else
      <!-- Static Table View -->
      <div class="cb" style="padding: 0;">
        <table>
          <thead>
            <tr>
              <th style="width: 20%;">SKU</th>
              <th style="width: 50%;">Raw Material</th>
              <th style="width: 15%; text-align: right;">Requested Qty</th>
              <th style="width: 15%; text-align: right;">Released Qty</th>
            </tr>
          </thead>
          <tbody>
            @foreach($materialRequest->items as $item)
            <tr>
              <td class="mono">{{ $item->material->sku }}</td>
              <td>
                <div style="font-weight: 600; color: var(--txt);">{{ $item->material->name }}</div>
                <div style="font-size: 11px; color: var(--txt3);">Unit: {{ $item->material->unit }}</div>
              </td>
              <td class="mono font-semibold" style="text-align: right;">
                {{ number_format($item->quantity_requested, 2) }} {{ $item->material->unit }}
              </td>
              <td class="mono font-semibold" style="text-align: right; color: {{ $item->quantity_released > 0 ? 'var(--green-tx)' : 'var(--txt3)' }};">
                {{ $materialRequest->status === 'released' ? number_format($item->quantity_released, 2) . ' ' . $item->material->unit : '—' }}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  <!-- Actions & Sidebar info -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    <!-- Workflow Actions for Store Manager -->
    @if($materialRequest->status === 'pending' && in_array(auth()->user()->role, ['admin', 'gm', 'store_manager']))
    <div class="card">
      <div class="ch">
        <div class="ch-title">Request Review</div>
      </div>
      <div class="cb">
        <p style="font-size: 12.5px; color: var(--txt2); margin-bottom: 15px;">
          As Store Manager, review and approve this material request from the kitchen.
        </p>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
          <form action="{{ route('material-requests.approve', $materialRequest->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn-pri" style="width: 100%; justify-content: center; background: var(--btn);">
              Approve Request
            </button>
          </form>
          
          <form action="{{ route('material-requests.reject', $materialRequest->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn-ghost" style="width: 100%; justify-content: center; color: var(--red-tx); border-color: var(--red-tx);">
              Reject Request
            </button>
          </form>
        </div>
      </div>
    </div>
    @endif

    @if($materialRequest->status === 'approved' && in_array(auth()->user()->role, ['admin', 'gm', 'store_manager']))
    <div class="card">
      <div class="ch">
        <div class="ch-title">Reject Request</div>
      </div>
      <div class="cb">
        <form action="{{ route('material-requests.reject', $materialRequest->id) }}" method="POST">
          @csrf
          <button type="submit" class="btn-ghost" style="width: 100%; justify-content: center; color: var(--red-tx); border-color: var(--red-tx);">
            Reject Request
          </button>
        </form>
      </div>
    </div>
    @endif

    @if(!in_array(auth()->user()->role, ['laban_chef', 'baklava_chef', 'dough_chef']))
    <div class="card">
      <div class="ch">
        <div class="ch-title">Request Notes</div>
      </div>
      <div class="cb">
        <p style="font-size: 13px; color: var(--txt2); line-height: 1.6; white-space: pre-line;">
          {{ $materialRequest->notes ?? 'No remarks provided.' }}
        </p>
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
