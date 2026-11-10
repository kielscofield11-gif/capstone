<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const PROVIDER_CASH = 'cash';

    protected $fillable = [
        'document_request_id',
        'reference_number',
        'provider',
        'status',
        'amount',
        'currency',
        'payment_method',
        'paid_at',
        'failure_reason',
        'metadata',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function documentRequest()
    {
        return $this->belongsTo(DocumentRequest::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getProviderLabelAttribute(): string
    {
        return match ($this->provider) {
            self::PROVIDER_CASH => 'Cash',
            default => ucfirst((string) $this->provider),
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            null, '' => 'Not selected',
            default => ucfirst(str_replace('_', ' ', (string) $this->payment_method)),
        };
    }
}
