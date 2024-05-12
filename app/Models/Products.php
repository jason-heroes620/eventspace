<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Products extends Model
{
    use HasFactory;

    const CREATED_AT = 'created';
    const UPDATED_AT = null;

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendors::class);
    }

    // public function events(): BelongsToMany
    // {
    //     return $this->belongsToMany(Events::class);
    // }
}
