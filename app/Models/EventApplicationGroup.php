<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventApplicationGroup extends Model
{
    use HasFactory;

    protected $table = 'event_application_group';
    protected $primaryKey = 'id';

    protected $fillable = [
        'event_group_id',
        'application_code',
        'contact_person',
        'contact_no',
        'email',
        'organization',
        'registration',
        'participants',
        'description',
        'requirements',
        'social_media_account',
        'plug',
        'status',
        'discount',
        'discount_value',
    ];

    const CREATED_AT = 'created';
    const UPDATED_AT = null;
}
