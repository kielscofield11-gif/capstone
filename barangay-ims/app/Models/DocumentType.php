<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'requirements',
        'processing_days',
        'document_template_id',
        'fee_amount',
        'is_active',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'processing_days' => 'integer',
    ];

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function documentTemplate() { return $this->belongsTo(DocumentTemplate::class); }
}
