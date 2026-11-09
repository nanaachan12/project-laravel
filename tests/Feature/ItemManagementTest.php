<?php

namespace Tests\Feature;

use App\Models\Item;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ItemManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_display_items_index_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Daftar Barang Gudang');
    }

    public function test_can_create_new_item_with_valid_data(): void
    {
        $response = $this->post('/items', [
            'kode' => 'BRG-001',
            'nama' => 'Buku Tulis Sinar Dunia',
            'satuan' => 'pcs',
            'stock' => 50,
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('items', [
            'kode' => 'BRG-001',
            'nama' => 'Buku Tulis Sinar Dunia',
            'satuan' => 'pcs',
            'stock' => 50,
        ]);
    }

    public function test_validation_fails_when_required_fields_are_missing(): void
    {
        $response = $this->post('/items', [
            'kode' => '',
            'nama' => '',
            'satuan' => '',
        ]);

        $response->assertSessionHasErrors(['kode', 'nama', 'satuan']);
    }

    public function test_validation_fails_when_kode_is_not_unique(): void
    {
        Item::create([
            'kode' => 'BRG-001',
            'nama' => 'Barang Lama',
            'satuan' => 'box',
            'stock' => 10,
        ]);

        $response = $this->post('/items', [
            'kode' => 'BRG-001',
            'nama' => 'Barang Baru',
            'satuan' => 'pcs',
            'stock' => 5,
        ]);

        $response->assertSessionHasErrors('kode');
    }
}
