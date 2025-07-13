<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentSlip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'tenant_id',
        'file_path',
        'remarks',
        'status',
        'approved_by',
        'approved_at',
    ];

    public function schedule()
    {
        return $this->belongsTo(UnitPaymentSchedules::class, 'unit_payment_schedule_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenants::class, 'tenant_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

