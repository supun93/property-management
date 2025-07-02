<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Payments extends Model implements AuditableContract
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

    public function contract()
    {
        return $this->hasOne(Contract::class, 'id', 'contract_id');
    }

    public function paymentType()
    {
        return $this->hasOne(PaymentTypes::class, 'id', 'payment_type_id');
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
