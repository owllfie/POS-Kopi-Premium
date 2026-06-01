<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BahanAlat extends Model
{
    use SoftDeletes;

    protected $table = 'bahan_alat';
    protected $primaryKey = 'id_item';

    protected $fillable = [
        'nama_item',
        'tipe',
        'kategori',
        'stok',
        'satuan',
        'harga_estimasi',
        'keterangan',
    ];

    protected $casts = [
        'stok' => 'float',
        'harga_estimasi' => 'integer',
    ];
}
