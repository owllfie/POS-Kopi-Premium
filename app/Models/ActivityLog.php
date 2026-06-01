<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class ActivityLog extends Model
{
    use SoftDeletes;

    protected $table = 'activity_log';
    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_user',
        'aktivitas',
        'detail_aktivitas',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user')->withTrashed();
    }
}
