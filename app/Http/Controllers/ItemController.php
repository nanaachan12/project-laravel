<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Menampilkan daftar barang.
     */
    public function index(Request $request)
    {
        $query = Item::query()->withCount('movements');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('satuan', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Statistik ringkas
        $totalItems = Item::count();
        $totalStock = Item::sum('stock');
        $outOfStock = Item::where('stock', '<=', 0)->count();

        return view('items.index', compact('items', 'totalItems', 'totalStock', 'outOfStock'));
    }

    /**
     * Menyimpan barang baru dengan validasi.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:items,kode',
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'stock' => 'nullable|integer|min:0',
        ], [
            'kode.required' => 'Kode barang wajib diisi.',
            'kode.unique' => 'Kode barang sudah digunakan. Silakan gunakan kode unik yang lain.',
            'nama.required' => 'Nama barang wajib diisi.',
            'satuan.required' => 'Satuan barang wajib diisi.',
            'stock.integer' => 'Stok harus berupa bilangan bulat.',
            'stock.min' => 'Stok awal tidak boleh bernilai negatif.',
        ]);

        $validated['stock'] = $validated['stock'] ?? 0;

        Item::create($validated);

        return redirect()->route('items.index')->with('success', "Barang '{$validated['nama']}' ({$validated['kode']}) berhasil ditambahkan!");
    }
}
