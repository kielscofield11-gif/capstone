<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'name', 'title', 'header_line_1', 'header_line_2', 'header_line_3', 'office_name',
        'barangay_name', 'municipality', 'province', 'opening_phrase', 'body', 'closing_text',
        'signatory_name', 'signatory_position', 'footer_text', 'logo_path', 'show_control_number',
        'show_fee', 'show_issue_date', 'show_resident_photo', 'show_logo', 'is_default', 'is_active', 'created_by',
    ];

    protected $casts = [
        'show_control_number' => 'boolean', 'show_fee' => 'boolean', 'show_issue_date' => 'boolean',
        'show_resident_photo' => 'boolean', 'show_logo' => 'boolean', 'is_default' => 'boolean', 'is_active' => 'boolean',
    ];

    public function documentTypes() { return $this->hasMany(DocumentType::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
