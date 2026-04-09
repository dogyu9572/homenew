<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="robots" content="index, follow">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	@php
		$v = view();
		$pageTitle = trim($v->yieldContent('title', ''));
		$layoutDocumentTitle = $pageTitle !== '' ? $pageTitle : '대한민국 1세대 웹에이전시 홈페이지코리아';
		$pageDescription = trim($v->yieldContent('description', ''));
		$layoutMetaDescription = $pageDescription !== '' ? $pageDescription : '홈페이지코리아';
		// 이중 따옴표 속성 안에서는 작은따옴표를 &#039;로 바꿀 필요 없음(ENT_QUOTES 대신 ENT_COMPAT)
		$layoutDocumentTitleAttr = htmlspecialchars($layoutDocumentTitle, ENT_COMPAT | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
		$layoutMetaDescriptionAttr = htmlspecialchars($layoutMetaDescription, ENT_COMPAT | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
	@endphp
	<title>{!! $layoutDocumentTitleAttr !!}</title>
	<meta name="title" content="{!! $layoutDocumentTitleAttr !!}" />
	<meta name="subject" content="홈페이지코리아" />
	@yield('meta_tags')
    <meta name="description" content="{!! $layoutMetaDescriptionAttr !!}">
    <meta name="author" content="홈페이지코리아">
	<meta name="copyright" content="홈페이지코리아" />
    <meta property="og:title" content="{!! $layoutDocumentTitleAttr !!}">
	<meta property="og:subject" content="홈페이지코리아" />
    <meta property="og:description" content="{!! $layoutMetaDescriptionAttr !!}">
    <meta property="og:image" content="@yield('og_image', asset('images/og_image.jpg'))">
	<link rel="icon" href="/images/favicon.png" type="image/x-icon"/>
    <meta property="og:site_name" content="홈페이지코리아">
    <link rel="canonical" href="@yield('canonical_url', url()->current())" />
	<meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{!! $layoutDocumentTitleAttr !!}">
    <meta name="twitter:description" content="{!! $layoutMetaDescriptionAttr !!}">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og_image.jpg'))">
	
	<meta name="theme-color" content="#ffffff">
	<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
	<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: dark)">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">

	<!-- SEO,GEO 용 사이트 소개 표 - 추가되는 부분은 sga_plus로 각 페이지에서 관리합니다. -->
	<script type="application/ld+json">
	{
		"@@context": "https://schema.org",
		"@@type": "WebPage",
		"name": @json($layoutDocumentTitle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		"description": @json($layoutMetaDescription, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
		"keywords": "@yield('keywords', '홈페이지코리아, 웹 에이전시, 홈페이지 제작')",
		"url": "{{ url()->current() }}",
		"inLanguage": "ko-KR",
		"publisher": {
			"@@type": "Organization",
			"name": "홈페이지코리아",
			"url": "https://homepagekorea.com",
			"logo": {
				"@@type": "ImageObject",
				"url": "https://homepagekorea.com/images/logo.png"
			}
		},
		"breadcrumb": {
			"@@type": "BreadcrumbList",
			"itemListElement": [
				{ "@@type": "ListItem", "position": 1, "name": "홈", "item": "https://homepagekorea.com" }
				@if(!empty($gName))
				,{ "@@type": "ListItem", "position": 2, "name": "{{ $gName }}", "item": "https://homepagekorea.com/{{ request()->segment(1) }}" }
					@if(isset($gNum) && ($gNum == '01' || $gNum == '02' || ($page ?? '') == 'view'))
					,{ "@@type": "ListItem", "position": 3, "name": "{{ $sName ?? '' }}", "item": "{{ strtok(url()->current(), '?') }}" }
					@endif
				@endif
			]
		},
		{
			"@type": "SiteNavigationElement",
			"@id": "https://homepagekorea.com/#main-navi",
			"name": "홈페이지코리아 메인 네비게이션",
			"itemListElement": [
				{ "@type": "ListItem", "position": 1, "name": "홈페이지코리아 소개", "url": "https://homepagekorea.com/about" },
				{ "@type": "ListItem", "position": 2, "name": "SEO·GEO 최적화", "url": "https://homepagekorea.com/service/homepage-seo-geo" },
				{ "@type": "ListItem", "position": 3, "name": "홈페이지 제작", "url": "https://homepagekorea.com/service/homepage-development" },
				{ "@type": "ListItem", "position": 4, "name": "홈페이지 유지보수", "url": "https://homepagekorea.com/service/website-maintenance" },
				{ "@type": "ListItem", "position": 5, "name": "온라인 쇼핑몰 제작", "url": "https://homepagekorea.com/service/ecommerce-website-development" },
				{ "@type": "ListItem", "position": 6, "name": "통합 SI 시스템 개발", "url": "https://homepagekorea.com/service/integrated-si-system-development" },
				{ "@type": "ListItem", "position": 7, "name": "앱 개발", "url": "https://homepagekorea.com/service/mobile-app-development" },
				{ "@type": "ListItem", "position": 8, "name": "맞춤형 AI 솔루션", "url": "https://homepagekorea.com/service/ai-solution" },
				{ "@type": "ListItem", "position": 9, "name": "중견/대기업", "url": "https://homepagekorea.com/industries/enterprise" },
				{ "@type": "ListItem", "position": 10, "name": "학회/협회", "url": "https://homepagekorea.com/industries/academic-association" },
				{ "@type": "ListItem", "position": 11, "name": "공공기관", "url": "https://homepagekorea.com/industries/government" },
				{ "@type": "ListItem", "position": 12, "name": "병원/의료", "url": "https://homepagekorea.com/industries/hospital-medical-website-development" },
				{ "@type": "ListItem", "position": 13, "name": "대학·연구실", "url": "https://homepagekorea.com/industries/university-research-lab-website" },
				{ "@type": "ListItem", "position": 14, "name": "포트폴리오", "url": "https://homepagekorea.com/portfolio" },
				{ "@type": "ListItem", "position": 15, "name": "블로그", "url": "https://homepagekorea.com/blog/" },
				{ "@type": "ListItem", "position": 16, "name": "문의하기", "url": "https://homepagekorea.com/contact/" }
			]
		},
		@yield('sga_plus', '')
	}
	</script>

	<?php
		$css_path		= $_SERVER['DOCUMENT_ROOT'] . '/css/styles.css';
		$reactive_path	= $_SERVER['DOCUMENT_ROOT'] . '/css/reactive.css';
		$ver_styles		= file_exists($css_path)      ? filemtime($css_path)      : date('YmdHis');
		$ver_reactive	= file_exists($reactive_path) ? filemtime($reactive_path) : date('YmdHis');
	?>
    <link rel="preload" href="/css/font/Pretendard-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/css/font/Pretendard-Bold.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="/css/font/Nohemi-Regular.woff2" as="font" type="font/woff2" crossorigin>
	<link rel="stylesheet" href="/css/font.css" media="all">
	<link rel="stylesheet" href="/css/aos.css">
    <link rel="stylesheet" href="/css/styles.css?v=<?=$ver_styles?>">
	<link rel="stylesheet" href="/css/reactive.css?v=<?=$ver_reactive?>">
    <!-- page Styles -->
    @yield('styles')

	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-073X7ZDZQC"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', 'G-073X7ZDZQC');
	</script>
    
    <!-- jQuery -->
    <script src="//code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="/js/aos.js"></script>
    <script src="/js/com.js"></script>
</head>
<body>
	<div class="blind_link">
		@if(isset($gNum) && $gNum == 'main')
			<a href="#main_visual">본문 바로가기</a>
		@else
			<a href="#mainContent">본문 바로가기</a>
		@endif
	</div>
	
	@if(isset($gNum) && $gNum !== '99')
    <header class="header {{ (isset($gNum) && $gNum == 'main') ? 'main' : '' }} {{ (isset($gNum) && $gNum == '03' && ($page ?? '') == 'view' || $gNum == '01' || $gNum == '02') ? 'white_mode' : '' }}">
		<div class="inbox flex items-center justify-between">
			<a href="/" class="logo" aria-label="홈페이지코리아 홈으로 이동"><img src="/images/logo.svg" alt="사이트 로고"></a>
			<div class="gnb_wrap">
				<nav class="gnb" id="main-navi" aria-label="주 메뉴">
					<ul class="flex">
						<li class="menu {{ ($gNum ?? '') == '00' ? 'on' : '' }}">
							<a href="/about" id="main-menu-00" aria-haspopup="true" aria-expanded="{{ ($gNum ?? '') == '00' ? 'true' : 'false' }}"{{ ($gNum ?? '') == '00' ? 'aria-current="page"' : '' }}>ABOUT</a>
						</li>
						<li class="menu {{ ($gNum ?? '') == '01' ? 'on' : '' }}">
							<a href="/service/homepage-seo-geo" id="main-menu-01" aria-haspopup="true" aria-expanded="{{ ($gNum ?? '') == '01' ? 'true' : 'false' }}"{{ ($gNum ?? '') == '01' ? 'aria-current="page"' : '' }} class="pc_vw">SERVICE</a>
							<button type="button" class="mo_vw">SERVICE</button>
							<ul class="snb" aria-labelledby="main-menu-01">
								<li><a href="/service/homepage-seo-geo" @if(($gNum ?? '') == '01' && ($sNum ?? '') == '01') class="on" aria-current="page" @endif>SEO·GEO 최적화</a></li>
								<li><a href="/service/homepage-development" @if(($gNum ?? '') == '01' && ($sNum ?? '') == '02') class="on" aria-current="page" @endif>홈페이지 제작</a></li>
								<li><a href="/service/website-maintenance" @if(($gNum ?? '') == '01' && ($sNum ?? '') == '03') class="on" aria-current="page" @endif>홈페이지 유지보수</a></li>
								<li><a href="/service/ecommerce-website-development" @if(($gNum ?? '') == '01' && ($sNum ?? '') == '04') class="on" aria-current="page" @endif>온라인 쇼핑몰 제작</a></li>
								<li><a href="/service/integrated-si-system-development" @if(($gNum ?? '') == '01' && ($sNum ?? '') == '05') class="on" aria-current="page" @endif>통합 SI 시스템 개발</a></li>
								<li><a href="/service/mobile-app-development" @if(($gNum ?? '') == '01' && ($sNum ?? '') == '06') class="on" aria-current="page" @endif>앱 개발</a></li>
								<li><a href="/service/ai-solution" @if(($gNum ?? '') == '01' && ($sNum ?? '') == '07') class="on" aria-current="page" @endif>맞춤형 AI 솔루션</a></li>
							</ul>
						</li>
						<li class="menu {{ ($gNum ?? '') == '02' ? 'on' : '' }}">
							<a href="/industries/enterprise" id="main-menu-02" aria-haspopup="true" aria-expanded="{{ ($gNum ?? '') == '02' ? 'true' : 'false' }}"{{ ($gNum ?? '') == '03' ? 'aria-current="page"' : '' }} class="pc_vw">INDUSTRY</a>
							<button type="button" class="mo_vw">INDUSTRY</button>
							<ul class="snb" aria-labelledby="main-menu-02">
								<li><a href="/industries/enterprise" @if(($gNum ?? '') == '02' && ($sNum ?? '') == '01') class="on" aria-current="page" @endif>중견/대기업</a></li>
								<li><a href="/industries/academic-association" @if(($gNum ?? '') == '02' && ($sNum ?? '') == '02') class="on" aria-current="page" @endif>학회/협회</a></li>
								<li><a href="/industries/government" @if(($gNum ?? '') == '02' && ($sNum ?? '') == '03') class="on" aria-current="page" @endif>공공기관</a></li>
								<li><a href="/industries/hospital-medical-website-development" @if(($gNum ?? '') == '02' && ($sNum ?? '') == '04') class="on" aria-current="page" @endif>병원/의료</a></li>
								<li><a href="/industries/university-research-lab-website" @if(($gNum ?? '') == '02' && ($sNum ?? '') == '05') class="on" aria-current="page" @endif>대학·연구실</a></li>
							</ul>
						</li>
						<li class="menu {{ ($gNum ?? '') == '03' ? 'on' : '' }}">
							<a href="/portfolio/" id="main-menu-03" aria-haspopup="true" aria-expanded="{{ ($gNum ?? '') == '03' ? 'true' : 'false' }}"{{ ($gNum ?? '') == '03' ? 'aria-current="page"' : '' }} class="pc_vw">PORTFOLIO</a>
							<button type="button" class="mo_vw">PORTFOLIO</button>
							<ul class="snb" aria-labelledby="main-menu-03">
								<li><a href="/portfolio/" @if(($gNum ?? '') == '03' && !request('category')) class="on" aria-current="page" @endif>전체</a></li>
								<li><a href="/portfolio?category=중견%2F대기업" @if(request('category') == '중견/대기업') class="on" aria-current="page" @endif>중견/대기업</a></li>
								<li><a href="/portfolio?category=학회%2F협회" @if(request('category') == '학회/협회') class="on" aria-current="page" @endif>학회/협회</a></li>
								<li><a href="/portfolio?category=공공기관" @if(request('category') == '공공기관') class="on" aria-current="page" @endif>공공기관</a></li>
								<li><a href="/portfolio?category=병원%2F의료" @if(request('category') == '병원/의료') class="on" aria-current="page" @endif>병원/의료</a></li>
								<li><a href="/portfolio?category=대학%2F학원" @if(request('category') == '대학/학원') class="on" aria-current="page" @endif>대학/학원/연구실</a></li>
								<li><a href="/portfolio?category=쇼핑몰" @if(request('category') == '쇼핑몰') class="on" aria-current="page" @endif>쇼핑몰</a></li>
								<li><a href="/portfolio?category=일반" @if(request('category') == '일반') class="on" aria-current="page" @endif>일반</a></li>
							</ul>
						</li>
						<li class="menu {{ ($gNum ?? '') == '04' ? 'on' : '' }}">
							<a href="/blog/" id="main-menu-04" aria-haspopup="true" aria-expanded="{{ ($gNum ?? '') == '04' ? 'true' : 'false' }}"{{ ($gNum ?? '') == '04' ? 'aria-current="page"' : '' }} class="pc_vw">BLOG</a>
							<button type="button" class="mo_vw">BLOG</button>
							<ul class="snb" aria-labelledby="main-menu-04">
								<li><a href="{{ route('blog.blog_list') }}" @if(request()->routeIs('blog.blog_list')) class="on" aria-current="page" @endif>전체</a></li>
								<li><a href="{{ route('blog.blog_list_category', ['blogCategoryPath' => 'team-story']) }}" @if(request()->routeIs('blog.blog_list_category') && request()->route('blogCategoryPath') === 'team-story') class="on" aria-current="page" @endif>팀스토리</a></li>
								<li><a href="{{ route('blog.blog_list_category', ['blogCategoryPath' => 'website-insights']) }}" @if(request()->routeIs('blog.blog_list_category') && request()->route('blogCategoryPath') === 'website-insights') class="on" aria-current="page" @endif>웹 개발 인사이트</a></li>
								<li><a href="{{ route('blog.blog_list_category', ['blogCategoryPath' => 'website-trends']) }}" @if(request()->routeIs('blog.blog_list_category') && request()->route('blogCategoryPath') === 'website-trends') class="on" aria-current="page" @endif>홈페이지 트렌드</a></li>
								<li><a href="{{ route('blog.blog_list_category', ['blogCategoryPath' => 'success-stories']) }}" @if(request()->routeIs('blog.blog_list_category') && request()->route('blogCategoryPath') === 'success-stories') class="on" aria-current="page" @endif>성공사례</a></li>
							</ul>
						</li>
					</ul>
				</nav>
				<a href="/contact/" class="btn_contactus"><span>CONTACT US</span></a>
			</div>
			<button type="button" class="btn_menu" aria-label="전체 메뉴 열기" aria-controls="sitemap-menu" aria-expanded="false">
				<span class="line t" aria-hidden="true"></span>
				<span class="line m" aria-hidden="true"></span>
				<span class="line b" aria-hidden="true"></span>
			</button>
		</div>
		<svg style="display:none;">
			<filter id="glass-refraction">
				<feTurbulence type="fractalNoise" baseFrequency="0.01 0.05" numOctaves="2" result="noise" />
				<feDisplacementMap in="SourceGraphic" in2="noise" scale="30" xChannelSelector="R" yChannelSelector="G" />
			</filter>
		</svg>
	</header>
	<div class="head_snb_bg" aria-hidden="true"></div>
	@endif
    
	<div class="container_wrap {{ (isset($gNum) && $gNum !== 'main') ? 'sub_wrap' : '' }} {{ (isset($gNum) && $gNum !== 'main') ? 'g'.$gNum : '' }} {{ (isset($gNum) && $gNum == '03' && ($page ?? '') == 'view') ? 'portfolio_view_wrap' : '' }}" id="mainContent">
        @yield('content')
    </div>
	@if(isset($gNum) && $gNum !== '99')
    <!-- 팝업 영역 -->
    @yield('popups')
    <script src="{{ asset('js/popup.js') }}"></script>
    @stack('scripts')
	
	<footer class="footer">
		<div class="point" aria-hidden="true"></div>
		<button type="button" class="gotop" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" aria-label="페이지 맨 위로 이동">TOP</button>
		<section class="info" aria-label="협회 및 사무국 연락처 정보">
			<div class="inbox">
				<div class="address_area">
					<ul class="award_list">
						<li><img src="/images/icon_award01.png" alt="벤처확인기업"></li>
						<li><img src="/images/icon_award02.svg" alt="SA 인증"></li>
						<li><img src="/images/icon_award03.svg" alt="WA 인증"></li>
						<li><img src="/images/icon_award04.svg" alt="이노비즈 인증"></li>
						<li><img src="/images/icon_award05.svg" alt="가족친화 우수기업 인증"></li>
					</ul>
					<address>
						<div class="homepagename">홈페이지코리아</div>
						<ul class="office_info" aria-label="회사 연락처">
							<li><strong>대표</strong> <span>염하은</span></li>
							<li><strong>사업자등록번호</strong> <span>107-86-55192</span></li>
							<li class="w100p"><strong>주소</strong> <span>서울특별시 영등포구 경인로 775 에이스하이테크시티 2동 202호</span></li>
							<li class="w100p"><strong>프로젝트 문의</strong> <a href="mailto:sales@homepagekorea.com">sales@homepagekorea.com</a></li>
							<li class="w100p"><strong>유지보수 문의</strong> <a href="mailto:superweb@homepagerkorea.com ">superweb@homepagerkorea.com</a></li>
						</ul>
					</address>
					<a href="/terms/privacy_policy" class="btn_privacy_policy">개인정보처리방침</a>
					<p class="copy"><span class="sound_only">Copyright</span> © 2026 HOMEPAGEKREA ALL RIGHTS RESERVED.</p>
				</div>
				<nav class="footer_menus" aria-label="푸터 메뉴">
					<ul class="flex">
						<li class="menu {{ ($gNum ?? '') == '00' ? 'on' : '' }}">
							<a href="/about" id="main-menu-00" aria-haspopup="true" aria-expanded="{{ ($gNum ?? '') == '00' ? 'true' : 'false' }}"{{ ($gNum ?? '') == '00' ? 'aria-current="page"' : '' }}>ABOUT</a>
						</li>
						<li class="menu {{ ($gNum ?? '') == '01' ? 'on' : '' }}">
							<a href="/service/homepage-seo-geo" id="booter-menu-01" aria-haspopup="true" aria-expanded="{{ ($gNum ?? '') == '01' ? 'true' : 'false' }}"{{ ($gNum ?? '') == '01' ? 'aria-current="page"' : '' }}>SERVICE</a>
							<ul class="snb" aria-labelledby="booter-menu-01">
								<li>
									<a href="/service/homepage-seo-geo" {{ (($gNum ?? '') == '01' && ($sNum ?? '') == '01') ? 'class="on" aria-current="page"' : '' }}>SEO·GEO 최적화</a>
									<a href="/service/homepage-development" {{ (($gNum ?? '') == '01' && ($sNum ?? '') == '02') ? 'class="on" aria-current="page"' : '' }}>홈페이지 제작</a>
									<a href="/service/website-maintenance" {{ (($gNum ?? '') == '01' && ($sNum ?? '') == '03') ? 'class="on" aria-current="page"' : '' }}>홈페이지 유지보수</a>
									<a href="/service/ecommerce-website-development" {{ (($gNum ?? '') == '01' && ($sNum ?? '') == '04') ? 'class="on" aria-current="page"' : '' }}>온라인 쇼핑몰 제작</a>
									<a href="/service/integrated-si-system-development" {{ (($gNum ?? '') == '01' && ($sNum ?? '') == '05') ? 'class="on" aria-current="page"' : '' }}>통합 SI 시스템 개발</a>
									<a href="/service/mobile-app-development" {{ (($gNum ?? '') == '01' && ($sNum ?? '') == '06') ? 'class="on" aria-current="page"' : '' }}>앱 개발</a>
									<a href="/service/ai-solution" {{ (($gNum ?? '') == '01' && ($sNum ?? '') == '07') ? 'class="on" aria-current="page"' : '' }}>맞춤형 AI 솔루션</a>
								</li>	
							</ul>
						</li>
						<li class="menu {{ ($gNum ?? '') == '02' ? 'on' : '' }}">
							<a href="/industries/about" id="booter-menu-02" aria-haspopup="true" aria-expanded="{{ ($gNum ?? '') == '02' ? 'true' : 'false' }}"{{ ($gNum ?? '') == '03' ? 'aria-current="page"' : '' }}>INDUSTRY</a>
							<ul class="snb" aria-labelledby="booter-menu-02">
								<li>
									<a href="/industries/enterprise" {{ (($gNum ?? '') == '02' && ($sNum ?? '') == '01') ? 'class="on" aria-current="page"' : '' }}>중견/대기업</a>
									<a href="/industries/academic-association" {{ (($gNum ?? '') == '02' && ($sNum ?? '') == '02') ? 'class="on" aria-current="page"' : '' }}>학회/협회</a>
									<a href="/industries/government" {{ (($gNum ?? '') == '02' && ($sNum ?? '') == '03') ? 'class="on" aria-current="page"' : '' }}>공공기관</a>
									<a href="/industries/hospital-medical-website-development" {{ (($gNum ?? '') == '02' && ($sNum ?? '') == '04') ? 'class="on" aria-current="page"' : '' }}>병원/의료</a>
									<a href="/industries/university-research-lab-website" {{ (($gNum ?? '') == '02' && ($sNum ?? '') == '05') ? 'class="on" aria-current="page"' : '' }}>대학·연구실</a>
								</li>	
							</ul>
						</li>
						<li class="menu {{ ($gNum ?? '') == '03' ? 'on' : '' }}">
							<a href="/portfolio/" id="booter-menu-03" aria-haspopup="true" aria-expanded="{{ ($gNum ?? '') == '03' ? 'true' : 'false' }}"{{ ($gNum ?? '') == '03' ? 'aria-current="page"' : '' }}>PORTFOLIO</a>
							<ul class="snb" aria-labelledby="booter-menu-03">
								<li><a href="/portfolio/" @if(($gNum ?? '') == '03' && !request('category')) class="on" aria-current="page" @endif>전체</a></li>
								<li><a href="/portfolio?category=중견%2F대기업" @if(request('category') == '중견/대기업') class="on" aria-current="page" @endif>중견/대기업</a></li>
								<li><a href="/portfolio?category=학회%2F협회" @if(request('category') == '학회/협회') class="on" aria-current="page" @endif>학회/협회</a></li>
								<li><a href="/portfolio?category=공공기관" @if(request('category') == '공공기관') class="on" aria-current="page" @endif>공공기관</a></li>
								<li><a href="/portfolio?category=병원%2F의료" @if(request('category') == '병원/의료') class="on" aria-current="page" @endif>병원/의료</a></li>
								<li><a href="/portfolio?category=대학%2F학원" @if(request('category') == '대학/학원') class="on" aria-current="page" @endif>대학/학원/연구실</a></li>
								<li><a href="/portfolio?category=쇼핑몰" @if(request('category') == '쇼핑몰') class="on" aria-current="page" @endif>쇼핑몰</a></li>
								<li><a href="/portfolio?category=일반" @if(request('category') == '일반') class="on" aria-current="page" @endif>일반</a></li>
							</ul>
						</li>
						<li class="menu {{ ($gNum ?? '') == '04' ? 'on' : '' }}">
							<a href="/blog/" id="booter-menu-04" aria-haspopup="true" aria-expanded="{{ ($gNum ?? '') == '04' ? 'true' : 'false' }}"{{ ($gNum ?? '') == '04' ? 'aria-current="page"' : '' }}>BLOG</a>
							<ul class="snb" aria-labelledby="booter-menu-04">
								<li><a href="{{ route('blog.blog_list') }}" @if(request()->routeIs('blog.blog_list')) class="on" aria-current="page" @endif>전체</a></li>
								<li><a href="{{ route('blog.blog_list_category', ['blogCategoryPath' => 'team-story']) }}" @if(request()->routeIs('blog.blog_list_category') && request()->route('blogCategoryPath') === 'team-story') class="on" aria-current="page" @endif>팀스토리</a></li>
								<li><a href="{{ route('blog.blog_list_category', ['blogCategoryPath' => 'website-insights']) }}" @if(request()->routeIs('blog.blog_list_category') && request()->route('blogCategoryPath') === 'website-insights') class="on" aria-current="page" @endif>웹 개발 인사이트</a></li>
								<li><a href="{{ route('blog.blog_list_category', ['blogCategoryPath' => 'website-trends']) }}" @if(request()->routeIs('blog.blog_list_category') && request()->route('blogCategoryPath') === 'website-trends') class="on" aria-current="page" @endif>홈페이지 트렌드</a></li>
								<li><a href="{{ route('blog.blog_list_category', ['blogCategoryPath' => 'success-stories']) }}" @if(request()->routeIs('blog.blog_list_category') && request()->route('blogCategoryPath') === 'success-stories') class="on" aria-current="page" @endif>성공사례</a></li>
							</ul>
						</li>
					</ul>
				</nav>
				<div class="btm_space" aria-hidden="true"><img src="/images/txt_btm_space.svg" alt="HOMEPAGEKOREA"></div>
			</div>
		</section>
	</footer>
	@endif
	
	<script type="text/javascript" src="//wcs.naver.net/wcslog.js"> </script> 
	<script type="text/javascript"> 
	if (!wcs_add) var wcs_add={};
	wcs_add["wa"] = "s_379aa81fac95";
	if (!_nasa) var _nasa={};
	if(window.wcs){
	wcs.inflow();
	wcs_do();
	}
	</script>
	<!-- MR Script Ver 2.0 -->
	<script async="true" src="//log1.toup.net/mirae_log_chat_common.js?adkey=nqscn" charset="UTF-8"></script>
	<!-- MR Script END Ver 2.0 -->
	
	<script type="text/javascript">
	(function(w, d, a){
		w.__beusablerumclient__ = {
			load : function(src){
				var b = d.createElement("script");
				b.src = src; b.async=true; b.type = "text/javascript";
				d.getElementsByTagName("head")[0].appendChild(b);
			}
		};w.__beusablerumclient__.load(a + "?url=" + encodeURIComponent(d.URL));
	})(window, document, "//rum.beusable.net/load/b231017e175233u115");
	</script>

</body>
</html>
