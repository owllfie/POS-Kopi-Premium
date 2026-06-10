<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Promo extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'promo';
    protected $primaryKey = 'id_promo';

    protected $fillable = [
        'nama_promo',
        'deskripsi',
        'start_time',
        'end_time',
        'status',
        'nominal_potongan',
        'menu_ids',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'menu_ids' => 'array',
    ];

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'id_promo', 'id_promo');
    }
}
