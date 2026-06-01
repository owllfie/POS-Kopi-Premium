<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Models\Pesanan;

class Meja extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'meja';
    protected $primaryKey = 'id_meja';

    protected $fillable = [
        'nomor_meja',
        'qrcode_token',
        'status',
    ];

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'id_meja', 'id_meja');
    }
}
