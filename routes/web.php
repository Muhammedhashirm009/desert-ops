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

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('suppliers', SupplierController::class);
Route::resource('materials', MaterialController::class);
Route::resource('purchase-orders', PurchaseOrderController::class);

Route::get('purchase-orders/{purchase_order}/receive', [GrnController::class, 'create'])->name('purchase-orders.receive');
Route::post('purchase-orders/{purchase_order}/receive', [GrnController::class, 'store'])->name('purchase-orders.receive.store');
Route::resource('grns', GrnController::class)->only(['index', 'show']);

// Module 2: Central Kitchen Routes
Route::resource('products', ProductController::class);
Route::resource('material-requests', MaterialRequestController::class);
Route::post('material-requests/{material_request}/approve', [MaterialRequestController::class, 'approve'])->name('material-requests.approve');
Route::post('material-requests/{material_request}/release', [MaterialRequestController::class, 'release'])->name('material-requests.release');
Route::post('material-requests/{material_request}/reject', [MaterialRequestController::class, 'reject'])->name('material-requests.reject');
Route::resource('production-runs', ProductionRunController::class);
Route::post('production-runs/{production_run}/complete', [ProductionRunController::class, 'complete'])->name('production-runs.complete');


