@extends('layouts.app')

@section('title', 'Add Material — DessertOps')
@section('breadcrumb', 'New Material')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Add New Material Item</div>
    <div class="ph-sub">Define a new raw ingredient or packaging item in inventory</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('materials.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
  <div class="ch">
    <div class="ch-title">Material Properties</div>
  </div>
  <div class="cb">
    <form action="{{ route('materials.store') }}" method="POST">
      @csrf

      <div class="grid-2">
        <div class="form-grp">
          <label for="name">Material Name *</label>
          <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}" required placeholder="e.g. White Sugar">
          @error('name')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp">
          <label for="sku">SKU Code *</label>
          <input type="text" name="sku" id="sku" class="form-input" value="{{ old('sku') }}" required placeholder="e.g. RAW-SUG-001">
          @error('sku')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="grid-2">
        <div class="form-grp">
          <label for="category">Category *</label>
          <select name="category" id="category" class="form-input" required style="height: 38px;">
            <option value="ingredient" {{ old('category') === 'ingredient' ? 'selected' : '' }}>Ingredient</option>
            <option value="packaging" {{ old('category') === 'packaging' ? 'selected' : '' }}>Packaging (Wrapper/Box)</option>
          </select>
          @error('category')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp">
          <label for="unit">Stock Unit *</label>
          <input type="text" name="unit" id="unit" class="form-input" value="{{ old('unit') }}" required placeholder="e.g. kg, L, pcs, box">
          @error('unit')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="grid-2">
        <div class="form-grp">
          <label for="current_stock">Initial Stock Level *</label>
          <input type="number" step="0.01" name="current_stock" id="current_stock" class="form-input" value="{{ old('current_stock', 0) }}" required>
          @error('current_stock')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp">
          <label for="min_stock_alert">Minimum Alert Level *</label>
          <input type="number" step="0.01" name="min_stock_alert" id="min_stock_alert" class="form-input" value="{{ old('min_stock_alert', 0) }}" required>
          @error('min_stock_alert')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="mt-4" style="display: flex; justify-content: flex-end; gap: 10px;">
        <a href="{{ route('materials.index') }}" class="btn-ghost">Cancel</a>
        <button type="submit" class="btn-pri">Save Material</button>
      </div>
    </form>
  </div>
</div>
@endsection
