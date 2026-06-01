<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Models\User;

class Shift extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'shift';
    protected $primaryKey = 'id_shift';

    protected $fillable = [
        'id_user',
        'jam_mulai',
        'jam_selesai',
        'cash_masuk',
        'qris_masuk',
        'total_masuk',
    ];

    protected $casts = [
        'jam_mulai' => 'datetime',
        'jam_selesai' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
