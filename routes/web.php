<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GrnController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MaterialRequestController;
use App\Http\Controllers\ProductionRunController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\SalesLogController;
use App\Http\Controllers\OutletPortalController;
use App\Http\Controllers\OutletCatalogController;
use App\Http\Controllers\AccountingController;

// ══ Unified Authentication Routes ══
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Override portal login view to use unified login page
Route::get('portal', function () {
    return redirect()->route('login');
})->name('portal.login');

Route::post('portal/login', [OutletPortalController::class, 'login'])->name('portal.login.post');
Route::post('portal/logout', [OutletPortalController::class, 'logout'])->name('portal.logout');


// ══ Protected Admin ERP Routes ══
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // API & Utility Routes
    Route::get('api/search', [\App\Http\Controllers\UniversalSearchController::class, 'search'])->name('api.search');
    Route::get('outlets/{outlet}/mini-dashboard', [\App\Http\Controllers\OutletController::class, 'miniDashboard'])->name('outlets.mini-dashboard');
    Route::get('api/outlets', function() {
        return response()->json(\App\Models\Outlet::orderBy('name')->get(['id', 'name', 'type']));
    })->name('api.outlets');

    // 1. Administration (User Management)
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', \App\Http\Controllers\AdminUserController::class);
    });

    // 2. Procurement (Suppliers, POs, GRNs)
    Route::middleware(['role:admin,gm,store_manager'])->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::post('suppliers/{supplier}/materials', [SupplierController::class, 'linkMaterial'])->name('suppliers.link-material');
        Route::delete('suppliers/{supplier}/materials/{material}', [SupplierController::class, 'unlinkMaterial'])->name('suppliers.unlink-material');
        Route::resource('purchase-orders', PurchaseOrderController::class);
        Route::get('purchase-orders/{purchase_order}/receive', [GrnController::class, 'create'])->name('purchase-orders.receive');
        Route::post('purchase-orders/{purchase_order}/receive', [GrnController::class, 'store'])->name('purchase-orders.receive.store');
        Route::resource('grns', GrnController::class)->only(['index', 'show']);
    });

    // 3. Materials Management
    // Raw materials full access for store manager/admin/gm
    Route::resource('materials', MaterialController::class)->except(['index', 'show'])->middleware('role:admin,gm,store_manager');
    // Raw materials read-only for kitchen chef
    Route::resource('materials', MaterialController::class)->only(['index', 'show'])->middleware('role:admin,gm,store_manager,laban_chef,baklava_chef,dough_chef');


    // 4. Material Requests
    // Request creation and viewing for kitchen chef, store manager, GM, Admin
    Route::middleware(['role:admin,gm,laban_chef,baklava_chef,dough_chef,store_manager'])->group(function () {
        Route::resource('material-requests', MaterialRequestController::class)->except(['destroy']);
    });
    // Request approvals/releases only for store manager, GM, Admin
    Route::middleware(['role:admin,gm,store_manager'])->group(function () {
        Route::post('material-requests/{material_request}/approve', [MaterialRequestController::class, 'approve'])->name('material-requests.approve');
        Route::post('material-requests/{material_request}/release', [MaterialRequestController::class, 'release'])->name('material-requests.release');
        Route::post('material-requests/{material_request}/reject', [MaterialRequestController::class, 'reject'])->name('material-requests.reject');
    });

    // 5. Central Kitchen (Production, Products, Dispatches)
    Route::middleware(['role:admin,gm,laban_chef,baklava_chef,dough_chef'])->group(function () {
        Route::get('kitchen/stocks', [MaterialController::class, 'kitchenStock'])->name('kitchen.stocks');
        Route::get('kitchen/stock-report', [MaterialController::class, 'kitchenStockReport'])->name('kitchen.stock-report');
        Route::resource('products', ProductController::class);
        Route::resource('outlet-catalog', OutletCatalogController::class);
        Route::get('api/outlet-catalog/{outlet_catalog}/ingredients', [OutletCatalogController::class, 'ingredients'])->name('api.outlet-catalog.ingredients');
        Route::resource('production-runs', ProductionRunController::class);
        Route::post('production-runs/{production_run}/complete', [ProductionRunController::class, 'complete'])->name('production-runs.complete');
        Route::get('outlet-orders', [DispatchController::class, 'outletOrders'])->name('dispatches.orders');
        Route::resource('dispatches', DispatchController::class)->except(['edit', 'update']);
        Route::post('dispatches/{dispatch}/dispatch', [DispatchController::class, 'dispatch'])->name('dispatches.dispatch');
    });

    // 6. Distribution & Outlets (Outlets, Sales Logs)
    Route::middleware(['role:admin,gm'])->group(function () {
        Route::get('outlets/monitor', [\App\Http\Controllers\OutletMonitorController::class, 'index'])->name('outlets.monitor');
        Route::get('outlets/showcase-requests', [\App\Http\Controllers\OutletMonitorController::class, 'showcaseRequests'])->name('outlets.showcase-requests');
        Route::resource('outlets', OutletController::class);
        Route::post('outlets/{outlet}/assignments', [OutletController::class, 'updateAssignments'])->name('outlets.update-assignments');
        Route::get('api/outlets/{outlet}/assigned-products', [OutletController::class, 'assignedProducts'])->name('api.outlets.assigned-products');
        Route::resource('sales-logs', SalesLogController::class)->only(['index', 'show', 'create', 'store', 'destroy']);
    });



    // Live Notification API routes
    Route::get('api/notifications', function() {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
            'notifications' => auth()->user()->unreadNotifications()->take(5)->get()->map(function($n) {
                return [
                    'id' => $n->id,
                    'data' => $n->data,
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            })
        ]);
    })->name('api.notifications');

    Route::post('api/notifications/{id}/read', function($id) {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();
        return response()->json(['success' => true]);
    })->name('api.notifications.read');

    // Data Pulse API — returns a fingerprint of latest data for live page refresh
    Route::get('api/data-pulse', function() {
        $pulse = collect([
            App\Models\Dispatch::max('updated_at'),
            App\Models\Product::max('updated_at'),
            App\Models\OutletStock::max('updated_at'),
            App\Models\SalesLog::max('updated_at'),
            App\Models\ProductionRun::max('updated_at'),
            App\Models\Material::max('updated_at'),
            App\Models\PurchaseOrder::max('updated_at'),
            App\Models\MaterialRequest::max('updated_at'),
        ])->filter()->max();
        return response()->json([
            'pulse' => $pulse ? $pulse->toIso8601String() : now()->toIso8601String(),
        ]);
    })->name('api.data-pulse');
});


// ══ Protected Outlet Portal Routes ══
Route::middleware(['portal.outlet'])->group(function () {
    Route::get('portal/dashboard', [OutletPortalController::class, 'dashboard'])->name('portal.dashboard');
    Route::get('portal/dispatches', [OutletPortalController::class, 'dispatches'])->name('portal.dispatches');
    Route::post('portal/dispatches/{dispatch}/receive', [OutletPortalController::class, 'receiveDispatch'])->name('portal.dispatches.receive');
    Route::get('portal/api/dispatches/{dispatch}/status', function(App\Models\Dispatch $dispatch) {
        return response()->json([
            'status' => $dispatch->status,
            'updated_at' => $dispatch->updated_at->toIso8601String(),
        ]);
    })->name('portal.api.dispatches.status');
    Route::get('portal/sales', [OutletPortalController::class, 'salesIndex'])->name('portal.sales.index');
    Route::get('portal/sales/create', [OutletPortalController::class, 'salesCreate'])->name('portal.sales.create');
    Route::post('portal/sales', [OutletPortalController::class, 'salesStore'])->name('portal.sales.store');

    // Product requests
    Route::get('portal/requests/create', [OutletPortalController::class, 'requestsCreate'])->name('portal.requests.create');
    Route::post('portal/requests', [OutletPortalController::class, 'requestsStore'])->name('portal.requests.store');

    // Internal Stock Movements & Showcase Requests
    Route::post('portal/stock/move', [OutletPortalController::class, 'moveStock'])->name('portal.stock.move');
    Route::get('portal/showcase-requests', [\App\Http\Controllers\PortalShowcaseRequestController::class, 'index'])->name('portal.showcase-requests.index');
    Route::get('portal/showcase-requests/create', [\App\Http\Controllers\PortalShowcaseRequestController::class, 'create'])->name('portal.showcase-requests.create');
    Route::post('portal/showcase-requests', [\App\Http\Controllers\PortalShowcaseRequestController::class, 'store'])->name('portal.showcase-requests.store');
    Route::get('portal/showcase-requests/{showcaseRequest}', [\App\Http\Controllers\PortalShowcaseRequestController::class, 'show'])->name('portal.showcase-requests.show');
    Route::post('portal/showcase-requests/{showcaseRequest}/approve', [\App\Http\Controllers\PortalShowcaseRequestController::class, 'approve'])->name('portal.showcase-requests.approve');
    Route::post('portal/showcase-requests/{showcaseRequest}/reject', [\App\Http\Controllers\PortalShowcaseRequestController::class, 'reject'])->name('portal.showcase-requests.reject');
    Route::post('portal/showcase-requests/{showcaseRequest}/release', [\App\Http\Controllers\PortalShowcaseRequestController::class, 'release'])->name('portal.showcase-requests.release');

    // Outlet Employee Management (Admin only — enforced in controller)
    Route::get('portal/employees', [\App\Http\Controllers\OutletEmployeeController::class, 'index'])->name('portal.employees.index');
    Route::get('portal/employees/create', [\App\Http\Controllers\OutletEmployeeController::class, 'create'])->name('portal.employees.create');
    Route::post('portal/employees', [\App\Http\Controllers\OutletEmployeeController::class, 'store'])->name('portal.employees.store');
    Route::get('portal/employees/{employee}/edit', [\App\Http\Controllers\OutletEmployeeController::class, 'edit'])->name('portal.employees.edit');
    Route::put('portal/employees/{employee}', [\App\Http\Controllers\OutletEmployeeController::class, 'update'])->name('portal.employees.update');
    Route::delete('portal/employees/{employee}', [\App\Http\Controllers\OutletEmployeeController::class, 'destroy'])->name('portal.employees.destroy');

    // Outlet Kitchen Production (Admin creates/completes, Salesperson can view)
    Route::get('portal/production', [\App\Http\Controllers\OutletProductionController::class, 'index'])->name('portal.production.index');
    Route::get('portal/production/create', [\App\Http\Controllers\OutletProductionController::class, 'create'])->name('portal.production.create');
    Route::post('portal/production', [\App\Http\Controllers\OutletProductionController::class, 'store'])->name('portal.production.store');
    Route::get('portal/production/{run}', [\App\Http\Controllers\OutletProductionController::class, 'show'])->name('portal.production.show');
    Route::post('portal/production/{run}/complete', [\App\Http\Controllers\OutletProductionController::class, 'complete'])->name('portal.production.complete');
    Route::delete('portal/production/{run}', [\App\Http\Controllers\OutletProductionController::class, 'destroy'])->name('portal.production.destroy');

    // Outlet Stock Report
    Route::get('portal/stock-report', [\App\Http\Controllers\OutletPortalController::class, 'stockReport'])->name('portal.stock-report');

    // Portal Live Notification API routes
    Route::get('portal/api/notifications', function() {
        $outlet = auth('outlet')->user();
        if (!$outlet && session('portal_outlet_id')) {
            $outlet = \App\Models\Outlet::find(session('portal_outlet_id'));
        }
        if (!$outlet) {
            return response()->json(['count' => 0, 'notifications' => []]);
        }
        return response()->json([
            'count' => $outlet->unreadNotifications()->count(),
            'notifications' => $outlet->unreadNotifications()->take(5)->get()->map(function($n) {
                return [
                    'id' => $n->id,
                    'data' => $n->data,
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            })
        ]);
    })->name('portal.api.notifications');

    Route::post('portal/api/notifications/{id}/read', function($id) {
        $outlet = auth('outlet')->user();
        if (!$outlet && session('portal_outlet_id')) {
            $outlet = \App\Models\Outlet::find(session('portal_outlet_id'));
        }
        if ($outlet) {
            $outlet->notifications()->findOrFail($id)->markAsRead();
        }
        return response()->json(['success' => true]);
    })->name('portal.api.notifications.read');

    // Portal Data Pulse API — for live page refresh
    Route::get('portal/api/data-pulse', function() {
        $outlet = auth('outlet')->user();
        if (!$outlet && session('portal_outlet_id')) {
            $outlet = \App\Models\Outlet::find(session('portal_outlet_id'));
        }
        $pulse = collect([
            App\Models\Dispatch::where('outlet_id', $outlet ? $outlet->id : 0)->max('updated_at'),
            App\Models\OutletStock::where('outlet_id', $outlet ? $outlet->id : 0)->max('updated_at'),
            App\Models\SalesLog::where('outlet_id', $outlet ? $outlet->id : 0)->max('updated_at'),
        ])->filter()->max();
        return response()->json([
            'pulse' => $pulse ? $pulse->toIso8601String() : now()->toIso8601String(),
        ]);
    })->name('portal.api.data-pulse');
});

// ══ Protected Accountant Portal Routes ══
Route::middleware(['portal.accountant'])->prefix('accounting')->name('accounting.')->group(function () {
    Route::get('/', [AccountingController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'accountantLogout'])->name('logout');

    // Payment Vouchers (Money Going Out)
    Route::get('/payment-vouchers', [AccountingController::class, 'paymentVouchersIndex'])->name('payment-vouchers.index');
    Route::get('/payment-vouchers/create', [AccountingController::class, 'paymentVoucherCreate'])->name('payment-vouchers.create');
    Route::post('/payment-vouchers', [AccountingController::class, 'paymentVoucherStore'])->name('payment-vouchers.store');
    Route::get('/payment-vouchers/show', [AccountingController::class, 'paymentVoucherShow'])->name('payment-vouchers.show');

    // Receipt Vouchers (Money Coming In)
    Route::get('/receipt-vouchers', [AccountingController::class, 'receiptVouchersIndex'])->name('receipt-vouchers.index');
    Route::get('/receipt-vouchers/create', [AccountingController::class, 'receiptVoucherCreate'])->name('receipt-vouchers.create');
    Route::post('/receipt-vouchers', [AccountingController::class, 'receiptVoucherStore'])->name('receipt-vouchers.store');
    Route::get('/receipt-vouchers/show', [AccountingController::class, 'receiptVoucherShow'])->name('receipt-vouchers.show');

    // Pending Dues
    Route::get('/bills', [AccountingController::class, 'billsIndex'])->name('bills.index');
    Route::get('/bills/{bill}', [AccountingController::class, 'billShow'])->name('bills.show');
    Route::get('/franchise-invoices', [AccountingController::class, 'franchiseInvoicesIndex'])->name('franchise-invoices.index');
    Route::get('/franchise-invoices/{invoice}', [AccountingController::class, 'franchiseInvoiceShow'])->name('franchise-invoices.show');

    // Transaction History & Reports
    Route::get('/transaction-history', [AccountingController::class, 'transactionHistory'])->name('transaction-history');
    Route::get('/ledger', function() { return redirect()->route('accounting.transaction-history', request()->query()); }); // backward compat
    Route::get('/reports', [AccountingController::class, 'reports'])->name('reports');

    // Account Transfers
    Route::get('/transfers', [AccountingController::class, 'transfersIndex'])->name('transfers.index');
    Route::get('/transfers/create', [AccountingController::class, 'transferCreate'])->name('transfers.create');
    Route::post('/transfers', [AccountingController::class, 'transferStore'])->name('transfers.store');
});

