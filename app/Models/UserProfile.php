<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'is_in',
        'point',
        'name',
        'phone',
        'id_card',
    ];

    public $incrementing = false;

    protected $keyType = 'string';
}
