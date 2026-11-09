<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'jenis',
        'jumlah',
        'keterangan',
        'tanggal',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'tanggal' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}

