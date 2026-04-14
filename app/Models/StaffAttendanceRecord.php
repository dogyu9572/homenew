<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendanceRecord extends Model
{
    public const KIND_CLOCK_IN = 'clock_in';

    public const KIND_CLOCK_OUT = 'clock_out';

    public const WORKPLACE_OFFICE = 'office';

    public const WORKPLACE_REMOTE = 'remote';

    protected $fillable = [
        'user_id',
        'kind',
        'workplace',
        'recorded_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function kindLabel(string $kind): string
    {
        return match ($kind) {
            self::KIND_CLOCK_IN => '출근',
            self::KIND_CLOCK_OUT => '퇴근',
            default => $kind,
        };
    }

    public static function workplaceLabel(string $workplace): string
    {
        return match ($workplace) {
            self::WORKPLACE_OFFICE => '사무실',
            self::WORKPLACE_REMOTE => '재택',
            default => $workplace,
        };
    }
}
