@extends('layouts.app')
@section('title', $sName)
@section('sName', $sName)
@section('description', '홈페이지 기획부터 SEO 최적화, 사용자 경험 개선까지, 성공적인 온라인 비즈니스를 위한 유용한 인사이트를 만나보세요.')
@section('sga_plus')
,"mainEntity": {
    "@@type": "BlogPosting",
    "headline": @json($post->title),
    "description": @json('홈페이지 기획부터 SEO 최적화, 사용자 경험 개선까지, 성공적인 온라인 비즈니스를 위한 유용한 인사이트를 만나보세요.'),
    "datePublished": @json($publishedIso),
    "dateModified": @json($publishedIso),
    @if($heroImage)
    "image": @json(url($heroImage)),
    @endif
    "articleSection": @json($post->category_label),
    "url": @json($canonicalUrl),
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
    data-event-url="{{ route('blog.event', ['blogPost' => $post->id]) }}"
    data-like-url="{{ route('blog.like', ['blogPost' => $post->id]) }}">

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
				<p class="tb">내용</p>
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
                    @continue(empty($section->subtitle) && empty($section->content))
                <section @if(!empty($section->subtitle)) aria-labelledby="blog-section-{{ $section->id }}" @endif>
                    @if(!empty($section->subtitle))
					<h2 id="blog-section-{{ $section->id }}">{{ $section->subtitle }}</h2>
                    @endif
                    @if(!empty($section->content))
					<p>{!! nl2br(e($section->content)) !!}</p>
                    @endif
				</section>
                @empty
                <section>
                    <p>등록된 본문 내용이 없습니다.</p>
                </section>
                @endforelse
			</article>

			<div class="view_btm">
				<div class="left">
					<a href="{{ route('blog.blog_list') }}" class="btn btn_list">목록으로</a>
				</div>
				<div class="right">
					<button type="button" class="like" aria-pressed="false" aria-label="좋아요"><span aria-hidden="true"><i></i><p>좋아요<strong class="blog_like_count">{{ number_format($likeCount) }}</strong></p></span></button>
					<button type="button" class="btn btn_link_copy slim">링크 복사</button>
					<button type="button" class="btn btn_share slim">공유하기</button>
				</div>
			</div>
			
			<section class="service_faq blog_faq_wrap" aria-label="blog-faq-title" data-header="dark">
				<div class="inner">
					<h2 id="blog-faq-title"><strong>자주 묻는 질문</strong></h2>
					<ul class="faq_list">
						<li class="on">
							<h3 class="faq_title"><button type="button" aria-expanded="true" aria-controls="main-faq-con-7">홈페이지 제작 기간은 얼마나 걸리나요?</button></h3>
							<div class="con" id="main-faq-con-7" style="display: block;"><p>프로젝트 규모에 따라 다르지만, 일반적인 기업 홈페이지는 4~6주, 쇼핑몰이나 복잡한 SI 시스템은 8~12주 정도 소요됩니다.&nbsp;</p><p>정확한 일정은 초기 상담 시 WBS 기반으로 안내해 드립니다.</p></div>
						</li>
						<li>
							<h3 class="faq_title"><button type="button" aria-expanded="true" aria-controls="main-faq-con-7">홈페이지 제작 기간은 얼마나 걸리나요?</button></h3>
							<div class="con" id="main-faq-con-7" style="display: block;"><p>프로젝트 규모에 따라 다르지만, 일반적인 기업 홈페이지는 4~6주, 쇼핑몰이나 복잡한 SI 시스템은 8~12주 정도 소요됩니다.&nbsp;</p><p>정확한 일정은 초기 상담 시 WBS 기반으로 안내해 드립니다.</p></div>
						</li>
					</ul>
				</div>
			</section>
		</div>

		<div class="recommended_area open_opacity">
			<div class="tit">추천 콘텐츠</div>
			<ul class="recommended_list">
                @forelse($recommended as $item)
				<li>
                    <a href="{{ route('blog.blog_view', ['blogPost' => $item->id]) }}">
                        <span class="imgfit" aria-hidden="true"><img src="{{ $item->thumbnail_path ? \Illuminate\Support\Facades\Storage::url($item->thumbnail_path) : '/images/img_blog_sample.jpg' }}" alt=""></span>
                        <span class="txt">{{ $item->title }}</span>
                    </a>
                </li>
                @empty
                <li><span class="txt">추천 콘텐츠가 없습니다.</span></li>
                @endforelse
			</ul>
			<a href="/contact" class="btn_contact"><p aria-hidden="true">Contact Us</p><span>프로젝트 문의하기</span></a>
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