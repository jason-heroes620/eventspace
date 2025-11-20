<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

class EventDeposit extends Model
{
    protected $table = 'event_deposit';
    protected $primaryKey = 'event_deposit_id';
    protected $fillable = [
        'event_deposit',
        'start_date',
        'end_date',
        'event_deposit_status',
    ];

    protected $casts = [
        'event_deposit_status' => 'boolean'
    ];
}
