@extends('layouts.app')
@section('title'){{ $gName }} | {{ $sName }}@endsection
@section('gName', $gName)
@section('sName', $sName)
@section('description', '아이디어를 현실로 만드는 앱 개발 서비스. 1,100개 조직이 선택한 홈페이지코리아가 27년 경험으로 기획·개발·스토어 출시·운영까지 책임집니다.')
@section('keywords', '앱 개발 (4.7천), 앱 개발 업체 (3천)')
@section('sga_plus')
,"mainEntity": [
    {
        "@@type": "Question",
        "name": "앱 개발 기간은 얼마나 걸리나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "MVP는 2~3개월, 중규모 서비스 앱은 3~5개월 정도 소요됩니다. 초기 기획 단계에서 명확한 일정과 마일스톤을 제시하며 단계별 검수를 통해 약속된 날짜에 정확히 출시합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "앱 스토어 심사는 어떻게 진행되나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "기획 단계부터 앱스토어·플레이스토어 가이드라인을 준수하여 개발하며, 심사 제출부터 승인까지 전 과정을 대행합니다. 반려 시 신속하게 수정하여 재제출합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "네이티브 앱과 하이브리드 앱 중 어떤 것을 선택해야 하나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "빠른 출시와 비용 효율이 중요하다면 하이브리드 앱(Flutter, React Native)을, 고성능과 네이티브 기능 활용이 필요하다면 네이티브 앱을 권장합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "기존 웹사이트를 앱으로 전환할 수 있나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "기존 웹 서비스의 디자인과 기능을 앱 환경에 최적화하여 전환할 수 있으며, 웹과 앱 간 데이터를 실시간 동기화하여 통합 관리가 가능합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "앱 출시 후 유지보수는 어떻게 진행되나요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "구축을 담당한 PM이 직접 유지보수를 담당합니다. 월 단위 계약을 통해 오류 수정, OS 업데이트 대응, 기능 개선, 스토어 업데이트를 지속적으로 지원합니다."
        }
    },
    {
        "@@type": "Question",
        "name": "인앱 결제 기능도 구현 가능한가요?",
        "acceptedAnswer": {
            "@@type": "Answer",
            "text": "애플 인앱 결제, 구글 인앱 결제, 일반 PG 결제 모두 구현 가능하며, 결제 내역 관리와 영수증 검증까지 안정적으로 처리합니다."
        }
    }
]
@endsection

@section('content')
<main class="sub_contents_wrap infopage_wrap">

	<section class="infopage_head service_head main_service" aria-labelledby="service-head-title" data-header="dark">
		<div class="inner">
			<h1 id="service-head-title" class="mojo_aos">앱 개발, 맞춤형 설계부터 스토어 출시까지 홈페이지코리아가 끝까지 책임집니다.</h1>
			<div class="btns flex_center mojo_aos">
				<a href="/portfolio/" class="btn_link slim">포트폴리오 보러가기</a>
				<a href="/contact/" class="btn_link slim">프로젝트 문의하기</a>
			</div>
		</div>
		<div class="marquee_banner mojo_aos">
			<ul class="slide" aria-label="주요 고객사 목록">
				<li><img src="/images/main_service_a01.svg" alt="United Nations" title="United Nations"></li>
				<li><img src="/images/main_service_a02.svg" alt="서울대학교 농생명과학공동기기원" title="서울대학교 농생명과학공동기기원"></li>
				<li><img src="/images/main_service_a03.svg" alt="파크랜드" title="파크랜드"></li>
				<li><img src="/images/main_service_a04.svg" alt="국립스포츠박물관" title="국립스포츠박물관"></li>
				<li><img src="/images/main_service_a05.svg" alt="KB부동산신탁" title="KB부동산신탁"></li>
				<li><img src="/images/main_service_a06.svg" alt="PARADISE CITY" title="PARADISE CITY"></li>
				<li><img src="/images/main_service_a07.svg" alt="CJ Innovation" title="CJ Innovation"></li>
				<li><img src="/images/main_service_a08.svg" alt="한양대학교" title="한양대학교"></li>
				<li><img src="/images/main_service_a09.svg" alt="세종대학교" title="세종대학교"></li>
				<li><img src="/images/main_service_b01.png" alt="KOREAN AIR" title="KOREAN AIR"></li>
				<li><img src="/images/main_service_b02.png" alt="GS 글로벌" title="GS 글로벌"></li>
				<li><img src="/images/main_service_b03.png" alt="GS 에너지" title="GS 에너지"></li>
				<li><img src="/images/main_service_b04.svg" alt="GS 파워" title="GS 파워"></li>
				<li><img src="/images/main_service_b05.svg" alt="OMRON" title="OMRON"></li>
				<li><img src="/images/main_service_b06.svg" alt="국민체육진흥공단" title="국민체육진흥공단"></li>
			</ul>
		</div>
		<div class="inner bg_round_start" data-aos="fade-up" data-aos-offset="200">
			<div class="bg_round"><div class="in_gradient"></div></div>
			<div class="problem service">
				<p class="tit_label" data-aos="fade-up">PROBLEM</p>
				<h2 id="service-problem-title" data-aos="fade-up">앱 개발, 아이디어를 어떻게 실현해야 할지<br class="pc_vw"> <strong>막막하신가요?</strong></h2>
				<ul class="problem_list">
					<li data-aos="fade-up">
						<h3>앱 스토어 심사 거절로 런칭이 계속 늦어져요. <img src="/images/emoji_tears.png" alt="" aria-hidden="true"></h3>
						<p>애플과 구글의 까다로운 심사 기준을 모른 채 개발하면, 반복되는 반려로 비즈니스 일정에 큰 차질이 생깁니다.</p>
					</li>
					<li data-aos="fade-up">
						<h3>앱 개발, 배포 절차가 너무 생소하고 복잡합니다. <img src="/images/emoji_sad.png" alt="" aria-hidden="true"></h3>
						<p>기업용 개발자 계정 생성, 해외기업식별번호(D-U-N-S) 발급 등 기술 외적인 행정 절차에서 시간만 허비하는 경우가 많습니다.</p>
					</li>
					<li data-aos="fade-up">
						<h3>운영 중인 웹 시스템과 연동이 고민입니다. <img src="/images/emoji_anger.png" alt="" aria-hidden="true"></h3>
						<p>기존의 웹 서비스와 앱 간 데이터가 실시간으로 연동되지 않으면, 관리 포인트만 늘어나 운영 효율이 떨어지게 됩니다.</p>
					</li>
				</ul>
				<div class="dots" aria-hidden="true" data-aos="fade-up"><i class="t"></i><i class="m"></i><i class="b"></i></div>
			</div>
		</div>
	</section>
	
	<section class="infopage_solution service_solution bg_white" aria-labelledby="service-solution-title" data-header="light">
		<div class="inner">
			<p class="tit_label" data-aos="fade-up">SOLUTION</p>
			<h2 id="service-solution-title" data-aos="fade-up">비즈니스 모델을 이해하는 <br class="pc_vw"><strong>앱 개발 파트너는 다릅니다.</strong></h2>
			<ul class="solution_list" data-aos="fade-up">
				<li class="i_f1">
					<h3>승인 리스크를 차단하는 전략적 개발</h3>
					<p>수많은 프로젝트 경험으로 축적된 '심사 대응 매뉴얼'을 바탕으로, 기획 단계부터 가이드라인을 준수하여 <br class="pc_vw">약속된 날짜에 정확히 출시합니다.</p>
				</li>
				<li class="i_f2">
					<h3>준비부터 런칭까지 완벽 가이드</h3>
					<p>계정 생성부터 결제 심사, 본인인증(PASS/카카오) API 연동까지, 고객사가 기술적 절차로 에너지를 낭비하지 않도록 <br class="pc_vw">모든 과정을 상세히 안내합니다.</p>
				</li>
				<li class="i_f3">
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
						<h3>실시간 <strong>웹앱 연동 시스템</strong></h3>
						<p>서버-앱 간 <strong>안정적인 푸시(Push) 알림</strong>과 <br class="pc_vw">웹 서비스와의 완벽한 데이터 동기화 구현</p>
					</div>
				</li>
				<li class="i_f2">
					<div class="imgfit"><img src="/images/img_service_how_f02.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>고도화된 <strong>기능 구현</strong></h3>
						<p><strong>사용자 위치 기반</strong>(GPS) <strong>서비스, <br class="pc_vw">음성 인터페이스</strong>(STT/TTS), <strong>기기별 카메라 최적화 제어</strong></p>
					</div>
				</li>
				<li class="i_f3">
					<div class="imgfit"><img src="/images/img_service_how_f03.png" alt="" aria-hidden="true"></div>
					<div class="txt">
						<h3>안정적인 <strong>인앱 결제 모듈</strong></h3>
						<p>복잡한 결제 프로세스도 데이터 누락 없이 처리하는<br class="pc_vw"> <strong>강력한 보안 및 결제 시스템 구축</strong></p>
					</div>
				</li>
				<li class="i_f4">
					<div class="imgfit"><img src="/images/img_service_how_f04.png" alt="" aria-hidden="true"></div>
					<div class="txt">
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
					<a href="{{ route('portfolio.portfolio_view', ['portfolio' => $item->id]) }}" class="box" aria-label="{{ $item->title }} — {{ $marqueeTypeLabel }} 포트폴리오 보기">
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
    (function () {
        const $banner = $(".infopage_head .marquee_banner");
        const $slide  = $banner.find(".slide").first();
        if (! $slide.length || ! $slide.children("li").length) {
            return;
        }
        const speed      = 2;
        let posX         = 0;
        let isPaused     = false;
        let totalWidth   = 0;
        function measure() {
            const el = $slide.get(0);
            totalWidth = el && el.scrollWidth > 0 ? el.scrollWidth : $slide.outerWidth(true);
        }
        measure();
        function marqueeLoop() {
            if (!isPaused && totalWidth > 0) {
                posX -= speed;
                if (posX <= -totalWidth) posX = 0;
                $slide.css("transform", `translateX(${posX}px)`);
            }
            requestAnimationFrame(marqueeLoop);
        }
        marqueeLoop();
        $(window).on("load resize", measure);
        /*$banner.on("mouseenter", function () { isPaused = true; })
               .on("mouseleave", function () { isPaused = false; });*/
    })();
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
});
</script>


@push('scripts')
<script src="{{ asset('js/faq-accordion.js') }}"></script>
@endpush
@endsection