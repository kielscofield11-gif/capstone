<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForModel($query, string $modelType, $modelId = null)
    {
        $query->where('model_type', $modelType);
        if ($modelId !== null) {
            $query->where('model_id', $modelId);
        }
        return $query;
    }

    public function getModuleLabelAttribute(): string
    {
        return class_basename($this->model_type);
    }
}
