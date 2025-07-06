<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class InvoiceLine extends Model implements AuditableContract
{
    use SoftDeletes, Auditable, HasAuditFields;

    protected $fillable = [
        'invoice_id',
        'unit_payment_schedule_id',
        'unit_billing_type_id',
        'description',
        'amount',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function unitPaymentSchedule()
    {
        return $this->belongsTo(UnitPaymentSchedules::class);
    }
}
