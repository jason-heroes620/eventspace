<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentEntryError extends Model
{
    use HasFactory;

    protected $table = 'payment_entry_error';

    const CREATED_AT = 'created';
    const UPDATED_AT = null;
}
