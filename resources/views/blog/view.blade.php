@extends('layouts.app')
@section('title', $sName)
@section('sName', $sName)
@section('description', $metaDescription)
@section('canonical_url', $canonicalUrl)
@section('og_type', 'article')
@if(! empty($ogImageAbsolute))
@section('og_image', $ogImageAbsolute)
@endif
@section('sga_plus')
@php
    $sgaJsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
@endphp
,"mainEntity": {
    "@@type": "BlogPosting",
    "headline": @json($post->title, $sgaJsonFlags),
    "description": @json($metaDescription, $sgaJsonFlags),
    "datePublished": @json($publishedIso, $sgaJsonFlags),
    "dateModified": @json($publishedIso, $sgaJsonFlags),
    @if($heroImage)
    "image": @json(url($heroImage), $sgaJsonFlags),
    @endif
    "articleSection": @json($post->category_label, $sgaJsonFlags),
    "url": @json($canonicalUrl, $sgaJsonFlags),
    "author": {
        "@@type": "Organization",
        "name": "홈페이지코리아",
        "url": "https://homepagekorea.com"
    }
}
@endsection

@section('content')
<main class="sub_contents_wrap" id="blog_view_root"
    data-post-id="{{ $post->id }}"
    data-event-url="{{ route('blog.event', $post) }}"
    data-like-url="{{ route('blog.like', $post) }}">

	<section class="blog_view_wrap" aria-labelledby="blog-view-title" data-header="light">

        @if($tocSections->count() > 0)
		<div class="index_area open_opacity" role="navigation" aria-label="목차">
			<button type="button" class="tit" aria-expanded="false" aria-controls="contents-list">CONTENTS</button>
			<ul class="index_list" id="contents-list">
                @foreach($tocSections as $section)
				<li><a href="#blog-section-{{ $section->id }}">{{ $section->subtitle }}</a></li>
                @endforeach
			</ul>
		</div>
        @endif

		<div class="blog_view_con mojo_aos">
			<div class="blog_view_head">
				<p class="type">{{ $post->category_label }}</p>
				<h1 id="blog-view-title">{{ $post->title }}</h1>
                @if(!empty($post->lead_content))
				<div class="tb blog-view-lead">{!! $post->lead_content !!}</div>
                @endif
				<div class="view_btm view_top">
					<div class="left">
						@if($publishedDate !== '')
						<time class="date" datetime="{{ $post->published_at?->toDateString() }}">{{ $publishedDate }}</time>
						@endif
					</div>
					<div class="right">
						<button type="button" class="like" aria-pressed="false" aria-label="좋아요"><span aria-hidden="true"><i></i><p>좋아요<strong class="blog_like_count">{{ number_format($likeCount) }}</strong></p></span></button>
						<button type="button" class="btn btn_link_copy slim">링크 복사</button>
						<button type="button" class="btn btn_share slim">공유하기</button>
					</div>
				</div>
                
                @if($heroImage)
				<div class="imgfit" aria-hidden="true"><img src="{{ $heroImage }}" alt=""></div>
                @endif
			</div>

			<article aria-label="본문" class="blog_view_body">
                @forelse($post->sections as $section)
                    @if(empty($section->subtitle) && $section->items->filter(fn ($i) => filled($i->subheading) || filled($i->content))->isEmpty())
                        @continue
                    @endif
                <section @if(!empty($section->subtitle)) aria-labelledby="blog-section-{{ $section->id }}" @endif>
                    @if(!empty($section->subtitle))
					<h2 id="blog-section-{{ $section->id }}">{{ $section->subtitle }}</h2>
                    @endif
                    @foreach($section->items as $item)
                        @if(empty($item->subheading) && empty($item->content))
                            @continue
                        @endif
                        @if(!empty($item->subheading))
					<h3>{{ $item->subheading }}</h3>
                        @endif
                        @if(!empty($item->content))
					<div class="blog-section-body">{!! $item->content !!}</div>
                        @endif
                    @endforeach
				</section>
                @empty
                <section>
                    <p>등록된 본문 내용이 없습니다.</p>
                </section>
                @endforelse
			</article>

			<div class="view_btm">
				<div class="left">
					<a href="{{ $blogListUrl ?? route('blog.blog_list') }}" class="btn btn_list">목록으로</a>
				</div>
				<div class="right">
					<button type="button" class="like" aria-pressed="false" aria-label="좋아요"><span aria-hidden="true"><i></i><p>좋아요<strong class="blog_like_count">{{ number_format($likeCount) }}</strong></p></span></button>
					<button type="button" class="btn btn_link_copy slim">링크 복사</button>
					<button type="button" class="btn btn_share slim">공유하기</button>
				</div>
			</div>
			
            @if($blogFaqItems->isNotEmpty())
			<section class="service_faq blog_faq_wrap" aria-label="blog-faq-title" data-header="dark">
				<div class="inner">
					<h2 id="blog-faq-title"><strong>자주 묻는 질문</strong></h2>
					<ul class="faq_list">
                        @include('partials.public-faq-list', ['faqItems' => $blogFaqItems, 'idPrefix' => 'blog-faq-'.$post->id, 'variant' => 'main'])
					</ul>
				</div>
			</section>
            @endif
		</div>

		<div class="recommended_area open_opacity">
			<div class="tit">추천 콘텐츠</div>
			<ul class="recommended_list">
                @forelse($recommended as $item)
				<li>
                    <a href="{{ route('blog.blog_view', $item) }}">
                        <span class="imgfit" aria-hidden="true"><img src="{{ $item->thumbnail_path ? \Illuminate\Support\Facades\Storage::url($item->thumbnail_path) : '/images/img_blog_sample.jpg' }}" alt=""></span>
                        <span class="txt">{{ $item->title }}</span>
                    </a>
                </li>
                @empty
                <li><span class="txt">추천 콘텐츠가 없습니다.</span></li>
                @endforelse
			</ul>
			<a href="{{ route('contact.contact', ['source_type' => 'blog', 'source_url' => $canonicalUrl, 'source_title' => $post->title]) }}" class="btn_contact"><p aria-hidden="true">Contact Us</p><span>프로젝트 문의하기</span></a>
		</div>

	</section>

</main>

<script>
function updateTitButton() {
    const $btn = $('.index_area .tit');
    if ($(window).width() >= 768) {
        $btn.attr('aria-disabled', 'true').attr('tabindex', '-1');
    } else {
        $btn.removeAttr('aria-disabled').removeAttr('tabindex');
    }
}
$(document).ready(function () {
    updateTitButton();
    $('.index_area .tit').on('click', function () {
        if ($(window).width() >= 768) return;
        const $btn = $(this);
        const isExpanded = $btn.attr('aria-expanded') === 'true';
        $btn.attr('aria-expanded', !isExpanded);
        $btn.next('.index_list').stop(true, true).slideToggle('fast');
        $btn.parent().stop(true, true).toggleClass('on');
    });
    $(window).on('resize', updateTitButton);
});
</script>

@endsection

@push('scripts')
<script src="{{ asset('js/blog-view.js') }}"></script>
<script src="{{ asset('js/faq-accordion.js') }}"></script>
@endpush