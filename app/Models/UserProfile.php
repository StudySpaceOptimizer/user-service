<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserProfile extends Model implements JWTSubject
{
    use HasFactory;

    protected $table = 'user_profiles';

    protected $fillable = [
        'email',
        'is_in',
        'point',
        'name',
        'role',
        'ban_reason',
        'ban_end_at'
    ];

    public $incrementing = false;
    protected $primaryKey = 'email';
    protected $keyType = 'string';

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'role' => $this->role,
            'iss' => 'shared-issuer',
        ];
    }
}
