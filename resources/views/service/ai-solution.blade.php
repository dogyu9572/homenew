@extends('layouts.app')
@section('title'){{ $gName }} | {{ $sName }}@endsection
@section('gName', $gName)
@section('sName', $sName)
@section('description', '비즈니스 생산성을 높이는 맞춤형 AI 솔루션. 4,400개 조직이 선택한 홈페이지코리아가 27년 경험으로 AI 챗봇·QA 자동화·기업용 AI 구축까지 책임집니다.')
@section('keywords', 'AI 솔루션(6천), AI 챗봇 (7천), AI QA ( 90), AX 솔루션 (69), 기업용 llm (13), 기업용 ai (180)')
@section('sga_plus')
,"mainEntity": [
    {
        "@@type": "Question",
        "name": "AI 솔루션 구축 기간은 얼마나 걸리나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "기본 AI 챗봇은 2~4주, RAG 기반 기업용 AI는 1~2개월, AI QA 자동화 시스템은 2~3개월 정도 소요됩니다. 초기 컨설팅 단계에서 명확한 일정과 단계별 목표를 제시합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "기존 시스템과 AI 솔루션 연동이 가능한가요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "슬랙, 지메일, 노션, 사내 메신저, ERP, CRM 등 기존 시스템과 API 연동을 통해 통합할 수 있습니다. 레거시 시스템과의 안정적인 연동과 데이터 마이그레이션도 안전하게 처리합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "AI 챗봇이 잘못된 답변을 하면 어떻게 하나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "Temperature 제어 기술과 프롬프트 엔지니어링으로 할루시네이션을 최소화합니다. 답변 로그 실시간 모니터링과 지속적인 학습 데이터 업데이트를 통해 답변 정확도를 개선합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "AI 솔루션의 데이터 보안은 어떻게 보장되나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "온프레미스 또는 프라이빗 클라우드 환경을 제공하며 데이터 암호화, 접근 권한 관리, 정기 보안 감사를 통해 외부 유출 위험을 차단합니다. ISMS 인증 기업으로 엄격한 보안 기준을 준수합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "AI 솔루션 도입 후 유지보수는 어떻게 진행되나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "구축을 담당한 PM이 직접 유지보수를 담당합니다. 월 단위 계약을 통해 AI 모델 업데이트, 데이터 추가 학습, 성능 최적화, 오류 수정을 지속적으로 지원합니다."
        }
    }
]
@endsection

@section('content')
<main class="sub_contents_wrap infopage_wrap">

	<section class="infopage_head service_head main_service" aria-labelledby="service-head-title" data-header="dark">
		<div class="inner">
			<h1 id="service-head-title">기업 맞춤형 AI 솔루션으로 <br class="pc_vw">비즈니스 생산성을 혁신하세요.</h1>
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
				<h2 id="service-problem-title"><strong>기업용 AI 솔루션 도입</strong>이 필요하신가요?</h2>
				<ul class="problem_list">
					<li>
						<h3>기존 AI 상담 챗봇 답변이 부정확할 때가 많아요.</h3>
						<p>일반 AI는 내부 규정을 몰라 엉뚱한 답(할루시네이션)을 내놓아 브랜드 신뢰도를 떨어뜨리고 고객을 혼란에 빠뜨립니다.</p>
					</li>
					<li>
						<h3>웹사이트 출시 전, 테스트할 인력과 시간이 부족합니다.</h3>
						<p>사람이 일일이 모든 기능을 검증하는 방식은 막대한 시간이 소요될 뿐만 아니라, 테스터가 놓치는 휴먼 에러의 위험이 항상 존재합니다.</p>
					</li>
					<li>
						<h3>사내 정보를 찾는 데 많은 시간을 허비하고 있어요.</h3>
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
			<h2 id="service-solution-title"><strong>홈페이지코리아는 기업의 실무 환경에 최적화된</strong><br/> 맞춤형 AX 솔루션을 구축합니다.</h2>
			<ul class="solution_list">
				<li class="i1">
					<h3>맞춤형 AI 챗봇 솔루션</h3>
					<p>정해진 답만 하는 기존 챗봇과 다릅니다. 브랜드의 성격에 맞춰 답변 품질을 최적화하고, 난수(Temperature) 제어 기술을 통해 오답 없는 정확한 정보로 24시간 끊김 없는 고품질 상담 환경을 제공합니다.</p>
				</li>
				<li class="i2">
					<h3>AI 기반 품질 관리(QA) 자동화 솔루션</h3>
					<p>기획안을 학습한 AI가 테스트 시나리오를 자동 설계하고, 서비스의 처음부터 끝까지 검증하는 E2E 자동 테스트를 수행합니다. 데이터 기반 보고서를 통해 출시 후 발생할 운영 리스크를 사전에 완벽히 차단합니다.</p>
				</li>
				<li class="i3">
					<h3>AI 에이전트 · 기업용 AI 솔루션</h3>
					<p>RAG(검색 증강 생성) 모델을 구축해 사내 데이터를 완벽히 이해하는 지식 아카이브를 만듭니다. MCP 모델로 슬랙, 지메일 등과 연동하여 AI가 회의록을 요약하고 메일 초안을 작성하는 즉시 실행형 업무 환경을 구현합니다.</p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_how service_how" aria-labelledby="service-how-title" data-header="dark">
		<div class="inner">
			<p class="tit_label">HOW</p>
			<h2 id="service-how-title"><strong>27년 웹 구축 기술력으로 빠르고 안전한</strong><br/> 기업용 AX 구축 프로세스</h2>
			<ul class="how_list">
				<li class="i1">
					<div class="imgfit"><img src="/images/service_how01.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>웹, 앱, AI 전문 엔지니어의 AI 네이티브 시스템 구축</h3>
						<p>Gemini, Cursor AI 등 최신 모델을 활용해 개발 기간은 단축하고 비용은 합리적으로, 결과물은 시니어 개발자가 직접 검수하여 보안성을 보장합니다.</p>
					</div>
				</li>
				<li class="i2">
					<div class="imgfit"><img src="/images/service_how02.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>현장에 꼭 필요한 '실전형' AI 도입 제안</h3>
						<p>무조건적인 최신 기술 적용이 아닌, PM이 직접 고객사의 업무 프로세스를 분석합니다. 실제 업무 흐름을 기반으로 최소 비용으로 최대 효율을 낼 수 있는 현실적인 AI 도입 방안을 제안합니다.</p>
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
			<h2 id="service-review-title"><strong>성공적인 AI 솔루션 개발</strong> 홈페이지코리아와 함께하세요.</h2>
			<ul class="review_list">
				<li>
					<h3 class="sound_only">제조/유통</h3>
					<h4>철근이라는 특수한 상품 특성 때문에 일반 쇼핑몰보다 복잡한 주문 구조가 필요했습니다. 상품 특성을 이해하고, 성능을 개선해주셔서 만족스러운 결과물을 얻을 수 있었어요.</h4>
					<p><i><img src="/images/icon_review_logo1.svg" alt="" aria-hidden="true"></i>서주엔터프라이즈<span aria-hidden="true">제조/유통</span></p>
				</li>
				<li>
					<h3 class="sound_only">F&B</h3>
					<h4>복잡한 B2B 거래 특성을 완벽히 이해하고 자사몰 구축을 진행해주셨어요. 구축 후 매출은 15% 상승했고요. 불필요한 수작업이 줄고 주문/재고 관리 시스템이 일원화돼 업무 효율이 40% 올랐습니다.</h4>
					<p><i><img src="/images/icon_review_logo2.svg" alt="" aria-hidden="true"></i>골든브라운<span aria-hidden="true">F&B</span></p>
				</li>
				<li>
					<h3 class="sound_only">병원/의료</h3>
					<h4>치과의사와 기공소별로 판매 상품 구조가 달라 쇼핑몰 분리가 필요했고, 국비지원 교육 신청까지 함께 운영해야 했습니다. 서로 다른 서비스 성격이 하나의 브랜드 경험 안에서 정리됐고, 속도 이슈도 완벽하게 해결됐습니다.</h4>
					<p><i><img src="/images/icon_review_logo3.svg" alt="" aria-hidden="true"></i>하이덴탈코리아<span aria-hidden="true">병원/의료</span></p>
				</li>
			</ul>
			<div class="flex_center">
				<a href="/portfolio/" class="btn_link slim">앱 개발 사례 더보기</a>
			</div>
		</div>
	</section>
	
	<section class="infopage_contact service_contact page_contact" aria-label="service-contact-title" data-header="dark">
		<div class="inner">
			<p class="tit_label sound_only">CTA</p>
			<h2 id="service-contact-title" class="port_tit"><strong>우리 기업에 꼭 맞는 AI 솔루션</strong>, <br class="pc_vw">지금 상담해 보세요.</h2>
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
				<li>
					<h3><button type="button">Q. AI 솔루션 구축 기간은 얼마나 걸리나요?</button></h3>
					<div class="con">프로젝트 규모와 요구사항에 따라 다르지만, 기본적인 AI 챗봇은 2~4주, RAG 기반 기업용 AI는 1~2개월, AI QA 자동화 시스템은 2~3개월 정도 소요됩니다. 홈페이지코리아는 초기 컨설팅 단계에서 명확한 일정과 단계별 목표를 제시하며, 정기적인 진행 상황 공유를 통해 투명하게 프로젝트를 진행합니다.</div>
				</li>
				<li>
					<h3><button type="button">Q. 기존 시스템과 연동이 가능한가요?</button></h3>
					<div class="con">네, 가능합니다. 슬랙, 지메일, 노션, 사내 메신저, ERP, CRM 등 기존에 사용 중인 시스템과 API 연동을 통해 seamless하게 통합할 수 있습니다. 홈페이지코리아는 27년간의 시스템 구축 경험을 바탕으로 레거시 시스템과의 안정적인 연동을 보장하며, 데이터 마이그레이션도 안전하게 처리합니다.</div>
				</li>
				<li>
					<h3><button type="button">Q. AI 챗봇이 잘못된 답변을 하면 어떻게 하나요?</button></h3>
					<div class="con">홈페이지코리아의 AI 챗봇은 Temperature(난수) 제어 기술과 프롬프트 엔지니어링을 통해 할루시네이션(오답)을 최소화합니다. 또한 답변 로그를 실시간으로 모니터링하고, 지속적인 학습 데이터 업데이트를 통해 답변 정확도를 개선합니다. 만약 오답이 발생하더라도 즉시 수정하고 재학습하여 같은 실수가 반복되지 않도록 관리합니다.</div>
				</li>
				<li>
					<h3><button type="button">Q. 데이터 보안은 어떻게 보장되나요?</button></h3>
					<div class="con">기업의 민감한 내부 데이터는 철저한 보안 프로토콜로 보호됩니다. 온프레미스(On-premise) 구축 또는 프라이빗 클라우드 환경을 제공하며, 데이터 암호화, 접근 권한 관리, 정기적인 보안 감사를 통해 외부 유출 위험을 차단합니다. 홈페이지코리아는 정보보호 관리체계(ISMS) 인증 기업으로, 엄격한 보안 기준을 준수합니다.</div>
				</li>
				<li>
					<h3><button type="button">Q. AI 솔루션 도입 후 유지보수는 어떻게 진행되나요?</button></h3>
					<div class="con">구축을 담당한 PM이 직접 유지보수를 담당하여 시스템 구조를 완벽히 이해한 상태에서 신속한 대응이 가능합니다. 월 단위 유지보수 계약을 통해 AI 모델 업데이트, 데이터 추가 학습, 성능 최적화, 오류 수정을 지속적으로 지원합니다. 또한 새로운 비즈니스 요구사항에 맞춰 기능 확장도 유연하게 진행할 수 있습니다.</div>
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