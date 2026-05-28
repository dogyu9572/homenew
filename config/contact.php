<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 스팸으로 간주할 본문/회사명 등 통합 검사용 정규식(PCRE)
    |--------------------------------------------------------------------------
    |
    | 영문 SEO·링크 스킴 자동 제출에 대응합니다. 과탐 시 패턴만 완화하면 됩니다.
    |
    */
    'spam_content_patterns' => [
        '/\blink\s+exchange\b/ui',
        '/\breciprocal\s+link/ui',
        '/\bmutual\s+.*\b(link|links|seo)\b/ui',
        '/\bguest\s+post/ui',
        '/\bguest\s+blogging/ui',
        '/\bbacklink(s)?\b/ui',
        '/\bseo\s+value\b/ui',
        '/\bpurely\s+for\s+mutual\b/ui',
        '/\bdo-?follow\b/ui',
        '/\bDR\s*\d{2,}\b/ui',
        '/outreach\s+seo/ui',
        '/\blink\s+to\s+homepagekorea\.com\b/ui',
    ],

    /*
    |--------------------------------------------------------------------------
    | 문의 접수 내부 알림 수신 주소
    |--------------------------------------------------------------------------
    |
    | 쉼표로 구분하여 여러 주소를 지정할 수 있습니다.
    | 예: CONTACT_INTERNAL_MAIL_TO=dogyu@homepagekorea.com
    | 운영: na@homepagekorea.com,haeun@homepagekorea.com
    |
    */
    'internal_recipients' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CONTACT_INTERNAL_MAIL_TO', ''))
    ))),

];
