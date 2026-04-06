@extends('layouts.app')
@section('title'){{ $gName }} | {{ $sName }}@endsection
@section('gName', $gName)
@section('sName', $sName)
@section('description', '27년 경력 홈페이지코리아가 다양한 학회 홈페이지 제작 경험으로 우리 학회에 꼭 맞게 설계합니다.')
@section('keywords', '학회 홈페이지 제작, 홈페이지 제작 업체')

@section('content')
<main class="sub_contents_wrap infopage_wrap">

	<section class="infopage_head industry_head main_service" aria-labelledby="industry-head-title" data-header="dark">
		<div class="inner">
			<h1 id="industry-head-title" class="mojo_aos">학회/협회 홈페이지 제작, 학술 생태계를 이해하는 <br class="pc_vw">파트너 홈페이지코리아에게 맡기세요</h1>
			<p class="tb mojo_aos"><strong>의료, 공학, 법조계 등 학회/협회 웹사이트 구축을 경험한</strong> <br class="pc_vw">홈페이지코리아가 꼭 맞는 시스템을 설계합니다. </p>
			<div class="btns flex_center mojo_aos">
				<a href="/portfolio/" class="btn_link slim">포트폴리오 보러가기</a>
				<a href="/contact/" class="btn_link slim">프로젝트 문의하기</a>
			</div>
			<div class="flex_center mojo_aos">
				<div class="img"><img src="/images/img_industry_b01.jpg" alt=""></div>
			</div>
		</div>
		<div class="marquee_banner_wrap mojo_aos">
			<div class="marquee_inbox">
				<div class="marquee_banner" id="marquee_banner_b"></div>
			</div>
		</div>
		<div class="inner bg_round_start" data-aos="fade-up" data-aos-offset="200">
			<div class="bg_round"><div class="in_gradient"></div></div>
			<div class="problem service">
				<p class="tit_label" data-aos="fade-up">PROBLEM</p>
				<h2 id="industry-problem-title" data-aos="fade-up">학회/협회 홈페이지 제작, <br class="pc_vw"><strong> 이런 고민 때문에 시작도 못하고 있나요?</strong></h2>
				<ul class="problem_list" data-aos="fade-up">
					<li>
						<h3>어떤 홈페이지 제작 업체에 맡겨야 할지 모르겠어요 <img src="/images/emoji_sell.png" alt="" aria-hidden="true"></h3>
						<p>학회 특유의 회원 등급 관리, 학술대회 운영, 논문 투고 시스템을 이해하는 업체를 찾기 어렵습니다.</p>
					</li>
					<li>
						<h3>서버, 도메인, 호스팅 등 전문 용어가 너무 어려워요 <img src="/images/emoji_whirl.png" alt="" aria-hidden="true"></h3>
						<p>학회 운영만으로 바쁜데, 기술 용어까지 공부하기 벅찹니다. 쉽게 설명해주는 업체가 필요해요.</p>
					</li>
					<li>
						<h3>우리 학회에 맞는 시스템을 만들고 싶어요</h3>
						<p>우리 학회 운영 방식에 맞는 맞춤형 시스템이 필요합니다. <img src="/images/emoji_soft.png" alt="" aria-hidden="true"></p>
					</li>
				</ul>
				<div class="dots" aria-hidden="true"><i class="t"></i><i class="m"></i><i class="b"></i></div>
			</div>
		</div>
	</section>
	
	<section class="infopage_solution industry_solution bg_white" aria-labelledby="industry-solution-title" data-header="light">
		<div class="inner">
			<p class="tit_label">SOLUTION</p>
			<h2 id="industry-solution-title">학술 생태계를 이해하는 홈페이지코리아가 <br class="pc_vw"><strong>시작부터 끝까지 책임집니다.</strong></h2>
			<ul class="solution_list">
				<li class="i_b1">
					<object data="/images/icon_industry_b01.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>27년 이상 <br class="pc_vw">학회 전문 홈페이지 구축 경험</h3>
					<p>심장학회, 대한안과의사회, 고분자학회 등 <br class="pc_vw">00건 이상의 학회 홈페이지 제작 경험이 있습니다.</p>
				</li>
				<li class="i_b2">
					<object data="/images/icon_industry_b02.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>학회 전문 PM과의 <br class="pc_vw">커뮤니케이션</h3>
					<p>학회 전문 PM이 쉬운 언어로 설명하고, <br class="pc_vw">프로젝트를 성공으로 이끕니다.</p>
				</li>
				<li class="i_b3">
					<object data="/images/icon_industry_b03.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>학회 업무 프로세스 <br class="pc_vw">시스템화</h3>
					<p>템플릿에 끼워 맞추지 않고, <br class="pc_vw">우리 학회 운영 방식에 맞는 시스템을 구축합니다.</p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_why industry_why" aria-labelledby="industry-why-title" data-header="dark">
		<div class="inner">
			<p class="tit_label">WHY HOMEPAGEKOREA?</p>
			<h2 id="industry-why-title">왜 학회/협회가 <br class="pc_vw"><strong>홈페이지코리아를 선택했을까요?</strong></h2>
			<ul class="why_list">
				<li>
					<div class="imgfit"><img src="/images/industry_way_b01.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>학회를 이해하는 <br class="pc_vw"><strong>전문 PM</strong></h3>
						<p><strong>학회 홈페이지 제작을 다수 경험한 전문 PM</strong>이 우리 학회의 <strong>운영 방식을 이해</strong>하고, <strong>필요한 기능을 먼저 제안</strong>합니다.</p>
					</div>
				</li>
				<li>
					<div class="imgfit"><img src="/images/industry_way_b02.webp" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>학회 백엔드 시스템 <br class="pc_vw"><strong>설계 전문성</strong></h3>
						<p>회원 등급별 권한 관리, 학술대회 등록, <br class="pc_vw">수료증 발급, 시설 예약 시스템까지. <br/>학회 운영에 필요한 <strong>복잡한 백엔드 시스템을 설계하고 구축</strong>합니다.</p>
					</div>
				</li>
				<li>
					<div class="imgfit"><img src="/images/industry_way_b03.webp" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3><strong>합리적 비용</strong>과 <br class="pc_vw"><strong>명확한 일정</strong> 관리</h3>
						<p><strong>타사 대비 20~30% 합리적인 비용</strong>으로 진행하며, <br class="pc_vw">WBS 기반 일정 관리로 <strong>약속된 일정을 준수</strong>합니다. </p>
					</div>
				</li>
				<li>
					<div class="imgfit"><img src="/images/industry_way_a04.webp" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>SEO/GEO까지 고려한 <br class="pc_vw"><strong>웹사이트 구조화</strong></h3>
						<p>디자인만 예쁜 웹사이트가 아니라, <br class="pc_vw"><strong>검색 엔진과 AI에 최적화된 구조로 설계</strong>하여 <br class="pc_vw">회원들이 <strong>검색을 통해 유입</strong>될 수 있도록 합니다.</p>
					</div>
				</li>
			</ul>
		</div>
		
		<div class="portfolio_marquee">
			<ul class="list">
				@include('partials.service-portfolio-marquee-list', ['portfolioItems' => $industryPortfolioItems])
			</ul>
		</div>
		
		<div class="flex_center">
			<a href="/portfolio?category=학회%2F협회" class="btn_link slim white">학회/협회 개발 사례 더보기</a>
		</div>
	</section>
	
	<section class="infopage_expertise industry_expertise" aria-labelledby="industry-how-title" data-header="dark">
		<div class="inner">
			<p class="tit_label">HOW</p>
			<h2 id="industry-how-title" aria-hidden="true"><strong>학회/협회 홈페이지 제작,</strong> 이렇게 진행됩니다.</h2>
			<ul class="expertise_list step_list">
				<li class="i_a1">
					<object data="/images/icon_expertise_b01.svg" type="image/svg+xml" aria-hidden="true"></object>
					<span>STEP 01</span>
					<h3>요구사항 분석</h3>
					<ul>
						<li>업무 현장 방문 또는 온라인 미팅을 통해 실제 업무 흐름 관찰</li>
						<li>요구사항 정리</li>
					</ul>
				</li>
				<li class="i_a2">
					<object data="/images/icon_expertise_b02.svg" type="image/svg+xml" aria-hidden="true"></object>
					<span>STEP 02</span>
					<h3>맞춤 제안서 및 견적 제공</h3>
					<ul>
						<li>공공기관에 맞는 기능 제안</li>
						<li>견적서 제공</li>
					</ul>
				</li>
				<li class="i_a3">
					<object data="/images/icon_expertise_b03.svg" type="image/svg+xml" aria-hidden="true"></object>
					<span>STEP 03</span>
					<h3>기획 및 디자인</h3>
					<ul>
						<li>화면 구조(와이어프레임) 공유</li>
						<li>확정 후 디자인 진행</li>
					</ul>
				</li>
				<li class="i_a4">
					<object data="/images/icon_expertise_b04.svg" type="image/svg+xml" aria-hidden="true"></object>
					<span>STEP 04</span>
					<h3>개발 및 배포</h3>
					<ul>
						<li>개발 및 QC</li>
						<li>홈페이지 배포</li>
					</ul>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_review industry_review bg_white" aria-labelledby="industry-review-title" data-header="light">
		<div class="line_wrap">
			<i class="t1" aria-hidden="true"></i><i class="b1" aria-hidden="true"></i>
			<svg class="line_svg" aria-hidden="true"></svg>
		</div>
		<div class="inner">
			<p class="tit_label">REVIEW</p>
			<h2 id="industry-review-title">학회·협회를 경험해본 홈페이지 제작 업체와 <br class="pc_vw"><strong>해보지 않은 업체는 결과가 다릅니다.</strong></h2>
			<ul class="review_list">
				<li>
					<h3 class="sound_only">학회</h3>
					<div class="flex_tit">
						<h4>학술대회 등록 과정이 복잡하고 예외 사항도 많아서 개발 중 오류가 발생할까 걱정이 컸습니다. 하지만 <strong>홈페이지코리아는 우리 학회의 업무 흐름을 정확히 	이해</strong>하고, <strong>요구사항을 체계적으로 문서화</strong>해주셨습니다.<br/>
						덕분에 <strong>개발이 원활하게 진행</strong>되었고, 예외 상황까지 <strong>완벽하게 반영된 시스템</strong>을 받았습니다.</h4>
					</div>
					<p><i><img src="/images/icon_review_logo_b3.svg" alt="" aria-hidden="true"></i>고분자학회<span aria-hidden="true">학회</span></p>
				</li>
				<li>
					<h3 class="sound_only">학회</h3>
					<div class="flex_tit">
						<h4>매년 수천 명이 참가하는 대규모 학술대회를 엑셀과 이메일로 관리하는 데 한계를 느꼈습니다. <br/>
						홈페이지코리아가 <strong>등록-결제-프로그램 안내-수료증 발급까지 전 과정을 자동화</strong>해주셔서, <strong>운영 효율이 3배 이상 향상</strong>되었고 담당자 업무 부담도 크게 줄었습니다.</h4>
					</div>
					<p><i><img src="/images/icon_review_logo_i1.svg" alt="" aria-hidden="true"></i>대한심초음파학회<span aria-hidden="true">학회</span></p>
				</li>
				<li>
					<h3 class="sound_only">협회</h3>
					<div class="flex_tit">
						<h4>회원 정보, 교육 신청, 지원 사업이 각각 분리되어 있어 데이터를 여러 번 입력해야 했습니다. 홈페이지코리아가 <strong>WBI-net 내부 시스템을 구축</strong>해 <strong>모든 데이터를 통합 	관리</strong>하게 되면서, <strong>담당자 업무 시간이 40% 단축</strong>되었습니다.</h4>
					</div>
					<p><i><img src="/images/icon_review_logo_i2.svg" alt="" aria-hidden="true"></i>지질동맥경화학회<span aria-hidden="true">협회</span></p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_contact industry_contact page_contact" aria-label="industry-contact-title" data-header="dark">
		<div class="inner">
			<p class="tit_label sound_only">CTA</p>
			<h2 id="industry-contact-title" class="port_tit"><strong>학회/협회 홈페이지 제작,</strong> <br class="pc_vw">경험 많은 홈페이코리아와 상담하세요</h2>
			<div class="btns flex_center">
				<a href="/portfolio/" class="btn_link">포트폴리오 보러가기</a>
				<a href="/contact/" class="btn_link">프로젝트 문의하기</a>
			</div>
		</div>
	</section>

</main>

<script>
$(document).ready(function(){
// marquee
	initMarquee("#marquee_banner_b", MARQUEE_DATA.b);
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
				$list.find("li.clone").remove();
				cloned = false;
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
// step_list
    const $items   = $(".expertise_list.step_list > li");
    const interval = 2000;
    let current    = 0;
    let timer      = null;
    let started    = false;
    function stepNext() {
        $items.removeClass("on");
        $items.eq(current).addClass("on");
        current = (current + 1) % $items.length;
    }
    function startStep() {
        if (started) return;
        started = true;
        stepNext();
        timer = setInterval(stepNext, interval);
    }
    function checkStepList() {
        if (started) return;
        const $list     = $(".expertise_list.step_list");
        if (!$list.length) return;
        const listBottom = $list.offset().top + $list.outerHeight();
        const viewBottom = $(window).scrollTop() + $(window).height();
        if (viewBottom >= listBottom) {
            startStep();
        }
    }
    $(window).on("scroll", checkStepList);
    checkStepList();
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
// review_list
	function equalReviewHeight() {
		const $items = $(".review_list .flex_tit");
		$items.css("height", ""); // 초기화
		if ($(window).width() >= 768) {
			let maxH = 0;
			$items.each(function () {
				maxH = Math.max(maxH, $(this).outerHeight());
			});
			$items.css("height", maxH + "px");
		}
	}
	$(window).on("load resize", equalReviewHeight);
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
<script src="{{ asset('js/marquee.js') }}"></script>
@endpush
@endsection