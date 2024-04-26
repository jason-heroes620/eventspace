<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationCategories extends Model
{
    use HasFactory;

        protected $table = 'application_categories';

    const CREATED_AT = 'created';
    const UPDATED_AT = null;
}
