@extends('layouts.app')
@section('title'){{ $gName }} | {{ $sName }}@endsection
@section('gName', $gName)
@section('sName', $sName)
@section('description', '아이디어를 현실로 만드는 앱 개발 서비스. 1,100개 조직이 선택한 홈페이지코리아가 27년 경험으로 기획·개발·스토어 출시·운영까지 책임집니다.')
@section('keywords', '앱 개발, 앱 개발 업체')
@section('sga_plus')
@include('partials.service-faq-sga-jsonld', ['faqItems' => $faqItems])
@endsection

@section('content')
<main class="sub_contents_wrap infopage_wrap">

	<section class="infopage_head service_head main_service" aria-labelledby="service-head-title" data-header="dark">
		<div class="inner">
			<h1 id="service-head-title" class="mojo_aos">앱 개발, 맞춤형 설계부터 스토어 출시까지 홈페이지코리아가 끝까지 책임집니다.</h1>
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
				<h2 id="service-problem-title" data-aos="fade-up">앱 개발, 아이디어를 어떻게 실현해야 할지<br class="pc_vw"> <strong>막막하신가요?</strong></h2>
				<ul class="problem_list" data-aos="fade-up">
					<li>
						<h3>앱 스토어 심사 거절로 런칭이 계속 늦어져요. <img src="/images/emoji_tears.png" alt="" aria-hidden="true"></h3>
						<p>애플과 구글의 까다로운 심사 기준을 모른 채 개발하면, 반복되는 반려로 비즈니스 일정에 큰 차질이 생깁니다.</p>
					</li>
					<li>
						<h3>앱 개발, 배포 절차가 너무 생소하고 복잡합니다. <img src="/images/emoji_sad.png" alt="" aria-hidden="true"></h3>
						<p>기업용 개발자 계정 생성, 해외기업식별번호(D-U-N-S) 발급 등 기술 외적인 행정 절차에서 시간만 허비하는 경우가 많습니다.</p>
					</li>
					<li>
						<h3>운영 중인 웹 시스템과 연동이 고민입니다. <img src="/images/emoji_anger.png" alt="" aria-hidden="true"></h3>
						<p>기존의 웹 서비스와 앱 간 데이터가 실시간으로 연동되지 않으면, 관리 포인트만 늘어나 운영 효율이 떨어지게 됩니다.</p>
					</li>
				</ul>
				<div class="dots" aria-hidden="true"><i class="t"></i><i class="m"></i><i class="b"></i></div>
			</div>
		</div>
	</section>
	
	<section class="infopage_solution service_solution bg_white" aria-labelledby="service-solution-title" data-header="light">
		<div class="inner">
			<p class="tit_label">SOLUTION</p>
			<h2 id="service-solution-title">비즈니스 모델을 이해하는 <br class="pc_vw"><strong>앱 개발 파트너는 다릅니다.</strong></h2>
			<ul class="solution_list">
				<li class="i_f1">
					<object data="/images/icon_solution_f01.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>승인 리스크를 차단하는 전략적 개발</h3>
					<p>수많은 프로젝트 경험으로 축적된 '심사 대응 매뉴얼'을 바탕으로, 기획 단계부터 가이드라인을 준수하여 <br class="pc_vw">약속된 날짜에 정확히 출시합니다.</p>
				</li>
				<li class="i_f2">
					<object data="/images/icon_solution_f02.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>준비부터 런칭까지 완벽 가이드</h3>
					<p>계정 생성부터 결제 심사, 본인인증(PASS/카카오) API 연동까지, 고객사가 기술적 절차로 에너지를 낭비하지 않도록 <br class="pc_vw">모든 과정을 상세히 안내합니다.</p>
				</li>
				<li class="i_f3">
					<object data="/images/icon_solution_f03.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>비즈니스에 최적화된 기술 스택 제안</h3>
					<p>빠른 검증을 위한 하이브리드(Flutter, React Native)부터 고성능 네이티브 앱까지, 예산과 확장성을 고려한 <br class="pc_vw">가장 합리적인 구조를 설계합니다.</p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_how service_how" aria-labelledby="service-how-title" data-header="dark">
		<div class="inner">
			<p class="tit_label">HOW</p>
			<h2 id="service-how-title">사용자 경험을 극대화하는<br class="pc_vw"> <strong>홈페이지코리아의 핵심 기술</strong></h2>
			<ul class="how_list p_large">
				<li class="i_f1">
					<div class="imgfit"><img src="/images/img_service_how_f01.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_f01.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3>실시간 <strong>웹앱 연동 시스템</strong></h3>
						<p>서버-앱 간 <strong>안정적인 푸시(Push) 알림</strong>과 <br class="pc_vw">웹 서비스와의 완벽한 데이터 동기화 구현</p>
					</div>
				</li>
				<li class="i_f2">
					<div class="imgfit"><img src="/images/img_service_how_f02.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_f02.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3>고도화된 <strong>기능 구현</strong></h3>
						<p><strong>사용자 위치 기반</strong>(GPS) <strong>서비스, <br class="pc_vw">음성 인터페이스</strong>(STT/TTS), <strong>기기별 카메라 최적화 제어</strong></p>
					</div>
				</li>
				<li class="i_f3">
					<div class="imgfit"><img src="/images/img_service_how_f03.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_f03.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3>안정적인 <strong>인앱 결제 모듈</strong></h3>
						<p>복잡한 결제 프로세스도 데이터 누락 없이 처리하는<br class="pc_vw"> <strong>강력한 보안 및 결제 시스템 구축</strong></p>
					</div>
				</li>
				<li class="i_f4">
					<div class="imgfit"><img src="/images/img_service_how_f04.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_f04.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3>앱 배포 전 <strong>전수 테스트</strong></h3>
						<p><strong>TestFlight 및 테스트 트랙을 활용</strong>해 <br class="pc_vw">안드로이드와 아이폰 <strong>기기별 오류를 사전에 완벽 차단</strong></p>
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
			<h2 id="service-review-title"><strong>성공적인 앱 개발</strong> <br class="pc_vw">홈페이지코리아와 함께합니다.</h2>
		</div>
		<div class="portfolio_marquee">
			<ul class="list">
				@forelse($mobileAppPortfolioItems ?? collect() as $item)
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
			<h2 id="service-contact-title" class="port_tit"><strong>준비부터 출시, 안정적인 운영까지</strong> <br class="pc_vw">성공하는 앱 개발, 홈페이지코리아와 시작하세요.</h2>
			<div class="btns flex_center">
				<a href="/portfolio/" class="btn_link">포트폴리오 보러가기</a>
				<a href="/contact/" class="btn_link">프로젝트 문의하기</a>
			</div>
		</div>
	</section>

	<section class="infopage_faq service_faq" aria-label="service-faq-title" data-header="dark">
		<div class="inner">
			<p class="tit_label">FAQ</p>
			<h2 id="service-faq-title"><strong>자주 묻는 질문</strong></h2>
			<ul class="faq_list">
				@include('partials.public-faq-list', ['faqItems' => $faqItems, 'idPrefix' => 'service-faq-mobile', 'variant' => 'service'])
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
		const $banner = $(".portfolio_marquee");
		const $list   = $banner.find(".list").first();
		if (!$list.length || !$list.children("li").length) {
			return;
		}
		const speed = 1;
		let posX    = 0;
		let isPaused  = false;
		let totalWidth  = 0;
		let isStopped   = false;
		let cloned    = false;
		let marqueeResizeTimer;

		function measure() {
			totalWidth = 0;
			$list.children("li:not(.clone)").each(function () {
				totalWidth += $(this).outerWidth(true);
			});

			const bannerWidth = $banner.outerWidth();

			if (totalWidth < bannerWidth) {
				isStopped = true;
				posX = 0;
				$list.css("transform", "translateX(0px)");
				$banner.addClass("stop");
			} else {
				isStopped = false;
				$banner.removeClass("stop");
				if (!cloned) {
					$list.children("li:not(.clone)").clone().addClass("clone").attr("aria-hidden", "true").appendTo($list);
					cloned = true;
				}
			}
		}

		measure();
		$(window).on("load", measure);
		$(window).on("resize", function () {
			clearTimeout(marqueeResizeTimer);
			marqueeResizeTimer = setTimeout(measure, 100);
		});

		function marqueeLoop() {
			if (!isPaused && !isStopped && totalWidth > 0) {
				posX -= speed;
				// 원본 너비만큼 이동하면 초기화 → 복제본이 이어받아 끊김 없음
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