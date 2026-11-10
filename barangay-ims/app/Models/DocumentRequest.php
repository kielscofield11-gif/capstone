<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
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
        'issued_document_snapshot',
        'issued_at',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'approved_date' => 'date',
        'released_date' => 'date',
        'issued_document_snapshot' => 'array',
        'issued_at' => 'datetime',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class)->withTrashed();
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function paidPayment(): ?Payment
    {
        $payments = $this->relationLoaded('payments') ? $this->payments : $this->payments()->get();

        return $payments
            ->filter(fn (Payment $payment) => $payment->isPaid()
                && (float) $payment->amount >= (float) $this->fee_amount)
            ->sortByDesc(fn (Payment $payment) => $payment->paid_at ?? $payment->created_at)
            ->first();
    }

    public function isPaid(): bool
    {
        if ((float) $this->fee_amount <= 0) {
            // Free documents (fee <= 0) are considered paid by default.
            // No payment record is required for release.
            return true;
        }

        return $this->paidPayment() !== null;
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
