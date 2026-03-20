@extends('layouts.app')
@section('title'){{ $gName }} | {{ $sName }}@endsection
@section('gName', $gName)
@section('sName', $sName)
@section('description', '공공기관 웹사이트 구축을 위한 맞춤형 솔루션. 전자정부 프레임워크부터 웹접근성 인증까지, 27년 경험의 홈페이지코리아가 책임집니다.')
@section('keywords', '전자정부 프레임워크(4,466), 웹접근성(1.7천), 개인정보 유출(4,4천), 전자정부법(2,380), 소프트웨어 개발보안 가이드(530), 홈페이지 제작 업체')

@section('content')
<main class="sub_contents_wrap infopage_wrap">

	<section class="infopage_head industry_head main_service" aria-labelledby="industry-head-title">
		<div class="inner">
			<h1 id="industry-head-title">공공기관 홈페이지 제작, <br class="pc_vw">홈페이지코리아가 함께합니다. </h1>
			<p class="tb"><strong>기술 요구사항이 중요한 공공기관 홈페이지</strong>, <br class="pc_vw">홈페이지코리아에게 맡기세요.</p>
			<div class="btns flex_center">
				<a href="/portfolio/" class="btn_link slim">포트폴리오 보러가기</a>
				<a href="/contact/" class="btn_link slim">프로젝트 문의하기</a>
			</div>
			<div class="flex_center">
				<div class="img"><img src="/images/img_industry_a01.jpg" alt=""></div>
			</div>
		</div>
		<div class="marquee_banner">
			<ul class="slide" aria-label="주요 고객사 목록">
				<li><img src="/images/main_service_01.svg" alt="United Nations"></li>
				<li><img src="/images/main_service_02.svg" alt="서울대학교 농생명과학공동기기원"></li>
				<li><img src="/images/main_service_03.svg" alt="파크랜드"></li>
				<li><img src="/images/main_service_04.svg" alt="국립스포츠박물관"></li>
				<li><img src="/images/main_service_05.svg" alt="KB부동산신탁"></li>
				<li><img src="/images/main_service_06.svg" alt="PARADISE CITY"></li>
				<li><img src="/images/main_service_07.svg" alt="CJ Innovation"></li>
				<li><img src="/images/main_service_08.svg" alt="한양대학교"></li>
				<li><img src="/images/main_service_09.svg" alt="세종대학교"></li>
			</ul>
		</div>
		<div class="inner">
			<div class="bg_gradient"></div>
			<div class="bg_round"></div>
			<div class="problem service">
				<p class="tit_label">PROBLEM</p>
				<h2 id="industry-problem-title"><strong>공공기관 담당자님,</strong><br class="pc_vw"> 이런 고민 있으신가요?</h2>
				<ul class="problem_list">
					<li>
						<h3>전자정부프레임워크 기반 개발할 수 있는 업체가 필요해요</h3>
						<p>전자정부법에 따라 공공기관은 전자정부 프레임워크 기반 구축이 필수입니다.</p>
					</li>
					<li>
						<h3>웹접근성 인증부터 웹표준까지, 법적 요구사항이 너무 많습니다</h3>
						<p>공공기관 웹사이트는 다양한 법규를 준수해야 하는데, 이런 상황을 이해하는 업체가 필요해요.</p>p>전자정부법에 따라 공공기관은 전자정부 프레임워크 기반 구축이 필수입니다.</p>
					</li>
					<li>
						<h3>기술 용어가 어렵고, 서류 작업이 복잡합니다</h3>
						<p>홈페이지 제작 업체에서 소통하는 기술 용어가 어렵게 느껴지고, 복잡한 서류 작업으로 프로젝트 진행이 부담스럽습니다.</p>
					</li>
				</ul>
				<div class="dots" aria-hidden="true"><i class="t"></i><i class="m"></i><i class="b"></i></div>
			</div>
		</div>
	</section>
	
	<section class="infopage_solution industry_solution bg_white" aria-labelledby="industry-solution-title">
		<div class="inner">
			<p class="tit_label">SOLUTION</p>
			<h2 id="industry-solution-title"><strong>공공기관 프로젝트만 27년 이상</strong> <br class="pc_vw">경험한 홈페이지코리아는 다릅니다</h2>
			<ul class="solution_list">
				<li class="i1">
					<h3>전자정부 프레임워크 기반 표준 구축</h3>
					<p>기관의 보안 정책과 업무 환경에 맞춰 코어 구조부터 설계합니다.</p>
				</li>
				<li class="i2">
					<h3>웹접근성 및 웹표준 등 법적 가이드라인 준수</h3>
					<p>공공기관 홈페이지에 요구되는 모든 법적 가이드라인을 이해하고 준수합니다.</p>
				</li>
				<li class="i3">
					<h3>공공기관 맞춤 커뮤니케이션</h3>
					<p>공공기관 프로젝트를 수행하며 쌓은 경험으로 전문 용어는 쉽게 소통하고, 서류 작업 등 복잡한 절차는 명확하게 진행합니다.</p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_why industry_why" aria-labelledby="industry-why-title">
		<div class="inner">
			<p class="tit_label">WHY HOMEPAGEKOREA?</p>
			<h2 id="industry-why-title">왜 공공기관이 <br class="pc_vw"><strong>홈페이지코리아를 선택했을까요?</strong></h2>
			<ul class="why_list">
				<li>
					<div class="imgfit"><img src="/images/industry_way_a01.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>1999년부터 쌓아온 <br class="pc_vw"><strong>공공기관 프로젝트 노하우</strong></h3>
						<p>공공기관마다 다른 보안 정책, 예산 집행 절차, 승인 <strong>프로세스를 정확히 이해</strong>하고 있어 일정 지연 없이 <strong>안정적으로 프로젝트를 완수</strong>합니다.</p>
						<a href="/portfolio/" class="btn_link">학회/협회 개발 사례 더보기</a>
					</div>
				</li>
				<li>
					<div class="imgfit"><img src="/images/industry_way_a02.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>공공기관을 이해하는 <br class="pc_vw"><strong>전문 PM</strong></h3>
						<p><strong>공공기관 홈페이지 제작을 다수 경험한 전문 PM</strong>이 전 과정을 관리하며, 담당자가 이해할 때까지 충분히 소통합니다.</p>
					</div>
				</li>
				<li>
					<div class="imgfit"><img src="/images/industry_way_a03.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>전자정부 프레임워크 <br class="pc_vw"><strong>전문 개발팀</strong></h3>
						<p>전자정부 프레임워크를 단순히 사용하는 것이 아니라, <strong>전자정부법과 관련 지침에 맞춰 기관의 업무 프로세스와 보안 요구사항을 반영한 맞춤형 설계</strong>를 제공합니다. </p>
					</div>
				</li>
				<li>
					<div class="imgfit"><img src="/images/industry_way_a04.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>개인정보 유출 방지를 위한 <br class="pc_vw"><strong>철저한 보안</strong></h3>
						<p>담당자의 책임 문제로 이어질 수 있는 개인정보 유출 사고, 홈페이지코리아가 <strong>위험을 최소화</strong>합니다.</p>
					</div>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_how industry_process" aria-labelledby="industry-process-title">
		<div class="inner">
			<p class="tit_label">Process</p>
			<h2 id="industry-process-title"><strong>공공기관 홈페이지 제작</strong>, 이렇게 진행됩니다</h2>
			<ul class="how_list">
				<li class="i1">
					<div class="imgfit"><img src="/images/service_how01.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>STEP 1 요구사항 분석</h3>
						<ul>
							<li>- 업무 현장 방문 또는 온라인 미팅을 통해 실제 업무 흐름 관찰</li>
							<li>- 요구사항 정리</li>
						</ul>
					</div>
				</li>
				<li class="i2">
					<div class="imgfit"><img src="/images/service_how02.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>STEP 2 맞춤 제안서 및 견적 제공</h3>
						<ul>
							<li>- 공공기관에 맞는 기능 제안</li>
							<li>- 견적서 제공</li>
						</ul>
					</div>
				</li>
				<li class="i3">
					<div class="imgfit"><img src="/images/service_how03.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>STEP 3 기획 및 디자인</h3>
						<ul>
							<li>- 화면 구조(와이어프레임) 공유</li>
							<li>- 확정 후 디자인 진행</li>
						</ul>
					</div>
				</li>
				<li class="i4">
					<div class="imgfit"><img src="/images/service_how02.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>STEP 4 개발 및 배포</h3>
						<ul>
							<li>- 개발 및 QC</li>
							<li>- 홈페이지 배포</li>
						</ul>
					</div>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_review industry_review bg_white" aria-labelledby="industry-review-title">
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
					<h4><strong>전자정부 프레임워크 기반 구축과 웹접근성 인증이 필수</strong>였지만, 이전 업체는 공공기관 요구사항을 제대로 이해하지 못해 프로젝트가 지연되었습니다. <br><br>홈페이지코리아는 전자정부법과 관련 지침을 정확히 숙지하고 있어 초기 <strong>기획 단계부터 법적 요구사항을 빠짐없이 반영</strong>해주셨습니다.</h4>
					<p><i><img src="/images/icon_review_logo_blank.svg" alt="" aria-hidden="true"></i>환경부<span aria-hidden="true">공공기관</span></p>
				</li>
				<li>
					<h3 class="sound_only">공공기관</h3>
					<h4>DMZ 구성과 내외부망 분리 등 <strong>복잡한 보안 환경</strong> 때문에 일반 웹 개발 업체와는 협업이 어려웠습니다. <br><br>홈페이지코리아는 자체 모듈 기반 개발로 외부 서비스 의존 없이 우리 기관의 <strong>보안 정책에 맞춰 시스템을 유연하게 배치</strong>해주셨습니다. </h4>
					<p><i><img src="/images/icon_review_logo_blank.svg" alt="" aria-hidden="true"></i>과학기술정보통신부<span aria-hidden="true">공공기관</span></p>
				</li>
				<li>
					<h3 class="sound_only">공공기관</h3>
					<h4>담당자가 IT 전문가가 아니다 보니 업체에서 설명하는 기술 용어를 이해하기 어려워 소통에 어려움을 겪었습니다. <br><br>홈페이지코리아는 쉬운 언어로 설명해주셨고, <strong>예산 집행부터 감리 대응까지 복잡한 절차도 단계별로 명확하게 안내</strong>해주셨습니다. 덕분에 프로젝트가 막힘 없이 진행되었고, 최종 결과물도 매우 만족스러웠습니다.</h4>
					<p><i><img src="/images/icon_review_logo_blank.svg" alt="" aria-hidden="true"></i>국립체육진흥공단<span aria-hidden="true">공공기관</span></p>
				</li>
			</ul>
			<div class="flex_center">
				<a href="/portfolio/" class="btn_link slim">홈페이지 제작 사례 더보기</a>
			</div>
		</div>
	</section>
	
	<section class="infopage_contact industry_contact page_contact" aria-label="industry-contact-title">
		<div class="inner">
			<p class="tit_label sound_only">CTA</p>
			<h2 id="industry-contact-title" class="port_tit"><strong>공공기관 홈페이지 제작 경험 많은</strong> <br class="pc_vw">홈페이지코리아와 상의하세요</h2>
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
    (function () {
        const $banner    = $(".infopage_head .marquee_banner");
        const $origSlide = $banner.find(".slide");
        const speed      = 2;
        let posX         = 0;
        let isPaused     = false;
        $banner.append($origSlide.clone().removeAttr("aria-label").attr("aria-hidden", "true"));
        const totalWidth = $origSlide.outerWidth(true);
        function marqueeLoop() {
            if (!isPaused) {
                posX -= speed;
                if (Math.abs(posX) >= totalWidth) posX = 0;
                $banner.find(".slide").css("transform", `translateX(${posX}px)`);
            }
            requestAnimationFrame(marqueeLoop);
        }
        marqueeLoop();
        /*$banner.on("mouseenter", function () { isPaused = true; })
               .on("mouseleave", function () { isPaused = false; });*/
    })();
// bg_round
    function bgRoundScroll() {
		const $mainIndustry = $(".infopage_head");
		const $bgRound      = $(".bg_round");
		const $marquee      = $(".infopage_head .marquee_banner");
		const scrollTop     = $(window).scrollTop();
		const windowHeight  = $(window).height();
		const serviceTop    = $mainIndustry.offset().top;
		const paddingTop    = parseInt($mainIndustry.css("padding-top"));
		const marqueeHeight = $marquee.outerHeight();
		const marqueeMargin = parseInt($marquee.css("margin-bottom"));
		const initY         = -(paddingTop + marqueeHeight + marqueeMargin);

		// bg_round가 화면에 보이기 시작하는 시점 기준
		const bgRoundTop   = $bgRound.offset().top;
		const scrollStart  = bgRoundTop - windowHeight;  // bg_round 상단이 뷰포트 하단에 닿는 시점
		const scrollEnd    = bgRoundTop;                  // bg_round 상단이 뷰포트 상단에 닿는 시점

		if (scrollTop >= scrollStart) {
			$mainIndustry.addClass("start");
		} else {
			$mainIndustry.removeClass("start");
		}

		const progress     = Math.min(Math.max((scrollTop - scrollStart) / (scrollEnd - scrollStart), 0), 1);
		const translateY   = initY + (-initY * progress);
		const scale        = 1 + 3 * progress;
		const brBottom     = 50 * (1 - progress);
		const borderRadius = `50% 50% ${brBottom}% ${brBottom}%`;

		if (scrollTop >= scrollStart) {
			$bgRound.css({ "transform": `translate(-50%, ${translateY}px) scale(${scale})`, "border-radius": borderRadius });
		} else {
			$bgRound.css({ "transform": `translate(-50%, ${initY}px) scale(1)`, "border-radius": "50%" });
		}
	}
	$(window).on("scroll", bgRoundScroll);
	bgRoundScroll();
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
});
</script>

@endsection