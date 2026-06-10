<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class SlipGaji extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'slip_gaji';
    protected $primaryKey = 'id_slip';

    protected $fillable = [
        'id_karyawan',
        'bulan',
        'tahun',
        'gaji_pokok',
        'tunjangan',
        'potongan',
        'total_gaji',
        'catatan',
        'tanggal_pembayaran',
        'metode_pembayaran',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'gaji_pokok' => 'integer',
        'tunjangan' => 'integer',
        'potongan' => 'integer',
        'total_gaji' => 'integer',
        'tanggal_pembayaran' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }
}
