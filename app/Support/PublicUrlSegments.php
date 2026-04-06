<?php

namespace App\Support;

/**
 * 루트 경로 `/{segment}` 포트폴리오 slug와 충돌하지 않도록 예약된 1세그먼트 목록.
 */
final class PublicUrlSegments
{
    /**
     * @var list<string>
     */
    public const RESERVED_ROOT = [
        'about',
        'service',
        'industries',
        'portfolio',
        'blog',
        'contact',
        'terms',
        'popup',
        'auth',
        'backoffice',
        'team-story',
        'website-insights',
        'website-trends',
        'success-stories',
    ];

    public static function isReservedForPortfolio(string $slug): bool
    {
        return in_array(strtolower($slug), self::RESERVED_ROOT, true);
    }
}
