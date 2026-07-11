<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'DessertOps ERP')</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@yield('styles')
<style>
  /* Mobile Tab / Top Bar Active Overrides */
  @media (max-width: 768px) {
    .mobile-tab.active {
      color: var(--purple-tx) !important;
    }
    .mobile-tab.active svg {
      stroke: var(--purple-tx) !important;
    }
    .mobile-top-logo-mark {
      background: var(--purple-tx) !important;
    }
    .bottom-sheet-item:hover, .bottom-sheet-item:active {
      border-color: var(--purple-tx) !important;
      color: var(--purple-tx) !important;
      background: var(--purple-lt) !important;
    }
    .bottom-sheet-item:hover svg, .bottom-sheet-item:active svg {
      stroke: var(--purple-tx) !important;
    }
  }

  /* Universal Search Results */
  .tb-search {
    position: relative;
  }
  .tb-search-results {
    display: none;
    position: absolute;
    top: 38px;
    left: 0;
    width: 320px;
    max-height: 400px;
    background: var(--card);
    border: 1px solid var(--div2);
    border-radius: var(--radius);
    box-shadow: var(--card-sh-md);
    z-index: 2000;
    overflow-y: auto;
    text-align: left;
  }
  .search-result-group {
    border-bottom: 1px solid var(--div);
  }
  .search-result-group-title {
    padding: 8px 12px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--txt3);
    background: var(--pg-bg);
  }
  .search-result-item {
    display: block;
    padding: 8px 12px;
    text-decoration: none;
    color: var(--txt);
    border-bottom: 1px solid var(--div2);
    transition: background 0.2s ease;
  }
  .search-result-item:last-child {
    border-bottom: none;
  }
  .search-result-item:hover {
    background: var(--pg-bg);
  }
  .search-result-item-title {
    font-size: 12.5px;
    font-weight: 600;
  }
  .search-result-item-subtitle {
    font-size: 11px;
    color: var(--txt3);
    margin-top: 2px;
  }
  .search-no-results {
    padding: 16px;
    text-align: center;
    font-size: 13px;
    color: var(--txt3);
  }

  /* Outlet Dashboard Modal */
  .modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
  }
  .modal-container {
    background: var(--card);
    border-radius: var(--radius);
    border-top: 4px solid var(--purple-tx);
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    width: 90%;
    max-width: 700px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: modalFadeIn 0.3s ease-out;
  }
  @keyframes modalFadeIn {
    from { transform: translateY(10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }
  .modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--div2);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .modal-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--txt);
  }
  .modal-close {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--txt3);
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
  }
  .modal-close:hover {
    color: var(--txt);
  }
  .modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
  }
  .modal-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--div2);
    background: var(--pg-bg);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }

  .outlet-dropdown-item {
    display: block;
    padding: 8px 14px;
    font-size: 13px;
    text-decoration: none;
    color: var(--txt);
    border-bottom: 1px solid var(--div2);
    transition: background 0.2s;
  }
  .outlet-dropdown-item:last-child {
    border-bottom: none;
  }
  .outlet-dropdown-item:hover {
    background: var(--pg-bg);
  }

  #toast-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 360px;
    width: 100%;
  }

  .toast-notif {
    background: var(--card);
    border-left: 4px solid var(--purple-tx);
    border-top: 1px solid var(--div2);
    border-right: 1px solid var(--div2);
    border-bottom: 1px solid var(--div2);
    border-radius: var(--radius);
    padding: 16px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    display: flex;
    gap: 12px;
    align-items: flex-start;
    animation: toastSlideIn 0.3s ease-out;
    position: relative;
  }

  @keyframes toastSlideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }

  .toast-notif-close {
    position: absolute;
    top: 12px;
    right: 12px;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--txt3);
    padding: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .toast-notif-close:hover {
    color: var(--txt);
  }

  .omd-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
  }
  @media (max-width: 500px) {
    .omd-grid {
      grid-template-columns: 1fr;
      gap: 12px;
    }
  }
</style>
</head>
<body>

<!-- ══ MOBILE TOP BAR ══ -->
<header class="mobile-top-bar">
  <a href="{{ route('dashboard') }}" class="mobile-top-logo">
    <div class="mobile-top-logo-mark" style="background: var(--purple-tx);">
      <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    </div>
    <span class="mobile-top-logo-name">DessertOps ERP</span>
  </a>
</header>

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

  @if(in_array(auth()->user()->role, ['admin', 'gm', 'laban_chef', 'baklava_chef', 'dough_chef', 'store_manager']))
  <div class="sb-grp">
    <div class="sb-grp-label">Central Kitchen</div>
    @if(in_array(auth()->user()->role, ['admin', 'gm', 'laban_chef', 'baklava_chef', 'dough_chef']))
    <a href="{{ route('production-runs.index') }}" class="nav-i {{ request()->routeIs('production-runs.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span>Production Runs
    </a>
     <a href="{{ route('kitchen.stocks') }}" class="nav-i {{ request()->routeIs('kitchen.stocks') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg></span>Kitchen Stock
    </a>
    <a href="{{ route('kitchen.stock-report') }}" class="nav-i {{ request()->routeIs('kitchen.stock-report') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>Kitchen Daily Stock
    </a>
    @endif
    <a href="{{ route('material-requests.index') }}" class="nav-i {{ request()->routeIs('material-requests.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg></span>Material Requests
      @php
        $pendingMrCount = \App\Models\MaterialRequest::where('status', 'pending')->count();
      @endphp
      @if($pendingMrCount > 0)
        <span class="nb red">{{ $pendingMrCount }}</span>
      @endif
    </a>
    @if(in_array(auth()->user()->role, ['admin', 'gm', 'laban_chef', 'baklava_chef', 'dough_chef']))
    <a href="{{ route('products.index') }}" class="nav-i {{ request()->routeIs('products.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>Products Catalog
    </a>
    <a href="{{ route('outlet-catalog.index') }}" class="nav-i {{ request()->routeIs('outlet-catalog.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><line x1="9" y1="7" x2="16" y2="7"/><line x1="9" y1="11" x2="14" y2="11"/></svg></span>Outlet Catalog
    </a>
    @endif
  </div>
  @endif

  @if(in_array(auth()->user()->role, ['admin', 'gm', 'store_manager', 'laban_chef', 'baklava_chef', 'dough_chef']))
  <div class="sb-grp">
    <div class="sb-grp-label">Procurement</div>
    @if(in_array(auth()->user()->role, ['admin', 'gm', 'store_manager']))
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
    @endif
    <a href="{{ route('materials.index') }}" class="nav-i {{ request()->routeIs('materials.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></span>Raw Materials
      @php
        $lowStockCount = \App\Models\Material::lowStock()->count();
      @endphp
      @if($lowStockCount > 0)
        <span class="nb red">{{ $lowStockCount }}</span>
      @endif
    </a>
    @if(in_array(auth()->user()->role, ['admin', 'gm', 'store_manager']))
    <a href="{{ route('suppliers.index') }}" class="nav-i {{ request()->routeIs('suppliers.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>Suppliers
    </a>
    @endif
  </div>
  @endif

  @if(in_array(auth()->user()->role, ['admin', 'gm', 'laban_chef', 'baklava_chef', 'dough_chef']))
  <div class="sb-grp">
    <div class="sb-grp-label">Distribution</div>
    @if(in_array(auth()->user()->role, ['admin', 'gm']))
    <a href="{{ route('outlets.index') }}" class="nav-i {{ request()->routeIs('outlets.index') || request()->routeIs('outlets.create') || request()->routeIs('outlets.edit') || request()->routeIs('outlets.show') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></span>Outlets Management
    </a>
    <a href="{{ route('outlets.monitor') }}" class="nav-i {{ request()->routeIs('outlets.monitor') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></span>Outlet Monitor
    </a>
    <a href="{{ route('outlets.showcase-requests') }}" class="nav-i {{ request()->routeIs('outlets.showcase-requests') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></span>Showcase Requests
    </a>
    @endif
    <a href="{{ route('dispatches.orders') }}" class="nav-i {{ request()->routeIs('dispatches.orders') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg></span>Outlet Orders
      @php
        $pendingDispCount = \App\Models\Dispatch::where('status', 'pending')->count();
      @endphp
      @if($pendingDispCount > 0)
        <span class="nb red">{{ $pendingDispCount }}</span>
      @endif
    </a>
    <a href="{{ route('dispatches.index') }}" class="nav-i {{ request()->routeIs('dispatches.index') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></span>Product Dispatches
    </a>
    @if(in_array(auth()->user()->role, ['admin', 'gm']))
    <a href="{{ route('sales-logs.index') }}" class="nav-i {{ request()->routeIs('sales-logs.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>Outlet Sales Logs
    </a>
    @endif
  </div>
  @endif

  @if(in_array(auth()->user()->role, ['admin', 'accountant']))
  <div class="sb-grp">
    <div class="sb-grp-label">Finance Portal</div>
    <a href="{{ route('accounting.dashboard') }}" class="nav-i" target="_blank">
      <span class="ni"><svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="2" ry="2"/><line x1="12" y1="2" x2="12" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg></span>Accounting Portal
    </a>
  </div>
  @endif

  @if(auth()->user()->role === 'admin')
  <div class="sb-grp">
    <div class="sb-grp-label">Administration</div>
    <a href="{{ route('admin.users.index') }}" class="nav-i {{ request()->routeIs('admin.users.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>User Management
    </a>
  </div>
  @endif

  <div class="sb-sep"></div>

  <div class="sb-foot" style="display:flex; flex-direction:column; gap:10px;">
    <div class="sb-user">
      <div class="sb-av">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}</div>
      <div>
        <div class="sb-un">{{ Auth::user()->name }}</div>
        <div class="sb-ur" style="text-transform: capitalize;">
          @if(Auth::user()->role === 'admin')
            System Administrator
          @elseif(Auth::user()->role === 'gm')
            General Manager
          @elseif(Auth::user()->role === 'store_manager')
            Store Manager
          @elseif(Auth::user()->role === 'laban_chef')
            Laban Chef
          @elseif(Auth::user()->role === 'baklava_chef')
            Baklava Chef
          @elseif(Auth::user()->role === 'dough_chef')
            Dough Chef
          @else
            {{ Auth::user()->role }}
          @endif
        </div>
      </div>
    </div>
    <form action="{{ route('logout') }}" method="POST" style="width: 100%;">
      @csrf
      <button type="submit" class="btn-pri" style="width: 100%; background: var(--sb-border); color: var(--sb-text-hi); font-size:12px; font-weight:600; padding:6px 10px; border-radius: var(--radius-sm); border:none; display:flex; align-items:center; justify-content:center; gap:6px; cursor:pointer;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:13px; height:13px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Sign Out ERP
      </button>
    </form>
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

    <div style="position: relative;" id="outlet-selector-wrapper">
      <div class="tb-sel" id="outlet-selector-btn">
        <span class="on-dot"></span>
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        <span id="outlet-selector-label">All Outlets</span>
        <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
      </div>
      <div id="outlet-selector-dropdown" style="display: none; position: absolute; top: 38px; left: 0; min-width: 220px; background: var(--card); border: 1px solid var(--div2); border-radius: var(--radius); box-shadow: var(--card-sh-md); z-index: 2000; overflow: hidden; text-align: left;">
        <div style="padding: 10px 14px; font-size: 13px; color: var(--txt3);">Loading outlets...</div>
      </div>
    </div>

    <div class="tb-sel" style="min-width:auto;">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      {{ now()->format('F Y') }}
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <a href="{{ route('portal.login') }}" class="tb-sel" style="color:var(--purple-tx); font-weight:600; text-decoration:none; display:flex; align-items:center; gap:6px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px; height:14px;"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      Outlet Portal
    </a>

    <div class="tb-icon" id="notif-bell-container" style="position: relative; cursor: pointer;">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      <span class="notif-pip" id="notif-pip-badge" style="display: none; position: absolute; top: 4px; right: 4px; width: 8px; height: 8px; background: var(--red); border-radius: 50%;"></span>
      @if($lowStockCount > 0 && false)
        <span class="notif-pip"></span>
      @endif

      <!-- Dropdown -->
      <div id="notif-dropdown" style="display: none; position: absolute; top: 38px; right: -80px; width: 320px; background: var(--card); border: 1px solid var(--div2); border-radius: var(--radius); box-shadow: var(--card-sh-md); z-index: 1000; overflow: hidden; text-align: left;">
        <div style="padding: 10px 14px; border-bottom: 1px solid var(--div); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--txt2); display: flex; justify-content: space-between; align-items: center; background: var(--acc-lt);">
          <span>Notifications</span>
          <span id="notif-count-text" style="font-size: 11px; background: var(--purple-lt); color: var(--purple-tx); padding: 1px 6px; border-radius: 10px; font-weight: 700;">0 new</span>
        </div>
        <div id="notif-list" style="max-height: 280px; overflow-y: auto; font-size: 13px;">
          <div style="padding: 20px; text-align: center; color: var(--txt3);">Loading notifications...</div>
        </div>
      </div>
    </div>

    <div class="tb-user">
      <div class="tb-uav">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}</div>
      <span class="tb-uname" style="color: #111827 !important;">{{ Auth::user()->name ?? 'User' }}</span>
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

      @if(!request()->routeIs('dashboard'))
      <!-- ══ PAGE SEARCH BAR ══ -->
      <div class="page-search-bar" id="page-search-bar">
        <div class="page-search-inner">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="page-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="page-search-input" placeholder="Search this page..." autocomplete="off">
          <button type="button" id="page-search-clear" style="display:none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </div>
      @endif

      @yield('content')
  </div><!-- /content -->
</div><!-- /main -->

<div id="toast-container"></div>

<!-- Outlet Mini Dashboard Modal -->
<div id="outlet-mini-dashboard-modal" class="modal-overlay">
  <div class="modal-container">
    <div class="modal-header">
      <div>
        <div class="modal-title" id="omd-title">Outlet Mini-Dashboard</div>
        <div style="font-size:12px; color:var(--txt3); margin-top:2px;" id="omd-subtitle">Loading...</div>
      </div>
      <button class="modal-close" onclick="closeOutletModal();">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body" id="omd-body">
      <!-- Content populated by JS -->
      <div style="padding: 40px; text-align: center; color: var(--txt3);">
        Loading dashboard metrics...
      </div>
    </div>
    <div class="modal-footer">
      <a href="#" id="omd-view-details" class="btn-pri" style="text-decoration:none; display: inline-flex; align-items: center; justify-content: center;">View Full Details</a>
      <button class="btn-ghost" onclick="closeOutletModal();">Close</button>
    </div>
  </div>
</div>
<!-- ══ MOBILE BOTTOM BAR ══ -->
<nav class="mobile-bottom-bar">
  <a href="{{ route('dashboard') }}" class="mobile-tab {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    <span>Home</span>
  </a>

  @if(in_array(auth()->user()->role, ['laban_chef', 'baklava_chef', 'dough_chef']))
    <a href="{{ route('production-runs.index') }}" class="mobile-tab {{ request()->routeIs('production-runs.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
      <span>Production</span>
    </a>
    <a href="{{ route('kitchen.stocks') }}" class="mobile-tab {{ request()->routeIs('kitchen.stocks') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
      <span>Stock</span>
    </a>
    <a href="{{ route('material-requests.index') }}" class="mobile-tab {{ request()->routeIs('material-requests.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
      <span>Requests</span>
    </a>
  @elseif(auth()->user()->role === 'store_manager')
    <a href="{{ route('materials.index') }}" class="mobile-tab {{ request()->routeIs('materials.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
      <span>Materials</span>
    </a>
    <a href="{{ route('purchase-orders.index') }}" class="mobile-tab {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      <span>POs</span>
    </a>
    <a href="{{ route('material-requests.index') }}" class="mobile-tab {{ request()->routeIs('material-requests.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
      <span>Requests</span>
    </a>
  @else
    <!-- Admin / GM -->
    <a href="{{ route('production-runs.index') }}" class="mobile-tab {{ request()->routeIs('production-runs.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
      <span>Production</span>
    </a>
    <a href="{{ route('purchase-orders.index') }}" class="mobile-tab {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      <span>POs</span>
    </a>
    <a href="{{ route('outlets.index') }}" class="mobile-tab {{ request()->routeIs('outlets.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      <span>Outlets</span>
    </a>
  @endif

  <button type="button" class="mobile-tab" id="mobile-more-tab-btn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
    <span>More</span>
  </button>
</nav>

<!-- ══ BOTTOM SHEET OVERLAY & MENUS ══ -->
<div class="bottom-sheet-overlay" id="more-sheet-overlay"></div>

<!-- More Sheet -->
<div class="bottom-sheet" id="more-sheet">
  <div class="bottom-sheet-handle"></div>
  <div class="bottom-sheet-title">ERP Options</div>

  <div style="background: rgba(124, 58, 237, 0.05); border: 1px solid var(--div2); border-radius: var(--radius); padding: 12px; margin-bottom: 20px; text-align: center;">
    <div style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Logged In As</div>
    <div style="font-size: 15px; font-weight: 700; color: var(--txt); margin-top: 4px;">{{ auth()->user()->name }}</div>
    <span class="badge bg-purple" style="font-size: 9.5px; padding: 1px 6px; margin-top: 6px; display: inline-block;">
      {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
    </span>
  </div>

  <div class="bottom-sheet-grid">
    @if(in_array(auth()->user()->role, ['admin', 'gm']))
      <a href="{{ route('suppliers.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Suppliers</span>
      </a>
      <a href="{{ route('grns.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
        <span>GRNs</span>
      </a>
      <a href="{{ route('sales-logs.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        <span>Sales Logs</span>
      </a>
      <a href="{{ route('dispatches.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        <span>Dispatches</span>
      </a>
      <a href="{{ route('products.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span>Products</span>
      </a>
      <a href="{{ route('material-requests.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
        <span>Material Requests</span>
      </a>
      <a href="{{ route('materials.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
        <span>Raw Materials</span>
      </a>
      <a href="{{ route('kitchen.stock-report') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span>Kitchen Daily Stock</span>
      </a>
      @if(auth()->user()->role === 'admin')
        <a href="{{ route('admin.users.index') }}" class="bottom-sheet-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>User Directory</span>
        </a>
      @endif
    @elseif(in_array(auth()->user()->role, ['laban_chef', 'baklava_chef', 'dough_chef']))
      <a href="{{ route('materials.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
        <span>Raw Materials</span>
      </a>
      <a href="{{ route('kitchen.stock-report') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span>Kitchen Daily Stock</span>
      </a>
      <a href="{{ route('products.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span>Products</span>
      </a>
      <a href="{{ route('dispatches.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        <span>Dispatches</span>
      </a>
      <a href="{{ route('dispatches.orders') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
        <span>Outlet Orders</span>
      </a>
    @elseif(auth()->user()->role === 'store_manager')
      <a href="{{ route('suppliers.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Suppliers</span>
      </a>
      <a href="{{ route('grns.index') }}" class="bottom-sheet-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
        <span>GRNs</span>
      </a>
    @endif
  </div>

  <div style="margin-top: 20px; border-top: 1px solid var(--div2); padding-top: 16px;">
    <form action="{{ route('logout') }}" method="POST" style="width: 100%;">
      @csrf
      <button type="submit" class="bottom-sheet-item logout-btn" style="width: 100%; border: 1px solid rgba(220, 38, 38, 0.2); font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; background: none; padding: 12px 10px; cursor: pointer; border-radius: var(--radius); color: var(--red-tx);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="stroke: var(--red-tx);"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout Session
      </button>
    </form>
  </div>
</div>

<script>
window.initSearchableSelect = function(select) {
    if (!select || select.dataset.searchableInitialized === "true") return;

    // 1. Hide the original select
    select.style.display = 'none';
    select.dataset.searchableInitialized = "true";

    // 2. Create the wrapper container
    const wrapper = document.createElement('div');
    wrapper.className = 'searchable-select';

    // Insert wrapper before select, then move select inside wrapper
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    // 3. Create the text input
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'searchable-select-input';
    input.autocomplete = 'off';
    input.style.height = select.style.height || '38px';
    
    // Set placeholder/initial value
    const selectedOpt = select.options[select.selectedIndex];
    const initialText = selectedOpt ? selectedOpt.textContent.trim() : '';
    const hasPlaceholder = select.options[0] && select.options[0].value === '';
    
    if (hasPlaceholder && select.selectedIndex === 0) {
        input.placeholder = select.options[0].textContent.trim();
        input.value = '';
    } else {
        input.value = initialText;
    }

    wrapper.appendChild(input);

    // 4. Create the arrow icon
    const arrowContainer = document.createElement('div');
    arrowContainer.innerHTML = `<svg class="searchable-select-arrow" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>`;
    const arrow = arrowContainer.firstElementChild;
    wrapper.appendChild(arrow);

    // 5. Create the dropdown list
    const dropdown = document.createElement('div');
    dropdown.className = 'searchable-select-dropdown';
    wrapper.appendChild(dropdown);

    const noResults = document.createElement('div');
    noResults.className = 'searchable-select-no-results';
    noResults.textContent = 'No options match...';
    noResults.style.display = 'none';
    dropdown.appendChild(noResults);

    // Populate dropdown options
    const items = [];
    Array.from(select.options).forEach((opt, index) => {
        // Skip the blank/placeholder option in the searchable options list if preferred,
        // but let's keep it if they want to deselect.
        const item = document.createElement('div');
        item.className = 'searchable-select-option';
        item.textContent = opt.textContent.trim();
        item.dataset.value = opt.value;
        item.dataset.index = index;
        
        if (opt.selected) {
            item.classList.add('selected');
        }

        dropdown.appendChild(item);
        items.push(item);
    });

    // Helper functions
    function openDropdown() {
        // Close other open searchable selects first
        document.querySelectorAll('.searchable-select.open').forEach(el => {
            if (el !== wrapper) el.classList.remove('open');
        });

        wrapper.classList.add('open');
        input.select(); // Highlight text for easy typing over it
        filterOptions();
    }

    function closeDropdown() {
        wrapper.classList.remove('open');
        // Reset input value to current selection
        const currSelected = select.options[select.selectedIndex];
        if (currSelected && (currSelected.value !== '' || select.selectedIndex !== 0)) {
            input.value = currSelected.textContent.trim();
        } else {
            input.value = '';
        }
        
        // Remove highlights
        items.forEach(item => item.classList.remove('highlighted'));
    }

    function filterOptions() {
        const query = input.value.toLowerCase().trim();
        let matchCount = 0;

        items.forEach(item => {
            const val = item.textContent.toLowerCase();
            const isMatch = val.includes(query);
            
            if (isMatch) {
                item.style.display = 'block';
                matchCount++;
            } else {
                item.style.display = 'none';
            }
            item.classList.remove('highlighted');
        });

        if (matchCount === 0) {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    }

    function selectOption(item) {
        const index = parseInt(item.dataset.index);
        select.selectedIndex = index;
        
        // Update input
        const opt = select.options[index];
        if (opt.value === '' && index === 0) {
            input.value = '';
            input.placeholder = opt.textContent.trim();
        } else {
            input.value = opt.textContent.trim();
        }

        // Highlight in dropdown
        items.forEach(i => i.classList.remove('selected'));
        item.classList.add('selected');

        // Trigger change events
        select.dispatchEvent(new Event('change', { bubbles: true }));

        closeDropdown();
    }

    // Event Listeners
    input.addEventListener('click', function(e) {
        e.stopPropagation();
        if (wrapper.classList.contains('open')) {
            // Already open
        } else {
            openDropdown();
        }
    });

    input.addEventListener('input', function() {
        if (!wrapper.classList.contains('open')) {
            wrapper.classList.add('open');
        }
        filterOptions();
    });

    // Option clicks
    items.forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            selectOption(item);
        });
    });

    // Keyboard navigation
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            closeDropdown();
            return;
        }

        if (!wrapper.classList.contains('open')) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter') {
                e.preventDefault();
                openDropdown();
                return;
            }
        }

        const visibleItems = items.filter(i => i.style.display !== 'none');
        let currentHighlighted = visibleItems.find(i => i.classList.contains('highlighted'));
        let currentIndex = visibleItems.indexOf(currentHighlighted);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (currentIndex < visibleItems.length - 1) {
                if (currentHighlighted) currentHighlighted.classList.remove('highlighted');
                const nextItem = visibleItems[currentIndex + 1];
                nextItem.classList.add('highlighted');
                nextItem.scrollIntoView({ block: 'nearest' });
            } else if (visibleItems.length > 0 && currentIndex === -1) {
                visibleItems[0].classList.add('highlighted');
                visibleItems[0].scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (currentIndex > 0) {
                currentHighlighted.classList.remove('highlighted');
                const prevItem = visibleItems[currentIndex - 1];
                prevItem.classList.add('highlighted');
                prevItem.scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentHighlighted) {
                selectOption(currentHighlighted);
            } else if (visibleItems.length > 0) {
                selectOption(visibleItems[0]);
            }
        } else if (e.key === 'Escape') {
            e.preventDefault();
            closeDropdown();
        }
    });

    // Handle updates when native select is changed programmatically
    select.addEventListener('change-programmatic', function() {
        const currSelected = select.options[select.selectedIndex];
        if (currSelected) {
            input.value = currSelected.textContent.trim();
            items.forEach(i => i.classList.remove('selected'));
            const matchingItem = items.find(i => i.dataset.index == select.selectedIndex);
            if (matchingItem) matchingItem.classList.add('selected');
        }
    });
};

// Global click-away listener to close any open dropdowns
document.addEventListener('click', function(e) {
    if (!e.target.closest('.searchable-select')) {
        document.querySelectorAll('.searchable-select.open').forEach(el => {
            // Find input and call its close logic or just toggle the open class
            el.classList.remove('open');
            const select = el.querySelector('select');
            const input = el.querySelector('.searchable-select-input');
            if (select && input) {
                const currSelected = select.options[select.selectedIndex];
                if (currSelected && (currSelected.value !== '' || select.selectedIndex !== 0)) {
                    input.value = currSelected.textContent.trim();
                } else {
                    input.value = '';
                }
            }
        });
    }
});

// Initialize on standard select elements with "searchable-select" class
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('select.searchable-select').forEach(select => {
        window.initSearchableSelect(select);
    });

    // Mobile Bottom Sheet drawer navigation toggle
    const moreTabBtn = document.getElementById('mobile-more-tab-btn');
    const moreSheet = document.getElementById('more-sheet');
    const moreOverlay = document.getElementById('more-sheet-overlay');
    if (moreTabBtn && moreSheet && moreOverlay) {
        moreTabBtn.addEventListener('click', () => {
            moreSheet.classList.add('open');
            moreOverlay.classList.add('open');
        });
        moreOverlay.addEventListener('click', () => {
            moreSheet.classList.remove('open');
            moreOverlay.classList.remove('open');
        });
        moreSheet.querySelectorAll('.bottom-sheet-item').forEach(item => {
            item.addEventListener('click', () => {
                moreSheet.classList.remove('open');
                moreOverlay.classList.remove('open');
            });
        });
    }
});

// Notifications Toggle & Live Polling
document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('notif-bell-container');
    const dropdown = document.getElementById('notif-dropdown');
    const pip = document.getElementById('notif-pip-badge');
    const countText = document.getElementById('notif-count-text');
    const list = document.getElementById('notif-list');
    const toastContainer = document.getElementById('toast-container');
    const shownNotifs = new Set();

    if (!bell) return;

    // Toggle dropdown
    bell.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    });

    document.addEventListener('click', function() {
        dropdown.style.display = 'none';
    });

    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Notification chime using Web Audio API
    function playNotificationSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;

            // First tone (E5 - 659 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.3, now);
            gain1.gain.exponentialRampToValueAtTime(0.01, now + 0.3);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.3);

            // Second tone (A5 - 880 Hz) slightly delayed
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.15);
            gain2.gain.setValueAtTime(0.01, now);
            gain2.gain.setValueAtTime(0.25, now + 0.15);
            gain2.gain.exponentialRampToValueAtTime(0.01, now + 0.5);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.15);
            osc2.stop(now + 0.5);

            // Cleanup
            setTimeout(() => ctx.close(), 600);
        } catch(e) {
            // Web Audio not supported, silent fallback
        }
    }

    function showToastNotification(n) {
        if (!toastContainer) return;

        // Play notification chime
        playNotificationSound();

        const toast = document.createElement('div');
        toast.className = 'toast-notif';
        toast.dataset.id = n.id;
        toast.dataset.url = n.data.url;

        toast.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: var(--purple-lt); color: var(--purple-tx); flex-shrink: 0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 18px; height: 18px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </div>
            <div style="flex: 1; padding-right: 18px;">
                <div style="font-weight: 700; color: var(--txt); font-size: 13px; margin-bottom: 2px;">${n.data.title || 'Notification'}</div>
                <div style="font-weight: 600; color: var(--purple-tx); font-size: 11px; margin-bottom: 4px;">${n.data.outlet_name || ''}</div>
                <div style="color: var(--txt2); font-size: 12px; line-height: 1.4; margin-bottom: 8px;">${n.data.message}</div>
                <button type="button" class="btn-pri toast-action-btn" style="padding: 4px 10px; font-size: 11px; background: var(--purple-tx); border-color: var(--purple-tx); color: #fff; font-weight: 600; cursor: pointer; border-radius: var(--radius-sm); border: none;">View Details</button>
            </div>
            <button type="button" class="toast-notif-close" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;

        toastContainer.appendChild(toast);

        // Click close
        const closeBtn = toast.querySelector('.toast-notif-close');
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toast.remove();
        });

        // Click CTA or toast body
        const handleAction = function() {
            fetch(`/api/notifications/${n.id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(() => {
                window.location.href = n.data.url;
            })
            .catch(() => {
                window.location.href = n.data.url;
            });
        };

        toast.querySelector('.toast-action-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            handleAction();
        });

        toast.addEventListener('click', function() {
            handleAction();
        });
    }

    // Fetch notifications
    function fetchNotifications() {
        fetch('{{ route('api.notifications') }}')
            .then(res => res.json())
            .then(data => {
                // Update badge and text
                if (data.count > 0) {
                    pip.style.display = 'block';
                    countText.textContent = `${data.count} new`;
                    countText.style.background = 'var(--red-lt)';
                    countText.style.color = 'var(--red-tx)';
                } else {
                    pip.style.display = 'none';
                    countText.textContent = `0 new`;
                    countText.style.background = 'var(--purple-lt)';
                    countText.style.color = 'var(--purple-tx)';
                }

                // Check for new notifications to show toast
                data.notifications.forEach(n => {
                    if (!shownNotifs.has(n.id)) {
                        shownNotifs.add(n.id);
                        showToastNotification(n);
                    }
                });

                // Render list
                if (data.notifications.length === 0) {
                    list.innerHTML = `<div style="padding: 20px; text-align: center; color: var(--txt3);">No new notifications</div>`;
                } else {
                    let html = '';
                    data.notifications.forEach(n => {
                        html += `
                            <div class="notif-item" data-id="${n.id}" data-url="${n.data.url}" style="padding: 10px 14px; border-bottom: 1px solid var(--div); cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='var(--row-hov)'" onmouseout="this.style.background='transparent'">
                                <div style="font-weight: 600; color: var(--txt);">${n.data.outlet_name}</div>
                                <div style="color: var(--txt2); font-size: 12px; margin-top: 2px;">${n.data.message}</div>
                                <div style="color: var(--txt3); font-size: 11px; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 10px; height: 10px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    ${n.created_at}
                                </div>
                            </div>
                        `;
                    });
                    list.innerHTML = html;

                    // Bind click listeners
                    list.querySelectorAll('.notif-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const id = this.dataset.id;
                            const url = this.dataset.url;

                            fetch(`/api/notifications/${id}/read`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(() => {
                                window.location.href = url;
                            })
                            .catch(() => {
                                window.location.href = url;
                            });
                        });
                    });
                }
            })
            .catch(err => console.error('Error fetching notifications:', err));
    }

    // Initial load and poll
    fetchNotifications();
    setInterval(fetchNotifications, 10000); // 10s polling for live updates
});

// ══ Universal Search & Outlet Selector Mini-Dashboard ══
document.addEventListener('DOMContentLoaded', function() {
    // ══ PAGE SEARCH FILTER (Desktop + Mobile) ══
    const pageSearchInput = document.getElementById('page-search-input');
    const pageSearchClear = document.getElementById('page-search-clear');

    if (pageSearchInput && pageSearchClear) {
        let debounceTimer;
        pageSearchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const query = pageSearchInput.value.toLowerCase().trim();
                pageSearchClear.style.display = query ? 'block' : 'none';

                // Filter table rows
                document.querySelectorAll('table.tbl tbody tr').forEach(row => {
                    if (row.querySelector('td[colspan]')) return; // skip empty-state rows
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });

                // Filter KPI cards
                document.querySelectorAll('.kpi-grid .kpi, .kpi-card').forEach(kpi => {
                    const text = kpi.textContent.toLowerCase();
                    kpi.style.display = text.includes(query) ? '' : 'none';
                });
            }, 150);
        });

        pageSearchClear.addEventListener('click', function() {
            pageSearchInput.value = '';
            pageSearchInput.dispatchEvent(new Event('input'));
            pageSearchInput.focus();
        });
    }

    // 1. Universal Search Logic
    const searchInput = document.querySelector('.tb-search input');
    if (searchInput) {
        searchInput.id = 'universal-search-input';
        
        // Add results container
        const resultsContainer = document.createElement('div');
        resultsContainer.id = 'universal-search-results';
        resultsContainer.className = 'tb-search-results';
        searchInput.parentNode.appendChild(resultsContainer);

        let debounceTimeout = null;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            const query = searchInput.value.trim();

            if (query.length < 2) {
                resultsContainer.style.display = 'none';
                resultsContainer.innerHTML = '';
                return;
            }

            debounceTimeout = setTimeout(() => {
                fetch(`/api/search?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        resultsContainer.innerHTML = '';
                        if (data.length === 0) {
                            resultsContainer.innerHTML = '<div class="search-no-results">No results found</div>';
                            resultsContainer.style.display = 'block';
                            return;
                        }

                        // Group by category
                        const groups = {};
                        data.forEach(item => {
                            if (!groups[item.category]) {
                                groups[item.category] = [];
                            }
                            groups[item.category].push(item);
                        });

                        for (const groupName in groups) {
                            const groupDiv = document.createElement('div');
                            groupDiv.className = 'search-result-group';
                            
                            const titleDiv = document.createElement('div');
                            titleDiv.className = 'search-result-group-title';
                            titleDiv.textContent = groupName;
                            groupDiv.appendChild(titleDiv);

                            groups[groupName].forEach(item => {
                                const link = document.createElement('a');
                                link.className = 'search-result-item';
                                link.href = item.url;
                                link.innerHTML = `
                                    <div class="search-result-item-title">${escapeHtml(item.title)}</div>
                                    <div class="search-result-item-subtitle">${escapeHtml(item.subtitle)}</div>
                                `;
                                groupDiv.appendChild(link);
                            });

                            resultsContainer.appendChild(groupDiv);
                        }
                        resultsContainer.style.display = 'block';
                    })
                    .catch(err => console.error('Search error:', err));
            }, 250); // 250ms debounce
        });

        // Hide on click away
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });

        // Re-show on focus if has query
        searchInput.addEventListener('focus', function() {
            if (searchInput.value.trim().length >= 2 && resultsContainer.innerHTML !== '') {
                resultsContainer.style.display = 'block';
            }
        });
    }

    // 2. Outlet Selector & Dropdown Logic
    const outletBtn = document.getElementById('outlet-selector-btn');
    const outletDropdown = document.getElementById('outlet-selector-dropdown');
    const outletLabel = document.getElementById('outlet-selector-label');

    if (outletBtn && outletDropdown) {
        // Toggle dropdown
        outletBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = outletDropdown.style.display === 'block';
            
            // Close other dropdowns first
            closeAllHeaderDropdowns();

            if (!isOpen) {
                outletDropdown.style.display = 'block';
                fetchOutletsList();
            }
        });

        // Click away
        document.addEventListener('click', function(e) {
            if (!outletBtn.contains(e.target) && !outletDropdown.contains(e.target)) {
                outletDropdown.style.display = 'none';
            }
        });
    }

    let outletsFetched = false;
    function fetchOutletsList() {
        if (outletsFetched) return;
        fetch('/api/outlets')
            .then(res => res.json())
            .then(data => {
                outletsFetched = true;
                outletDropdown.innerHTML = '';
                
                if (outletLabel) {
                    outletLabel.textContent = `All Outlets (${data.length})`;
                }

                // Add "All Outlets" default option
                const allOption = document.createElement('a');
                allOption.className = 'outlet-dropdown-item';
                allOption.href = '#';
                allOption.style.fontWeight = '600';
                allOption.innerHTML = `
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span class="on-dot" style="background:var(--purple-tx);"></span>
                        Overview (All Outlets)
                    </div>
                `;
                allOption.addEventListener('click', function(e) {
                    e.preventDefault();
                    outletDropdown.style.display = 'none';
                    window.location.href = "/";
                });
                outletDropdown.appendChild(allOption);

                // Add individual outlets
                data.forEach(outlet => {
                    const option = document.createElement('a');
                    option.className = 'outlet-dropdown-item';
                    option.href = '#';
                    option.innerHTML = `
                        <div style="display:flex; align-items:center; gap:6px; justify-content:space-between;">
                            <span>${escapeHtml(outlet.name)}</span>
                            <span style="font-size:10.5px; padding:2px 6px; border-radius:10px; background:var(--pg-bg); color:var(--txt3); font-weight:600;">
                                ${escapeHtml(outlet.type.toUpperCase())}
                            </span>
                        </div>
                    `;
                    option.addEventListener('click', function(e) {
                        e.preventDefault();
                        outletDropdown.style.display = 'none';
                        window.openOutletModal(outlet.id);
                    });
                    outletDropdown.appendChild(option);
                });
            })
            .catch(err => {
                console.error('Error fetching outlets:', err);
                outletDropdown.innerHTML = '<div style="padding:10px 14px; font-size:13px; color:var(--red-tx);">Error loading outlets</div>';
            });
    }

    function closeAllHeaderDropdowns() {
        const notifDropdown = document.getElementById('notif-dropdown');
        if (notifDropdown) notifDropdown.style.display = 'none';
        if (outletDropdown) outletDropdown.style.display = 'none';
        const searchResults = document.getElementById('universal-search-results');
        if (searchResults) searchResults.style.display = 'none';
    }

    // Set count on load
    fetch('/api/outlets')
        .then(res => res.json())
        .then(data => {
            if (outletLabel) {
                outletLabel.textContent = `All Outlets (${data.length})`;
            }
        }).catch(() => {});

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Expose modal functions to window so click events can call them
    window.openOutletModal = function(outletId) {
        const modal = document.getElementById('outlet-mini-dashboard-modal');
        const title = document.getElementById('omd-title');
        const subtitle = document.getElementById('omd-subtitle');
        const body = document.getElementById('omd-body');
        const viewDetailsBtn = document.getElementById('omd-view-details');

        if (!modal) return;

        body.innerHTML = `
            <div style="padding: 40px; text-align: center; color: var(--txt3);">
                <svg class="anim-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:24px;height:24px;margin: 0 auto 10px;animation:spin 1s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/></svg>
                Fetching outlet metrics...
            </div>
        `;
        title.textContent = "Outlet Dashboard";
        subtitle.textContent = "Loading...";
        modal.style.display = 'flex';

        fetch(`/outlets/${outletId}/mini-dashboard`)
            .then(res => res.json())
            .then(data => {
                title.textContent = data.outlet.name;
                subtitle.textContent = `${data.outlet.type} Outlet • Phone: ${data.outlet.phone} • Email: ${data.outlet.email}`;
                viewDetailsBtn.href = data.outlet.url;

                let stocksHtml = '';
                if (data.stocks.length === 0) {
                    stocksHtml = `<tr><td colspan="3" style="text-align:center;color:var(--txt3);padding:14px;">No items in stock.</td></tr>`;
                } else {
                    data.stocks.forEach(s => {
                        stocksHtml += `
                            <tr>
                                <td><span class="mono">${escapeHtml(s.sku)}</span></td>
                                <td style="font-weight:600; color:var(--txt);">${escapeHtml(s.name)}</td>
                                <td style="text-align:right; font-weight:600;">${s.quantity.toFixed(1)} ${s.unit}</td>
                            </tr>
                        `;
                    });
                }

                let salesHtml = '';
                if (data.recent_sales.length === 0) {
                    salesHtml = `<tr><td colspan="3" style="text-align:center;color:var(--txt3);padding:14px;">No sales logs recorded.</td></tr>`;
                } else {
                    data.recent_sales.forEach(s => {
                        salesHtml += `
                            <tr>
                                <td>${s.date}</td>
                                <td>${s.items_count} items sold</td>
                                <td style="text-align:right;font-weight:700;color:var(--green-tx);">₹${s.total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            </tr>
                        `;
                    });
                }

                body.innerHTML = `
                    <div class="omd-grid">
                        <div style="background:var(--pg-bg); padding:14px; border-radius:var(--radius); border:1px solid var(--div2); text-align:center;">
                            <div style="font-size:11px; text-transform:uppercase; color:var(--txt3); font-weight:600; letter-spacing:0.5px; margin-bottom:4px;">MTD Revenue</div>
                            <div style="font-size:18px; font-weight:700; color:var(--green-tx);">₹${data.mtd_revenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                        </div>
                        <div style="background:var(--pg-bg); padding:14px; border-radius:var(--radius); border:1px solid var(--div2); text-align:center;">
                            <div style="font-size:11px; text-transform:uppercase; color:var(--txt3); font-weight:600; letter-spacing:0.5px; margin-bottom:4px;">Stock Items</div>
                            <div style="font-size:18px; font-weight:700; color:var(--txt);">${data.stocks_count} items</div>
                        </div>
                        <div style="background:var(--pg-bg); padding:14px; border-radius:var(--radius); border:1px solid var(--div2); text-align:center;">
                            <div style="font-size:11px; text-transform:uppercase; color:var(--txt3); font-weight:600; letter-spacing:0.5px; margin-bottom:4px;">Shipments in Transit</div>
                            <div style="font-size:18px; font-weight:700; color:var(--blue-tx);">${data.incoming_count} pending</div>
                        </div>
                    </div>

                    <div style="background:var(--pg-bg); padding:14px; border-radius:var(--radius); border:1px solid var(--div2); margin-bottom:24px; font-size:13px;">
                        <strong style="color:var(--txt2);">Contact Person:</strong> ${escapeHtml(data.outlet.contact_person)}<br>
                        <strong style="color:var(--txt2); margin-top:4px; display:inline-block;">Outlet Address:</strong><br>
                        <div style="color:var(--txt3); white-space:pre-line; margin-top:2px;">${escapeHtml(data.outlet.address)}</div>
                    </div>

                    <h4 style="font-size:12.5px; font-weight:700; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;stroke:var(--purple-tx);"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                        Current Stock Levels
                    </h4>
                    <div style="max-height:180px; overflow-y:auto; border:1px solid var(--div2); border-radius:var(--radius); margin-bottom:24px;">
                        <table style="width:100%; border:none; margin:0; font-size:12.5px;">
                            <thead>
                                <tr style="background:var(--pg-bg);">
                                    <th style="width:20%; padding:6px 10px;">SKU</th>
                                    <th style="width:55%; padding:6px 10px;">Product / Material</th>
                                    <th style="width:25%; padding:6px 10px; text-align:right;">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${stocksHtml}
                            </tbody>
                        </table>
                    </div>

                    <h4 style="font-size:12.5px; font-weight:700; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;stroke:var(--green-tx);"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        Recent Store Sales (Last 5 Logs)
                    </h4>
                    <div style="max-height:180px; overflow-y:auto; border:1px solid var(--div2); border-radius:var(--radius);">
                        <table style="width:100%; border:none; margin:0; font-size:12.5px;">
                            <thead>
                                <tr style="background:var(--pg-bg);">
                                    <th style="width:30%; padding:6px 10px;">Log Date</th>
                                    <th style="width:45%; padding:6px 10px;">Details</th>
                                    <th style="width:25%; padding:6px 10px; text-align:right;">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${salesHtml}
                            </tbody>
                        </table>
                    </div>
                `;
            })
            .catch(err => {
                console.error('Error fetching mini-dashboard:', err);
                body.innerHTML = `
                    <div style="padding:40px; text-align:center; color:var(--red-tx); font-weight:600;">
                        Error loading dashboard metrics. Please try again.
                    </div>
                `;
            });
    };

    window.closeOutletModal = function() {
        const modal = document.getElementById('outlet-mini-dashboard-modal');
        if (modal) modal.style.display = 'none';
    };

    // Modal click away to close
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('outlet-mini-dashboard-modal');
        if (modal && e.target === modal) {
            window.closeOutletModal();
        }
    });

    const spinStyle = document.createElement('style');
    spinStyle.innerHTML = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .anim-spin {
            animation: spin 1s linear infinite;
        }
    `;
    document.head.appendChild(spinStyle);
});
</script>

<script>
// ══ Data Pulse: Auto-refresh pages when data changes across users ══
(function() {
    let lastPulse = null;
    let isFormPage = document.querySelector('form[method="POST"]') !== null;

    // Don't auto-refresh on form pages (create/edit) to prevent data loss
    if (isFormPage && !document.querySelector('.steps')) return;

    function checkPulse() {
        fetch('/api/data-pulse', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                if (lastPulse === null) {
                    lastPulse = data.pulse;
                } else if (data.pulse !== lastPulse) {
                    lastPulse = data.pulse;
                    window.location.reload();
                }
            })
            .catch(() => {});
    }

    checkPulse();
    setInterval(checkPulse, 10000); // Check every 10 seconds
})();
</script>

@yield('scripts')
</body>
</html>
