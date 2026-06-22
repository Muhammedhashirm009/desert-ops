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
});
</script>

@yield('scripts')
</body>
</html>
