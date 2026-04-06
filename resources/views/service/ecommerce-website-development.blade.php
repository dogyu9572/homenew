@extends('layouts.app')
@section('title'){{ $gName }} | {{ $sName }}@endsection
@section('gName', $gName)
@section('sName', $sName)
@section('description', '매출 성장을 만드는 온라인 쇼핑몰 제작. 1,100개 조직이 선택한 홈페이지코리아가 27년 경험으로 귀사에 맞는 자사몰을 설계합니다.')
@section('keywords', '이커머스, 온라인 쇼핑몰, 쇼핑몰, 쇼핑몰 제작, 쇼핑몰 제작 업체, 자사몰, 자사몰 제작, 웹빌더')
@section('sga_plus')
,"mainEntity": [
    {
        "@@type": "Question",
        "name": "웹빌더(카페24, 아임웹 등)와 맞춤형 자사몰의 차이는 무엇인가요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "웹빌더는 초기 구축이 빠르고 저렴하지만 템플릿 기반이라 브랜드 차별화가 어렵고 매출이 늘어날수록 거래 수수료가 계속 발생합니다. 맞춤형 자사몰은 PG사 직접 계약으로 중간 수수료 없이 운영할 수 있어 장기적으로 훨씬 경제적입니다."
        }
    },
    {
        "@@type": "Question",
        "name": "B2B와 B2C 쇼핑몰의 차이점은 무엇인가요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "B2C는 간편한 결제와 직관적인 구매 프로세스가 중요하고, B2B는 거래처별 차등 가격, 대량 주문, 견적서 발행, ERP 연동 등 복잡한 거래 구조를 지원해야 합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "쇼핑몰에서 SEO(검색엔진 최적화)가 왜 중요한가요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "검색엔진 상위 노출 시 광고비 없이 고객을 유입시킬 수 있습니다. 구조화된 데이터, 메타태그 최적화, 빠른 로딩 속도 등 SEO 필수 요소를 반영하여 개발합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "쇼핑몰 제작 시 어떤 결제 시스템을 사용하나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "국내 주요 PG사와 직접 계약하여 카드 결제, 계좌이체, 가상계좌, 카카오페이·네이버페이 등 간편결제를 모두 지원합니다. 웹빌더를 거치지 않아 최저 수수료로 운영이 가능합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "쇼핑몰 구축 후 재고 관리나 주문 관리는 어떻게 하나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "맞춤형 관리자 시스템(CMS)을 통해 상품 등록, 재고 관리, 주문 조회, 배송 처리, 회원 관리를 처리할 수 있습니다. 기존 ERP·물류 시스템 연동 및 엑셀 업로드로 대량 작업도 가능합니다."
        }
    }
]
@endsection

@section('content')
<main class="sub_contents_wrap infopage_wrap">

	<section class="infopage_head service_head main_service" aria-labelledby="service-head-title" data-header="dark">
		<div class="inner">
			<h1 id="service-head-title" class="mojo_aos">매출 성장을 만드는 온라인 쇼핑몰 홈페이지 코리아에서 맞춤형 자사몰 시작하세요.</h1>
			<div class="btns flex_center mojo_aos">
				<a href="/portfolio/" class="btn_link slim">포트폴리오 보러가기</a>
				<a href="/contact/" class="btn_link slim">프로젝트 문의하기</a>
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
				<h2 id="service-problem-title" data-aos="fade-up">치열한 경쟁속에서도 성장하는 온라인 쇼핑몰을<br class="pc_vw"> <strong>제작하고 싶나요?</strong></h2>
				<ul class="problem_list" data-aos="fade-up">
					<li>
						<h3>제품 강점을 살린 자사몰을 만들고 싶어요. <img src="/images/emoji_smile.png" alt="" aria-hidden="true"></h3>
						<p>웹빌더나 템플릿 웹사이트는 우리 브랜드만의 차별점을 보여주기 어려워요.</p>
					</li>
					<li>
						<h3>마케팅에 효과적인 홈페이지가 필요해요. <img src="/images/emoji_worry.png" alt="" aria-hidden="true"></h3>
						<p>갈수록 검색 광고나 SNS 마케팅 효율이 떨어지는 것 같아요.</p>
					</li>
					<li>
						<h3>웹빌더 자사몰, 성장할 수록 수수료가 부담돼요. <img src="/images/emoji_tears2.png" alt="" aria-hidden="true"></h3>
						<p>매출이 커질수록 월 사용료와 거래 수수료가 계속 늘어나서 부담스러워요.</p>
					</li>
				</ul>
				<div class="dots" aria-hidden="true"><i class="t"></i><i class="m"></i><i class="b"></i></div>
			</div>
		</div>
	</section>
	
	<section class="infopage_solution service_solution bg_white" aria-labelledby="service-solution-title" data-header="light">
		<div class="inner">
			<p class="tit_label">SOLUTION</p>
			<h2 id="service-solution-title">온라인 쇼핑몰도 이제 <br/><strong>맞춤형 자사몰로 구축해야 성장합니다.</strong></h2>
			<ul class="solution_list">
				<li class="i_d1">
					<object data="/images/icon_solution_d01.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>제품에 맞는 고객 여정</h3>
					<p>B2C, B2B 제품 특성을 고려한 <br class="pc_vw">자사몰을 설계하면 전환율이 높아집니다.</p>
				</li>
				<li class="i_d2">
					<object data="/images/icon_solution_d02.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>검색 엔진 최적화</h3>
					<p>제품 정보가 구조화 된 자사몰은 <br class="pc_vw">고객이 검색할 때 발견하기 쉽습니다.</p>
				</li>
				<li class="i_d3">
					<object data="/images/icon_solution_d03.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>중간 마진 없는 PG</h3>
					<p>웹빌더 등 솔루션 없이 직접 자사몰을 제작하면 <br class="pc_vw">결제 수수료가 절감됩니다.</p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_how service_how" aria-labelledby="service-how-title" data-header="dark">
		<div class="inner">
			<p class="tit_label">HOW</p>
			<h2 id="service-how-title"><strong>이커머스를 위한 자사몰,</strong> <br/>27년 경력 홈페이지 코리아에서 제작하세요.</h2>
			<ul class="how_list p_large">
				<li class="i_d1">
					<div class="imgfit"><img src="/images/service_how_d01.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_d01.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3><strong>1,100개</strong> 업종 경험</h3>
						<p><strong>B2C부터 B2B까지 다양한 업종 경험</strong>으로<br class="pc_vw"> <strong>최적의 UI/UX를 디자인</strong>합니다.</p>
					</div>
				</li>
				<li class="i_d2">
					<div class="imgfit"><img src="/images/service_how_d02.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_d02.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3>SEO/GEO <strong>전문성</strong></h3>
						<p><strong>검색 엔진</strong>과 <strong>AI가 발견하기 쉽게 <br class="pc_vw">구조화된 홈페이지로 개발</strong>합니다.</p>
					</div>
				</li>
				<li class="i_d3">
					<div class="imgfit"><img src="/images/service_how_d03.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_d03.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3>PG사 <strong>직접 계약</strong></h3>
						<p>홈페이지코리아와 <strong>PG사가 직접 계약</strong>해 <br class="pc_vw"><strong>최저 수수료 설계가 가능</strong>합니다. </p>
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
			<h2 id="service-review-title">성장하는 쇼핑몰은 <br/><strong>홈페이지코리아와 함께합니다.</strong></h2>
			<ul class="review_list">
				<li>
					<h3 class="sound_only">제조/유통</h3>
					<div class="flex_tit">
						<h4>철근이라는 특수한 상품 특성 때문에 일반 쇼핑몰보다 <strong>복잡한 주문 구조가 필요</strong>했습니다. <strong>상품 특성을 이해</strong>하고, <strong>성능을 개선</strong>해주셔서 <strong>만족스러운 결과물</strong>을 얻을 수 있었어요.</h4>
					</div>
					<p><i><img src="/images/icon_review_logo_d1.svg" alt="" aria-hidden="true"></i>서주엔터프라이즈<span aria-hidden="true">제조/유통</span></p>
				</li>
				<li>
					<h3 class="sound_only">F&B</h3>
					<div class="flex_tit">
						<h4>복잡한 B2B 거래 특성을 완벽히 이해하고 자사몰 구축을 진행해주셨어요. <strong>구축 후 매출은 15% 상승</strong>했고요. <strong>불필요한 수작업이 줄고 주문/재고 관리 시스템이 일원화돼 업무 효율이 40% 올랐습니다.</strong></h4>
					</div>
					<p><i><img src="/images/icon_review_logo_d2.png" alt="" aria-hidden="true"></i>골든브라운<span aria-hidden="true">F&B</span></p>
				</li>
				<li>
					<h3 class="sound_only">병원/의료</h3>
					<div class="flex_tit">
						<h4>치과의사와 기공소별로 판매 상품 구조가 달라 <strong>쇼핑몰 분리가 필요했고, 국비지원 교육 신청까지 함께 운영</strong>해야 했습니다. 서로 다른 서비스 성격이 <strong>하나의 브랜드 경험 안에서 정리</strong>됐고, <strong>속도 이슈도 완벽하게 해결</strong>됐습니다.</h4>
					</div>
					<p><i><img src="/images/icon_review_logo_d3.png" alt="" aria-hidden="true"></i>하이덴탈코리아<span aria-hidden="true">병원/의료</span></p>
				</li>
			</ul>
			<div class="flex_center">
				<a href="/portfolio/" class="btn_link slim">홈페이지 제작 사례 더보기</a>
			</div>
		</div>
	</section>
	
	<section class="infopage_contact service_contact page_contact" aria-label="service-contact-title" data-header="dark">
		<div class="inner">
			<p class="tit_label sound_only">CTA</p>
			<h2 id="service-contact-title" class="port_tit"><strong>매출이 성장하는 온라인 쇼핑몰 제작,</strong><br/> 홈페이지 코리아에게 문의하세요.</h2>
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
				@include('partials.public-faq-list', ['faqItems' => $faqItems, 'idPrefix' => 'service-faq-ecommerce', 'variant' => 'service'])
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
<script src="{{ asset('js/faq-accordion.js') }}"></script>
<script src="{{ asset('js/marquee.js') }}"></script>
@endpush
@endsection