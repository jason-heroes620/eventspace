<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationError extends Model
{
    use HasFactory;

    protected $table = 'application_error';

    const CREATED_AT = 'created';
    const UPDATED_AT = null;
}
