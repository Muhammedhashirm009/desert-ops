@extends('layouts.app')

@section('title', $supplier->name . ' — DessertOps')
@section('breadcrumb', 'Supplier Details')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">{{ $supplier->name }}</div>
    <div class="ph-sub">
      {{ $supplier->contact_person ?? 'No contact person' }}
      <span class="ph-sub-dot"></span>
      {{ $supplier->email ?? 'No email' }}
      <span class="ph-sub-dot"></span>
      {{ $supplier->phone ?? 'No phone' }}
    </div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit Supplier
    </a>
    <a href="{{ route('suppliers.index') }}" class="btn-ghost">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Back to List
    </a>
  </div>
</div>

<div class="row r-3-1" style="grid-template-columns: 1fr 340px; gap: 16px;">
  <!-- Left: Linked Materials -->
  <div class="card">
    <div class="ch">
      <div class="ch-ic" style="background:var(--div);">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke:var(--txt2)"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
      </div>
      <div class="ch-title">Linked Materials Catalog</div>
      <button type="button" class="btn-pri" id="toggle-link-form" style="margin-left:auto; font-size:12px; padding:6px 14px;">
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Link Material
      </button>
    </div>
    <table class="tbl">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Material Name</th>
          <th>Category</th>
          <th style="text-align:right;">Supplier Unit Price (₹)</th>
          <th>Preferred</th>
          <th>Notes</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($supplier->materials as $mat)
        <tr>
          <td data-label="SKU" class="mono">{{ $mat->sku }}</td>
          <td data-label="Material Name" class="td-name">{{ $mat->name }}</td>
          <td data-label="Category">
            @if($mat->category === 'ingredient')
              <span class="badge bp">Ingredient</span>
            @else
              <span class="badge bb">Packaging</span>
            @endif
          </td>
          <td data-label="Unit Price" class="mono" style="text-align:right;">₹{{ number_format($mat->pivot->unit_price, 2) }}</td>
          <td data-label="Preferred">
            @if($mat->pivot->is_preferred)
              <span class="badge bg">Yes</span>
            @else
              <span style="color:var(--txt3); font-size:12px;">No</span>
            @endif
          </td>
          <td data-label="Notes" style="max-width:160px; font-size:12px; color:var(--txt2);">{{ $mat->pivot->notes ?? '—' }}</td>
          <td data-label="Actions">
            <form action="{{ route('suppliers.unlink-material', [$supplier->id, $mat->id]) }}" method="POST" onsubmit="return confirm('Unlink this material from the supplier?');" style="display:inline;">
              @csrf
              @method('DELETE')
              <button type="submit" class="td-act po-row-btn" style="padding:0; font-size:13px; color:var(--red-tx);">
                <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
                Unlink
              </button>
            </form>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center td2" style="padding:30px 10px;">No materials linked to this supplier yet.</td>
        </tr>
        @endforelse
      </tbody>
    </table>

    <!-- Link Material Inline Form -->
    <div id="link-form" style="display:none; border-top:1px solid var(--div); padding:20px;">
      <div style="font-size:13px; font-weight:700; color:var(--txt); margin-bottom:14px;">Link a New Material</div>
      <form action="{{ route('suppliers.link-material', $supplier->id) }}" method="POST">
        @csrf
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
          <div class="form-grp">
            <label for="material_id" style="font-size:12px; font-weight:600; color:var(--txt2); margin-bottom:4px; display:block;">Material</label>
            <select name="material_id" id="material_id" class="form-input searchable-select" required style="height:38px;">
              <option value="">Select material…</option>
              @foreach($allMaterials as $material)
                @if(!$supplier->materials->contains($material->id))
                  <option value="{{ $material->id }}">{{ $material->sku }} — {{ $material->name }}</option>
                @endif
              @endforeach
            </select>
          </div>
          <div class="form-grp">
            <label for="unit_price" style="font-size:12px; font-weight:600; color:var(--txt2); margin-bottom:4px; display:block;">Unit Price (₹)</label>
            <input type="number" name="unit_price" id="unit_price" class="form-input" step="0.01" min="0" required placeholder="0.00">
          </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px;">
          <div class="form-grp">
            <label for="notes" style="font-size:12px; font-weight:600; color:var(--txt2); margin-bottom:4px; display:block;">Notes</label>
            <textarea name="notes" id="notes" class="form-input" rows="2" placeholder="Optional notes…" style="resize:vertical;"></textarea>
          </div>
          <div class="form-grp" style="display:flex; flex-direction:column; justify-content:center;">
            <label style="font-size:12px; font-weight:600; color:var(--txt2); display:flex; align-items:center; gap:8px; cursor:pointer;">
              <input type="checkbox" name="is_preferred" value="1" style="width:16px; height:16px;">
              Mark as Preferred Supplier
            </label>
          </div>
        </div>
        <div style="margin-top:16px; display:flex; gap:10px;">
          <button type="submit" class="btn-pri" style="font-size:13px; padding:8px 20px;">
            <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            Link Material
          </button>
          <button type="button" class="btn-ghost" id="cancel-link-form" style="font-size:13px; padding:8px 20px;">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Right: Sidebar -->
  <div style="display: flex; flex-direction: column; gap: 16px;">
    <!-- Supplier Info Card -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Supplier Information</div>
      </div>
      <div class="cb" style="font-size: 13px;">
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Supplier Name</div>
          <div style="font-weight:600; color:var(--txt);">{{ $supplier->name }}</div>
        </div>
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Contact Person</div>
          <div style="color:var(--txt2);">{{ $supplier->contact_person ?? 'None specified' }}</div>
        </div>
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Email</div>
          <div style="color:var(--txt2); font-family: 'JetBrains Mono', monospace;">{{ $supplier->email ?? 'None specified' }}</div>
        </div>
        <div style="margin-bottom: 8px;">
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Phone</div>
          <div style="color:var(--txt2); font-family: 'JetBrains Mono', monospace;">{{ $supplier->phone ?? 'None specified' }}</div>
        </div>
        <div>
          <div style="font-size:11px; color:var(--txt3); font-weight:600; text-transform:uppercase;">Address</div>
          <div style="color:var(--txt2); white-space: pre-line;">{{ $supplier->address ?? 'No address provided' }}</div>
        </div>
      </div>
    </div>

    <!-- Recent Purchase Orders -->
    <div class="card">
      <div class="ch">
        <div class="ch-title">Recent Purchase Orders</div>
      </div>
      <div class="cb" style="padding:0;">
        <div class="act-feed">
          @forelse($supplier->purchaseOrders->take(10) as $po)
          <div class="act-item" style="padding: 10px 14px;">
            <div style="flex:1;">
              <div class="act-ti" style="font-size:12.5px;">
                <a href="{{ route('purchase-orders.show', $po->id) }}" style="color:inherit; text-decoration:none;">
                  {{ $po->po_number }}
                </a>
              </div>
              <div class="act-de" style="font-size: 11.5px;">
                Total: ₹{{ number_format($po->total_amount, 2) }}
              </div>
              <div style="margin-top: 4px;">
                @if($po->status === 'received')
                  <span class="badge bg">Received</span>
                @elseif($po->status === 'cancelled')
                  <span class="badge br">Cancelled</span>
                @else
                  <span class="badge ba">Pending</span>
                @endif
              </div>
            </div>
          </div>
          @empty
          <div style="padding: 16px; text-align:center; color:var(--txt3); font-size:12px;">No purchase orders yet.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggle-link-form');
    const cancelBtn = document.getElementById('cancel-link-form');
    const linkForm = document.getElementById('link-form');

    toggleBtn.addEventListener('click', function() {
        linkForm.style.display = linkForm.style.display === 'none' ? 'block' : 'none';
    });

    cancelBtn.addEventListener('click', function() {
        linkForm.style.display = 'none';
    });

    const matSelect = document.getElementById('material_id');
    if (matSelect && window.initSearchableSelect) {
        window.initSearchableSelect(matSelect);
    }
});
</script>
@endsection
