@extends('layouts.app')

@section('title', 'Outlet Details — DessertOps')
@section('breadcrumb', 'Outlet Details')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">{{ $outlet->name }}</div>
    <div class="ph-sub">
      Type: @if($outlet->type === 'own') Own Outlet @else Franchise ({{ number_format($outlet->commission_rate, 1) }}% commission) @endif
      <span class="ph-sub-dot"></span>
      Manager: {{ $outlet->contact_person ?? 'N/A' }}
    </div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('outlets.edit', $outlet->id) }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit Profile
    </a>
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 340px; gap: 16px;">
  <!-- Left: Inventory Stock at Outlet -->
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
      </div>
      <div class="ch-title">Current Dessert Stock at Location</div>
    </div>
    <table>
      <thead>
        <tr>
          <th>SKU</th>
          <th>Dessert Product</th>
          <th style="text-align: right;">Current Stock</th>
          <th style="text-align: right;">Unit Price (₹)</th>
        </tr>
      </thead>
      <tbody>
        @forelse($outlet->stocks as $stock)
        <tr>
          <td class="mono">{{ $stock->product->sku }}</td>
          <td style="font-weight: 600;">{{ $stock->product->name }}</td>
          <td class="mono font-semibold" style="text-align: right; color: var(--green-tx); font-weight: 600;">
            {{ number_format($stock->quantity, 0) }} Units
          </td>
          <td class="mono" style="text-align: right;">₹{{ number_format($stock->product->retail_price, 2) }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="4" class="text-center td2" style="padding: 30px 10px;">
            No stock dispatched to this location yet. 
            <div style="margin-top:10px;">
              <a href="{{ route('dispatches.create', ['outlet_id' => $outlet->id]) }}" class="btn-pri" style="display:inline-flex;">
                Ship Desserts
              </a>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Right: Metadata & History Panels -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    <!-- Outlet Contact Info Card -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Store Information</div>
      </div>
      <div class="cb" style="font-size: 13px;">
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Type</div>
          <div style="font-weight:600; color:var(--txt);">
            @if($outlet->type === 'own') Company-Owned Retailer @else Franchise Partner @endif
          </div>
        </div>
        @if($outlet->type === 'franchise')
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Franchise Commission</div>
          <div style="font-weight:600; color:var(--purple-tx);">{{ number_format($outlet->commission_rate, 1) }}% per Sale</div>
        </div>
        @endif
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Contact Person</div>
          <div style="color:var(--txt2);">{{ $outlet->contact_person ?? 'None specified' }}</div>
        </div>
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Phone</div>
          <div style="color:var(--txt2); font-family: 'JetBrains Mono', monospace;">{{ $outlet->phone ?? 'None specified' }}</div>
        </div>
        <div>
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Location Address</div>
          <div style="color:var(--txt2); white-space: pre-line;">{{ $outlet->address ?? 'No address provided' }}</div>
        </div>
      </div>
    </div>

    <!-- Recent Dispatches -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Recent Shipments</div>
        <a href="{{ route('dispatches.create', ['outlet_id' => $outlet->id]) }}" class="ch-link">
          Ship
        </a>
      </div>
      <div class="cb" style="padding:0;">
        <div class="act-feed">
          @forelse($outlet->dispatches as $disp)
          <div class="act-item" style="padding: 10px 14px;">
            <div style="flex:1;">
              <div class="act-ti" style="font-size:12.5px;">
                <a href="{{ route('dispatches.show', $disp->id) }}" style="color:inherit; text-decoration:none;">
                  {{ $disp->dispatch_number }}
                </a>
              </div>
              <div class="act-de" style="font-size: 11.5px;">
                Date: {{ $disp->dispatch_date->format('Y-m-d') }}
              </div>
              <div style="margin-top: 4px;">
                @if($disp->status === 'pending')
                  <span class="badge bn">Pending</span>
                @elseif($disp->status === 'dispatched')
                  <span class="badge ba">In Transit</span>
                @else
                  <span class="badge bg">Delivered</span>
                @endif
              </div>
            </div>
          </div>
          @empty
          <div style="padding: 16px; text-align:center; color:var(--txt3); font-size:12px;">No shipments logged.</div>
          @endforelse
        </div>
      </div>
    </div>

    <!-- Recent Sales Logs -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Recent Sales Reports</div>
        <a href="{{ route('sales-logs.create', ['outlet_id' => $outlet->id]) }}" class="ch-link">
          Log Sales
        </a>
      </div>
      <div class="cb" style="padding:0;">
        <div class="act-feed">
          @forelse($outlet->salesLogs as $log)
          <div class="act-item" style="padding: 10px 14px;">
            <div style="flex:1;">
              <div class="act-ti" style="font-size:12.5px;">
                <a href="{{ route('sales-logs.show', $log->id) }}" style="color:inherit; text-decoration:none;">
                  Sales Report — {{ $log->log_date->format('M d, Y') }}
                </a>
              </div>
              <div class="act-tm">Logged at {{ $log->created_at->format('M d, H:i') }}</div>
            </div>
          </div>
          @empty
          <div style="padding: 16px; text-align:center; color:var(--txt3); font-size:12px;">No sales logged.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
