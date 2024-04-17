<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCategories extends Model
{
    use HasFactory;

    protected $table = 'payment_categories';

    const CREATED_AT = 'created';
    const UPDATED_AT = null;
}
