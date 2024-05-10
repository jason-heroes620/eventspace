<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventOrderTotals extends Model
{
    use HasFactory;

    protected $table = "event_order_totals";
    public $timestamps = false;
}
