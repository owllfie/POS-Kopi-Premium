<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoryUpdate extends Model
{
    use SoftDeletes;

    protected $table = 'history_update';
    protected $primaryKey = 'id_update';

    protected $fillable = [
        'table',
        'record_id',
        'data_lama',
        'data_baru',
    ];
}
