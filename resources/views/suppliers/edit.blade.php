@extends('layouts.app')

@section('title', 'Edit Supplier — DessertOps')
@section('breadcrumb', 'Modify Supplier')

@section('content')
<div class="ph">
  <div>
    <div class="ph-title">Edit Supplier: {{ $supplier->name }}</div>
    <div class="ph-sub">Update registered details for this partner</div>
  </div>
  <div class="ph-acts">
    <a href="{{ route('suppliers.index') }}" class="btn-ghost">
      Cancel
    </a>
  </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
  <div class="ch">
    <div class="ch-title">Supplier Information</div>
  </div>
  <div class="cb">
    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="form-grp">
        <label for="name">Supplier Name *</label>
        <input type="text" name="name" id="name" class="form-input" value="{{ old('name', $supplier->name) }}" required placeholder="e.g. Kerala Dairy Co.">
        @error('name')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="form-grp">
        <label for="contact_person">Contact Person</label>
        <input type="text" name="contact_person" id="contact_person" class="form-input" value="{{ old('contact_person', $supplier->contact_person) }}" placeholder="e.g. Rajesh Kumar">
        @error('contact_person')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="grid-2">
        <div class="form-grp">
          <label for="email">Email Address</label>
          <input type="email" name="email" id="email" class="form-input" value="{{ old('email', $supplier->email) }}" placeholder="e.g. sales@keraladairy.com">
          @error('email')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-grp">
          <label for="phone">Phone Number</label>
          <input type="text" name="phone" id="phone" class="form-input" value="{{ old('phone', $supplier->phone) }}" placeholder="e.g. +91 9876543210">
          @error('phone')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="form-grp">
        <label for="address">Postal Address</label>
        <textarea name="address" id="address" class="form-input" rows="4" placeholder="Full postal address...">{{ old('address', $supplier->address) }}</textarea>
        @error('address')<span style="color: var(--red-tx); font-size: 12px;">{{ $message }}</span>@enderror
      </div>

      <div class="mt-4" style="display: flex; justify-content: flex-end; gap: 10px;">
        <a href="{{ route('suppliers.index') }}" class="btn-ghost">Cancel</a>
        <button type="submit" class="btn-pri">Update Supplier</button>
      </div>
    </form>
  </div>
</div>
@endsection
