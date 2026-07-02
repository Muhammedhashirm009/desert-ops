@extends('layouts.portal')

@section('title', "Request {$showcaseRequest->request_number} — DessertOps Portal")
@section('breadcrumb', "Showcase Request Details")

@section('content')
@php
  $isManagerOrChef = auth('web')->check() && in_array(auth('web')->user()->role, ['admin', 'gm', 'kitchen_chef', 'store_manager']);
@endphp

<div class="ph">
  <div>
    <div class="ph-title">Showcase Request: {{ $showcaseRequest->request_number }}</div>
    <div class="ph-sub">Submitted by <b>{{ $showcaseRequest->requested_by }}</b> on {{ $showcaseRequest->requested_date ? $showcaseRequest->requested_date->format('Y-m-d') : $showcaseRequest->created_at->format('Y-m-d') }}</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('portal.showcase-requests.index') }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="width: 14px; height: 14px; margin-right: 4px;"><polyline points="15 18 9 12 15 6"/></svg>
      Back to List
    </a>
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 320px; gap: 16px;">
  <!-- Left Side: Items & Action Forms -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    
    <!-- Items Card -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Requested Items</div>
        <div style="margin-left: auto;">
          @if($showcaseRequest->status === 'pending')
            <span class="badge bn">Pending Approval</span>
          @elseif($showcaseRequest->status === 'approved')
            <span class="badge ba">Approved (Awaiting Release)</span>
          @elseif($showcaseRequest->status === 'rejected')
            <span class="badge br">Rejected</span>
          @elseif($showcaseRequest->status === 'released')
            <span class="badge bg">Released to Showcase</span>
          @endif
        </div>
      </div>
      <div class="cb" style="padding: 0;">
        <table class="tbl">
          <thead>
            <tr>
              <th style="width: 40%;">Product</th>
              <th style="width: 20%; text-align: right;">Qty Requested</th>
              <th style="width: 20%; text-align: right;">Qty Released</th>
              <th style="width: 20%; text-align: center;">Release Source</th>
            </tr>
          </thead>
          <tbody>
            @foreach($showcaseRequest->items as $item)
            <tr>
              <td data-label="Product">
                <div style="font-weight: 600; color: var(--txt);">{{ $item->product->name }}</div>
                <div style="font-size: 11px; color: var(--txt3);" class="mono">{{ $item->product->sku }}</div>
              </td>
              <td data-label="Qty Requested" class="mono" style="text-align: right; font-weight: 600;">
                {{ number_format($item->quantity_requested, 2) }}
              </td>
              <td data-label="Qty Released" class="mono" style="text-align: right;">
                @if($showcaseRequest->status === 'released')
                  <span style="color: var(--green-tx); font-weight: 600;">{{ number_format($item->quantity_released, 2) }}</span>
                @else
                  <span class="td3">—</span>
                @endif
              </td>
              <td data-label="Release Source" class="text-center">
                @if($showcaseRequest->status === 'released' && $item->release_source)
                  <span class="badge bp">{{ $item->release_source }}</span>
                @else
                  <span class="td3">—</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- Manager/Chef Release Form (Visible only when status is 'approved' for managers/chefs) -->
    @if($showcaseRequest->status === 'approved' && $isManagerOrChef)
    <div class="card" style="border: 1px solid var(--purple-tx);">
      <div class="ch" style="background: rgba(139, 92, 246, 0.05);">
        <div class="ch-ic" style="background: var(--purple-lt);">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" style="stroke: var(--purple-tx)"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="ch-title" style="color: var(--purple-tx);">Release Stock to Showcase</div>
      </div>
      <div class="cb">
        <form action="{{ route('portal.showcase-requests.release', $showcaseRequest->id) }}" method="POST">
          @csrf
          <p style="font-size: 13px; color: var(--txt2); margin-bottom: 16px;">
            Specify the inventory source (Store Stock or Kitchen Stock) and quantity to release for each requested dessert product.
          </p>

          <table class="tbl" style="margin-bottom: 20px;">
            <thead>
              <tr>
                <th>Product</th>
                <th style="width: 20%; text-align: right;">Requested</th>
                <th style="width: 40%;">Release Source *</th>
                <th style="width: 25%;">Qty to Release *</th>
              </tr>
            </thead>
            <tbody>
              @foreach($showcaseRequest->items as $item)
                @php
                  $stockRecord = $detailedStocks[$item->product_id] ?? null;
                  $storeQty = $stockRecord ? (float)$stockRecord->store_quantity : 0.00;
                  $kitchenQty = $stockRecord ? (float)$stockRecord->kitchen_quantity : 0.00;
                @endphp
                <tr>
                  <td>
                    <div style="font-weight: 500;">{{ $item->product->name }}</div>
                  </td>
                  <td class="mono" style="text-align: right;">
                    {{ number_format($item->quantity_requested, 2) }}
                  </td>
                  <td>
                    <select name="items[{{ $item->id }}][release_source]" class="form-input" required style="width: 100%; padding: 4px 8px; font-size: 12.5px;">
                      <option value="store" {{ $storeQty >= $item->quantity_requested ? 'selected' : '' }}>Store Stock (Avail: {{ number_format($storeQty, 1) }})</option>
                      <option value="kitchen" {{ $storeQty < $item->quantity_requested && $kitchenQty >= $item->quantity_requested ? 'selected' : '' }}>Kitchen Stock (Avail: {{ number_format($kitchenQty, 1) }})</option>
                    </select>
                  </td>
                  <td>
                    <input type="number" step="0.01" name="items[{{ $item->id }}][quantity_released]" class="form-input mono" required min="0.01" value="{{ $item->quantity_requested }}" style="width: 100%; padding: 4px 8px; font-size: 12.5px; text-align: right;">
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

          <div style="display: flex; justify-content: flex-end;">
            <button type="submit" class="btn-pri" style="background: var(--purple-tx); border-color: var(--purple-tx); font-weight: 600;">
              Confirm and Release to Showcase
            </button>
          </div>
        </form>
      </div>
    </div>
    @endif

    <!-- Manager/Chef Approval Controls (Visible when status is 'pending' for managers/chefs) -->
    @if($showcaseRequest->status === 'pending' && $isManagerOrChef)
    <div class="card" style="border: 1px solid var(--div2);">
      <div class="ch">
        <div class="ch-title">Request Authorization Actions</div>
      </div>
      <div class="cb" style="display: flex; gap: 12px; align-items: center;">
        <form action="{{ route('portal.showcase-requests.approve', $showcaseRequest->id) }}" method="POST" style="display:inline;">
          @csrf
          <button type="submit" class="btn-pri" style="background: var(--green); border-color: var(--green); color: #fff; font-weight: 600;">
            Approve Request
          </button>
        </form>

        <form action="{{ route('portal.showcase-requests.reject', $showcaseRequest->id) }}" method="POST" style="display:inline;">
          @csrf
          <button type="submit" class="btn-pri" style="background: var(--red); border-color: var(--red); color: #fff; font-weight: 600;">
            Reject Request
          </button>
        </form>
      </div>
    </div>
    @endif

  </div>

  <!-- Right Side: Meta Summary Info -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    
    <!-- Summary Card -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Request Summary</div>
      </div>
      <div class="cb" style="display: flex; flex-direction: column; gap: 14px;">
        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Request Number</label>
          <div class="mono" style="font-size: 14px; font-weight: 700; color: var(--txt); margin-top: 2px;">{{ $showcaseRequest->request_number }}</div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Status</label>
          <div style="margin-top: 4px;">
            @if($showcaseRequest->status === 'pending')
              <span class="badge bn">Pending</span>
            @elseif($showcaseRequest->status === 'approved')
              <span class="badge ba">Approved</span>
            @elseif($showcaseRequest->status === 'rejected')
              <span class="badge br">Rejected</span>
            @elseif($showcaseRequest->status === 'released')
              <span class="badge bg">Released</span>
            @endif
          </div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Requested By</label>
          <div style="font-size: 13px; font-weight: 500; color: var(--txt2); margin-top: 2px;">{{ $showcaseRequest->requested_by }}</div>
        </div>

        <div>
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Date Submitted</label>
          <div class="mono" style="font-size: 13px; color: var(--txt2); margin-top: 2px;">
            {{ $showcaseRequest->requested_date ? $showcaseRequest->requested_date->format('Y-m-d') : $showcaseRequest->created_at->format('Y-m-d') }}
          </div>
        </div>

        @if($showcaseRequest->notes)
        <div style="border-top: 1px solid var(--div); padding-top: 12px;">
          <label style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600;">Notes</label>
          <div style="font-size: 12.5px; color: var(--txt2); margin-top: 4px; line-height: 1.4; white-space: pre-line;">{{ $showcaseRequest->notes }}</div>
        </div>
        @endif
      </div>
    </div>

    <!-- Manager/Chef Authorization Status Notice -->
    <div class="card" style="background: rgba(255, 255, 255, 0.01); border: 1px dashed var(--div2);">
      <div class="cb" style="font-size: 12.5px; line-height: 1.4; color: var(--txt2);">
        @if($isManagerOrChef)
          <div style="display: flex; align-items: center; gap: 6px; color: var(--green-tx); font-weight: 600; margin-bottom: 6px;">
            <span class="on-dot" style="background: var(--green); width: 6px; height: 6px;"></span>
            Authorized Session
          </div>
          <span>You are logged in as <b>{{ auth('web')->user()->name }}</b> ({{ ucfirst(auth('web')->user()->role) }}). You have administrative access to approve, reject, or release items.</span>
        @else
          <div style="display: flex; align-items: center; gap: 6px; color: var(--txt3); font-weight: 600; margin-bottom: 6px;">
            <span class="on-dot" style="background: var(--txt3); width: 6px; height: 6px;"></span>
            Outlet Operator View
          </div>
          <span style="opacity: 0.85;">Approval and release operations require authorization. Log in as a Store Manager, Chef, or GM on the central system to manage status.</span>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection
