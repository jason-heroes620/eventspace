<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventOrderProducts extends Model
{
    use HasFactory;

    protected $table = 'event_order_products';
    public $timestamps = false;
}
