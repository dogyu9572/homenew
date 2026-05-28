<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalDocument extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'doc_no',
        'template_key',
        'form_type',
        'title',
        'content',
        'writer_id',
        'status',
        'submitted_at',
        'completed_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function writer()
    {
        return $this->belongsTo(User::class, 'writer_id');
    }

    public function lines()
    {
        return $this->hasMany(ApprovalLine::class, 'document_id');
    }

    public function attachments()
    {
        return $this->hasMany(ApprovalAttachment::class, 'document_id');
    }

    public function opinions()
    {
        return $this->hasMany(ApprovalOpinion::class, 'document_id')->latest();
    }
}
