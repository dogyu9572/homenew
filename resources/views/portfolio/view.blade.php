@extends('layouts.app')
@section('title', $sName)
@section('sName', $sName)
@section('description', '중견/대기업, 학회/협회, 공공기관, 병원/의료, 대학/학원 등 다양한 분야의 홈페이지 제작 포트폴리오를 확인하세요. 홈페이지제작, 유지보수, 온라인쇼핑몰, SI시스템개발, 앱개발, AI솔루션까지 제공합니다.')
@section('canonical_url', $canonicalUrl)
@if(! empty($ogImageAbsolute))
@section('og_image', $ogImageAbsolute)
@endif
@section('sga_plus')
@php
    $sgaJsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
@endphp
,"mainEntity": {
    "@@type": "CreativeWork",
    "name": @json($portfolio->title, $sgaJsonFlags),
    "description": "@yield('description')",
    "url": @json($canonicalUrl, $sgaJsonFlags),
    "image": @json($ogImageAbsolute ?? null, $sgaJsonFlags),
    "creator": {
        "@@type": "Organization",
        "name": "홈페이지코리아",
        "url": "https://homepagekorea.com"
    },
    "client": @json($portfolio->title, $sgaJsonFlags)
}
@endsection

@section('content')
<main class="sub_contents_wrap">

	<section class="portfolio_head" aria-label="portfolio-head-title">
        @if($portfolio->top_image || $portfolio->thumbnail_image)
		<div class="bg imgfit"><img src="{{ \Illuminate\Support\Facades\Storage::url($portfolio->top_image ?: $portfolio->thumbnail_image) }}" alt="" aria-hidden="true"></div>
        @endif
		<div class="inner">
            <span class="type mojo_aos">
                @foreach((!empty($portfolio->categories) ? $portfolio->categories : array_filter([$primaryCategory])) as $categoryItem)
                    <span>{{ $categoryItem }}</span>
                @endforeach
            </span>
			<h1 id="portfolio-head-title" class="mojo_aos">{{ $portfolio->title }}</h1>
			<h2 class="sound_only"><span>Industry</span>{{ $primaryCategory }} 홈페이지 개편 / 업종</h2>
			<p class="mojo_aos">{!! nl2br(e($portfolio->development_summary ?? '')) !!}</p>
			<div class="tar mojo_aos">
                @if(!empty($portfolio->site_url))
				<a href="{{ $portfolio->site_url }}" class="btn_link" target="_blank" rel="noopener">사이트 방문하기</a>
                @endif
			</div>
		</div>
	</section>
	
	<section class="portfolio_padding portfolio_problem" aria-label="portfolio-problem-title" data-header="light">
		<div class="line_wrap">
			<i class="t1" aria-hidden="true"></i><i class="b1" aria-hidden="true"></i>
			<i class="t2" aria-hidden="true"></i><i class="b2" aria-hidden="true"></i>
			<i class="t3" aria-hidden="true"></i><i class="b3" aria-hidden="true"></i>
			<i class="t4" aria-hidden="true"></i><i class="b4" aria-hidden="true"></i>
			<svg class="line_svg" aria-hidden="true"></svg>
		</div>
		<div class="inner">
			<p class="tit_label" aria-hidden="true">PROBLEM #1</p>
			<h3 id="portfolio-problem-title">{{ $portfolio->problem_title }}</h3>
			<h4>{!! nl2br(e($portfolio->problem_content ?? '')) !!}</h4>
		</div>
	</section>
	
	<section class="portfolio_padding portfolio_solution" aria-label="portfolio-solution-title" data-header="dark">
		<div class="inner">
			<p class="tit_label" aria-hidden="true">SOLUTION #1</p>
			<h3 id="portfolio-solution-title"><strong>{!! nl2br(e($portfolio->solution_title ?? '')) !!}</strong></h3>           
			<div class="before_after">
				<div class="before">
					<p class="tit">BEFORE</p>
                    <div class="img">
                        @if(!empty($portfolio->solution_before_image))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($portfolio->solution_before_image) }}" alt="">
                        @endif
                    </div>
				</div>
				<div class="after">
					<p class="tit">AFTER</p>
                    <div class="img">
                        @if(!empty($portfolio->solution_after_image))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($portfolio->solution_after_image) }}" alt="">
                        @endif
                    </div>
				</div>
			</div>
		</div>
	</section>
	
	<section class="portfolio_padding portfolio_production_composition tac" aria-label="portfolio-production-composition-title" data-header="dark">
		<div class="inner">
			<h3 id="portfolio-production-composition-title" class="sound_only">제작 구성 및 수행 영역</h3>
			<ul class="production_setting_area">
                @forelse($portfolio->featureDevelopments as $feature)
				<li class="{{ \Illuminate\Support\Str::of($feature->title ?? 'feature')->lower()->replace(' ', '_') }}">
					<div class="imgfit" aria-hidden="true">
                        @if(!empty($feature->image_path))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($feature->image_path) }}" alt="">
                        @endif
                    </div>
					<div class="txt">
						<h4 class="port_tit">{{ $feature->title }}</h4>
						<p>{!! nl2br(e($feature->content ?? '')) !!}</p>
						<div class="slide_txt" aria-hidden="true">{{ $feature->background_text ?? '' }}</div>
					</div>
				</li>
                @empty
                <li class="feature_development">
                    <div class="txt">
                        <h4 class="port_tit">제작 구성 정보가 없습니다.</h4>
                    </div>
                </li>
                @endforelse
			</ul>
		</div>
	</section>
	
	<section class="portfolio_review" aria-label="portfolio-review-title" data-header="light">
		<div class="inner">
			<h3 id="portfolio-review-title" class="tit_label">Review</h3>
            @if($portfolio->reviews->count() > 0)
			<p class="port_tit large">{{ $portfolio->reviews->first()->title }}</p>
            @endif
			<ul class="review_list">
                @forelse($portfolio->reviews as $review)
				<li>
					<div class="tt">
						<h4>{{ $review->manager_name }}</h4>
						<div class="star"><img src="/images/img_star5.svg" alt="별점 5점" aria-label="별점 5점"></div>
					</div>
                    <p>{!! nl2br($review->content ?? '') !!}</p>
				</li>
                @empty
                <li><p class="empty">등록된 리뷰가 없습니다.</p></li>
                @endforelse
			</ul>
			<div class="view_btm">
				<div class="left">
					<a href="{{ route('portfolio.portfolio_list') }}" class="btn btn_list">목록으로</a>
				</div>
				<div class="right">
					<button type="button" class="btn btn_link_copy">링크 복사</button>
					<button type="button" class="btn btn_share">공유하기</button>
				</div>
			</div>
		</div>
	</section>
	
	<section class="portfolio_contact page_contact" aria-label="portfolio-contact-title" data-header="dark">
		<div class="inner" data-aos="fade-up">
			<h3 id="portfolio-contact-title" class="port_tit"><strong>전문 의료기관에 최적화된 솔루션,</strong><br/>홈페이지코리아와 상의하세요.</h3>
			<a href="{{ route('contact.contact', ['source_type' => 'portfolio', 'source_id' => $portfolio->id, 'source_url' => $canonicalUrl]) }}" class="btn_link slim">프로젝트 문의하기</a>
		</div>
	</section>

</main>

<script>
$(document).ready(function(){
// AOS
	AOS.init({
		duration: 1000,
	});
//IOS
	function isApple() {
		return /iPhone|iPad|iPod/i.test(navigator.userAgent);
	}
	const isAppleDevice = isApple();
	if (isAppleDevice) {
		$("body").addClass("ios_fix");
	}
});
</script>

@endsection

@push('scripts')
<script src="{{ asset('js/portfolio-view.js') }}"></script>
@endpush