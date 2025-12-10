<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPaymentReference extends Model
{
    protected $table = 'event_payment_reference';
    protected $fillable = [
        'application_code',
        'reference_no',
        'payment_reference',
        'payment_amount',
        'bank',
        'account_name',
        'account_no',
        'bank_id'
    ];
}
