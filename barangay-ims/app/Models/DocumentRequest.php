<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_number',
        'resident_id',
        'document_type_id',
        'purpose',
        'remarks',
        'status',
        'fee_amount',
        'requested_by',
        'approved_by',
        'approved_date',
        'released_date',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'approved_date' => 'date',
        'released_date' => 'date',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
