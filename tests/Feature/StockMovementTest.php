<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Fitur 2 & 3: Pencatatan barang masuk menambah stok otomatis.
     */
    public function test_barang_masuk_increases_stock_automatically(): void
    {
        $item = Item::create([
            'kode' => 'BRG-MSK-01',
            'nama' => 'Kertas A4',
            'satuan' => 'rim',
            'stock' => 10,
        ]);

        $response = $this->post('/movements', [
            'item_id' => $item->id,
            'jenis' => 'masuk',
            'jumlah' => 15,
            'tanggal' => now()->toDateString(),
            'keterangan' => 'Pengadaan stok baru',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        // Pastikan mutasi tercatat
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'jenis' => 'masuk',
            'jumlah' => 15,
            'keterangan' => 'Pengadaan stok baru',
        ]);

        // Fitur 3: Pastikan stok berubah otomatis: 10 + 15 = 25
        $item->refresh();
        $this->assertEquals(25, $item->stock);
    }

    /**
     * Fitur 2 & 3: Pencatatan barang keluar mengurangi stok otomatis jika stok mencukupi.
     */
    public function test_barang_keluar_decreases_stock_when_sufficient(): void
    {
        $item = Item::create([
            'kode' => 'BRG-KLR-01',
            'nama' => 'Spidol Boardmarker',
            'satuan' => 'pcs',
            'stock' => 20,
        ]);

        $response = $this->post('/movements', [
            'item_id' => $item->id,
            'jenis' => 'keluar',
            'jumlah' => 8,
            'tanggal' => now()->toDateString(),
            'keterangan' => 'Distribusi ke Divisi IT',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        // Pastikan mutasi tercatat
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'jenis' => 'keluar',
            'jumlah' => 8,
        ]);

        // Fitur 3: Pastikan stok berubah otomatis: 20 - 8 = 12
        $item->refresh();
        $this->assertEquals(12, $item->stock);
    }

    /**
     * Fitur 4: Barang keluar tidak boleh melebihi stock yang tersedia.
     * Sistem harus menolak transaksi tersebut, dan stok tidak boleh berubah.
     */
    public function test_barang_keluar_rejected_and_stock_unchanged_when_exceeding_stock(): void
    {
        $item = Item::create([
            'kode' => 'BRG-REJECT-01',
            'nama' => 'Printer Ink Black',
            'satuan' => 'botol',
            'stock' => 5, // Stok saat ini hanya 5
        ]);

        // Coba keluarkan 10 botol (melebihi stok 5)
        $response = $this->post('/movements', [
            'item_id' => $item->id,
            'jenis' => 'keluar',
            'jumlah' => 10,
            'tanggal' => now()->toDateString(),
            'keterangan' => 'Pengeluaran melebihi batas',
        ]);

        // Sistem menolak transaksi dan memberikan error validasi
        $response->assertSessionHasErrors('jumlah');

        // Pastikan mutasi TIDAK dicatat
        $this->assertDatabaseMissing('stock_movements', [
            'item_id' => $item->id,
            'jenis' => 'keluar',
            'jumlah' => 10,
        ]);

        // Pastikan stok sama sekali tidak berubah (tetap 5)
        $item->refresh();
        $this->assertEquals(5, $item->stock);
    }

    /**
     * Fitur 4: Validasi mutasi saat stok bernilai 0.
     */
    public function test_barang_keluar_rejected_when_stock_is_zero(): void
    {
        $item = Item::create([
            'kode' => 'BRG-ZERO-01',
            'nama' => 'Flashdisk 32GB',
            'satuan' => 'pcs',
            'stock' => 0,
        ]);

        $response = $this->post('/movements', [
            'item_id' => $item->id,
            'jenis' => 'keluar',
            'jumlah' => 1,
            'tanggal' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('jumlah');

        $item->refresh();
        $this->assertEquals(0, $item->stock);
    }

    /**
     * Fitur 5: Riwayat mutasi per barang dapat diakses pada halaman detail dan API.
     */
    public function test_can_view_item_mutation_history(): void
    {
        $item = Item::create([
            'kode' => 'BRG-HIST-01',
            'nama' => 'Stapler Besar',
            'satuan' => 'unit',
            'stock' => 10,
        ]);

        StockMovement::create([
            'item_id' => $item->id,
            'jenis' => 'masuk',
            'jumlah' => 10,
            'keterangan' => 'Stok awal',
            'tanggal' => now()->subDays(2)->toDateString(),
        ]);

        StockMovement::create([
            'item_id' => $item->id,
            'jenis' => 'keluar',
            'jumlah' => 3,
            'keterangan' => 'Dipinjam HRD',
            'tanggal' => now()->subDay()->toDateString(),
        ]);

        // Test halaman detail (Fitur 5)
        $detailResponse = $this->get("/items/{$item->id}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Riwayat Mutasi Barang');
        $detailResponse->assertSee('Stok awal');
        $detailResponse->assertSee('Dipinjam HRD');

        // Test API riwayat mutasi untuk modal Alpine.js (Fitur 5)
        $apiResponse = $this->getJson("/items/{$item->id}/movements-api");
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonStructure([
            'item' => ['id', 'kode', 'nama', 'satuan', 'stock'],
            'movements' => [
                '*' => ['id', 'jenis', 'jumlah', 'keterangan', 'tanggal', 'created_at']
            ],
            'total_masuk',
            'total_keluar',
        ]);
        $apiResponse->assertJsonFragment(['jumlah' => 10, 'jenis' => 'masuk']);
        $apiResponse->assertJsonFragment(['jumlah' => 3, 'jenis' => 'keluar']);
    }
}

