<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GrnController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('suppliers', SupplierController::class);
Route::resource('materials', MaterialController::class);
Route::resource('purchase-orders', PurchaseOrderController::class);

Route::get('purchase-orders/{purchase_order}/receive', [GrnController::class, 'create'])->name('purchase-orders.receive');
Route::post('purchase-orders/{purchase_order}/receive', [GrnController::class, 'store'])->name('purchase-orders.receive.store');
Route::resource('grns', GrnController::class)->only(['index', 'show']);

