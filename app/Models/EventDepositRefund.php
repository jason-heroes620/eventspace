<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventDepositRefund extends Model
{
    protected $table = 'event_deposit_refund';
    protected $primaryKey = 'event_deposit_refund_id';
    protected $fillable = [
        'application_code',
        'refund_file',
        'refund_amount',
        'refund_received',
    ];

    public $casts = [
        'refund_received' => 'boolean'
    ];
}
