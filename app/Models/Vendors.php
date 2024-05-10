<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendors extends Model
{
    use HasFactory;

    protected $table = 'vendors';

    const CREATED_AT = 'created';
    const UPDATED_AT = null;

    public function products(): HasMany
    {
        return $this->hasMany(Products::class, 'vendor_id');
    }
}
