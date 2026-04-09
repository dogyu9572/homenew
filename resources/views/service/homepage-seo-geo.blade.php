@extends('layouts.app')
@section('title'){{ $gName }} | {{ $sName }}@endsection
@section('gName', $gName)
@section('sName', $sName)
@section('description', '검색엔진과 AI가 이해하는 웹사이트 개발. Schema 마크업, sitemap 자동 생성, AI 검색 최적화까지. 1,100개 고객사가 선택한 홈페이지코리아의 SEO·GEO 솔루션을 만나보세요.')
@section('keywords', 'SEO, GEO, AEO, 검색엔진 최적화, AI 검색 최적화, SEO 최적화, GEO 최적화, AI 검색')
@section('sga_plus')
@include('partials.service-faq-sga-jsonld', ['faqItems' => $faqItems])
@endsection

@section('content')
<main class="sub_contents_wrap infopage_wrap">

	<section class="infopage_head service_head main_service" aria-labelledby="service-head-title" data-header="dark">
		<div class="inner">
			<h1 id="service-head-title" class="mojo_aos">SEO · GEO 최적화<br/>홈페이지코리아와 함께하세요.</h1>
			<p class="tb mojo_aos"><strong>검색엔진과 AI가 이해하고 인용하는 홈페이지</strong>를 개발합니다.</p>
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
				<h2 id="service-problem-title" data-aos="fade-up"><strong>SEO·GEO 최적화 가능한 홈페이지 개발사,</strong><br/> 찾기 어려우셨죠?</h2>
				<!-- <p class="tb">각 서비스는 고객의 비즈니스 목표에 맞춰 <strong>최적화된 솔루션</strong>을 제공하며,<br/><strong>기획부터 디자인, 개발, 운영까지</strong> 전 과정을 지원합니다.</p> -->
				<ul class="problem_list" data-aos="fade-up">
					<li>
						<h3>개발사에 SEO를 요청했더니 잘 모르더라고요. <img src="/images/emoji_tears.png" alt="" aria-hidden="true"></h3>
						<p>여러 업체에 문의해봤는데 "SEO 됩니다"는 말만 하고, 정작 어떻게 하는지 물어보면 대답을 못 하더라고요.</p>
					</li>
					<li>
						<h3>콘텐츠가 검색 결과에 노출되지 않아요. <img src="/images/emoji_sad.png" alt="" aria-hidden="true"></h3>
						<p>개인정보보호법, 정보보안 정책, 내부망/외부망 분리 등 엄격한 보안 홈페이지 새로 만들고 나서 검색해봤는데 우리 회사가 안 나와요.<br/>개발사한테 물어봤더니 "시간이 지나면 된다"는 말만 하고요.기준을 만족하는 개발사를 찾기 어렵습니다.</p>
					</li>
					<li>
						<h3>사이트를 새로 만들었더니 SEO 유입이 사라졌어요. <img src="/images/emoji_anger.png" alt="" aria-hidden="true"></h3>
						<p>리뉴얼 전에는 검색에 잘 나왔는데, 새로 오픈하고 나서 순위가 전부 떨어졌어요.</p>
					</li>
				</ul>
				<div class="dots" aria-hidden="true"><i class="t"></i><i class="m"></i><i class="b"></i></div>
			</div>
		</div>
	</section>
	
	<section class="infopage_solution service_solution bg_white" aria-labelledby="service-solution-title" data-header="light">
		<div class="inner">
			<p class="tit_label">SOLUTION</p>
			<h2 id="service-solution-title">홈페이지코리아는 <br/><strong>기획 단계부터 SEO·GEO 구조를 함께 설계 합니다.</strong></h2>
			<ul class="solution_list">
				<li class="i_a1">
					<object data="/images/icon_solution_a01.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>SEO·GEO를 이해하는 <br class="pc_vw">홈페이지 개발 파트너</h3>
					<p>SEO를 아는 개발자는 기능을 구현하면서 <br class="pc_vw">동시에 검색 구조를 함께 설계합니다. <br class="pc_vw">나중에 따로 최적화 작업을 추가할 필요가 없습니다.</p>
				</li>
				<li class="i_a2">
					<object data="/images/icon_solution_a02.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>검색엔진이 정확히 읽는 <br class="pc_vw">홈페이지 구조</h3>
					<p>기술 구조를 기획 단계에서 설계합니다. <br class="pc_vw">신규 페이지가 생성될 때마다 자동으로 <br class="pc_vw">메타 정보가 생성되는 구조로 구축합니다.</p>
				</li>
				<li class="i_a3">
					<object data="/images/icon_solution_a03.svg" type="image/svg+xml" aria-hidden="true"></object>
					<h3>AI 검색에서 인용되는 <br class="pc_vw">콘텐츠 설계</h3>
					<p>AI가 신뢰 정보로 인식하는 <br class="pc_vw">Organization·Article·FAQ Schema 마크업, <br class="pc_vw">llms.txt 설정까지 적용합니다.</p>
				</li>
			</ul>
		</div>
	</section>
	
	<section class="infopage_how service_how" aria-labelledby="service-how-title" data-header="dark">
		<div class="inner">
			<p class="tit_label">HOW</p>
			<h2 id="service-how-title"><strong>홈페이지코리아의 SEO·GEO 최적화</strong><br/> 이렇게 다릅니다</h2>
			<ul class="how_list">
				<li class="i_a1">
					<div class="imgfit"><img src="/images/img_service_how_a01.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_a01.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3>검색엔진이 <strong>사이트 전체를 수집하도록</strong> <br class="pc_vw">홈페이지 시스템 개발</h3>
						<ul class="inlist">
							<li>
								<h4>SEO 친화적 URL 구조 설계</h4>
								<p>슬러그 기반 URL 설계</p>
							</li>
							<li>
								<h4>sitemap.xml 자동 생성</h4>
								<p>신규 페이지 등록 시 자동 업데이트</p>
							</li>
							<li>
								<h4>robots.txt · llms.txt 설정</h4>
								<p>검색엔진·AI 크롤러 수집 범위 제어</p>
							</li>
							<li>
								<h4>meta title · description 자동 생성 CMS</h4>
								<p>콘텐츠 등록 시 메타 정보 자동 생성</p>
							</li>
							<li>
								<h4>canonical 및 robots meta</h4>
								<p>중복 페이지 색인 오류 자동 방지</p>
							</li>
						</ul>
					</div>
				</li>
				<li class="i_a2">
					<div class="imgfit"><img src="/images/img_service_how_a02.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_a02.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3>검색엔진이 <strong>페이지 내용을 이해하도록</strong><br/> 콘텐츠 퍼블리싱</h3>
						<ul class="inlist">
							<li>
								<h4>HTML 구조와 마크업</h4>
								<p>시맨틱 태그로 콘텐츠 위계 설계</p>
							</li>
							<li>
								<h4>이미지 ALT 태그 및 파일 최적화</h4>
								<p>WebP 변환 · LCP 2.5초 이하 목표</p>
							</li>
							<li>
								<h4>Schema 마크업 직접 구현</h4>
								<p>JSON-LD 삽입 후 Rich Results 검증</p>
							</li>
							<li>
								<h4>내부 링크 구조 설계</h4>
								<p>앵커 텍스트 기반 크롤링 경로 설계</p>
							</li>
						</ul>
					</div>
				</li>
				<li class="i_a3">
					<div class="imgfit"><img src="/images/img_service_how_a03.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<object data="/images/icon_service_how_a03.svg" type="image/svg+xml" aria-hidden="true"></object>
						<h3>검색 키워드 기반 <br/><strong>신뢰도 높은 콘텐츠 가이드 제공</strong></h3>
						<ul class="inlist">
							<li>
								<h4>검색 키워드 기반 콘텐츠</h4>
								<p>검색 의도에 맞는 주제 선정</p>
							</li>
							<li>
								<h4>문제 해결 콘텐츠</h4>
								<p>고객 질문에 직접 답하는 글</p>
							</li>
							<li>
								<h4>전문성 있는 설명</h4>
								<p>업종 깊이가 담긴 콘텐츠</p>
							</li>
							<li>
								<h4>백링크</h4>
								<p>외부에서 인용되는 구조</p>
							</li>
							<li>
								<h4>브랜드 언급</h4>
								<p>온라인 브랜드 노출 확대 </p>
							</li>
							<li>
								<h4>도메인 신뢰도 확인</h4>
								<p>사이트 전반의 신뢰 지표</p>
							</li>
						</ul>
						<p>고객사 콘텐츠 및 브랜드 활동을 기준으로 가이드를 제공합니다.</p>
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
			<h2 id="service-review-title">1,100개+ 고객사가 <br/><strong>홈페이지코리아를 추천합니다.</strong></h2>
			<ul class="review_list">
				<li>
					<h3 class="sound_only">공공기관</h3>
					<div class="flex_tit">
						<h4>대국민 이해를 돕기 위해 <br class="pc_vw">콘텐츠를 <strong>CAROUSEL 구조로 구성하고 <br class="pc_vw">FAQ SCHEMA를 적용</strong>하여 검색 결과에서 <br class="pc_vw">정보 노출이 가능하도록 설계했습니다.</h4>
					</div>
					<p><i><img src="/images/icon_review_logo_a1.svg" alt="" aria-hidden="true"></i>생활폐기물 분리배출 누리집<span aria-hidden="true">공공기관</span></p>
				</li>
				<li>
					<h3 class="sound_only">대학교</h3>
					<div class="flex_tit">
						<h4>연구 장비 정보를 <br class="pc_vw"><strong>PRODUCT SCHEMA 형태로 구조화</strong>하여<br class="pc_vw"> 검색엔진이 장비 정보를 이해할 수 있도록<br class="pc_vw"> 데이터 구조를 설계했습니다.</h4>
					</div>
					<p><i><img src="/images/icon_review_logo_a2.svg" alt="" aria-hidden="true"></i>한양대학교<span aria-hidden="true">대학교</span></p>
				</li>
				<li>
					<h3 class="sound_only">기업</h3>
					<div class="flex_tit">
						<h4>여행 상품을 <strong>PRODUCT,<br class="pc_vw"> REVIEW SCHEMA 형태로 구성</strong>하여<br class="pc_vw"> 검색 결과에서 상품 정보와<br class="pc_vw"> 리뷰가 노출될 수 있도록 설계했습니다</h4>
					</div>
					<p><i><img src="/images/icon_review_logo_a3.svg" alt="" aria-hidden="true"></i>코스모진 여행사<span aria-hidden="true">기업</span></p>
				</li>
			</ul>
			<div class="flex_center">
				<a href="/portfolio/" class="btn_link slim">홈페이지 제작 사례 더보기</a>
			</div>
		</div>
	</section>
	
	<section class="infopage_contact service_contact page_contact" aria-label="service-contact-title" data-header="dark">
		<div class="inner" data-aos="fade-up">
			<p class="tit_label sound_only">CTA</p>
			<h2 id="service-contact-title" class="port_tit"><strong>검색엔진과 AI가 이해하는 홈페이지,</strong><br/>지금 홈페이지코리아와 시작하세요.</h2>
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
				@include('partials.public-faq-list', ['faqItems' => $faqItems, 'idPrefix' => 'service-faq-seo-geo', 'variant' => 'service'])
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