<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Models\Kategori;

class Menu extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'menu';
    protected $primaryKey = 'id_menu';

    protected $fillable = [
        'nama_menu',
        'id_kategori',
        'foto',
        'harga',
        'status',
        'paket_makanan',
        'paket_minuman',
        'paket_addons',
        'kode_menu',
        'deskripsi',
    ];

    protected $casts = [
        'paket_makanan' => 'array',
        'paket_minuman' => 'array',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function getPaketMakananNames()
    {
        if (empty($this->paket_makanan)) return [];
        
        $isAssociative = array_keys($this->paket_makanan) !== range(0, count($this->paket_makanan) - 1);
        
        if ($isAssociative) {
            $ids = array_keys($this->paket_makanan);
            $menus = self::whereIn('id_menu', $ids)->get()->keyBy('id_menu');
            $result = [];
            foreach ($this->paket_makanan as $id => $qty) {
                if (isset($menus[$id])) {
                    $result[] = $menus[$id]->nama_menu . " ({$qty}x)";
                }
            }
            return $result;
        } else {
            return self::whereIn('id_menu', $this->paket_makanan)->pluck('nama_menu')->toArray();
        }
    }

    public function getPaketMinumanNames()
    {
        if (empty($this->paket_minuman)) return [];
        
        $isAssociative = array_keys($this->paket_minuman) !== range(0, count($this->paket_minuman) - 1);
        
        if ($isAssociative) {
            $ids = array_keys($this->paket_minuman);
            $menus = self::whereIn('id_menu', $ids)->get()->keyBy('id_menu');
            $result = [];
            foreach ($this->paket_minuman as $id => $qty) {
                if (isset($menus[$id])) {
                    $result[] = $menus[$id]->nama_menu . " ({$qty}x)";
                }
            }
            return $result;
        } else {
            return self::whereIn('id_menu', $this->paket_minuman)->pluck('nama_menu')->toArray();
        }
    }
}
