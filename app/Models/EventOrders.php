<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventOrders extends Model
{
    use HasFactory;

    protected $table = "event_orders";

    const CREATED_AT = 'created';
    const UPDATED_AT = null;
}
