<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPayments extends Model
{
    use HasFactory;
    // public $timestamps = false;

    protected $table = 'event_payments';
    protected $primaryKey = "id";

    protected $fillable = [
        'application_id',
        'payment_total',
        'reference_no',
        'payment_reference',
        'status',
        'application_code',
    ];

    const CREATED_AT = 'created';
    const UPDATED_AT = null;
}
