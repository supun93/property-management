<?php

namespace App\Helpers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditAspect
{
    /**
     * Log audit information
     *
     * @param string $action      The type of action (create, update, delete, restore)
     * @param mixed  $model       The model instance
     * @param array|null $oldData Previous data (for update/delete)
     * @param array|null $newData Current data (for create/update)
     */
    public static function log(string $action, $model, array $oldData = null, array $newData = null): void
    {
        try {
            AuditLog::create([
                'model'     => class_basename($model),     // E.g. 'Property'
                'model_id'  => $model->id ?? null,
                'action'    => $action,
                'old_data'  => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
                'new_data'  => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
                'user_id'   => Auth::check() ? Auth::id() : null,
            ]);
        } catch (\Exception $e) {
            // Optional: log this somewhere if needed
            // \Log::error("Audit logging failed: " . $e->getMessage());
        }
    }
}
