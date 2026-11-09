@extends('layouts.app')

@section('title', 'Detail & Riwayat Mutasi: ' . $item->nama . ' - StockGudang')

@section('content')
<div x-data="{
    openMovementModal: false,
    movementJenis: 'masuk',
    jumlahInput: 1,
    itemStock: {{ $item->stock }},
    openModal(jenis) {
        this.movementJenis = jenis;
        this.jumlahInput = 1;
        this.openMovementModal = true;
    }
}">

    <!-- Breadcrumb & Back -->
    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('items.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Barang
        </a>
        <div class="flex items-center gap-2">
            <button 
                @click="openModal('masuk')" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Catat Masuk
            </button>
            <button 
                @click="openModal('keluar')" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                - Catat Keluar
            </button>
        </div>
    </div>

    <!-- Item Header Card -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-sm mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-mono text-xs font-bold px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-200">
                        {{ $item->kode }}
                    </span>
                    <span class="text-xs text-slate-400">&bull; Satuan: <strong class="text-slate-700 uppercase">{{ $item->satuan }}</strong></span>
                </div>
                <h2 class="text-2xl font-extrabold text-slate-900">{{ $item->nama }}</h2>
                <p class="text-xs text-slate-400 mt-1">Didaftarkan pada: {{ $item->created_at->format('d M Y, H:i') }}</p>
            </div>

            <!-- Current Stock Badge -->
            <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div class="text-right">
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Stok Tersedia Saat Ini</p>
                    <p class="text-3xl font-extrabold @if($item->stock > 10) text-emerald-600 @elseif($item->stock > 0) text-amber-600 @else text-rose-600 @endif">
                        {{ number_format($item->stock) }} <span class="text-sm font-medium text-slate-500">{{ $item->satuan }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Summary Mini Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 pt-6 border-t border-slate-100 text-center sm:text-left">
            <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-3.5">
                <span class="text-xs text-emerald-700 font-medium">Total Akumulasi Masuk</span>
                <p class="text-xl font-bold text-emerald-800 mt-0.5">+{{ number_format($totalMasuk) }} {{ $item->satuan }}</p>
            </div>
            <div class="bg-rose-50/50 border border-rose-100 rounded-xl p-3.5">
                <span class="text-xs text-rose-700 font-medium">Total Akumulasi Keluar</span>
                <p class="text-xl font-bold text-rose-800 mt-0.5">-{{ number_format($totalKeluar) }} {{ $item->satuan }}</p>
            </div>
            <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-3.5">
                <span class="text-xs text-indigo-700 font-medium">Total Transaksi Mutasi</span>
                <p class="text-xl font-bold text-indigo-800 mt-0.5">{{ number_format($item->movements->count()) }} Kali</p>
            </div>
        </div>
    </div>

    <!-- Riwayat Mutasi Table Card (Fitur 5) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Riwayat Mutasi Barang</h3>
                <p class="text-xs text-slate-500">Catatan kronologis seluruh arus masuk dan keluar barang ini.</p>
            </div>
            <span class="text-xs bg-slate-100 text-slate-600 font-medium px-2.5 py-1 rounded-full">
                {{ $item->movements->count() }} Transaksi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold uppercase text-slate-500 tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">Tanggal</th>
                        <th class="py-3.5 px-4 sm:px-6 text-center">Jenis Mutasi</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Jumlah</th>
                        <th class="py-3.5 px-4 sm:px-6">Keterangan</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Dicatat Pada</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($item->movements as $movement)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-4 sm:px-6 font-medium text-slate-800 whitespace-nowrap">
                                {{ $movement->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-4 px-4 sm:px-6 text-center whitespace-nowrap">
                                @if($movement->jenis === 'masuk')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                                        Barang Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <svg class="w-3 h-3 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                                        Barang Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 sm:px-6 text-right font-mono font-bold whitespace-nowrap @if($movement->jenis === 'masuk') text-emerald-600 @else text-rose-600 @endif">
                                {{ $movement->jenis === 'masuk' ? '+' : '-' }}{{ number_format($movement->jumlah) }} {{ $item->satuan }}
                            </td>
                            <td class="py-4 px-4 sm:px-6 text-slate-600">
                                {{ $movement->keterangan ?: '-' }}
                            </td>
                            <td class="py-4 px-4 sm:px-6 text-right text-xs text-slate-400 whitespace-nowrap">
                                {{ $movement->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto text-center text-slate-400">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-700">Belum Ada Mutasi</p>
                                    <p class="text-xs text-slate-400 mt-1">Barang ini belum pernah mengalami transaksi masuk atau keluar.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Mutasi (Alpine.js) -->
    <div 
        x-cloak 
        x-show="openMovementModal" 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-movement-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop -->
        <div 
            x-show="openMovementModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="openMovementModal = false"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div 
                x-show="openMovementModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative bg-white rounded-2xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100">

                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div 
                            :class="movementJenis === 'masuk' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'"
                            class="w-10 h-10 rounded-xl flex items-center justify-center font-bold">
                            <template x-if="movementJenis === 'masuk'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </template>
                            <template x-if="movementJenis === 'keluar'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            </template>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900" id="modal-movement-title">
                                Catat Mutasi <span x-text="movementJenis === 'masuk' ? 'Barang Masuk' : 'Barang Keluar'"></span>
                            </h3>
                            <p class="text-xs text-slate-500">
                                {{ $item->nama }} ({{ $item->kode }}) &bull; Stok saat ini: <strong>{{ $item->stock }} {{ $item->satuan }}</strong>
                            </p>
                        </div>
                    </div>
                    <button @click="openMovementModal = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Form -->
                <form action="{{ route('movements.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                    <input type="hidden" name="jenis" :value="movementJenis">

                    <!-- Peringatan Jika Stok Keluar Melebihi Stok -->
                    <div 
                        x-show="movementJenis === 'keluar' && jumlahInput > itemStock" 
                        class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 text-rose-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>
                            <strong>Peringatan Validasi:</strong> Jumlah barang keluar tidak boleh melebihi stok yang tersedia ({{ $item->stock }} {{ $item->satuan }}). Transaksi akan ditolak oleh sistem!
                        </span>
                    </div>

                    <!-- Tanggal Mutasi -->
                    <div>
                        <label for="tanggal_detail" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Tanggal Mutasi <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            name="tanggal" 
                            id="tanggal_detail"
                            value="{{ date('Y-m-d') }}" 
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>

                    <!-- Jumlah Mutasi -->
                    <div>
                        <label for="jumlah_detail" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Jumlah ({{ $item->satuan }}) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="jumlah" 
                            id="jumlah_detail"
                            x-model.number="jumlahInput"
                            min="1" 
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <p class="text-[11px] text-slate-400 mt-1">
                            <span x-show="movementJenis === 'masuk'">Stok akan otomatis bertambah sebesar jumlah ini.</span>
                            <span x-show="movementJenis === 'keluar'">Stok akan otomatis berkurang sebesar jumlah ini (maksimal: {{ $item->stock }} {{ $item->satuan }}).</span>
                        </p>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label for="keterangan_detail" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Keterangan (Opsional)
                        </label>
                        <textarea 
                            name="keterangan" 
                            id="keterangan_detail"
                            rows="2"
                            placeholder="Contoh: Penerimaan supplier / Kirim ke cabang / Barang rusak"
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                    </div>

                    <!-- Form Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button 
                            type="button" 
                            @click="openMovementModal = false" 
                            class="px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="movementJenis === 'keluar' && (itemStock <= 0 || jumlahInput > itemStock)"
                            :class="movementJenis === 'masuk' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed'"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm hover:shadow transition focus:ring-4">
                            <span x-text="movementJenis === 'masuk' ? 'Simpan Barang Masuk' : 'Simpan Barang Keluar'"></span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
