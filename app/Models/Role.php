<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Aksess;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'id_role';
    public $timestamps = false; // Static system config, no timestamps in schema

    protected $fillable = [
        'role',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_role', 'id_role');
    }

    public function accesses()
    {
        return $this->hasMany(Aksess::class, 'id_role', 'id_role');
    }
}
