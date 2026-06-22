<?php

use Illuminate\Support\Facades\Route;
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

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('suppliers', SupplierController::class);
Route::resource('materials', MaterialController::class);
Route::resource('purchase-orders', PurchaseOrderController::class);

Route::get('purchase-orders/{purchase_order}/receive', [GrnController::class, 'create'])->name('purchase-orders.receive');
Route::post('purchase-orders/{purchase_order}/receive', [GrnController::class, 'store'])->name('purchase-orders.receive.store');
Route::resource('grns', GrnController::class)->only(['index', 'show']);

// Module 2: Central Kitchen Routes
Route::get('kitchen/stocks', [MaterialController::class, 'kitchenStock'])->name('kitchen.stocks');
Route::resource('products', ProductController::class);
Route::resource('material-requests', MaterialRequestController::class);
Route::post('material-requests/{material_request}/approve', [MaterialRequestController::class, 'approve'])->name('material-requests.approve');
Route::post('material-requests/{material_request}/release', [MaterialRequestController::class, 'release'])->name('material-requests.release');
Route::post('material-requests/{material_request}/reject', [MaterialRequestController::class, 'reject'])->name('material-requests.reject');
Route::resource('production-runs', ProductionRunController::class);
Route::post('production-runs/{production_run}/complete', [ProductionRunController::class, 'complete'])->name('production-runs.complete');

// Module 3: Distribution & Outlets Routes
Route::resource('outlets', OutletController::class);
Route::resource('dispatches', DispatchController::class);
Route::post('dispatches/{dispatch}/dispatch', [DispatchController::class, 'dispatch'])->name('dispatches.dispatch');
Route::post('dispatches/{dispatch}/receive', [DispatchController::class, 'receive'])->name('dispatches.receive');
Route::resource('sales-logs', SalesLogController::class);


