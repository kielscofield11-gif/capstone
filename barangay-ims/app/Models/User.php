<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isKagawad(): bool
    {
        return $this->role === 'kagawad';
    }

    public function isCaptain(): bool
    {
        return $this->role === self::ROLE_CAPTAIN;
    }

    public function isSecretary(): bool
    {
        return $this->role === 'secretary';
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

    public function createdResidents()
    {
        return $this->hasMany(Resident::class, 'created_by');
    }

    public function createdHouseholds()
    {
        return $this->hasMany(Household::class, 'created_by');
    }

    public function createdBlotters()
    {
        return $this->hasMany(Blotter::class, 'created_by');
    }

    public function resolvedBlotters()
    {
        return $this->hasMany(Blotter::class, 'resolved_by');
    }

    public function requestedDocuments()
    {
        return $this->hasMany(DocumentRequest::class, 'requested_by');
    }

    public function approvedDocuments()
    {
        return $this->hasMany(DocumentRequest::class, 'approved_by');
    }
}
