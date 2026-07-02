@extends('layouts.app')

@section('title', 'Outlet Orders — DessertOps')
@section('breadcrumb', 'Outlet Orders')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Pending Store Orders</div>
    <div class="ph-sub">Review and fulfill product requests submitted by retail outlets and franchises</div>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success" style="margin-bottom: 20px;">
    {{ session('success') }}
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger" style="margin-bottom: 20px;">
    {{ session('error') }}
  </div>
@endif

<!-- Fulfillment Requirements Summary -->
@if($requirements->isNotEmpty())
  <div class="card mb-4">
    <div class="ch" style="background: var(--acc-lt);">
      <div class="ch-ic" style="background: var(--purple-lt); color: var(--purple-tx);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div>
        <div class="ch-title">Fulfillment Requirements Summary</div>
        <div class="ch-sub">Aggregated product quantities required to satisfy all pending orders</div>
      </div>
    </div>
    <div class="cb" style="padding: 0;">
      <table class="po-table">
        <thead>
          <tr>
            <th style="width: 20%;">SKU</th>
            <th style="width: 35%;">Dessert Product / Packaging</th>
            <th style="width: 15%; text-align: right;">Total Required</th>
            <th style="width: 15%; text-align: right;">Kitchen Stock</th>
            <th style="width: 15%; text-align: right;">Surplus / Deficit</th>
          </tr>
        </thead>
        <tbody>
          @foreach($requirements as $req)
            @php
              $sku = $req['sku'];
              $name = $req['name'];
              $reqQty = $req['requested_qty'];
              $stock = $req['stock'];
              $unit = $req['unit'];
              $diff = $stock - $reqQty;
            @endphp
            <tr>
              <td class="mono">{{ $sku }}</td>
              <td style="font-weight: 600;">
                {{ $name }}
                @if(!$req['is_product'])
                  <span style="font-size:11px; font-weight:normal; color:var(--txt3);"> (Packaging)</span>
                @endif
              </td>
              <td class="mono" style="text-align: right; font-weight: 700; color: var(--blue-tx);">
                {{ number_format($reqQty, 0) }} {{ $unit }}
              </td>
              <td class="mono" style="text-align: right; font-weight: 600;">
                {{ number_format($stock, 0) }} {{ $unit }}
              </td>
              <td class="mono" style="text-align: right; font-weight: 700; color: {{ $diff >= 0 ? 'var(--green)' : 'var(--red)' }};">
                @if($diff >= 0)
                  +{{ number_format($diff, 0) }} {{ $unit }}
                @else
                  {{ number_format($diff, 0) }} {{ $unit }}
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif

<!-- Pending Orders List -->
<div style="display: flex; flex-direction: column; gap: 20px;">
  @forelse($dispatches as $disp)
    <div class="card" style="overflow: visible;">
      <div class="ch" style="display: flex; justify-content: space-between; align-items: center; background: var(--acc-lt);">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: var(--radius); background: var(--purple-lt); color: var(--purple-tx);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
          </div>
          <div>
            <div style="font-weight: 700; font-size: 15px; color: var(--txt);">{{ $disp->outlet->name }}</div>
            <div class="td-meta" style="margin-top: 2px;">
              Order: <span class="mono" style="font-weight: 600;">{{ $disp->dispatch_number }}</span> &bull; 
              Submitted: <span class="mono">{{ $disp->created_at->diffForHumans() }}</span>
            </div>
          </div>
        </div>
        
        <div>
          <span class="badge {{ $disp->outlet->type === 'own' ? 'bg' : 'bp' }}">
            {{ $disp->outlet->type === 'own' ? 'Company Store' : 'Franchise Partner' }}
          </span>
        </div>
      </div>

      <div class="cb" style="padding: 0;">
        <table class="po-table">
          <thead>
            <tr>
              <th style="width: 25%;">SKU</th>
              <th style="width: 35%;">Dessert Product / Packaging</th>
              <th style="width: 20%; text-align: right;">Requested Qty</th>
              <th style="width: 20%; text-align: right;">Kitchen Stock</th>
            </tr>
          </thead>
          <tbody>
            @php $canFulfill = true; @endphp
            @foreach($disp->items as $item)
              @php 
                $isProduct = (bool)$item->product_id;
                $name = $isProduct ? $item->product->name : $item->material->name;
                $sku = $isProduct ? $item->product->sku : $item->material->sku;
                $reqQty = $item->quantity;
                
                if ($isProduct) {
                    $kitchenStock = $item->product->current_kitchen_stock;
                    $hasStock = $kitchenStock >= $reqQty;
                    $unit = 'Units';
                    $stockText = number_format($kitchenStock, 0) . ' Units';
                } else {
                    $material = $item->material;
                    $perBox = $material->per_box_qty ?: 1;
                    $reqBoxes = $reqQty / $perBox;
                    $kitchenStockBoxes = $material->kitchen_stock;
                    $hasStock = $kitchenStockBoxes >= $reqBoxes;
                    $unit = 'Pieces';
                    
                    $kitchenStockPieces = $kitchenStockBoxes * $perBox;
                    $stockText = number_format($kitchenStockPieces, 0) . ' Pieces (' . number_format($kitchenStockBoxes, 1) . ' boxes)';
                }

                if (!$hasStock) {
                    $canFulfill = false;
                }
              @endphp
              <tr>
                <td class="mono">{{ $sku }}</td>
                <td style="font-weight: 600;">
                  {{ $name }}
                  @if(!$isProduct)
                    <span style="font-size:11px; font-weight:normal; color:var(--txt3);"> (Packaging)</span>
                  @endif
                </td>
                <td class="mono" style="text-align: right; font-weight: 600; color: var(--blue-tx);">
                  {{ number_format($reqQty, 0) }} {{ $unit }}
                </td>
                <td class="mono" style="text-align: right; font-weight: 600; color: {{ $hasStock ? 'var(--green)' : 'var(--red)' }};">
                  {{ $stockText }}
                  <div style="font-size: 10.5px; font-weight: normal; margin-top: 2px;">
                    @if($hasStock)
                      <span style="color: var(--green);">&bull; Ready to Ship</span>
                    @else
                      <span style="color: var(--red);">&bull; Insufficient Stock</span>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <!-- Card Footer Actions -->
        <div style="padding: 16px; border-top: 1px solid var(--div2); display: flex; justify-content: space-between; align-items: center; background: #fafafa;">
          <div style="font-size: 12.5px; color: var(--txt2);">
            @if($disp->notes)
              <span style="font-weight: 600; color: var(--txt3);">Store Remarks:</span> "{{ $disp->notes }}"
            @else
              <span style="color: var(--txt3); font-style: italic;">No store remarks provided.</span>
            @endif
          </div>
          
          <div style="display: flex; gap: 12px; align-items: center;">
            <form action="{{ route('dispatches.destroy', $disp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel and reject this order?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-ghost" style="padding: 8px 14px; font-weight: 600; color: var(--red-tx); font-size: 13px;">
                Cancel Order
              </button>
            </form>

            @if($canFulfill)
              <form action="{{ route('dispatches.dispatch', $disp->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-pri" style="background: var(--purple-tx); border-color: var(--purple-tx); color: #fff; padding: 8px 16px; font-weight: 600;">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 14px; height: 14px; stroke: #fff;"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/></svg>
                  Process & Dispatch
                </button>
              </form>
            @else
              <button type="button" class="btn-pri" disabled style="opacity: 0.5; cursor: not-allowed; padding: 8px 16px; font-weight: 600;">
                Fulfillment Blocked
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="card" style="padding: 40px; text-align: center;">
      <div style="font-size: 32px; margin-bottom: 12px;">🎉</div>
      <div style="font-weight: 700; font-size: 16px; color: var(--txt); margin-bottom: 4px;">All Caught Up!</div>
      <div style="color: var(--txt3); font-size: 13.5px;">No pending orders from store outlets are currently awaiting fulfillment.</div>
    </div>
  @endforelse
</div>

<!-- Recently Cancelled Orders -->
@if($cancelledDispatches->isNotEmpty())
  <div class="card mt-4" style="margin-top: 30px;">
    <div class="ch" style="background: var(--row-hov);">
      <div class="ch-ic" style="background: var(--red-lt); color: var(--red-tx);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
      </div>
      <div>
        <div class="ch-title">Recently Cancelled Orders</div>
        <div class="ch-sub">History of cancelled requests submitted by outlets</div>
      </div>
    </div>
    <div class="cb" style="padding: 0;">
      <table class="po-table">
        <thead>
          <tr>
            <th style="width: 20%;">Order Number</th>
            <th style="width: 25%;">Outlet</th>
            <th style="width: 40%;">Requested Products</th>
            <th style="width: 15%; text-align: right;">Cancelled</th>
          </tr>
        </thead>
        <tbody>
          @foreach($cancelledDispatches as $disp)
            <tr>
              <td class="mono font-semibold" style="font-weight: 600;">{{ $disp->dispatch_number }}</td>
              <td style="font-weight: 600;">{{ $disp->outlet->name }}</td>
              <td>
                <ul style="margin: 0; padding: 0 0 0 14px; font-size: 12px; color: var(--txt2); line-height: 1.5;">
                  @foreach($disp->items as $item)
                    @if($item->product_id)
                      <li>{{ $item->product->name }}: <b>{{ number_format($item->quantity, 0) }} Units</b></li>
                    @else
                      <li>{{ $item->material->name }}: <b>{{ number_format($item->quantity, 0) }} Pieces</b></li>
                    @endif
                  @endforeach
                </ul>
              </td>
              <td class="mono" style="text-align: right; color: var(--txt3); font-size: 11.5px;">
                {{ $disp->updated_at->diffForHumans() }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endif
@endsection
