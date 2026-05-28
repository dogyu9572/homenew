<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLine extends Model
{
    use HasFactory;

    public const TYPE_APPROVAL = 'approval';

    public const TYPE_COOPERATION = 'cooperation';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CONFIRMED = 'confirmed';

    protected $fillable = [
        'document_id',
        'user_id',
        'line_type',
        'line_order',
        'status',
        'acted_at',
        'action_comment',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function document()
    {
        return $this->belongsTo(ApprovalDocument::class, 'document_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
