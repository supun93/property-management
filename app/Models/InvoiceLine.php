<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceLine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'unit_payment_schedule_id',
        'unit_billing_type_id',
        'description',
        'amount',
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
