<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;

class Property extends Model implements AuditableContract
{
    use SoftDeletes, Auditable, HasAuditFields;

    protected $fillable = [
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function category()
    {
        return $this->hasOne(PropertyCategory::class, 'id', 'category_id');
    }
    public function owner()
    {
        return $this->hasOne(User::class, 'id', 'owner_id');
    }
    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
    public function updatedBy()
    {
        return $this->hasOne(User::class, 'id', 'updated_by');
    }
    public function deletedBy()
    {
        return $this->hasOne(User::class, 'id', 'deleted_by');
    }
}
