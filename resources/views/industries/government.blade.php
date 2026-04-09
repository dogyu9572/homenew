@extends('layouts.app')
@section('title'){{ $gName }} | {{ $sName }}@endsection
@section('gName', $gName)
@section('sName', $sName)
@section('description', '공공기관 웹사이트 구축을 위한 맞춤형 솔루션. 전자정부 프레임워크부터 웹접근성 인증까지, 27년 경험의 홈페이지코리아가 책임집니다.')
@section('keywords', '전자정부 프레임워크, 웹접근성, 개인정보 유출, 전자정부법, 소프트웨어 개발보안 가이드, 홈페이지 제작 업체')

@section('content')
<main class="sub_contents_wrap infopage_wrap">

	<section class="infopage_head industry_head main_service" aria-labelledby="industry-head-title" data-header="dark">
		<div class="inner">
			<h1 id="industry-head-title" class="mojo_aos">공공기관 홈페이지 제작, <br class="pc_vw">홈페이지코리아가 함께합니다. </h1>
			<p class="tb mojo_aos"><strong>기술 요구사항이 중요한 공공기관 홈페이지</strong>, <br class="pc_vw">홈페이지코리아에게 맡기세요.</p>
			<div class="btns flex_center mojo_aos">
				<a href="/portfolio/" class="btn_link slim">포트폴리오 보러가기</a>
				<a href="{{ route('contact.contact', ['source_type' => 'industries', 'source_url' => url()->current(), 'source_title' => $sName]) }}" class="btn_link slim">프로젝트 문의하기</a>
			</div>
			<div class="flex_center mojo_aos">
				<div class="img"><img src="/images/img_industry_c01.jpg" alt=""></div>
			</div>
		</div>
		<div class="marquee_banner_wrap mojo_aos">
			<div class="marquee_inbox">
				<div class="marquee_banner" id="marquee_banner_c"></div>
			</div>
		</div>
		<div class="inner bg_round_start" data-aos="fade-up" data-aos-offset="200">
			<div class="bg_round"><div class="in_gradient"></div></div>
			<div class="problem service">
				<p class="tit_label" data-aos="fade-up">PROBLEM</p>
				<h2 id="industry-problem-title" data-aos="fade-up">공공기관 담당자님, <br class="pc_vw"><strong> 이런 고민 있으신가요?</strong></h2>
				<ul class="problem_list" data-aos="fade-up">
					<li>
						<h3>전자정부프레임워크 기반 개발할 수 있는 업체가 필요해요 <img src="/images/emoji_worry.png" alt="" aria-hidden="true"></h3>
						<p>전자정부법에 따라 공공기관은 전자정부 프레임워크 기반 구축이 필수입니다.</p>
					</li>
					<li>
						<h3>웹접근성 인증부터 웹표준까지, 법적 요구사항이 너무 많습니다 <img src="/images/emoji_quake.png" alt="" aria-hidden="true"></h3>
						<p>공공기관 웹사이트는 다양한 법규를 준수해야 하는데, 이런 상황을 이해하는 업체가 필요해요.</p>
					</li>
					<li>
						<h3>기술 용어가 어렵고, 서류 작업이 복잡합니다 <img src="/images/emoji_whirl.png" alt="" aria-hidden="true"></h3>
						<p>홈페이지 제작 업체에서 소통하는 기술 용어가 어렵게 느껴지고, 복잡한 서류 작업으로 프로젝트 진행이 부담스럽습니다. </p>
					</li>
				</ul>
				<div class="dots" aria-hidden="true"><i class="t"></i><i class="m"></i><i class="b"></i></div>
			</div>
		</div>
	</section>
	
	<section class="infopage_solution industry_solution bg_white" aria-labelledby="industry-solution-title" data-header="light">
		<div class="inner">
			<p class="tit_label">SOLUTION</p>
			<h2 id="industry-solution-title">공공기관 프로젝트만 27년 이상 경험한 <br class="pc_vw"><strong>홈페이지코리아는 다릅니다.</strong></h2>
			<ul class="solution_list">
				<li class="i_c1">
					<object data="/images/icon_industry_c01.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>전자정부 프레임워크 기반 <br class="pc_vw">표준 구축</h3>
					<p>기관의 보안 정책과 업무 환경에 맞춰 <br class="pc_vw">코어 구조부터 설계합니다.</p>
				</li>
				<li class="i_c2">
					<object data="/images/icon_industry_c02.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>웹접근성 및 웹표준 등 <br class="pc_vw">법적 가이드라인 준수</h3>
					<p>공공기관 홈페이지에 요구되는 <br class="pc_vw">모든 법적 가이드라인을 이해하고 준수합니다.</p>
				</li>
				<li class="i_c3">
					<object data="/images/icon_industry_c03.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>공공기관 맞춤 <br class="pc_vw">커뮤니케이션</h3>
					<p>공공기관 프로젝트를 수행하며 쌓은 경험으로 전문 용어는 쉽게 소통하고, 서류 작업 등 복잡한 절차는 명확하게 진행합니다.</p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_why industry_why" aria-labelledby="industry-why-title" data-header="dark">
		<div class="inner">
			<p class="tit_label">WHY HOMEPAGEKOREA?</p>
			<h2 id="industry-why-title">왜 공공기관이 <br class="pc_vw"><strong>홈페이지코리아를 선택했을까요?</strong></h2>
			<ul class="why_list">
				<li>
					<div class="imgfit"><img src="/images/industry_way_c01.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3><strong>1999년</strong>부터 쌓아온 <br class="pc_vw"><strong>공공기관 프로젝트 노하우</strong></h3>
						<p>공공기관마다 다른 보안 정책, 예산 집행 절차, <br class="pc_vw"><strong>승인 프로세스를 정확히 이해</strong>하고 있어 <br class="pc_vw">일정 지연 없이 <strong>안정적으로 프로젝트를 완수</strong>합니다.</p>
					</div>
				</li>
				<li>
					<div class="imgfit"><img src="/images/industry_way_c02.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>공공기관을 이해하는 <br class="pc_vw"><strong>전문 PM</strong></h3>
						<p><strong>공공기관 홈페이지 제작을 다수 경험한 <br class="pc_vw">전문 PM</strong>이 전 과정을 관리하며, <br class="pc_vw">담당자가 이해할 때까지 <strpmg>충분히 소통</strpmg>합니다.</p>
					</div>
				</li>
				<li>
					<div class="imgfit"><img src="/images/industry_way_c03.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>전자정부 프레임워크 <br class="pc_vw"><strong>전문 개발팀</strong></h3>
						<p>전자정부 프레임워크를 단순히 사용하는 것이 아니라, <br class="pc_vw"><strong>전자정부법과 관련 지침에 맞춰</strong> 기관의 업무 프로세스와 <br class="pc_vw">보안 요구사항을 반영한 <strong>맞춤형 설계를 제공</strong>합니다. </p>
					</div>
				</li>
				<li>
					<div class="imgfit"><img src="/images/industry_way_c04.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>개인정보 유출 방지를 위한 <br class="pc_vw"><strong>철저한 보안</strong></h3>
						<p>담당자의 책임 문제로 이어질 수 있는 개인정보 유출 사고, <br class="pc_vw"><strong>홈페이지코리아가 위험을 최소화</strong>합니다.</p>
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
			<a href="/portfolio?category=공공기관" class="btn_link slim white">공공기관 개발 사례 더보기</a>
		</div>
	</section>
	
	<section class="infopage_expertise industry_expertise" aria-labelledby="industry-how-title" data-header="dark">
		<div class="inner">
			<p class="tit_label">HOW</p>
			<h2 id="industry-how-title" aria-hidden="true"><strong>공공기관 홈페이지 제작,</strong> <br class="pc_vw">이렇게 진행됩니다.</h2>
			<ul class="expertise_list step_list">
				<li class="i_b1">
					<object data="/images/icon_expertise_b01.svg" type="image/svg+xml" aria-hidden="true"></object>
					<span>STEP 01</span>
					<h3>요구사항 분석</h3>
					<ul>
						<li>업무 현장 방문 또는 온라인 미팅을 통해 실제 업무 흐름 관찰</li>
						<li>요구사항 정리</li>
						<li>맞춤형 제안서 및 견적서 제공</li>
					</ul>
				</li>
				<li class="i_b2">
					<object data="/images/icon_expertise_b02.svg" type="image/svg+xml" aria-hidden="true"></object>
					<span>STEP 02</span>
					<h3>서류 작성 지원 및 계약</h3>
					<ul>
						<li>사업수행계획서, 기술제안요구서 문서 작성 지원</li>
						<li>정보화사업 보안성 검토 서류 작성 지원</li>
						<li>협의 후 계약</li>
					</ul>
				</li>
				<li class="i_b3">
					<object data="/images/icon_expertise_b03.svg" type="image/svg+xml" aria-hidden="true"></object>
					<span>STEP 03</span>
					<h3>기획 및 디자인</h3>
					<ul>
						<li>화면 구조(와이어프레임) 공유</li>
						<li>확정 후 디자인 진행</li>
					</ul>
				</li>
				<li class="i_b4">
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
			<h2 id="industry-review-title"><strong>이미 많은 공공기관이</strong> <br class="pc_vw">홈페이지코리아를 선택했습니다</h2>
			<ul class="review_list">
				<li>
					<h3 class="sound_only">공공기관</h3>
					<div class="flex_tit">
						<h4>전자정부 프레임워크 기반 구축과 웹접근성 인증이 필수였지만, 이전 업체는 공공기관 요구사항을 제대로 이해하지 못해 프로젝트가 지연되었습니다. 홈페이지코리아는 <strong>전자정부법과 관련 지침을 정확히 	숙지</strong>하고 있어 <strong>초기 기획 단계부터 법적 요구사항을 빠짐없이 반영</strong>해주셨습니다.</h4>
					</div>
					<p><i><img src="/images/icon_review_logo_j1.svg" alt="" aria-hidden="true"></i>환경부<span aria-hidden="true">공공기관</span></p>
				</li>
				<li>
					<h3 class="sound_only">공공기관</h3>
					<div class="flex_tit">
						<h4>DMZ 구성과 내외부망 분리 등 복잡한 보안 환경 때문에 일반 웹 개발 업체와는 협업이 어려웠습니다. 홈페이지코리아는 <strong>자체 모듈 기반 개발</strong>로 외부 서비스 의존 없이 <strong>우리 기관의 보안 정책에 맞춰 시스템을 유연하게 배치</strong>해주셨습니다.</h4>
					</div>
					<p><i><img src="/images/icon_review_logo_j2.png" alt="" aria-hidden="true"></i>과학기술정보통신부<span aria-hidden="true">공공기관</span></p>
				</li>
				<li>
					<h3 class="sound_only">공공기관</h3>
					<div class="flex_tit">
						<h4>담당자가 IT 전문가가 아니다 보니 업체에서 설명하는 기술 용어를 이해하기 어려워 소통에 어려움을 겪었습니다. 홈페이지코리아는 쉬운 언어로 설명해주셨고, <strong>예산 집행부터 감리 대응까지 복잡한 	절차도 단계별로 명확하게 안내</strong>해주셨습니다. 덕분에 <strong>프로젝트가 막힘 없이 진행</strong>되었고, <strong>최종 결과물도 매우 만족</strong>스러웠습니다.</h4>
					</div>
					<p><i><img src="/images/icon_review_logo_j3.png" alt="" aria-hidden="true"></i>국립체육진흥공단<span aria-hidden="true">공공기관</span></p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_contact industry_contact page_contact" aria-label="industry-contact-title" data-header="dark">
		<div class="inner">
			<p class="tit_label sound_only">CTA</p>
			<h2 id="industry-contact-title" class="port_tit"><strong>공공기관 홈페이지 제작</strong> <br class="pc_vw">경험 많은 홈페이지코리아와 상의하세요</h2>
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
    initMarquee("#marquee_banner_c", MARQUEE_DATA.c);
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