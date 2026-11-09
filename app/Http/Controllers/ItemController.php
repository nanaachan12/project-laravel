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
     * Menyimpan barang baru dengan validasi (Fitur 1).
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

    /**
     * Halaman detail barang beserta riwayat mutasi (Fitur 5).
     */
    public function show(Item $item)
    {
        $item->load(['movements' => function ($q) {
            $q->orderBy('tanggal', 'desc')->orderBy('id', 'desc');
        }]);

        // Hitung total masuk dan total keluar
        $totalMasuk = $item->movements->where('jenis', 'masuk')->sum('jumlah');
        $totalKeluar = $item->movements->where('jenis', 'keluar')->sum('jumlah');

        return view('items.show', compact('item', 'totalMasuk', 'totalKeluar'));
    }

    /**
     * Endpoint API JSON untuk mengambil riwayat mutasi barang (untuk modal Alpine.js di halaman daftar barang - Fitur 5).
     */
    public function movementsApi(Item $item)
    {
        $movements = $item->movements()->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        return response()->json([
            'item' => [
                'id' => $item->id,
                'kode' => $item->kode,
                'nama' => $item->nama,
                'satuan' => $item->satuan,
                'stock' => $item->stock,
            ],
            'movements' => $movements->map(function ($m) {
                return [
                    'id' => $m->id,
                    'jenis' => $m->jenis,
                    'jumlah' => $m->jumlah,
                    'keterangan' => $m->keterangan ?? '-',
                    'tanggal' => $m->tanggal->format('d/m/Y'),
                    'created_at' => $m->created_at->format('d/m/Y H:i'),
                ];
            }),
            'total_masuk' => $movements->where('jenis', 'masuk')->sum('jumlah'),
            'total_keluar' => $movements->where('jenis', 'keluar')->sum('jumlah'),
        ]);
    }
}
