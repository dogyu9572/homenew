@extends('layouts.app')
@section('title', $sName)
@section('sName', $sName)
@section('description', '홈페이지코리아는 단순한 웹 제작사가 아닙니다. 조직의 핵심 업무를 담는 시스템을 설계하고 구축하는 회사입니다.')

@section('content')
<main class="sub_contents_wrap about_wrap" id="contact_page_root">
	<h1 class="sound_only">홈페이지코리아 {{ $sName }}</h1>

	<section class="about_video_area" aria-label="회사 소개" data-header="dark">
		<video class="bg_video" autoplay muted loop playsinline aria-hidden="true">
			<source src="/video/video_sub_about.mp4" type="video/mp4">
		</video>
		<section class="about01 title_color_slide" aria-labelledby="about-head-title">
			<div class="outbox">
				<div class="inner">
					<h2 id="about-head-title" class="ani_title">
						<span>대한민국 <strong class="c_org">1세대 웹에이전시</strong>로</span> <br>
						<span>고객의 성공적인 디지털 전환을 함께합니다.</span>
					</h2>
					<div class="btns flex_center mojo_aos">
						<a href="/portfolio/" class="btn_link slim">포트폴리오 보러가기</a>
						<a href="{{ route('contact.contact', ['source_type' => 'about', 'source_url' => url()->current(), 'source_title' => $sName]) }}" class="btn_link slim">프로젝트 문의하기</a>
					</div>
				</div>
			</div>
		</section>

		<section class="about02 title_color_slide" aria-labelledby="about-body-title">
			<div class="inner">
				<div class="flex">
					<div class="left">
						<h2 id="about-body-title" class="ani_title"><span>홈페이지코리아는 단순한 <br class="pc_vw">웹 제작사가 아닙니다.</span><br/><span><strong class="c_org">조직의 핵심 업무를 담는 시스템</strong>을 <br class="pc_vw">설계하고 구축하는 회사입니다.</span></h2>
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
							<br/>
							<strong>우리는 비정형적 상황 속에서도 가장 현실적인 해답을 찾고, <br class="pc_vw">
							항상 대안을 준비하는 파트너입니다.</strong>
						</p>
					</div>
					<div class="right">
						<ul class="flex" role="list">
							<li class="i1"><p>공공기관 · 대기업 대상 <br class="pc_vw">엔터프라이즈 웹 시스템 구축</p></li>
							<li class="i2"><p>대형 행사 · 대규모 접수 · <br class="pc_vw">브랜딩 고도화 등 <br class="pc_vw">결정적 순간의 시스템 구현</p></li>
							<li class="i3"><p>AI 기반 솔루션 개발 및 <br class="pc_vw">장기 운영 · 유지보수</p></li>
							<li class="i4"><p>장기(3년 이상) <br class="pc_vw">유지보수 계약 유지율 78.6%</p></li>
						</ul>
					</div>
				</div>
			</div>
		</section>
		
		<section class="about03" aria-labelledby="Mission-Vision-title" data-header="dark">
			<div class="inner">
				<h2 id="Mission-Vision-title" class="sound_only">미션, 비전, 핵심가치</h2>
				<ul class="mission_vision_list">
					<li class="i1"><span aria-hidden="true">Vision</span><h3>비전</h3><p>공공·대기업 대상 <br class="pc_vw">중대형 디지털 시스템 구축 전문기업</p></li>
					<li class="i2"><span aria-hidden="true">Mission</span><h3>미션</h3><p>기획부터 운영까지, 고객의 결정적 순간을 함께하는 <br class="pc_vw">지속 가능한 파트너 입니다.</p></li>
					<li class="i3"><span aria-hidden="true">Core Values</span><h3>핵심가치</h3><p>역지사지·경청·책임·프로정신으로 <br class="pc_vw">신뢰를 쌓고 해결책을 찾습니다.</p></li>
				</ul>
			</div>
		</section>
	</section>

	<section class="about04" aria-labelledby="history-title" data-header="light">
		<div class="line_wrap">
			<i class="t1" aria-hidden="true"></i><i class="b1" aria-hidden="true"></i>
			<i class="t2" aria-hidden="true"></i><i class="b2" aria-hidden="true"></i>
			<i class="t3" aria-hidden="true"></i><i class="b3" aria-hidden="true"></i>
			<i class="t4" aria-hidden="true"></i><i class="b4" aria-hidden="true"></i>
			<svg class="line_svg" aria-hidden="true"></svg>
		</div>
		<div class="inner">
			<h2 id="history-title" class="font_eng">HOMEPAGEKOREA <strong>HISTORY</strong></h2>
			<ul class="history_list">
				<li>
					<h3><button type="button" aria-expanded="true" aria-controls="history-panel-1">현재 ~ 2023</button></h3>
					<ul class="list" id="history-panel-1">
						<li>2023 공공·관광·중소기업 디지털 서비스 수행 기업으로 연속 선정</li>
						<li>2023정부 지원사업(바우처·스마트서비스) 공식 공급기업 참여</li>
						<li>2023 한국관광공사 서비스 제공기업 선정 및 재선정</li>
						<li>2024 AI 기반 채용/추천 시스템 특허 출원 (출원번호 제 10-2024-0037018호)</li>
						<li>2024 웹어워드코리아 IT서비스 분야 최우수상 수상</li>
						<li>2024 신임 대표이사 취임을 통한 사업 전환 및 성장 기반 마련</li>
					</ul>
				</li>
				<li>
					<h3><button type="button" aria-expanded="false" aria-controls="history-panel-2">2022 ~ 2011</button></h3>
					<ul class="list" id="history-panel-2">
						<li><strong>2022 HUCMS (Homepage User Contents Management System) 특허 등록 (특허 제10-2428639호)</strong></li>
						<li>2021 웹어워드코리아 14년 연속 수상, 최다 수상 기록</li>
						<li>2020 ADT캡스 리뉴얼 공동수주 (일본계 대기업)</li>
						<li>2019 서울형 강소기업 선정</li>
						<li>2018 청년친화 강소기업 선정 (고용노동부)</li>
						<li>2017 강소기업 선정 (고용노동부)</li>
						<li><strong>2013 SNS를 이용한 기부시스템 특허 출원(특허 제10-1281234호)</strong></li>
						<li>2011 ㈜홈페이지코리아 브랜드 통합 출범 / 3,300여 프로젝트 성공 완료</li>
					</ul>
				</li>
				<li>
					<h3><button type="button" aria-expanded="false" aria-controls="history-panel-3">2010 ~ 2004</button></h3>
					<ul class="list" id="history-panel-3">
						<li>2009 인텔 온라인광고대행사 선정</li>
						<li>2008 이노비즈 기술혁신형 기업 선정</li>
						<li>2006 벤처기업 인증 획득 / 기업부설연구소 설립</li>
						<li>2005 온미디어그룹 통합 수주 (OCN·수퍼액션·스토리온·캐치온·온스타일)</li>
						<li>2005 캄보디아 17개 은행 신용정보시스템 개발사 선정</li>
						<li>2005 한국HP 웹에이전시 1순위 선정</li>
						<li>2004 ㈜오리온 웹에이전시 선정 / 한국HP eKorea 파트너 선정</li>
					</ul>
				</li>
				<li>
					<h3><button type="button" aria-expanded="false" aria-controls="history-panel-4">2003 ~ 1999</button></h3>
					<ul class="list" id="history-panel-4">
						<li>2002 대한민국 No.1 영화채널 통합 개발 (OCN·Tooniverse 외 5개)</li>
						<li>1999 웹에이전시 설립</li>
					</ul>
				</li>
			</ul>
		</div>
	</section>

	<section class="about05" aria-labelledby="organization-title" data-header="dark">
		<div class="inner">
			<h2 id="organization-title" class="font_eng">HOMEPAGEKOREA <br><strong>ORGANIZATION</strong></h2>
			<ul class="organization_chart">
				<li class="ceo">CEO</li>
				<li class="team"><div class="inbox"><span aria-hidden="true">STRATEGIC PLANNING</span><strong>전략기획팀</strong></div></li>
				<li class="team"><div class="inbox"><span aria-hidden="true">MANAGEMENT SUPPORT</span><strong>경영지원팀</strong></div></li>
				<li class="team_colm">
					<ul>
						<li class="team"><div class="inbox"><span aria-hidden="true">PLANNING</span><strong>기획팀</strong></div></li>
						<li class="team_group">
							<ul>
								<li class="team"><div class="inbox"><span aria-hidden="true">UI/UX</span><strong>UI/UX팀</strong></div></li>
								<li class="group">
									<ul>
										<li class="team"><div class="inbox"><span aria-hidden="true">DESIGN</span><strong>디자인팀</strong></div></li>
										<li class="team"><div class="inbox"><span aria-hidden="true">PUBLISHING</span><strong>퍼블리싱팀</strong></div></li>
									</ul>
								</li>
							</ul>
						</li>
						<li class="team"><div class="inbox"><span aria-hidden="true">DEVELOPMENT</span><strong>개발팀</strong></div></li>
						<li class="team"><div class="inbox"><span aria-hidden="true">MAINTENANCE</span><strong>유지보수팀</strong></div></li>
					</ul>
				</li>
			</ul>
		</div>
	</section>

	<section class="about06" aria-labelledby="about01-title" data-header="light">
		<div class="line_wrap">
			<i class="t1" aria-hidden="true"></i><i class="b1" aria-hidden="true"></i>
			<i class="t2" aria-hidden="true"></i><i class="b2" aria-hidden="true"></i>
			<svg class="line_svg" aria-hidden="true"></svg>
		</div>
		<div class="inner">
			<h2 id="about01-title" class="font_eng">CONTACT</h2>
			<ul class="contact_list">
				<li class="i1">서울특별시 영등포구 문래동 경인로 775 에이스하이테크시티 2동 202호</li>
				<li class="i2"><strong>E-MAIL</strong> <a href="mailto:sales@homepagekorea.com">sales@homepagekorea.com</a></li>
				<li class="i3"><strong>TEL</strong> <a href="tel:16448070">1644-8070</a></li>
			</ul>
		</div>
		<div class="map_area" role="region" aria-label="오시는 길 지도">
			<div id="map"></div>
			<div class="map_controls" aria-label="지도 컨트롤">
				<button type="button" class="map_btn btn_zoom_in" aria-label="지도 확대">
					<svg width="14" height="14" viewBox="0 0 14 14" aria-hidden="true">
						<line x1="7" y1="0" x2="7" y2="14" stroke="currentColor" stroke-width="2"/>
						<line x1="0" y1="7" x2="14" y2="7" stroke="currentColor" stroke-width="2"/>
					</svg>
				</button>
				<button type="button" class="map_btn btn_zoom_out" aria-label="지도 축소">
					<svg width="14" height="2" viewBox="0 0 14 2" aria-hidden="true">
						<line x1="0" y1="1" x2="14" y2="1" stroke="currentColor" stroke-width="2"/>
					</svg>
				</button>
				<button type="button" class="map_btn btn_reset" aria-label="원래 위치로">
					<svg width="14" height="14" viewBox="0 0 14 14" aria-hidden="true">
						<path d="M7 1C3.686 1 1 3.686 1 7s2.686 6 6 6 6-2.686 6-6" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/>
						<path d="M10 4l2.5-2.5M12.5 1.5V5H9" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			</div>
		</div>
	</section>

</main>

@endsection

@push('scripts')
<script src="https://dapi.kakao.com/v2/maps/sdk.js?appkey=fb0bc87a6e3abc550a07197cacf991f0&autoload=false"></script>
<script src="{{ asset('js/contact-form.js') }}"></script>
<script src="{{ asset('js/about.js') }}"></script>
@endpush
