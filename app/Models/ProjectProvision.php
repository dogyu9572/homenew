<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProvision extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STACK_LARAVEL = 'laravel';

    protected $fillable = [
        'project_name',
        'stack',
        'root_password',
        'ftp_username',
        'ftp_password',
        'db_username',
        'db_password',
        'db_database',
        'status',
        'step',
        'log',
        'site_url',
        'git_url',
        'created_by',
    ];

    protected $hidden = [
        'root_password',
        'ftp_password',
        'db_password',
    ];

    protected function casts(): array
    {
        return [
            'root_password' => 'encrypted',
            'ftp_password' => 'encrypted',
            'db_password' => 'encrypted',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_QUEUED => '대기',
            self::STATUS_RUNNING => '진행 중',
            self::STATUS_SUCCESS => '완료',
            self::STATUS_FAILED => '실패',
            default => (string) $this->status,
        };
    }

    public function stepLabel(): string
    {
        return match ($this->step) {
            'prepare' => '준비',
            'mysql' => '데이터베이스 생성',
            'clone' => '템플릿 복제',
            'github' => '깃 저장소 생성',
            'laravel' => '라라벨 설치',
            'permission' => '권한 및 FTP',
            'nginx' => '웹서버 설정',
            'done' => '완료',
            default => $this->step ? (string) $this->step : '-',
        };
    }

    public function stackLabel(): string
    {
        return $this->stack === self::STACK_LARAVEL ? '라라벨' : (string) $this->stack;
    }
}
