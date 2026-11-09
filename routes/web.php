<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

// Fitur 1: Daftar Barang & Tambah Barang
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::post('/items', [ItemController::class, 'store'])->name('items.store');

// Fitur 2, 3, 4: Pencatatan Mutasi Masuk/Keluar, Update Stok Otomatis, & Proteksi Stok Minus
Route::post('/movements', [StockMovementController::class, 'store'])->name('movements.store');

// Fitur 5: Riwayat Mutasi per Barang (Detail Page & JSON API untuk Modal Alpine.js)
Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
Route::get('/items/{item}/movements-api', [ItemController::class, 'movementsApi'])->name('items.movements.api');
