<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Karyawan extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'karyawan';
    protected $primaryKey = 'id_karyawan';

    protected $fillable = [
        'nama_karyawan',
        'id_jabatan',
        'pekerjaan',
        'gaji',
        'foto',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }
}
