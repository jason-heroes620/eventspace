<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventOrderPaymentHistory extends Model
{
    use HasFactory;

    protected $table = 'event_order_payment_history';

    const CREATED_AT = 'created';
    const UPDATED_AT = null;
}
