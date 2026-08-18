<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        return $this->role === 'admin';
    }

    public function isKagawad(): bool
    {
        return $this->role === 'kagawad';
    }

    public function isSecretary(): bool
    {
        return $this->role === 'secretary';
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
