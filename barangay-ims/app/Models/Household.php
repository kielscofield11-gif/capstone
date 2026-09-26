<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_number',
        'purok',
        'street_address',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function head()
    {
        return $this->hasOne(Resident::class)->where('is_household_head', true);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getMemberCountAttribute(): int
    {
        if ($this->relationLoaded('residents')) {
            return $this->residents->count();
        }

        if (isset($this->attributes['residents_count'])) {
            return (int) $this->attributes['residents_count'];
        }

        return $this->residents()->count();
    }
}
