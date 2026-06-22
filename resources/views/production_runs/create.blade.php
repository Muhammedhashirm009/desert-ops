@extends('layouts.app')

@section('title', 'Log Production — DessertOps')
@section('breadcrumb', 'Log Production Run')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Log Dessert Preparation Batch</div>
    <div class="ph-sub">Record finished dessert products prepared in the central kitchen</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('production-runs.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
  <div class="ch">
    <div class="ch-title">Production Batch details</div>
  </div>
  <div class="cb">
    <form action="{{ route('production-runs.store') }}" method="POST">
      @csrf

      <div class="form-grp">
        <label for="product_id">Finished Dessert Product *</label>
        <select name="product_id" id="product_id" class="form-input" required style="height: 38px;">
          <option value="">-- Choose Finished Product --</option>
          @foreach($products as $product)
            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
              {{ $product->name }} ({{ $product->sku }})
            </option>
          @endforeach
        </select>
        @error('product_id')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="grid-2">
        <div class="form-grp">
          <label for="quantity_produced">Quantity Prepared *</label>
          <input type="number" step="1" name="quantity_produced" id="quantity_produced" class="form-input" 
                 value="{{ old('quantity_produced', 10) }}" required min="1">
          @error('quantity_produced')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp">
          <label for="prepared_date">Prepared Date *</label>
          <input type="date" name="prepared_date" id="prepared_date" class="form-input" required 
                 value="{{ old('prepared_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
          @error('prepared_date')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="form-grp">
        <label for="notes">Production Notes / Batch Remarks</label>
        <textarea name="notes" id="notes" class="form-input" rows="4" 
                  placeholder="e.g. Batch #B42, checked taste profile, temperature limits normal...">{{ old('notes') }}</textarea>
        @error('notes')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="mt-4" style="display: flex; justify-content: flex-end; gap: 10px;">
        <a href="{{ route('production-runs.index') }}" class="btn-ghost">Cancel</a>
        <button type="submit" class="btn-pri">Save Batch</button>
      </div>
    </form>
  </div>
</div>
@endsection
