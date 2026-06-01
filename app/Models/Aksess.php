<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Models\Role;

class Aksess extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'aksess';
    protected $primaryKey = 'id_akses';

    protected $fillable = [
        'id_role',
        'modul',
        'allowed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }
}
