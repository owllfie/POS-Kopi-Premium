<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Models\HistoryUpdate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            self::logActivity('CREATE_' . strtoupper(class_basename($model)), 'Created new record in ' . $model->getTable() . ' with ID ' . $model->getKey());
        });

        static::updating(function ($model) {
            $dirty = $model->getDirty();
            $original = [];
            $new = [];
            foreach ($dirty as $key => $value) {
                if ($key !== 'updated_at') {
                    $original[$key] = $model->getOriginal($key);
                    $new[$key] = $value;
                }
            }

            if (!empty($new)) {
                HistoryUpdate::create([
                    'table' => $model->getTable(),
                    'record_id' => $model->getKey(),
                    'data_lama' => json_encode($original),
                    'data_baru' => json_encode($new),
                ]);
                
                self::logActivity('UPDATE_' . strtoupper(class_basename($model)), 'Updated record in ' . $model->getTable() . ' with ID ' . $model->getKey());
            }
        });

        static::deleted(function ($model) {
            $activity = method_exists($model, 'isForceDeleting') && $model->isForceDeleting() ? 'PERMANENT_DELETE_' : 'DELETE_';
            self::logActivity($activity . strtoupper(class_basename($model)), 'Deleted record in ' . $model->getTable() . ' with ID ' . $model->getKey());
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                self::logActivity('RESTORE_' . strtoupper(class_basename($model)), 'Restored record in ' . $model->getTable() . ' with ID ' . $model->getKey());
            });
        }
    }

    public static function logActivity($aktivitas, $detail)
    {
        $userId = null;
        if (Auth::check()) {
            $userId = Auth::user()->id_user;
        } elseif (session()->has('simulated_user_id')) {
            $userId = session('simulated_user_id');
        }
        
        ActivityLog::create([
            'id_user' => $userId,
            'aktivitas' => $aktivitas,
            'detail_aktivitas' => $detail,
            'ip_address' => Request::ip(),
        ]);
    }
}
