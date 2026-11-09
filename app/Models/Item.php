<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'stock',
    ];

    protected $casts = [
        'stock' => 'integer',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'item_id')->orderBy('tanggal', 'desc')->orderBy('id', 'desc');
    }
}

