<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birth_date',
        'birthplace',
        'gender',
        'civil_status',
        'occupation',
        'nationality',
        'blood_type',
        'phone',
        'email',
        // Legacy: existing rows may have photo_path (shown read-only in
        // residents.show). Create/edit no longer accept uploads by design
        // to avoid public-disk storage growth; keep fillable for backfill.
        'photo_path',
        'purok',
        'street_address',
        'is_voter',
        'is_pwd',
        'is_senior',
        'is_4ps',
        'household_id',
        'is_household_head',
        'created_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_voter' => 'boolean',
        'is_pwd' => 'boolean',
        'is_senior' => 'boolean',
        'is_4ps' => 'boolean',
        'is_household_head' => 'boolean',
    ];

    public function getFullNameAttribute(): string
    {
        $name = $this->last_name . ', ' . $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name[0] . '.';
        }
        if ($this->suffix) {
            $name .= ' ' . $this->suffix;
        }
        return $name;
    }

    public function getAgeAttribute(): int
    {
        return $this->birth_date?->age ?? 0;
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function complaints()
    {
        return $this->hasMany(Blotter::class, 'complainant_id');
    }

    public function responses()
    {
        return $this->hasMany(Blotter::class, 'respondent_id');
    }

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class);
    }
}
