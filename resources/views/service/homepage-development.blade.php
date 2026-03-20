@extends('layouts.app')
@section('title'){{ $gName }} | {{ $sName }}@endsection
@section('gName', $gName)
@section('sName', $sName)
@section('description', '비즈니스를 성장시키는 기업 홈페이지 제작. 4,400개 조직이 선택한 홈페이지코리아가 27년 경험으로 귀사에 맞는 웹사이트를 설계합니다.')
@section('keywords', '홈페이지 제작, 기업 홈페이지 제작, 웹사이트 제작, 홈페이지 리뉴얼, 홈페이지 제작 업체')
@section('sga_plus')
,"mainEntity": [
    {
        "@@type": "Question",
        "name": "홈페이지 제작 비용은 어떻게 산정되나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "프로젝트 규모와 요구 기능에 따라 비용이 결정됩니다. 로그인 기능이 없는 기본 홈페이지도 가능하며, 회원 관리, 예약 시스템, ERP 연동 등 복잡한 기능이 추가될 경우 비용이 추가됩니다."
        }
    },
    {
        "@@type": "Question",
        "name": "홈페이지 제작 기간은 얼마나 걸리나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "평균 1.5개월(6주) 내에 구축이 가능하며, 프로젝트 규모에 따라 기간이 달라질 수 있습니다. 대형 프로젝트의 경우 6개월까지 소요될 수 있습니다."
        }
    },
    {
        "@@type": "Question",
        "name": "프로젝트 진행 중 추가 비용이 발생하나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "초기 합의된 요구사항 범위 내에서는 추가 비용이 발생하지 않습니다. 새로운 기능이 추가되거나 요구사항이 크게 변경될 경우 사전에 공수와 비용을 명확히 안내드린 후 진행합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "홈페이지 제작 후 유지보수는 필수인가요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "유지보수는 필수가 아닌 선택사항입니다. 현재 약 50여 개 기관·기업이 홈페이지코리아의 유지보수 서비스를 이용 중이며, 월 50만 원부터 시스템 규모와 요청 빈도에 따라 비용이 산정됩니다."
        }
    },
    {
        "@@type": "Question",
        "name": "우리 업종과 비슷한 프로젝트 경험이 있나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "홈페이지코리아는 27년간 4,400개 이상의 프로젝트를 진행하며 대기업, 공공기관, 학회/협회, 대학교, 의료기관 등 다양한 업종의 홈페이지를 제작했습니다."
        }
    }
]
@endsection

@section('content')
<main class="sub_contents_wrap infopage_wrap">

	<section class="infopage_head service_head main_service" aria-labelledby="service-head-title">
		<div class="inner">
			<h1 id="service-head-title">비즈니스를 성장시키는 홈페이지 제작<br/>홈페이지코리아에서는 가능합니다</h1>
			<p class="tb"><strong>비즈니스를 이해하고 설계하는 맞춤형 홈페이지 제작, </strong>홈페이지코리아와 상의하세요.</p>
			<div class="btns flex_center">
				<a href="/portfolio/" class="btn_link slim">포트폴리오 보러가기</a>
				<a href="/contact/" class="btn_link slim">프로젝트 문의하기</a>
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
				<h2 id="service-problem-title"><strong>수천만 원을 투자하는 홈페이지 개편, </strong><br/>이런 고민 때문에 망설이고 있나요?</h2>
				<ul class="problem_list">
					<li>
						<h3>우리 조직에 맞는 웹사이트 시스템을 구축하고 싶어요. 😥</h3>
						<p>단순한 템플릿으로는 우리 조직의 업무 프로세스를 홈페이지에 제대로 구현할 수 없어요.</p>
					</li>
					<li>
						<h3>예산과 일정은 늘어나는데, 원하는 결과물이 나올지 불안해요. ☹️</h3>
						<p>프로젝트 중간에 요구사항이 제대로 반영되지 않거나, 일정이 지연되면서 비용이 증가하는 경험. 많은 기업이 겪고 있습니다.</p>
					</li>
					<li>
						<h3>담당자가 바뀌어도 계속 사용할 수 있는 홈페이지가 필요해요. 😡</h3>
						<p>만들고 끝이 아닙니다. 담당자가 변경되어도, 정책이 바뀌어도 지속적으로 사용할 수 있는 구조가 필요합니다.</p>
					</li>
				</ul>
				<div class="dots" aria-hidden="true"><i class="t"></i><i class="m"></i><i class="b"></i></div>
			</div>
		</div>
	</section>
	
	<section class="infopage_solution service_solution bg_white" aria-labelledby="service-solution-title">
		<div class="inner">
			<p class="tit_label">SOLUTION</p>
			<h2 id="service-solution-title"><strong>고객의 비즈니스를 이해하고 업종과 조직에 맞는</strong> 홈페이지를 제작합니다</h2>
			<ul class="solution_list">
				<li class="i1">
					<h3>4,400건의 프로젝트 경험으로 <br class="pc_vw">우리 조직에 딱 맞는 시스템 구축</h3>
					<p>공공기관, 대학, 협회, 중견/대기업 등 각종 프로젝트 경험으로 <br class="pc_vw">조직별 공통 고민을 이해하고 해결합니다.</p>
				</li>
				<li class="i2">
					<h3>합리적인 비용과 명확한 일정으로 <br class="pc_vw">프로젝트 완주</h3>
					<p>타사 대비 약 20~30% 낮은 합리적인 비용으로 <br class="pc_vw">일정 지연 없이 프로젝트를 진행합니다. </p>
				</li>
				<li class="i3">
					<h3>지속 가능한 시스템 설계로 <br class="pc_vw">안정적인 유지/보수 운영</h3>
					<p>운영 중 기능 개선이나 추가 개발이 필요한 경우에만 <br class="pc_vw">선택적으로 유지보수를 진행할 수 있도록 설계해, <br class="pc_vw">불필요한 고정 비용을 최소화합니다.</p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_how service_how" aria-labelledby="service-how-title">
		<div class="inner">
			<p class="tit_label">HOW</p>
			<h2 id="service-how-title"><strong>홈페이지코리아가 제작한 기업 웹사이트는</strong><br/> 이렇게 다릅니다</h2>
			<ul class="how_list">
				<li class="i1">
					<div class="imgfit"><img src="/images/service_how01.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>비즈니스에 최적화된 시스템</h3>
						<p>오픈소스가 아닌 Laravel, React 등 최신 기술 스택으로 자체 개발해 <br class="pc_vw">복잡한 요구사항도 정확히 구현합니다.</p>
					</div>
				</li>
				<li class="i2">
					<div class="imgfit"><img src="/images/service_how02.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>전환을 만드는 UX/UI 설계</h3>
						<p>고객이 원하는 정보를 빠르게 찾고, 자연스럽게 행동하도록 <br class="pc_vw">유도하는 UX 설계로 전환율을 높입니다.</p>
					</div>
				</li>
				<li class="i3">
					<div class="imgfit"><img src="/images/service_how03.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>검색에서 발견되는 SEO/GEO 최적화</h3>
						<p>구글 검색 엔진은 물론, ChatGPT와 같은 AI가 홈페이지의 정보를 <br class="pc_vw">정확하게 이해하고 인용할 수 있도록 홈페이지를 구조화하고 최적화합니다.</p>
					</div>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_review service_review bg_white" aria-labelledby="service-review-title">
		<div class="line_wrap">
			<i class="t1" aria-hidden="true"></i><i class="b1" aria-hidden="true"></i>
			<svg class="line_svg" aria-hidden="true"></svg>
		</div>
		<div class="inner">
			<p class="tit_label">REVIEW</p>
			<h2 id="service-review-title"><strong>4,400개+ 고객사가</strong> <br/>홈페이지코리아를 추천합니다</h2>
			<ul class="review_list">
				<li>
					<h3 class="sound_only">공공기관</h3>
					<h4>박물관 개관 전 짧은 기간 내에 프로젝트를 완료할 수 있을지 고민이었는데요.<br/>홈페이지코리아 PM님이 필요한 기획안을 먼저 전문성 있게 제안해주셔서, 일정에 맞춰 성공적으로 오픈할 수 있었습니다. 책임감 있는 진행에 감사드립니다.</h4>
					<p><i><img src="/images/icon_review_logo1.svg" alt="" aria-hidden="true"></i>국립스포츠박물관<span aria-hidden="true">공공기관</span></p>
				</li>
				<li>
					<h3 class="sound_only">재단</h3>
					<h4>다수의 웹사이트 개발사를 거치며 시스템이 파편화되어 있었고, 응답 속도가 현저히 저하되어 운영에 큰 어려움이 있었습니다.<br/>홈페이지코리아가 데이터 구조를 전면 재설계해, 회원 데이터 조회 시간을 기존 2분에서 7초로 단축하면서 업무 효율이 극적으로 개선되었습니다.</h4>
					<p><i><img src="/images/icon_review_logo2.svg" alt="" aria-hidden="true"></i>푸른나무재단<span aria-hidden="true">재단</span></p>
				</li>
				<li>
					<h3 class="sound_only">학회</h3>
					<h4>복잡한 요구사항과 예외 조건으로 인해 문서 변경 과정에서 오류가 발생할까 걱정이 많았습니다.<br/>홈페이지코리아에서 요구사항별로 문서를 체계적으로 관리해주신 덕에 개발 과정이 원활하게 진행될 수 있었습니다.</h4>
					<p><i><img src="/images/icon_review_logo3.svg" alt="" aria-hidden="true"></i>고분자학회<span aria-hidden="true">학회</span></p>
				</li>
			</ul>
			<div class="flex_center">
				<a href="/portfolio/" class="btn_link slim">홈페이지 제작 사례 더보기</a>
			</div>
		</div>
	</section>
	
	<section class="infopage_contact service_contact page_contact" aria-label="service-contact-title">
		<div class="inner">
			<p class="tit_label sound_only">CTA</p>
			<h2 id="service-contact-title" class="port_tit"><strong>우리 조직에 딱 맞는 홈페이지 제작, </strong><br/>홈페이지코리아에게 문의하세요</h2>
			<div class="btns flex_center">
				<a href="/portfolio/" class="btn_link">포트폴리오 보러가기</a>
				<a href="/contact/" class="btn_link">프로젝트 문의하기</a>
			</div>
		</div>
	</section>

	<section class="infopage_faq service_faq" aria-label="service-faq-title">
		<div class="inner">
			<p class="tit_label">FAQ</p>
			<h2 id="service-faq-title"><strong>자주 묻는 질문</strong></h2>
			<ul class="faq_list">
				<li>
					<h3><button type="button">Q. 홈페이지 제작 비용은 어떻게 산정되나요?</button></h3>
					<div class="con">프로젝트 규모와 요구 기능에 따라 비용이 결정됩니다. 로그인 기능이 없는 기본 홈페이지도 가능하며, 회원 관리, 예약 시스템, ERP 연동 등 복잡한 기능이 추가될 경우 비용이 추가됩니다.</div>
				</li>
				<li>
					<h3><button type="button">Q. 홈페이지 제작 기간은 얼마나 걸리나요?</button></h3>
					<div class="con">평균 1.5개월(6주) 내에 구축이 가능하며, 프로젝트 규모에 따라 기간이 달라질 수 있습니다. 대형 프로젝트(ERP 연동 등)의 경우 6개월까지 소요될 수 있습니다. 모든 프로젝트는 WBS 기반으로 관리되며, PM이 일정을 매일 점검하여 합의한 일정은 내부 사유로 미루지 않습니다.</div>
				</li>
				<li>
					<h3><button type="button">Q. 프로젝트 진행 중 추가 비용이 발생하나요?</button></h3>
					<div class="con">초기 합의된 요구사항 범위 내에서는 추가 비용이 발생하지 않습니다. 다만, 프로젝트 진행 중 새로운 기능이 추가되거나 요구사항이 크게 변경될 경우 별도 협의를 통해 추가 개발 비용이 발생할 수 있습니다. 이 경우에도 사전에 공수와 비용을 명확히 안내드린 후 진행합니다.</div>
				</li>
				<li>
					<h3><button type="button">Q. 홈페이지 제작 후 유지보수는 필수인가요?</button></h3>
					<div class="con">유지보수는 필수가 아닌 선택사항입니다. 관리자 시스템(CMS)에 운영에 꼭 필요한 기능을 충분히 반영하여 담당자가 직접 콘텐츠를 관리할 수 있도록 구축합니다. 운영 중 기능 개선이나 추가 개발이 필요한 경우에만 선택적으로 유지보수를 진행할 수 있습니다.<br/>
						현재 약 50여 개 기관·기업이 홈페이지코리아의 유지보수 서비스를 이용 중이며, 월 50만 원부터 시스템 규모와 요청 빈도에 따라 비용이 산정됩니다. 긴급 대응, 콘텐츠 수정, 보안 업데이트 등이 필요한 경우 유지보수 계약을 권장드립니다.
					</div>
				</li>

				<li>
					<h3><button type="button">Q. 우리 업종과 비슷한 프로젝트 경험이 있나요?</button></h3>
					<div class="con">홈페이지코리아는 27년간 4,400개 이상의 프로젝트를 진행하며 대기업, 공공기관, 학회/협회, 대학교, 의료기관 등 다양한 업종의 홈페이지를 제작했습니다.<br/>
						업종별로 특화된 PM이 있어 학회는 회원 관리와 학술대회 시스템, 대학은 장비예약관리시스템, 공공기관은 전자정부프레임워크 기반 보안 구축 등 각 업종의 요구사항을 정확히 이해하고 구현합니다.
						<a href="" class="btn_link">포트폴리오 보러가기</a>
					</div>
				</li>
			</ul>
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
		const $mainService  = $(".infopage_head");
		const $bgRound      = $(".bg_round");
		const $marquee      = $(".infopage_head .marquee_banner");
		const scrollTop     = $(window).scrollTop();
		const windowHeight  = $(window).height();
		const serviceTop    = $mainService.offset().top;
		const paddingTop    = parseInt($mainService.css("padding-top"));
		const marqueeHeight = $marquee.outerHeight();
		const marqueeMargin = parseInt($marquee.css("margin-bottom"));
		const initY         = -(paddingTop + marqueeHeight + marqueeMargin);

		const offset       = 200; // ← 이 값으로 기준점 조정 (높일수록 더 아래에서 시작)
		const scrollStart  = serviceTop - windowHeight + offset;
		const scrollEnd    = serviceTop + offset;

		if (scrollTop >= scrollStart) {
			$mainService.addClass("start");
		} else {
			$mainService.removeClass("start");
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
// FAQ
	$(".faq_list button").click(function(){
		$(this).parent().next(".con").stop(true,true).slideToggle("fast").parent().stop(true,true).toggleClass("on").siblings().removeClass("on").children(".con").slideUp("fast");
		$(".faq_list li").removeClass("on_before");
		$(".faq_list li.on").prev("li").addClass("on_before");
	});
});
</script>

@endsection