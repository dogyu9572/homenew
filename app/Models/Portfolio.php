<?php

namespace App\Models;

use App\Support\PublicUrlSegments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Portfolio extends Model
{
    use SoftDeletes;

    public const CATEGORIES = [
        '중견/대기업',
        '학회/협회',
        '공공기관',
        '병원/의료',
        '대학/학원',
        '쇼핑몰',
        '일반',
    ];

    public const SERVICE_SUBCATEGORIES = [
        '서비스-통합SI시스템 개발(예약)',
        '통합SI시스템 개발(ERP)',
        '통합SI시스템 개발(백오피스)',
        '통합SI시스템 개발(CMS)',
        '통합SI시스템 개발(LMS)',
        '서비스-앱',
        '서비스-AI',
        'Industry-중견/대기업',
        'Industry-학회',
        'Industry-공공기관',
        'Industry-병원/의료',
        'Industry-대학·연구실',
    ];

    protected $fillable = [
        'category',
        'categories',
        'slug',
        'development_summary',
        'title',
        'keywords',
        'thumbnail_image',
        'service_subcategories',
        'top_image',
        'sort_order',
        'is_main_display',
        'is_active',
        'detail_summary',
        'detail_editor',
        'site_url',
        'is_direct_site_link',
        'problem_title',
        'problem_content',
        'solution_title',
        'solution_content',
        'solution_before_image',
        'solution_after_image',
    ];

    protected $casts = [
        'categories' => 'array',
        'service_subcategories' => 'array',
        'keywords' => 'array',
        'is_main_display' => 'boolean',
        'is_active' => 'boolean',
        'is_direct_site_link' => 'boolean',
    ];

    /**
     * 사용자 포트폴리오 목록·카드 링크 주소 (외부 전용이면 site_url, 아니면 상세 URL)
     */
    public function publicListHref(): string
    {
        if ($this->is_direct_site_link && filled($this->site_url)) {
            return trim((string) $this->site_url);
        }

        return route('portfolio.portfolio_view', $this);
    }

    /**
     * 목록에서 새 창으로 열지 여부 (외부 전용 + site_url 있음)
     */
    public function publicListOpensInNewTab(): bool
    {
        return $this->is_direct_site_link && filled($this->site_url);
    }

    public function featureImages(): HasMany
    {
        return $this->hasMany(PortfolioFeatureImage::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PortfolioReview::class)->orderBy('sort_order');
    }

    public function featureDevelopments(): HasMany
    {
        return $this->hasMany(PortfolioFeatureDevelopment::class)->orderBy('sort_order');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * 제목 기반 기본 슬러그 (고유 보장은 서비스/검증에서 처리).
     */
    public static function baseSlugFromTitle(string $title): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'portfolio';
        }
        if (PublicUrlSegments::isReservedForPortfolio($base)) {
            $base = $base.'-case';
        }

        return $base;
    }
}
