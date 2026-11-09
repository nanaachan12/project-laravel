@extends('layouts.app')

@section('title', 'Daftar Barang - StockGudang')

@section('content')
<div x-data="{
    openCreateModal: {{ ($errors->has('kode') || $errors->has('nama') || $errors->has('satuan') || $errors->has('stock')) ? 'true' : 'false' }},
    satuanPreset(val) {
        document.getElementById('satuan_input').value = val;
    }
}">

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Barang Gudang</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola data barang inventaris dan pantau jumlah stok terkini.</p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                @click="openCreateModal = true" 
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition focus:ring-4 focus:ring-indigo-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Barang Baru</span>
            </button>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Jenis Barang</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ number_format($totalItems) }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Unit Tersedia</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ number_format($totalStock) }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Stok Kosong / Habis</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ number_format($outOfStock) }}</h3>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <!-- Search & Filter Bar -->
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
            <form action="{{ route('items.index') }}" method="GET" class="w-full sm:w-80 relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Cari kode, nama, satuan..." 
                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </form>

            @if(request('search'))
                <a href="{{ route('items.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    &larr; Reset Pencarian
                </a>
            @endif
        </div>

        <!-- Table Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">Kode Barang</th>
                        <th class="py-3.5 px-4 sm:px-6">Nama Barang</th>
                        <th class="py-3.5 px-4 sm:px-6">Satuan</th>
                        <th class="py-3.5 px-4 sm:px-6 text-center">Jumlah Stok</th>
                        <th class="py-3.5 px-4 sm:px-6 text-center">Mutasi</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-4 sm:px-6">
                                <span class="font-mono text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-md border border-slate-200">
                                    {{ $item->kode }}
                                </span>
                            </td>
                            <td class="py-4 px-4 sm:px-6 font-medium text-slate-900">
                                {{ $item->nama }}
                            </td>
                            <td class="py-4 px-4 sm:px-6 text-slate-600">
                                <span class="capitalize">{{ $item->satuan }}</span>
                            </td>
                            <td class="py-4 px-4 sm:px-6 text-center">
                                @if($item->stock > 10)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ number_format($item->stock) }} {{ $item->satuan }}
                                    </span>
                                @elseif($item->stock > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ number_format($item->stock) }} {{ $item->satuan }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        0 (Habis)
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 sm:px-6 text-center text-xs text-slate-500">
                                {{ $item->movements_count ?? 0 }} kali
                            </td>
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="text-xs text-slate-400 italic">Siap mutasi</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto">
                                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-base font-semibold text-slate-800">Belum Ada Data Barang</h4>
                                    <p class="text-xs text-slate-500 mt-1">
                                        @if(request('search'))
                                            Tidak ditemukan barang yang cocok dengan kata kunci "{{ request('search') }}".
                                        @else
                                            Mulai catat barang inventaris pertama Anda dengan menekan tombol di bawah.
                                        @endif
                                    </p>
                                    @if(!request('search'))
                                        <button 
                                            @click="openCreateModal = true" 
                                            class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-lg transition">
                                            + Tambah Barang Pertama
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($items->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah Barang (Alpine.js) -->
    <div 
        x-cloak 
        x-show="openCreateModal" 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop -->
        <div 
            x-show="openCreateModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="openCreateModal = false"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div 
                x-show="openCreateModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative bg-white rounded-2xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100">

                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900" id="modal-title">Tambah Barang Baru</h3>
                            <p class="text-xs text-slate-500">Lengkapi informasi barang untuk inventaris gudang.</p>
                        </div>
                    </div>
                    <button @click="openCreateModal = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Modal Form -->
                <form action="{{ route('items.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf

                    <!-- Kode Barang (Wajib & Unik) -->
                    <div>
                        <label for="kode" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Kode Barang <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="kode" 
                            id="kode"
                            value="{{ old('kode') }}"
                            placeholder="Contoh: BRG-001, ELK-102" 
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border @error('kode') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 uppercase font-mono">
                        @error('kode')
                            <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="text-[11px] text-slate-400 mt-1">Kode bersifat unik untuk setiap barang.</p>
                    </div>

                    <!-- Nama Barang (Wajib) -->
                    <div>
                        <label for="nama" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Nama Barang <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="nama" 
                            id="nama"
                            value="{{ old('nama') }}"
                            placeholder="Contoh: Kertas HVS A4 80gr" 
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border @error('nama') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        @error('nama')
                            <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Satuan Barang (Wajib) -->
                    <div>
                        <label for="satuan_input" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Satuan Barang <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="satuan" 
                            id="satuan_input"
                            value="{{ old('satuan') }}"
                            placeholder="Contoh: pcs, box, kg, rim, unit" 
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border @error('satuan') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        
                        <!-- Quick Presets -->
                        <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                            <span class="text-[11px] text-slate-400 mr-1">Preset cepat:</span>
                            @foreach(['pcs', 'box', 'rim', 'kg', 'unit', 'lusin'] as $preset)
                                <button 
                                    type="button" 
                                    @click="satuanPreset('{{ $preset }}')" 
                                    class="text-[11px] px-2 py-0.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 rounded-md border border-slate-200 transition">
                                    {{ $preset }}
                                </button>
                            @endforeach
                        </div>

                        @error('satuan')
                            <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Stok Awal (Opsional, Default 0) -->
                    <div>
                        <label for="stock" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Stok Awal (Opsional)
                        </label>
                        <input 
                            type="number" 
                            name="stock" 
                            id="stock"
                            value="{{ old('stock', 0) }}"
                            min="0"
                            placeholder="0" 
                            class="w-full px-3.5 py-2.5 rounded-xl border @error('stock') border-rose-400 bg-rose-50/30 @else border-slate-200 bg-slate-50/50 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        @error('stock')
                            <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="text-[11px] text-slate-400 mt-1">Default 0. Mutasi barang dapat ditambahkan setelah barang tersimpan.</p>
                    </div>

                    <!-- Form Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button 
                            type="button" 
                            @click="openCreateModal = false" 
                            class="px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm hover:shadow transition focus:ring-4 focus:ring-indigo-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Barang
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
