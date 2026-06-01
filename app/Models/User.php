<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;
use App\Models\Shift;
use App\Models\Pesanan;
use App\Models\ActivityLog;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'username',
        'email',
        'password',
        'id_role',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'id_user', 'id_user');
    }

    public function pesanans()
    {
        return $this->hasMany(Pesanan::class, 'id_user', 'id_user');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'id_user', 'id_user');
    }

    // Helper to check access for a module
    public function canAccess($module)
    {
        if ($this->role->role === 'superadmin') {
            return true; // Superadmin has access to everything
        }
        
        $access = Aksess::where('id_role', $this->id_role)
            ->where('modul', $module)
            ->first();

        return $access && $access->allowed === '1';
    }
}
