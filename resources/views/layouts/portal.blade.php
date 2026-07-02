<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Outlet Portal — DessertOps')</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="DessertOps">
<link rel="apple-touch-icon" href="/img/icon-192.png">
<link rel="manifest" href="/manifest.json">
@yield('styles')
<style>
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
</style>
</head>
<body>

<!-- ══ MOBILE TOP BAR ══ -->
<header class="mobile-top-bar">
  <a href="{{ route('portal.dashboard') }}" class="mobile-top-logo">
    <div class="mobile-top-logo-mark">
      <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
    </div>
    <span class="mobile-top-logo-name">Outlet Portal</span>
  </a>
  <div class="mobile-top-actions">
    <div class="tb-icon" id="mobile-notif-bell-container" style="position: relative; cursor: pointer; margin-right: 4px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 20px; height: 20px; stroke: var(--txt2);"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      <span class="notif-pip" id="mobile-notif-pip-badge" style="display: none; position: absolute; top: 1px; right: 1px; width: 7px; height: 7px; background: var(--red); border-radius: 50%;"></span>
    </div>
  </div>
</header>

<!-- ══ SIDEBAR ══ -->
<aside class="sb" style="background: var(--sb-bg2);">
  <div class="sb-logo">
    <div class="sb-mark" style="background: var(--purple-tx);">
      <svg viewBox="0 0 24 24" style="stroke:#fff;"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
    </div>
    <div>
      <div class="sb-brand">Outlet Portal</div>
      <div class="sb-ver">DessertOps Partner</div>
    </div>
  </div>

  @php
    $currentOutlet = \Illuminate\Support\Facades\Auth::guard('outlet')->user();
  @endphp

  @if($currentOutlet)
  <div style="padding: 14px 16px 6px;">
    <div style="font-size:10px; color:var(--txt3); font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Active Outlet</div>
    <div style="font-weight:700; color:var(--sb-text-hi); font-size:13.5px; margin-top:2px; display:flex; align-items:center; gap:6px;">
      <span class="on-dot" style="width:7px; height:7px;"></span>
      {{ $currentOutlet->name }}
    </div>
    <span class="badge {{ $currentOutlet->type === 'own' ? 'bg' : 'bp' }}" style="font-size:9.5px; padding:1px 5px; margin-top:4px;">
      {{ $currentOutlet->type === 'own' ? 'Company Owned' : 'Franchise' }}
    </span>
  </div>
  @endif

  <div class="sb-grp" style="margin-top: 10px;">
    <div class="sb-grp-label">Store Operations</div>
    <a href="{{ route('portal.dashboard') }}" class="nav-i {{ request()->routeIs('portal.dashboard') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>Store Dashboard
    </a>
    <a href="{{ route('portal.dispatches') }}" class="nav-i {{ request()->routeIs('portal.dispatches') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></span>Incoming Shipments
      @if($currentOutlet)
        @php
          $incCount = \App\Models\Dispatch::where('outlet_id', $currentOutlet->id)->where('status', 'dispatched')->count();
        @endphp
        @if($incCount > 0)
          <span class="nb red">{{ $incCount }}</span>
        @endif
      @endif
    </a>
    <a href="{{ route('portal.sales.index') }}" class="nav-i {{ request()->routeIs('portal.sales.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>Daily Sales Logs
    </a>
    <a href="{{ route('portal.requests.create') }}" class="nav-i {{ request()->routeIs('portal.requests.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></span>Request Products
    </a>
    <a href="{{ route('portal.showcase-requests.index') }}" class="nav-i {{ request()->routeIs('portal.showcase-requests.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg></span>Showcase Requests
    </a>
  </div>

  <div class="sb-sep"></div>

  <div class="sb-grp">
    <div class="sb-grp-label">Systems</div>
    <a href="{{ route('dashboard') }}" class="nav-i" style="color: var(--txt3);">
      <span class="ni"><svg viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span>Go to Admin ERP
    </a>
  </div>

  <div class="sb-foot">
    <form action="{{ route('portal.logout') }}" method="POST" style="width: 100%;">
      @csrf
      <button type="submit" class="btn-pri" style="width: 100%; background: var(--sb-border); color: var(--sb-text-hi); font-size:12px; font-weight:600; padding:6px 10px; border-radius: var(--radius-sm); border:none; display:flex; align-items:center; justify-content:center; gap:6px; cursor:pointer;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:13px; height:13px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Exit Store Portal
      </button>
    </form>
  </div>
</aside>

<!-- ══ MAIN ══ -->
<div class="main">
  <!-- TOPBAR -->
  <header class="tb">
    <div class="tb-bc">
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      <span>Outlet Portal</span>
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <b>@yield('breadcrumb', 'Store Dashboard')</b>
    </div>
    <div class="tb-sp"></div>

    <div class="tb-icon" id="notif-bell-container" style="position: relative; cursor: pointer; margin-right: 16px;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px; stroke: var(--txt2);"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      <span class="notif-pip" id="notif-pip-badge" style="display: none; position: absolute; top: 2px; right: 2px; width: 8px; height: 8px; background: var(--red); border-radius: 50%;"></span>

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

    <div class="tb-user" style="cursor: default;">
      <div class="tb-uav" style="background:var(--purple-tx);">OP</div>
      <span class="tb-uname">{{ $currentOutlet ? $currentOutlet->name : 'Outlet Portal User' }}</span>
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
});
</script>

<div id="toast-container" style="position: fixed; bottom: 24px; right: 24px; display: flex; flex-direction: column; gap: 12px; z-index: 10000; max-width: 360px; width: 100%;"></div>

<!-- ══ MOBILE BOTTOM BAR ══ -->
<nav class="mobile-bottom-bar">
  <a href="{{ route('portal.dashboard') }}" class="mobile-tab {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    <span>Home</span>
  </a>
  <a href="{{ route('portal.dispatches') }}" class="mobile-tab {{ request()->routeIs('portal.dispatches') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
    <span>Shipments</span>
    @if($currentOutlet)
      @php
        $incCount = \App\Models\Dispatch::where('outlet_id', $currentOutlet->id)->where('status', 'dispatched')->count();
      @endphp
      @if($incCount > 0)
        <span class="mobile-tab-badge" id="mobile-shipments-badge">{{ $incCount }}</span>
      @endif
    @endif
  </a>
  <a href="{{ route('portal.sales.index') }}" class="mobile-tab {{ request()->routeIs('portal.sales.*') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    <span>Sales Logs</span>
  </a>
  <a href="{{ route('portal.requests.create') }}" class="mobile-tab {{ request()->routeIs('portal.requests.*') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    <span>Order</span>
  </a>
  <button type="button" class="mobile-tab" id="mobile-more-tab-btn">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
    <span>More</span>
  </button>
</nav>

<!-- ══ BOTTOM SHEET OVERLAY & MENUS ══ -->
<div class="bottom-sheet-overlay" id="more-sheet-overlay"></div>

<!-- More Sheet -->
<div class="bottom-sheet" id="more-sheet">
  <div class="bottom-sheet-handle"></div>
  <div class="bottom-sheet-title">Store Options</div>

  @if($currentOutlet)
  <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--div2); border-radius: var(--radius); padding: 12px; margin-bottom: 20px; text-align: center;">
    <div style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Active Store Session</div>
    <div style="font-size: 15px; font-weight: 700; color: #fff; margin-top: 4px;">{{ $currentOutlet->name }}</div>
    <span class="badge {{ $currentOutlet->type === 'own' ? 'bg' : 'bp' }}" style="font-size: 9.5px; padding: 1px 6px; margin-top: 6px; display: inline-block;">
      {{ $currentOutlet->type === 'own' ? 'Company Owned' : 'Franchise (' . $currentOutlet->commission_rate . '% Comm.)' }}
    </span>
  </div>
  @endif

  <div class="bottom-sheet-grid">
    <!-- PWA Install Button (Dynamically shown) -->
    <button type="button" class="bottom-sheet-item" id="pwa-install-btn" style="display: none; border: none; background: rgba(139, 92, 246, 0.1);">
      <svg viewBox="0 0 24 24" style="stroke: var(--purple-tx);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      <span style="color: var(--purple-tx);">Install App</span>
    </button>

    <!-- Manual Install Guidance -->
    <div id="pwa-manual-instructions" style="grid-column: span 3; display: none; flex-direction: column; align-items: center; justify-content: center; padding: 12px; background: var(--acc-lt); border: 1px dashed var(--div2); border-radius: var(--radius); text-align: center; font-size: 11.5px; color: var(--txt2); margin-bottom: 4px; line-height: 1.4;">
      <span style="font-weight: 600; color: var(--txt); margin-bottom: 4px;">Install App Manually</span>
      <span>Tap your browser's menu (e.g. three dots in Chrome, or Share button in Safari on iOS) and select <strong>"Add to Home Screen"</strong>.</span>
    </div>

    <!-- Test Notifications -->
    <button type="button" class="bottom-sheet-item" id="mobile-test-notif-btn" style="border: none;">
      <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      <span>Test Chime</span>
    </button>

    <!-- Switch to ERP -->
    <a href="{{ route('dashboard') }}" class="bottom-sheet-item">
      <svg viewBox="0 0 24 24"><path d="M9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      <span>Admin ERP</span>
    </a>



    <!-- Logout -->
    <form action="{{ route('portal.logout') }}" method="POST" style="grid-column: span 3; width: 100%;">
      @csrf
      <button type="submit" class="bottom-sheet-item logout-btn" style="width: 100%; border: 1px solid rgba(239, 68, 68, 0.2); font-weight: 700; height: 50px; justify-content: center; display: flex; flex-direction: row; gap: 8px;">
        <svg viewBox="0 0 24 24" style="width: 18px; height: 18px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Exit Store Portal
      </button>
    </form>
  </div>
</div>

<!-- Mobile Notifications Sheet -->
<div class="bottom-sheet" id="mobile-notif-sheet">
  <div class="bottom-sheet-handle"></div>
  <div class="bottom-sheet-title">Notifications</div>
  <div id="mobile-notif-list" style="max-height: 50vh; overflow-y: auto; font-size: 13px; padding-bottom: 24px;">
    <div style="padding: 20px; text-align: center; color: var(--txt3);">Loading notifications...</div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // PWA Service Worker Registration
    let swRegistration = null;
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js')
            .then(reg => {
                swRegistration = reg;
                console.log('Service worker registered');
            })
            .catch(err => console.error('Service worker registration failed:', err));
    }

    // Request notification permission after a short delay
    setTimeout(() => {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }, 3000);

    // PWA Install Prompt
    let deferredPrompt;
    const installBtn = document.getElementById('pwa-install-btn');
    const manualInst = document.getElementById('pwa-manual-instructions');
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    let beforeInstallPromptFired = false;

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        beforeInstallPromptFired = true;
        if (installBtn) {
            installBtn.style.display = 'flex';
        }
        if (manualInst) {
            manualInst.style.display = 'none';
        }
    });

    if (installBtn) {
        installBtn.addEventListener('click', () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('PWA installed successfully');
                }
                deferredPrompt = null;
                installBtn.style.display = 'none';
            });
        });
    }

    // Show manual install guidance after 1.5s if not standalone and auto-install event hasn't fired (e.g., HTTP IP address or iOS Safari)
    if (!isStandalone && manualInst) {
        setTimeout(() => {
            if (!beforeInstallPromptFired) {
                manualInst.style.display = 'flex';
            }
        }, 1500);
    }

    // Mobile Sheet Toggles
    const moreTabBtn = document.getElementById('mobile-more-tab-btn');
    const moreSheet = document.getElementById('more-sheet');
    const moreOverlay = document.getElementById('more-sheet-overlay');
    const mobBell = document.getElementById('mobile-notif-bell-container');
    const mobNotifSheet = document.getElementById('mobile-notif-sheet');

    function openSheet(sheet) {
        sheet.classList.add('open');
        moreOverlay.classList.add('open');
    }

    function closeAllSheets() {
        if (moreSheet) moreSheet.classList.remove('open');
        if (mobNotifSheet) mobNotifSheet.classList.remove('open');
        if (moreOverlay) moreOverlay.classList.remove('open');
    }

    if (moreTabBtn) {
        moreTabBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            closeAllSheets();
            openSheet(moreSheet);
        });
    }

    if (mobBell) {
        mobBell.addEventListener('click', (e) => {
            e.stopPropagation();
            closeAllSheets();
            openSheet(mobNotifSheet);
        });
    }

    if (moreOverlay) {
        moreOverlay.addEventListener('click', closeAllSheets);
    }



    // Notification System Setup
    const bell = document.getElementById('notif-bell-container');
    const dropdown = document.getElementById('notif-dropdown');
    const pip = document.getElementById('notif-pip-badge');
    const mobPip = document.getElementById('mobile-notif-pip-badge');
    const countText = document.getElementById('notif-count-text');
    const list = document.getElementById('notif-list');
    const mobList = document.getElementById('mobile-notif-list');
    const toastContainer = document.getElementById('toast-container');
    const shownNotifs = new Set();

    // Desktop Bell Click Toggle
    if (bell && dropdown) {
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
    }

    // Sound chime using Web Audio API
    function playNotificationSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;

            // E5 - 659 Hz
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

            // A5 - 880 Hz
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

            setTimeout(() => ctx.close(), 600);
        } catch(e) {}
    }
    window.playNotificationSound = playNotificationSound;

    function showToastNotification(n) {
        if (!toastContainer) return;
        playNotificationSound();

        // Trigger system-level notification (appears in phone notification panel)
        if ('Notification' in window && Notification.permission === 'granted' && swRegistration) {
            swRegistration.showNotification('DessertOps — Shipment Dispatched', {
                body: n.data.message,
                icon: '/img/icon-192.png',
                badge: '/img/icon-192.png',
                tag: 'dispatch-' + n.id,
                renotify: true,
                vibrate: [200, 100, 200],
                data: { url: n.data.url, id: n.id }
            });
        }

        const toast = document.createElement('div');
        toast.className = 'toast-notif';
        toast.dataset.id = n.id;
        toast.dataset.url = n.data.url;

        toast.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: var(--purple-lt); color: var(--purple-tx); flex-shrink: 0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 18px; height: 18px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </div>
            <div style="flex: 1; padding-right: 18px;">
                <div style="font-weight: 700; color: var(--txt); font-size: 13px; margin-bottom: 2px;">Shipment Dispatched</div>
                <div style="color: var(--txt2); font-size: 12px; line-height: 1.4; margin-bottom: 8px;">${n.data.message}</div>
                <button type="button" class="btn-pri toast-action-btn" style="padding: 4px 10px; font-size: 11px; background: var(--purple-tx); border-color: var(--purple-tx); color: #fff; font-weight: 600; cursor: pointer; border-radius: var(--radius-sm); border: none;">View Shipment</button>
            </div>
            <button type="button" class="toast-notif-close" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;

        toastContainer.appendChild(toast);

        toast.querySelector('.toast-notif-close').addEventListener('click', function(e) {
            e.stopPropagation();
            toast.remove();
        });

        const handleAction = function() {
            fetch(`/portal/api/notifications/${n.id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(() => { window.location.href = n.data.url; })
            .catch(() => { window.location.href = n.data.url; });
        };

        toast.querySelector('.toast-action-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            handleAction();
        });

        toast.addEventListener('click', handleAction);
    }

    function fetchNotifications() {
        fetch('{{ route('portal.api.notifications') }}')
            .then(res => res.json())
            .then(data => {
                // Update pip status
                if (data.count > 0) {
                    if (pip) pip.style.display = 'block';
                    if (mobPip) mobPip.style.display = 'block';
                    if (countText) {
                        countText.textContent = `${data.count} new`;
                        countText.style.background = 'var(--red-lt)';
                        countText.style.color = 'var(--red-tx)';
                    }
                } else {
                    if (pip) pip.style.display = 'none';
                    if (mobPip) mobPip.style.display = 'none';
                    if (countText) {
                        countText.textContent = `0 new`;
                        countText.style.background = 'var(--purple-lt)';
                        countText.style.color = 'var(--purple-tx)';
                    }
                }

                // Show toast alerts
                data.notifications.forEach(n => {
                    if (!shownNotifs.has(n.id)) {
                        shownNotifs.add(n.id);
                        showToastNotification(n);
                    }
                });

                // Render list templates
                const renderListItem = (n) => `
                    <div class="notif-item" data-id="${n.id}" data-url="${n.data.url}" style="padding: 12px 16px; border-bottom: 1px solid var(--div); cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='var(--row-hov)'" onmouseout="this.style.background='transparent'">
                        <div style="font-weight: 600; color: var(--txt);">Central Kitchen</div>
                        <div style="color: var(--txt2); font-size: 12px; margin-top: 2px;">${n.data.message}</div>
                        <div style="color: var(--txt3); font-size: 11px; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 10px; height: 10px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            ${n.created_at}
                        </div>
                    </div>
                `;

                if (data.notifications.length === 0) {
                    if (list) list.innerHTML = `<div style="padding: 20px; text-align: center; color: var(--txt3);">No new notifications</div>`;
                    if (mobList) mobList.innerHTML = `<div style="padding: 20px; text-align: center; color: var(--txt3);">No new notifications</div>`;
                } else {
                    let html = '';
                    data.notifications.forEach(n => { html += renderListItem(n); });
                    
                    if (list) list.innerHTML = html;
                    if (mobList) mobList.innerHTML = html;

                    // Bind actions
                    document.querySelectorAll('.notif-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const id = this.dataset.id;
                            const url = this.dataset.url;

                            fetch(`/portal/api/notifications/${id}/read`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(() => { window.location.href = url; })
                            .catch(() => { window.location.href = url; });
                        });
                    });
                }
            })
            .catch(err => console.error('Error fetching notifications:', err));
    }

    // Trigger local test app notifications
    const testNotifBtn = document.getElementById('mobile-test-notif-btn');
    if (testNotifBtn) {
        testNotifBtn.addEventListener('click', () => {
            playNotificationSound();
            closeAllSheets();

            if (!("Notification" in window)) {
                showToastMessage("Web notifications not supported. Sound chime played!");
            } else if (Notification.permission === "granted") {
                triggerSystemNotification();
            } else if (Notification.permission !== "denied") {
                Notification.requestPermission().then(permission => {
                    if (permission === "granted") {
                        triggerSystemNotification();
                    } else {
                        showToastMessage("Notification permission denied. Sound chime played!");
                    }
                });
            } else {
                showToastMessage("Notification permission blocked. Sound chime played!");
            }
        });
    }

    function triggerSystemNotification() {
        try {
            const notif = new Notification("DessertOps Portal App Alert", {
                body: "Success! DessertOps Web App notifications are working on your mobile device.",
                icon: "/img/icon-192.png"
            });
            notif.onclick = () => {
                window.focus();
                notif.close();
            };
        } catch(e) {
            showToastMessage("Notification alert popped successfully!");
        }
    }

    function showToastMessage(msg) {
        const toast = document.createElement('div');
        toast.className = 'toast-notif';
        toast.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: var(--purple-lt); color: var(--purple-tx); flex-shrink: 0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 18px; height: 18px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </div>
            <div style="flex: 1; padding-right: 18px;">
                <div style="font-weight: 700; color: var(--txt); font-size: 13px; margin-bottom: 2px;">App Notification Test</div>
                <div style="color: var(--txt2); font-size: 12px; line-height: 1.4;">${msg}</div>
            </div>
            <button type="button" class="toast-notif-close" onclick="this.parentElement.remove()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;
        if (toastContainer) {
            toastContainer.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
    }

    fetchNotifications();
    setInterval(fetchNotifications, 15000); // 15s poll
});
</script>

<script>
// ══ Data Pulse: Auto-refresh portal pages when data changes ══
(function() {
    let lastPulse = null;
    let isFormPage = document.querySelector('form[method="POST"]') !== null;

    // Don't auto-refresh on form/create pages to prevent data loss
    if (isFormPage) return;

    function checkPulse() {
        fetch('/portal/api/data-pulse', { headers: { 'Accept': 'application/json' } })
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
