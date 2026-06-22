<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'DessertOps ERP')</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@yield('styles')
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sb">
  <div class="sb-logo">
    <div class="sb-mark">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    </div>
    <div>
      <div class="sb-brand">DessertOps</div>
      <div class="sb-ver">ERP Platform v2</div>
    </div>
  </div>

  <div class="sb-grp">
    <div class="sb-grp-label">Overview</div>
    <a href="{{ route('dashboard') }}" class="nav-i {{ request()->routeIs('dashboard') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>Dashboard
    </a>
  </div>

  <div class="sb-grp">
    <div class="sb-grp-label">Central Kitchen</div>
    <a href="{{ route('production-runs.index') }}" class="nav-i {{ request()->routeIs('production-runs.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span>Production Runs
    </a>
    <a href="{{ route('kitchen.stocks') }}" class="nav-i {{ request()->routeIs('kitchen.stocks') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg></span>Kitchen Stock
    </a>
    <a href="{{ route('material-requests.index') }}" class="nav-i {{ request()->routeIs('material-requests.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg></span>Material Requests
      @php
        $pendingMrCount = \App\Models\MaterialRequest::where('status', 'pending')->count();
      @endphp
      @if($pendingMrCount > 0)
        <span class="nb red">{{ $pendingMrCount }}</span>
      @endif
    </a>
    <a href="{{ route('products.index') }}" class="nav-i {{ request()->routeIs('products.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>Products Catalog
    </a>
  </div>

  <div class="sb-grp">
    <div class="sb-grp-label">Procurement</div>
    <a href="{{ route('purchase-orders.index') }}" class="nav-i {{ request()->routeIs('purchase-orders.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></span>Purchase Orders
      @php
        $pendingPoCount = \App\Models\PurchaseOrder::where('status', 'pending')->count();
      @endphp
      @if($pendingPoCount > 0)
        <span class="nb red">{{ $pendingPoCount }}</span>
      @endif
    </a>
    <a href="{{ route('grns.index') }}" class="nav-i {{ request()->routeIs('grns.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg></span>GRN
    </a>
    <a href="{{ route('materials.index') }}" class="nav-i {{ request()->routeIs('materials.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></span>Raw Materials
      @php
        $lowStockCount = \App\Models\Material::lowStock()->count();
      @endphp
      @if($lowStockCount > 0)
        <span class="nb red">{{ $lowStockCount }}</span>
      @endif
    </a>
    <a href="{{ route('suppliers.index') }}" class="nav-i {{ request()->routeIs('suppliers.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>Suppliers
    </a>
  </div>

  <div class="sb-grp">
    <div class="sb-grp-label">Distribution</div>
    <a href="#" class="nav-i" style="pointer-events: none; opacity: 0.5;">
      <span class="ni"><svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></span>Outlet Dispatch (M3)
    </a>
    <a href="#" class="nav-i" style="pointer-events: none; opacity: 0.5;">
      <span class="ni"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></span>Franchise Orders (M3)
    </a>
  </div>

  <div class="sb-sep"></div>

  <div class="sb-foot">
    <div class="sb-user">
      <div class="sb-av">HX</div>
      <div>
        <div class="sb-un">Hashir</div>
        <div class="sb-ur">System Administrator</div>
      </div>
    </div>
  </div>
</aside>

<!-- ══ MAIN ══ -->
<div class="main">

  <!-- TOPBAR -->
  <header class="tb">
    <div class="tb-bc">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      <a href="{{ route('dashboard') }}">Dashboard</a>
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <b>@yield('breadcrumb', 'Operations Overview')</b>
    </div>
    <div class="tb-sp"></div>

    <div class="tb-search">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" placeholder="Search orders, SKUs, outlets…">
    </div>

    <div class="tb-sel">
      <span class="on-dot"></span>
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      All Outlets (5)
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <div class="tb-sel" style="min-width:auto;">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      {{ now()->format('F Y') }}
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <div class="tb-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      @if($lowStockCount > 0)
        <span class="notif-pip"></span>
      @endif
    </div>

    <div class="tb-user">
      <div class="tb-uav">HX</div>
      <span class="tb-uname">Hashir</span>
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
  </header>

  <!-- CONTENT -->
  <div class="content">
      @if(session('success'))
          <div class="alert alert-success">
              {{ session('success') }}
          </div>
      @endif

      @if(session('error'))
          <div class="alert alert-danger">
              {{ session('error') }}
          </div>
      @endif

      @yield('content')
  </div><!-- /content -->
</div><!-- /main -->

@yield('scripts')
</body>
</html>
