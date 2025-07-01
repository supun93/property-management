<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;

class Property extends Model implements AuditableContract
{
    use SoftDeletes, Auditable;
}
