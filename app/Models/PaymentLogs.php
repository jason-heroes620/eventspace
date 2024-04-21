<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLogs extends Model
{
    use HasFactory;

        protected $table = 'payment_logs';

    const CREATED_AT = 'created';
    const UPDATED_AT = null;
}
