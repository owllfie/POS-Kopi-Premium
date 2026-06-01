<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Models\Menu;

class Kategori extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'kategori',
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class, 'id_kategori', 'id_kategori');
    }
}
