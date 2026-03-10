<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::logAudit('created', $model, null, $model->toArray());
        });

        static::updated(function ($model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getChanges();
            unset($newValues['updated_at']);
            if (!empty($newValues)) {
                self::logAudit('updated', $model, $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            self::logAudit('deleted', $model, $model->toArray(), null);
        });
    }

    protected static function logAudit(string $action, $model, ?array $oldValues, ?array $newValues): void
    {
        $user = Auth::user();
        $modelName = class_basename($model);

        $descriptions = [
            'created' => "{$modelName} record was created",
            'updated' => "{$modelName} record was updated",
            'deleted' => "{$modelName} record was deleted",
        ];

        AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'user_role' => $user ? ($user->roles->first()?->name ?? 'N/A') : 'System',
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'description' => $descriptions[$action] ?? "{$modelName} was {$action}",
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
