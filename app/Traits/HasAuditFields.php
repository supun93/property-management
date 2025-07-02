<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAuditFields
{
    public static function bootHasAuditFields()
    {
        static::creating(function ($model) {
            $userId = Auth::id();
            $model->created_by = $userId;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });

        static::deleting(function ($model) {
            $model->deleted_by = Auth::id();
            $model->save();
        });

        static::restoring(function ($model) {
            $model->updated_by = Auth::id();
        });
    }
}
