<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementController extends Controller
{
    /**
     * Mencatat mutasi barang masuk atau keluar.
     * Menggunakan DB Transaction untuk memastikan integritas data stok (Fitur 3 & 4).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'jenis' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'item_id.required' => 'Barang harus dipilih.',
            'item_id.exists' => 'Data barang tidak ditemukan dalam sistem.',
            'jenis.required' => 'Jenis mutasi harus ditentukan (masuk/keluar).',
            'jenis.in' => 'Jenis mutasi hanya boleh masuk atau keluar.',
            'jumlah.required' => 'Jumlah mutasi wajib diisi.',
            'jumlah.integer' => 'Jumlah mutasi harus berupa bilangan bulat.',
            'jumlah.min' => 'Jumlah mutasi minimal bernilai 1.',
            'tanggal.required' => 'Tanggal mutasi wajib diisi.',
            'tanggal.date' => 'Format tanggal mutasi tidak valid.',
        ]);

        try {
            DB::transaction(function () use ($validated, &$itemName, &$itemSatuan) {
                // Kunci baris barang untuk mencegah race condition (pessimistic locking)
                $item = Item::where('id', $validated['item_id'])->lockForUpdate()->firstOrFail();
                $itemName = $item->nama;
                $itemSatuan = $item->satuan;

                // Fitur 4: Validasi stok tidak boleh melebihi stok yang tersedia untuk barang keluar
                if ($validated['jenis'] === 'keluar' && $item->stock < $validated['jumlah']) {
                    throw ValidationException::withMessages([
                        'jumlah' => "Transaksi Ditolak! Jumlah barang keluar ({$validated['jumlah']} {$item->satuan}) melebihi stok yang tersedia saat ini ({$item->stock} {$item->satuan}). Stok tidak berubah."
                    ]);
                }

                // Fitur 3: Stok barang berubah otomatis setiap ada mutasi
                if ($validated['jenis'] === 'masuk') {
                    $item->increment('stock', $validated['jumlah']);
                } else {
                    $item->decrement('stock', $validated['jumlah']);
                }

                // Catat mutasi ke database
                StockMovement::create($validated);
            });

            $jenisText = $validated['jenis'] === 'masuk' ? 'Masuk (+)' : 'Keluar (-)';
            return redirect()->route('items.index')->with(
                'success',
                "Mutasi Barang {$jenisText} sebanyak {$validated['jumlah']} {$itemSatuan} untuk '{$itemName}' berhasil dicatat dan stok telah diperbarui."
            );

        } catch (ValidationException $e) {
            // Re-throw validasi agar pesan error tampil ke user
            throw $e;
        } catch (\Exception $e) {
            return redirect()->route('items.index')->with(
                'error',
                'Terjadi kesalahan saat memproses transaksi: ' . $e->getMessage()
            );
        }
    }
}
