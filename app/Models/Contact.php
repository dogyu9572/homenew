<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public const STATUS_RECEIVED = '접수';

    public const STATUS_IN_PROGRESS = '처리중';

    public const STATUS_DONE = '완료';

    public const STATUSES = [
        self::STATUS_RECEIVED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_DONE,
    ];

    public const SERVICE_OPTIONS = [
        '홈페이지 제작',
        '홈페이지 유지보수',
        '온라인 쇼핑몰 제작',
        '시스템 개발',
        '앱 개발',
        '맞춤형 AI 솔루션',
    ];

    protected $fillable = [
        'company',
        'contact_person',
        'phone',
        'email',
        'services',
        'current_site',
        'message',
        'budget',
        'attachments',
        'source_type',
        'source_id',
        'source_url',
        'source_title',
        'status',
        'admin_memo',
    ];

    protected function casts(): array
    {
        return [
            'services' => 'array',
            'attachments' => 'array',
            'source_id' => 'integer',
        ];
    }

    /**
     * 관리자 상세 등 단건 화면용: 저장된 제목 없으면 포트폴리오에서 조회
     */
    public function resolvedSourceTitle(): ?string
    {
        if ($this->source_title !== null && $this->source_title !== '') {
            return $this->source_title;
        }
        if ($this->source_type === 'portfolio' && $this->source_id) {
            return Portfolio::query()->whereKey($this->source_id)->value('title');
        }

        return null;
    }

    public static function sourceTypeAdminLabel(?string $type): string
    {
        return match ($type) {
            'portfolio' => '포트폴리오',
            'blog' => '블로그',
            'about' => '회사소개',
            'service' => '서비스',
            'industries' => '산업군',
            default => $type !== null && $type !== '' ? $type : '',
        };
    }

    /**
     * 내부 알림 메일 본문용 유입 경로 요약
     */
    public function inflowSummaryLine(): string
    {
        $parts = [];
        if (filled($this->source_url)) {
            $parts[] = 'URL: '.$this->source_url;
        }
        $typeLabel = self::sourceTypeAdminLabel($this->source_type);
        if ($typeLabel !== '') {
            $parts[] = '유형: '.$typeLabel;
        }
        if ($this->source_id !== null && (int) $this->source_id !== 0) {
            $parts[] = 'ID: '.(string) $this->source_id;
        }
        $title = $this->resolvedSourceTitle();
        if ($title !== null && $title !== '') {
            $parts[] = '제목: '.$title;
        }

        return $parts !== [] ? implode("\n", $parts) : '(유입 정보 없음)';
    }
}
