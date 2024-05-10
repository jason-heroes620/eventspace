<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Events extends Model
{
    use HasFactory;

    const CREATED_AT = 'created';
    const UPDATED_AT = null;

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Products::class)->using(EventProducts::class)->wherePivot('status', 0);
    }

    public function productsByShort($short): BelongsToMany
    {
        if ($short) {
            return $this->belongsToMany(Products::class)->using(EventProducts::class)->wherePivot('status', 0)->wherePivot('products.product_short', $short);
        } else {
            return $this->products();
        }
    }
}
