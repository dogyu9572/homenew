<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalOpinion extends Model
{
    use HasFactory;

    public const TYPE_REJECT = 'reject';

    public const TYPE_COMMENT = 'comment';

    protected $fillable = [
        'document_id',
        'user_id',
        'type',
        'content',
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
