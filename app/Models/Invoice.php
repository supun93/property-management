<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Invoice extends Model implements AuditableContract
{
    use SoftDeletes, Auditable, HasAuditFields;

    protected $fillable = [
        'created_by',
        'updated_by',
        'deleted_by', 
        'name', 'total_amount', 'payment_date', 'tenant_id', 'contract_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function contract()
    {
        return $this->hasOne(UnitContracts::class, 'id', 'contract_id');
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

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class, 'invoice_id', 'id');
    }

    public function slips()
    {
        return $this->hasMany(PaymentSlip::class, 'invoice_id', 'id');
    }

    public function schedules()
    {
        return $this->hasMany(UnitPaymentSchedules::class, 'invoice_id', 'id');
    }

    
}
