@extends('layouts.app')
@section('title'){{ $gName }} | {{ $sName }}@endsection
@section('gName', $gName)
@section('sName', $sName)
@section('description', '비즈니스 생산성을 높이는 맞춤형 AI 솔루션. 1,100개 조직이 선택한 홈페이지코리아가 27년 경험으로 AI 챗봇·QA 자동화·기업용 AI 구축까지 책임집니다.')
@section('keywords', 'AI 솔루션, AI 챗봇, AI QA, AX 솔루션, 기업용 llm, 기업용 ai')
@section('sga_plus')
@include('partials.service-faq-sga-jsonld', ['faqItems' => $faqItems])
@endsection

@section('content')
<main class="sub_contents_wrap infopage_wrap">

	<section class="infopage_head service_head main_service" aria-labelledby="service-head-title" data-header="dark">
		<div class="inner">
			<h1 id="service-head-title" class="mojo_aos">기업 맞춤형 AI 솔루션으로 <br class="pc_vw">비즈니스 생산성을 혁신하세요.</h1>
			<div class="btns flex_center mojo_aos">
				<a href="/portfolio/" class="btn_link slim">포트폴리오 보러가기</a>
				<a href="{{ route('contact.contact', ['source_type' => 'service', 'source_url' => url()->current(), 'source_title' => $sName]) }}" class="btn_link slim">프로젝트 문의하기</a>
			</div>
		</div>
		<div class="marquee_banner_wrap mojo_aos">
			<div class="marquee_inbox">
				<div class="marquee_banner" id="marquee_banner_random"></div>
			</div>
		</div>
		<div class="inner bg_round_start" data-aos="fade-up" data-aos-offset="200">
			<div class="bg_round"><div class="in_gradient"></div></div>
			<div class="problem service">
				<p class="tit_label" data-aos="fade-up">PROBLEM</p>
				<h2 id="service-problem-title" data-aos="fade-up">기업용 AI 솔루션 도입이 <br class="pc_vw"><strong>필요하신가요?</strong></h2>
				<ul class="problem_list" data-aos="fade-up">
					<li>
						<h3>기존 AI 상담 챗봇 답변이 부정확할 때가 많아요. <img src="/images/emoji_tears.png" alt="" aria-hidden="true"></h3>
						<p>일반 AI는 내부 규정을 몰라 엉뚱한 답(할루시네이션)을 내놓아 브랜드 신뢰도를 떨어뜨리고 고객을 혼란에 빠뜨립니다.</p>
					</li>
					<li>
						<h3>웹사이트 출시 전, 테스트할 인력과 시간이 부족합니다. <img src="/images/emoji_sad.png" alt="" aria-hidden="true"></h3>
						<p>사람이 일일이 모든 기능을 검증하는 방식은 막대한 시간이 소요될 뿐만 아니라, 테스터가 놓치는 휴먼 에러의 위험이 항상 존재합니다.</p>
					</li>
					<li>
						<h3>사내 정보를 찾는 데 많은 시간을 허비하고 있어요. <img src="/images/emoji_anger.png" alt="" aria-hidden="true"></h3>
						<p>사내 메신저, 노션, PDF에 흩어진 방대한 자료를 찾기 위해 매번 담당자에게 묻거나 직접 검색하느라 업무 흐름이 끊깁니다.</p>
					</li>
				</ul>
				<div class="dots" aria-hidden="true"><i class="t"></i><i class="m"></i><i class="b"></i></div>
			</div>
		</div>
	</section>
	
	<section class="infopage_solution service_solution bg_white" aria-labelledby="service-solution-title" data-header="light">
		<div class="inner">
			<p class="tit_label">SOLUTION</p>
			<h2 id="service-solution-title">홈페이지코리아는 기업의 실무 환경에 최적화된 <br class="pc_vw"><strong>맞춤형 AX 솔루션을 구축합니다.</strong></h2>
			<ul class="solution_list">
				<li class="i_g1">
					<object data="/images/icon_solution_g01.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>맞춤형 AI 챗봇 솔루션</h3>
					<p>정해진 답만 하는 기존 챗봇과 다릅니다. <br/>브랜드의 성격에 맞춰 답변 품질을 최적화하고, <br class="pc_vw">난수(Temperature) 제어 기술을 통해 오답 없는 정확한 정보로 24시간 끊김 없는 고품질 상담 환경을 제공합니다.</p>
				</li>
				<li class="i_g2">
					<object data="/images/icon_solution_g02.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>AI 기반 품질 관리(QA) <br class="pc_vw">자동화 솔루션</h3>
					<p>기획안을 학습한 AI가 테스트 시나리오를 자동 설계하고, <br class="pc_vw">서비스의 처음부터 끝까지 검증하는 E2E 자동 테스트를 수행합니다. <br/>데이터 기반 보고서를 통해 출시 후 발생할 운영 리스크를 <br class="pc_vw">사전에 완벽히 차단합니다.</p>
				</li>
				<li class="i_g3">
					<object data="/images/icon_solution_g03.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>AI 에이전트 · <br class="pc_vw">기업용 AI 솔루션</h3>
					<p>RAG(검색 증강 생성) 모델을 구축해 사내 데이터를 <br class="pc_vw">완벽히 이해하는 지식 아카이브를 만듭니다. <br class="pc_vw">MCP 모델로 슬랙, 지메일 등과 연동하여 AI가 회의록을 요약하고 <br class="pc_vw">메일 초안을 작성하는 즉시 실행형 업무 환경을 구현합니다.</p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_how service_how" aria-labelledby="service-how-title" data-header="dark">
		<div class="inner">
			<p class="tit_label">HOW</p>
			<h2 id="service-how-title">27년 웹 구축 기술력으로<br/> <strong>빠르고 안전한 기업용 AX 구축 프로세스</strong></h2>
			<ul class="how_list">
				<li class="i_g1">
					<div class="imgfit"><img src="/images/img_service_how_g01.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_g01.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3>웹, 앱, AI 전문 엔지니어의 <br class="pc_vw"><strong>AI 네이티브 시스템 구축</strong></h3>
						<p><strong>Gemini, Cursor AI 등</strong> 최신 모델을 활용해 <br class="pc_vw"><strong>개발 기간은 단축하고 비용은 합리적</strong>으로, <br class="pc_vw">결과물은 <strong>시니어 개발자가 직접 검수</strong>하여 <strong>보안성을 보장</strong>합니다.</p>
					</div>
				</li>
				<li class="i_g2">
					<div class="imgfit"><img src="/images/img_service_how_g02.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_g02.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3>현장에 꼭 필요한 <br class="pc_vw"><strong>'실전형' AI 도입 제안</strong></h3>
						<p>무조건적인 최신 기술 적용이 아닌, <strong>PM이 직접 고객사의 업무 <br class="pc_vw">프로세스를 분석</strong>합니다.  실제 업무 흐름을 기반으로 <strong>최소 비용으로 <br class="pc_vw">최대 효율을 낼 수 있는 현실적인 AI 도입 방안을 제안</strong>합니다.</p>
					</div>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_review service_review bg_white" aria-labelledby="service-review-title" data-header="light">
		<div class="line_wrap">
			<i class="t1" aria-hidden="true"></i><i class="b1" aria-hidden="true"></i>
			<svg class="line_svg" aria-hidden="true"></svg>
		</div>
		<div class="inner">
			<p class="tit_label">REVIEW</p>
			<h2 id="service-review-title"><strong>성공적인 AI 솔루션 개발</strong><br class="pc_vw"> 홈페이지코리아와 함께하세요.</h2>
		</div>
		<div class="portfolio_marquee">
			<ul class="list">
				@forelse($aiPortfolioItems ?? collect() as $item)
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
							<span class="before" aria-hidden="true">@if($marqueeThumb)<img src="{{ $marqueeThumb }}" alt="" class="bg">@endif<img src="/images/main_service_08.svg" alt="" class="logo"></span>
							<span class="after" aria-hidden="true">
								<span class="type">{{ $marqueeTypeLabel }}</span>
								<span class="tit">{{ $item->title }}</span>
								@if($marqueeDesc !== '')<p>{{ $marqueeDesc }}</p>@endif
								<span class="logo"><img src="/images/main_service_08.svg" alt=""></span>
							</span>
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
								<p>곧 다양한 개발 사례를 이곳에서 만나보실 수 있습니다.</p>
								<span class="logo"><img src="/images/main_service_08.svg" alt=""></span>
							</span>
						</span>
					</span>
				</li>
				@endforelse
			</ul>
		</div>
		<div class="flex_center">
			<a href="/portfolio/" class="btn_link slim">앱 개발 사례 더보기</a>
		</div>
	</section>
	
	<section class="infopage_contact service_contact page_contact" aria-label="service-contact-title" data-header="dark">
		<div class="inner" data-aos="fade-up">
			<p class="tit_label sound_only">CTA</p>
			<h2 id="service-contact-title" class="port_tit"><strong>우리 기업에 꼭 맞는 AI 솔루션</strong>, <br>지금 상담해 보세요.</h2>
			<div class="btns flex_center">
				<a href="/portfolio/" class="btn_link">포트폴리오 보러가기</a>
				<a href="/contact/" class="btn_link">쇼핑몰 제작 문의하기</a>
			</div>
		</div>
	</section>

	<section class="infopage_faq service_faq" aria-label="service-faq-title" data-header="dark">
		<div class="inner">
			<p class="tit_label">FAQ</p>
			<h2 id="service-faq-title"><strong>자주 묻는 질문</strong></h2>
			<ul class="faq_list">
				@include('partials.public-faq-list', ['faqItems' => $faqItems, 'idPrefix' => 'service-faq-ai', 'variant' => 'service'])
			</ul>
		</div>
	</section>

</main>

<script>
$(document).ready(function(){
// marquee
    initMarquee("#marquee_banner_random", getRandomItems(['a','b','c','d','e'], 40));
// review 사선
	function drawLines() {
		const $wrap = $('.line_wrap');
		const $svg  = $wrap.find('.line_svg');
		const pairs = [['t1','b1']];
		const wrapOffset = $wrap.offset();

		$svg.empty();

		pairs.forEach(function([tClass, bClass], index) {
			const $t = $wrap.find('.' + tClass);
			const $b = $wrap.find('.' + bClass);
			if (!$t.length || !$b.length) return;

			const tOffset = $t.offset();
			const bOffset = $b.offset();

			const x1 = tOffset.left - wrapOffset.left + $t.outerWidth()  / 2;
			const y1 = tOffset.top  - wrapOffset.top  + $t.outerHeight() / 2;
			const x2 = bOffset.left - wrapOffset.left + $b.outerWidth()  / 2;
			const y2 = bOffset.top  - wrapOffset.top  + $b.outerHeight() / 2;

			const length = Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));

			const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
			$(line).attr({
				x1, y1, x2, y2,
				stroke: '#8A949E33',
				'stroke-width': '1',
				'stroke-dasharray': length,
				'stroke-dashoffset': length  // 초기 숨김 상태
			});
			$(line).addClass('line_item').attr('data-delay', index * 0.15);

			$svg.append(line);
		});
	}

	function startLineAnimation() {
		$('.line_wrap .line_item').each(function() {
			const delay = $(this).attr('data-delay');
			$(this).css({
				animation: `drawLine 1.2s ease forwards`,
				'animation-delay': delay + 's'
			});
		});
	}

	// IntersectionObserver로 .portfolio_problem 중앙 도달 감지
	const observer = new IntersectionObserver(function(entries) {
		entries.forEach(function(entry) {
			if (entry.isIntersecting) {
				startLineAnimation();
				observer.unobserve(entry.target); // 한 번만 실행
			}
		});
	}, {
		rootMargin: '-50% 0px -50% 0px'  // 화면 중앙에 도달할 때
	});

	$(window).on('load', function() {
		drawLines();
		const target = document.querySelector('.infopage_review');
		if (target) observer.observe(target);
	});

	$(window).on('resize', function() {
		drawLines();
	});
// Portfolio marquee
    (function () {
        const $banner = $(".service_review .portfolio_marquee");
        const $list   = $banner.find(".list").first();
        if (! $list.length || ! $list.children("li").length) {
            return;
        }
        const speed       = 1;
        let posX          = 0;
        let isPaused      = false;
        let totalWidth    = 0;
        let marqueeResizeTimer;
        function measure() {
            const el = $list.get(0);
            totalWidth = el && el.scrollWidth > 0 ? el.scrollWidth : $list.outerWidth(true);
        }
        measure();
        $(window).on("load", measure);
        $(window).on("resize", function () {
            clearTimeout(marqueeResizeTimer);
            marqueeResizeTimer = setTimeout(measure, 100);
        });
        function marqueeLoop() {
            if (!isPaused && totalWidth > 0) {
                posX -= speed;
                if (posX <= -totalWidth) {
                    posX = 0;
                }
                $list.css("transform", `translateX(${posX}px)`);
            }
            requestAnimationFrame(marqueeLoop);
        }
        marqueeLoop();
        $banner.on("mouseenter", function () { isPaused = true; })
               .on("mouseleave", function () { isPaused = false; });
    })();
// contact
	const observerContact = new IntersectionObserver(function(entries) {
		entries.forEach(function(entry) {
			if (entry.isIntersecting) {
				$(".infopage_contact").addClass("start");
			} else {
				// 요소가 아직 아래에 있을 때만 해제
				if (entry.boundingClientRect.top > window.innerHeight / 2) {
					$(".infopage_contact").removeClass("start");
				}
			}
		});
	}, {
		threshold: 0.5  // 요소의 50%가 보일 때 트리거
	});

	const contactTarget = document.querySelector('.infopage_contact');
	if (contactTarget) observerContact.observe(contactTarget);
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


@push('scripts')
<script src="{{ asset('js/faq-accordion.js') }}"></script>
<script src="{{ asset('js/marquee.js') }}"></script>
@endpush
@endsection