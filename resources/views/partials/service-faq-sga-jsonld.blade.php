{{-- $faqItems: board_faq 공개 행 컬렉션 (title, content). layouts.app WebPage JSON-LD sga_plus 조각 --}}
@php
    $faqItems = $faqItems ?? collect();
    $mainEntity = app(\App\Services\FaqPublicService::class)->mainEntityForServicePageJsonLd($faqItems);
    $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
@endphp
,"mainEntity": @json($mainEntity, $jsonFlags)
