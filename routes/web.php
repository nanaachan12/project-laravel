<?php

use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

// Halaman utama: Daftar Barang & Form Tambah Barang (Fitur 1)
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::post('/items', [ItemController::class, 'store'])->name('items.store');
