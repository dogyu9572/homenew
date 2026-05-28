<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'original_name',
        'stored_path',
        'mime_type',
        'size',
    ];

    public function document()
    {
        return $this->belongsTo(ApprovalDocument::class, 'document_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
