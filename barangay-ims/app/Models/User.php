<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_SECRETARY = 'secretary';
    public const ROLE_CAPTAIN = 'captain';
    public const ROLE_KAGAWAD = 'kagawad';
    public const ROLE_STAFF = 'staff';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_SECRETARY,
        self::ROLE_CAPTAIN,
        self::ROLE_KAGAWAD,
        self::ROLE_STAFF,
    ];

    public const CORE_CREATORS = [self::ROLE_ADMIN, self::ROLE_SECRETARY, self::ROLE_STAFF];
    public const CORE_EDITORS = [self::ROLE_ADMIN, self::ROLE_SECRETARY];
    public const DOCUMENT_PROCESSORS = [self::ROLE_ADMIN, self::ROLE_SECRETARY, self::ROLE_CAPTAIN, self::ROLE_KAGAWAD];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_SECRETARY => 'Secretary',
            self::ROLE_CAPTAIN => 'Barangay Captain',
            self::ROLE_KAGAWAD => 'Kagawad',
            self::ROLE_STAFF => 'Staff',
            default => ucfirst((string) $this->role),
        };
    }
}
