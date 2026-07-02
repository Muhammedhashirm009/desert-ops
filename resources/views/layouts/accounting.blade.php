<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Accounting Portal — DessertOps')</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@yield('styles')
<style>
  /* Accountant Portal Custom Theme Color Override (using Emerald/Teal variables) */
  :root {
    --acc-green: #10b981;
    --acc-green-hov: #059669;
    --acc-green-lt: rgba(16, 185, 129, 0.15);
    --acc-green-tx: #34d399;
  }
  
  .nav-i.on {
    background: var(--acc-green-lt) !important;
    color: var(--acc-green-tx) !important;
    border-left: 3px solid var(--acc-green) !important;
  }

  .nav-i.on svg {
    stroke: var(--acc-green-tx) !important;
  }

  .badge.bg-green {
    background: var(--acc-green-lt);
    color: var(--acc-green-tx);
    border: 1px solid rgba(16, 185, 129, 0.3);
  }

  /* Secondary Button / Filter Tab Button Style */
  .btn-sec {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--input-bg, #ffffff);
    color: var(--txt2, #4b5563);
    border: 1px solid var(--input-b, #e5e7eb);
    border-radius: 20px;
    padding: 6px 16px;
    font-size: 12.5px;
    font-weight: 500;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
    text-decoration: none;
    transition: all 0.15s ease;
  }

  .btn-sec:hover {
    background: var(--div, #f3f4f6);
    color: var(--txt, #111827);
    border-color: var(--txt3, #9ca3af);
  }

  .btn-sec.on {
    background: var(--acc-green-lt, rgba(16, 185, 129, 0.15));
    color: var(--acc-green-hov, #059669);
    border-color: var(--acc-green, #10b981);
    font-weight: 600;
  }

  /* Mobile Tab / Top Bar Active Overrides */
  @media (max-width: 768px) {
    .mobile-tab.active {
      color: var(--acc-green) !important;
    }
    .mobile-tab.active svg {
      stroke: var(--acc-green) !important;
    }
    .mobile-top-logo-mark {
      background: var(--acc-green) !important;
    }
    .bottom-sheet-item:hover, .bottom-sheet-item:active {
      border-color: var(--acc-green) !important;
      color: var(--acc-green-hov) !important;
      background: var(--acc-green-lt) !important;
    }
    .bottom-sheet-item:hover svg, .bottom-sheet-item:active svg {
      stroke: var(--acc-green) !important;
    }
  }

  /* Responsive Grids & Columns Helper Classes */
  .grid-2-col {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
  .grid-3-col {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }
  .filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    align-items: flex-end;
  }
  .filter-form > div {
    flex: 1 1 200px;
    width: 100%;
  }
  .filter-form > button {
    flex: 1 1 auto;
    height: 38px;
    width: 100%;
  }

  @media (max-width: 768px) {
    .grid-2-col, .grid-3-col, .r-2, .r-3-1, .row, .grid-2 {
      grid-template-columns: 1fr !important;
      gap: 16px !important;
    }
    .grid-3-col > div {
      border-right: none !important;
      border-bottom: 1px solid var(--div2);
      padding-bottom: 14px;
      margin-bottom: 14px;
    }
    .grid-3-col > div:last-child {
      border-bottom: none !important;
      padding-bottom: 0;
      margin-bottom: 0;
    }
    
    /* Responsive Table Styles (Cards on Mobile) */
    table.tbl thead {
      display: none !important;
    }
    table.tbl tbody, table.tbl tr, table.tbl td {
      display: block !important;
      width: 100% !important;
    }
    table.tbl tr {
      margin-bottom: 14px !important;
      background: #FFFFFF !important;
      border: 1px solid var(--div2) !important;
      border-radius: var(--radius) !important;
      padding: 14px !important;
      box-shadow: var(--card-sh) !important;
    }
    table.tbl td {
      text-align: right !important;
      padding: 8px 0 !important;
      border-bottom: 1px solid var(--div) !important;
      position: relative !important;
      padding-left: 45% !important;
      font-size: 13px !important;
    }
    table.tbl td:last-child {
      border-bottom: none !important;
      padding-bottom: 0 !important;
    }
    table.tbl td::before {
      content: attr(data-label) !important;
      position: absolute !important;
      left: 0 !important;
      width: 40% !important;
      font-weight: 600 !important;
      text-align: left !important;
      color: var(--txt2) !important;
      font-size: 12.5px !important;
    }
  }
</style>
</head>
<body>

<!-- ══ MOBILE TOP BAR ══ -->
<header class="mobile-top-bar">
  <a href="{{ route('accounting.dashboard') }}" class="mobile-top-logo">
    <div class="mobile-top-logo-mark">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2" ry="2"/><line x1="12" y1="2" x2="12" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
    </div>
    <span class="mobile-top-logo-name">Finance Portal</span>
  </a>
</header>

<!-- ══ SIDEBAR ══ -->
<aside class="sb" style="background: rgba(13, 27, 24, 0.95); border-right: 1px solid rgba(16, 185, 129, 0.15);">
  <div class="sb-logo">
    <div class="sb-mark" style="background: var(--acc-green);">
      <svg viewBox="0 0 24 24" style="stroke:#fff; fill:none;"><rect x="2" y="2" width="20" height="20" rx="2" ry="2"/><line x1="12" y1="2" x2="12" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
    </div>
    <div>
      <div class="sb-brand" style="color: #fff;">Finance Portal</div>
      <div class="sb-ver" style="color: var(--acc-green-tx);">Accountant Session</div>
    </div>
  </div>

  @php
    $currentAccountant = \Illuminate\Support\Facades\Auth::guard('accountant')->user() 
        ?? \Illuminate\Support\Facades\Auth::guard('web')->user();
  @endphp

  @if($currentAccountant)
  <div style="padding: 14px 16px 6px;">
    <div style="font-size:10px; color:var(--txt3); font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Logged In As</div>
    <div style="font-weight:700; color:#fff; font-size:13.5px; margin-top:2px; display:flex; align-items:center; gap:6px;">
      <span class="on-dot" style="width:7px; height:7px; background-color: var(--acc-green);"></span>
      {{ $currentAccountant->name }}
    </div>
    <span class="badge bg-green" style="font-size:9.5px; padding:1px 5px; margin-top:4px; display:inline-block;">
      @if(isset($currentAccountant->role))
        {{ $currentAccountant->role === 'admin' ? 'Administrator' : 'Accountant' }}
      @else
        General Accountant
      @endif
    </span>
  </div>
  @endif

  <div class="sb-grp" style="margin-top: 10px;">
    <div class="sb-grp-label" style="color: rgba(16, 185, 129, 0.65);">Overview</div>
    <a href="{{ route('accounting.dashboard') }}" class="nav-i {{ request()->routeIs('accounting.dashboard') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>Money Overview
    </a>
  </div>

  <div class="sb-grp">
    <div class="sb-grp-label" style="color: rgba(16, 185, 129, 0.65);">Vouchers</div>
    <a href="{{ route('accounting.payment-vouchers.index') }}" class="nav-i {{ request()->routeIs('accounting.payment-vouchers.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg></span>Payment Vouchers
    </a>
    <a href="{{ route('accounting.receipt-vouchers.index') }}" class="nav-i {{ request()->routeIs('accounting.receipt-vouchers.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg></span>Receipt Vouchers
    </a>
    <a href="{{ route('accounting.transfers.index') }}" class="nav-i {{ request()->routeIs('accounting.transfers.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3L21 7L17 11"/><path d="M3 17L7 21L3 17"/><path d="M21 7H9"/><path d="M3 17H15"/></svg></span>Account Transfers
    </a>
  </div>

  <div class="sb-grp">
    <div class="sb-grp-label" style="color: rgba(16, 185, 129, 0.65);">Records</div>
    <a href="{{ route('accounting.bills.index') }}" class="nav-i {{ request()->routeIs('accounting.bills.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>Supplier Bills
    </a>
    <a href="{{ route('accounting.franchise-invoices.index') }}" class="nav-i {{ request()->routeIs('accounting.franchise-invoices.*') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg></span>Franchise Invoices
    </a>
  </div>

  <div class="sb-grp">
    <div class="sb-grp-label" style="color: rgba(16, 185, 129, 0.65);">Reports</div>
    <a href="{{ route('accounting.transaction-history') }}" class="nav-i {{ request()->routeIs('accounting.transaction-history') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></span>Transaction History
    </a>
    <a href="{{ route('accounting.reports') }}" class="nav-i {{ request()->routeIs('accounting.reports') ? 'on' : '' }}">
      <span class="ni"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>Summary Report
    </a>
  </div>

  <div class="sb-sep" style="border-top: 1px solid rgba(16, 185, 129, 0.15);"></div>

  <div class="sb-foot">
    <form action="{{ route('accounting.logout') }}" method="POST" style="width: 100%;">
      @csrf
      <button type="submit" class="btn-pri" style="width: 100%; background: rgba(16, 185, 129, 0.1); color: var(--acc-green-tx); font-size:12px; font-weight:600; padding:8px 10px; border-radius: var(--radius-sm); border: 1px solid rgba(16, 185, 129, 0.3); display:flex; align-items:center; justify-content:center; gap:6px; cursor:pointer; transition: all 0.2s;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:13px; height:13px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout Portal
      </button>
    </form>
  </div>
</aside>

<!-- ══ MAIN ══ -->
<div class="main">
  <!-- TOPBAR -->
  <header class="tb" style="border-bottom: 1px solid rgba(16, 185, 129, 0.15);">
    <div class="tb-bc">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" style="stroke: var(--acc-green-tx);"><rect x="2" y="2" width="20" height="20" rx="2" ry="2"/><line x1="12" y1="2" x2="12" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
      <span>Accounting Portal</span>
      <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <b>@yield('breadcrumb', 'Dashboard')</b>
    </div>
    <div class="tb-sp"></div>

    <div class="tb-user" style="cursor: default; border: 1px solid rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.05); color: #111827;">
      <div class="tb-uav" style="background: var(--acc-green); color: #fff;">{{ strtoupper(substr($currentAccountant ? $currentAccountant->name : 'AC', 0, 2)) }}</div>
      <span class="tb-uname" style="color: #111827 !important;">{{ $currentAccountant ? $currentAccountant->name : 'Accountant User' }}</span>
    </div>
  </header>

  <!-- CONTENT -->
  <div class="content">
      @if(session('success'))
          <div class="alert alert-success" style="border-left: 4px solid var(--acc-green); background: rgba(16, 185, 129, 0.1); color: var(--acc-green-tx);">
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

    // Hide native select
    select.style.display = 'none';
    select.dataset.searchableInitialized = "true";

    const wrapper = document.createElement('div');
    wrapper.className = 'searchable-select';

    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'searchable-select-input';
    input.autocomplete = 'off';
    input.style.height = select.style.height || '38px';
    
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

    const arrowContainer = document.createElement('div');
    arrowContainer.innerHTML = `<svg class="searchable-select-arrow" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>`;
    const arrow = arrowContainer.firstElementChild;
    wrapper.appendChild(arrow);

    const dropdown = document.createElement('div');
    dropdown.className = 'searchable-select-dropdown';
    wrapper.appendChild(dropdown);

    const noResults = document.createElement('div');
    noResults.className = 'searchable-select-no-results';
    noResults.textContent = 'No options match...';
    noResults.style.display = 'none';
    dropdown.appendChild(noResults);

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

    function openDropdown() {
        document.querySelectorAll('.searchable-select.open').forEach(el => {
            if (el !== wrapper) el.classList.remove('open');
        });
        wrapper.classList.add('open');
        input.select();
        filterOptions();
    }

    function closeDropdown() {
        wrapper.classList.remove('open');
        const currSelected = select.options[select.selectedIndex];
        if (currSelected && (currSelected.value !== '' || select.selectedIndex !== 0)) {
            input.value = currSelected.textContent.trim();
        } else {
            input.value = '';
        }
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
        
        const opt = select.options[index];
        if (opt.value === '' && index === 0) {
            input.value = '';
            input.placeholder = opt.textContent.trim();
        } else {
            input.value = opt.textContent.trim();
        }

        items.forEach(i => i.classList.remove('selected'));
        item.classList.add('selected');

        select.dispatchEvent(new Event('change', { bubbles: true }));
        closeDropdown();
    }

    input.addEventListener('click', function(e) {
        e.stopPropagation();
        if (!wrapper.classList.contains('open')) {
            openDropdown();
        }
    });

    input.addEventListener('input', function() {
        if (!wrapper.classList.contains('open')) {
            wrapper.classList.add('open');
        }
        filterOptions();
    });

    items.forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            selectOption(item);
        });
    });

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

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('select.searchable-select').forEach(select => {
        window.initSearchableSelect(select);
    });
});
</script>

<!-- ══ MOBILE BOTTOM BAR ══ -->
<nav class="mobile-bottom-bar">
  <a href="{{ route('accounting.dashboard') }}" class="mobile-tab {{ request()->routeIs('accounting.dashboard') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    <span>Home</span>
  </a>
  <a href="{{ route('accounting.payment-vouchers.index') }}" class="mobile-tab {{ request()->routeIs('accounting.payment-vouchers.*') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
    <span>Payments</span>
  </a>
  <a href="{{ route('accounting.receipt-vouchers.index') }}" class="mobile-tab {{ request()->routeIs('accounting.receipt-vouchers.*') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
    <span>Receipts</span>
  </a>
  <a href="{{ route('accounting.bills.index') }}" class="mobile-tab {{ request()->routeIs('accounting.bills.*') ? 'active' : '' }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
    <span>Bills</span>
  </a>
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
  <div class="bottom-sheet-title">Finance Options</div>

  @if($currentAccountant)
  <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid var(--div2); border-radius: var(--radius); padding: 12px; margin-bottom: 20px; text-align: center;">
    <div style="font-size: 11px; color: var(--txt3); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Active Accountant</div>
    <div style="font-size: 15px; font-weight: 700; color: var(--txt); margin-top: 4px;">{{ $currentAccountant->name }}</div>
    <span class="badge bg-green" style="font-size: 9.5px; padding: 1px 6px; margin-top: 6px; display: inline-block;">
      General Accountant
    </span>
  </div>
  @endif

  <div class="bottom-sheet-grid">
    <a href="{{ route('accounting.franchise-invoices.index') }}" class="bottom-sheet-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
      <span>Franchise Invoices</span>
    </a>
    <a href="{{ route('accounting.transfers.index') }}" class="bottom-sheet-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3L21 7L17 11"/><path d="M3 17L7 21L3 17"/><path d="M21 7H9"/><path d="M3 17H15"/></svg>
      <span>Account Transfers</span>
    </a>
    <a href="{{ route('accounting.transaction-history') }}" class="bottom-sheet-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
      <span>Transaction History</span>
    </a>
    <a href="{{ route('accounting.reports') }}" class="bottom-sheet-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      <span>Summary Report</span>
    </a>
  </div>

  <div style="margin-top: 20px; border-top: 1px solid var(--div2); padding-top: 16px;">
    <form action="{{ route('accounting.logout') }}" method="POST" style="width: 100%;">
      @csrf
      <button type="submit" class="bottom-sheet-item logout-btn" style="width: 100%; border: 1px solid rgba(220, 38, 38, 0.2); font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; background: none; padding: 12px 10px; cursor: pointer; border-radius: var(--radius);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout Finance Session
      </button>
    </form>
  </div>
</div>

<script>
// Mobile Sheet Toggles
document.addEventListener('DOMContentLoaded', function() {
    const moreTabBtn = document.getElementById('mobile-more-tab-btn');
    const moreSheet = document.getElementById('more-sheet');
    const moreOverlay = document.getElementById('more-sheet-overlay');

    function openSheet(sheet) {
        sheet.classList.add('open');
        moreOverlay.classList.add('open');
    }

    function closeAllSheets() {
        if (moreSheet) moreSheet.classList.remove('open');
        if (moreOverlay) moreOverlay.classList.remove('open');
    }

    if (moreTabBtn) {
        moreTabBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            closeAllSheets();
            openSheet(moreSheet);
        });
    }

    if (moreOverlay) {
        moreOverlay.addEventListener('click', closeAllSheets);
    }
});
</script>

@yield('scripts')
</body>
</html>
