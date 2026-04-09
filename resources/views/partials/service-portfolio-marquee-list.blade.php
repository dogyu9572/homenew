{{-- 서비스/산업 페이지 공통 포트폴리오 마퀴 슬라이드 항목 ($portfolioItems: Collection) --}}
@forelse($portfolioItems ?? collect() as $item)
@php
    $marqueeIndustry = $item->category ?: ($item->categories[0] ?? '');
    $marqueeTypeLabel = $marqueeIndustry !== '' ? $marqueeIndustry : '-';
    if (filled($item->development_summary)) {
        $marqueeTypeLabel .= ' / ' . $item->development_summary;
    }
    $marqueeDesc = \Illuminate\Support\Str::limit(strip_tags($item->detail_summary ?? $item->development_summary ?? ''), 180);
    $marqueeThumb = ! empty($item->thumbnail_image)
        ? \Illuminate\Support\Facades\Storage::url($item->thumbnail_image)
        : null;
@endphp
<li>
    <a href="{{ $item->publicListHref() }}" class="box" @if($item->publicListOpensInNewTab()) target="_blank" rel="noopener noreferrer" @endif aria-label="{{ $item->title }} — {{ $marqueeTypeLabel }} 포트폴리오 보기">
        <span class="flip">
            <span class="before" aria-hidden="true">@if($marqueeThumb)<img src="{{ $marqueeThumb }}" alt="" class="bg">@endif<img src="/images/main_service_08.svg" alt="" class="logo">
				<span class="tit"><p>{{ $marqueeTypeLabel }}</p><strong>{{ $item->title }}</strong></span>
			</span>
            <!-- <span class="after" aria-hidden="true">
                <span class="type">{{ $marqueeTypeLabel }}</span>
                <span class="tit">{{ $item->title }}</span>
                @if($marqueeDesc !== '')<p>{{ $marqueeDesc }}</p>@endif
                <span class="logo"><img src="/images/main_service_08.svg" alt=""></span>
            </span> -->
        </span>
    </a>
</li>
@empty
<li>
    <span class="box" tabindex="-1" aria-hidden="true">
        <span class="flip">
            <span class="before" aria-hidden="true"><img src="/images/main_service_08.svg" alt="" class="logo"></span>
            <span class="after" aria-hidden="true">
                <span class="type">-</span>
                <span class="tit">등록된 포트폴리오가 없습니다.</span>
                <p>해당 분류로 등록된 사례를 준비 중입니다.</p>
                <span class="logo"><img src="/images/main_service_08.svg" alt=""></span>
            </span>
        </span>
    </span>
</li>
@endforelse
