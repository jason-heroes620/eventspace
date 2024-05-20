<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsProductsDiscounts extends Model
{
    use HasFactory;

    protected $table = 'events_products_discounts';

    const CREATED_AT = 'created';
    const UPDATED_AT = null;
}
