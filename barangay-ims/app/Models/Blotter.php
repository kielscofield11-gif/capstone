<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blotter extends Model
{
    use HasFactory;

    protected $fillable = [
        'blotter_number',
        'complainant_id',
        'respondent_id',
        'incident_type',
        'incident_date',
        'incident_location',
        'details',
        'status',
        'hearing_date',
        'resolution',
        'resolved_by',
        'created_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'hearing_date' => 'date',
    ];

    public function complainant()
    {
        return $this->belongsTo(Resident::class, 'complainant_id');
    }

    public function respondent()
    {
        return $this->belongsTo(Resident::class, 'respondent_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeHearingsBetween($query, $from, $to)
    {
        return $query->whereNotNull('hearing_date')
            ->whereBetween('hearing_date', [$from, $to]);
    }
}
