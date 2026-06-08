<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Models\Pesanan;
use App\Models\Menu;
use App\Models\Meja;

class DetailPesanan extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'detail_pesanan';
    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'id_pesanan',
        'id_menu',
        'jumlah',
        'harga_satuan',
        'subtotal',
        'catatan',
        'status',
        'id_meja_temp',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu', 'id_menu')->withTrashed();
    }

    public function mejaTemp()
    {
        return $this->belongsTo(Meja::class, 'id_meja_temp', 'id_meja');
    }
}
