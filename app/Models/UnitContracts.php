<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class UnitContracts extends Model implements AuditableContract
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

    public function tenant()
    {
        return $this->hasOne(Tenants::class, 'id', 'tenant_id');
    }

    public function unit()
    {
        return $this->hasOne(Unit::class, 'id', 'unit_id');
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
