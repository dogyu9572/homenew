@extends('layouts.app')
@section('title', $sName)
@section('sName', $sName)
@section('description', '홈페이지코리아는 단순한 웹 제작사가 아닙니다. 조직의 핵심 업무를 담는 시스템을 설계하고 구축하는 회사입니다.')

@section('content')
<main class="sub_contents_wrap about_wrap" id="contact_page_root" data-contact-success="{{ session('contact_submitted') ? '1' : '0' }}">
	<h1 class="sound_only">홈페이지코리아 {{ $sName }}</h1>

	<section class="about_video_area">
		<video class="bg_video" autoplay muted loop playsinline aria-hidden="true">
			<source src="/video/video_sub_about.mp4" type="video/mp4">
		</video>
		<section class="about01 title_color_slide" aria-labelledby="about-head-title" data-header="dark">
			<div class="inner">
				<h2 id="about-head-title" class="">대한민국 <strong class="c_org">1세대 웹에이전시</strong>로 <br>고객의 성공적인 디지털 전환을 함께합니다.</h2>
				<div class="btns flex_center mojo_aos">
					<a href="/portfolio/" class="btn_link slim">포트폴리오 보러가기</a>
					<a href="/contact/" class="btn_link slim">프로젝트 문의하기</a>
				</div>
			</div>
		</section>

		<section class="about02 title_color_slide" aria-labelledby="about-body-title" data-header="dark">
			<div class="inner">
				<div class="flex">
					<div class="left">
						<h2 id="about-body-title">홈페이지코리아는 단순한 <br class="pc_vw">웹 제작사가 아닙니다.<br/><strong class="c_org">조직의 핵심 업무를 담는 시스템</strong>을 <br class="pc_vw">설계하고 구축하는 회사입니다.</h2>
						<p>
							대형 행사, 대규모 접수, 공공 서비스 운영, 기업 브랜딩 고도화 등 <br class="pc_vw">
							결정적인 순간에 안정적으로 작동해야 하는 시스템을 구축합니다.<br/>
							<br/>
							AI 시대일수록 기획력과 판단력이 탁월한 회사가 필요합니다.<br/>
							기업의 시스템은 단순 자동화가 아니라, <br class="pc_vw">
							신중한 의사결정과 맥락 이해가 담긴 구조로 설계되어야 합니다.<br/>
							조직의 구조, 담당자의 부담, 의사결정자의 고민, 사용자의 불편...<br/>
							<br/>
							홈페이지코리아는 왜 이 기능이 지금 필요한지, 이 구조가 조직에 적합한지, <br class="pc_vw">
							현재 서비스를 한 차원 높일 수 있는지까지 고려하여 확장 가능한 구조를 설계하고, <br class="pc_vw">
							AI를 활용해 빠르고 효율적으로 구현합니다.<br/>
							중대형 프로젝트는 정책 변경, 내부 조직 개편 등 늘 변수로 가득합니다.<br/>
							우리는 비정형적 상황 속에서도 가장 현실적인 해답을 찾고, <br class="pc_vw">
							항상 대안을 준비하는 파트너입니다.<br/>
							<br/>
							<strong>우리는 비정형적 상황 속에서도 가장 현실적인 해답을 찾고, <br class="pc_vw">
							항상 대안을 준비하는 파트너입니다.</strong>
						</p>
					</div>
					<div class="right">
						<ul class="flex">
							<li class="i1">공공기관 · 대기업 대상 <br class="pc_vw">엔터프라이즈 웹 시스템 구축</li>
							<li class="i2">대형 행사 · 대규모 접수 · <br class="pc_vw">브랜딩 고도화 등 <br class="pc_vw">결정적 순간의 시스템 구현</li>
							<li class="i3">AI 기반 솔루션 개발 및 <br class="pc_vw">장기 운영 · 유지보수</li>
							<li class="i4">장기(3년 이상) <br class="pc_vw">유지보수 계약 유지율 78.6%</li>
						</ul>
					</div>
				</div>
			</div>
		</section>
		
		<section class="about03" aria-labelledby="Mission-Vision-title" data-header="dark">
			<div class="inner">
				<h2 id="Mission-Vision-title" class="sound_only">미션,비전,핵심가치</h2>
				<ul class="mission_vision_list">
					<li class="i1"><span aria-hidden="true">Vision</span><strong>비전</strong><p>공공·대기업 대상 <br class="pc_vw">중대형 디지털 시스템 구축 전문기업</p></li>
					<li class="i2"><span aria-hidden="true">Mission</span><strong>미션</strong><p>기획부터 운영까지, 고객의 결정적 순간을 함께하는 <br class="pc_vw">지속 가능한 파트너 입니다.</p></li>
					<li class="i3"><span aria-hidden="true">Core Values</span><strong>핵심가치</strong><p>역지사지·경청·책임·프로정신으로 <br class="pc_vw">신뢰를 쌓고 해결책을 찾습니다.</p></li>
				</ul>
			</div>
		</section>
	</section>

	<section class="about04" aria-labelledby="history-title" data-header="light">
		<div class="inner">
			<h2 id="history-title" class="font_eng">HomepageKorea <strong>HISTORY</strong></h2>
			<ul class="history_list">
				<li>
					<h3><button type="button">2023~</button></h3>
					<ul class="list">
						<li>공공·관광·중소기업 디지털 서비스 수행 기업으로 연속 선정</li>
						<li>정부 지원사업(바우처·스마트서비스) 공식 공급기업 참여</li>
						<li>한국관광공사 서비스 제공기업 선정 및 재선정</li>
						<li>AI 기반 채용/추천 시스템 특허 출원 (특허 제 00-0000000호)</li>
						<li><strong>웹어워드코리아 IT서비스 분야 최우수상 수상</strong></li>
						<li>신임 대표이사 취임을 통한 사업 전환 및 성장 기반 마련</li>
					</ul>
				</li>
				<li>
					<h3><button type="button">2011 ~ 2022</button></h3>
					<ul class="list">
						<li>내용</li>
					</ul>
				</li>
				<li>
					<h3><button type="button">2004 ~ 2010</button></h3>
					<ul class="list">
						<li>내용</li>
					</ul>
				</li>
				<li>
					<h3><button type="button">1999 ~ 2003</button></h3>
					<ul class="list">
						<li>내용</li>
					</ul>
				</li>
			</ul>
		</div>
	</section>

	<section class="about05" aria-labelledby="organization-title" data-header="dark">
		<div class="inner">
			<h2 id="organization-title" class="font_eng">HOMEPAGEKOREA <br><strong>ORGANIZATION</strong></h2>
			<div class="organization_chart">
				<div class="ceo">CEO</div>
				<div class="team"><span aria-hidden="true">STRATEGIC PLANNING</span><strong>전략기획팀</strong></div>
				<div class="team"><span aria-hidden="true">MANAGEMENT SUPPORT</span><strong>경영지원팀</strong></div>
				<div class="team_colm">
					<div class="team"><span aria-hidden="true">PLANNING</span><strong>기획팀</strong></div>
					<div class="team_group">
						<div class="team"><span aria-hidden="true">UI/UX</span><strong>UI/UX팀</strong></div>
						<div class="group">
							<div class="team"><span aria-hidden="true">DESIGN</span><strong>디자인팀</strong></div>
							<div class="team"><span aria-hidden="true">PUBLISHING</span><strong>퍼블리싱팀</strong></div>
						</div>
					</div>
					<div class="team"><span aria-hidden="true">DEVELOPMENT</span><strong>개발팀</strong></div>
					<div class="team"><span aria-hidden="true">MAINTENANCE</span><strong>유지보수팀</strong></div>
				</div>
			</div>
		</div>
	</section>

	<section class="about06" aria-labelledby="about01-title" data-header="light">
		<div class="inner">
			<h2 id="about01-title" class="font_eng">CONTACT</h2>
			<ul>
				<li class="i1">서울특별시 영등포구 문래동 경인로 775 에이스하이테크시티 2동 202호</li>
				<li class="i2"><strong>E-MAIL</strong>sales@homepagekorea.com</li>
				<li class="i3"><strong>TEL</strong>1644-8070</li>
			</ul>
		</div>
		<div class="map_area">
			
		</div>
	</section>

</main>

<script>
//about01
	
</script>

@endsection

@push('scripts')
<script src="{{ asset('js/contact-form.js') }}"></script>
@endpush
