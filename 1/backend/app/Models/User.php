<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'birthday',
        'phone',
        'email',
        'password',
        'role',
    ];

    // Role constants
    const ROLE_REQUESTOR = 'requestor';
    const ROLE_STAFF = 'staff';
    const ROLE_ADMIN = 'admin';

    // Role helper methods
    public function isRequestor(): bool
    {
        return $this->role === self::ROLE_REQUESTOR;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaffOrAdmin(): bool
    {
        return $this->isStaff() || $this->isAdmin();
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class);
    }
}
