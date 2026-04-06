{{-- 고객 안내 메일: /mailform 미리보기와 동일 레이아웃 (발송 시에만 {{ $contact->email }} 치환) --}}
@php
    $appUrl = rtrim((string) config('app.url'), '/');
@endphp
<section style="width:600px; margin:0 auto; font-family:'Pretandard', sans-serif;">
	<section style="display:flex; align-items:center; gap:10px; padding:30px 0; border-bottom:#ddd 1px solid;">
		<img src="{{ $appUrl }}/images/logo.png" alt="홈페이지코리아">
		<span style="font-size:24px; color:#040404; font-weight:600;">문의 접수 안내</span>
	</section>
	<div style="width:100%; height:5px; background:linear-gradient(90deg,#142B93,#0391E7);" aria-hidden="true"></div>

	<section style="padding:80px 0;">
		<h1 style="font-size:18px; color:#040404; font-weight:600; line-height:1.2; margin-bottom:30px;">{{ $contact->email }} 님의 문의가 정상적으로 접수되었습니다.</h1>
		<p style="font-size:15px; color:#555; line-height:1.4;">문의하신 내용을 확인하여 신속하게 답변드리겠습니다.<br/>※ 평일 비업무 시간 또는 공휴일에 문의를 주신 경우 영업일에 확인 후 답변드리겠습니다.</p>
		<p style="font-size:15px; color:#555; line-height:1.4; margin-top:10px;">홈페이지코리아를 이용해 주시는 고객님께 진심으로 감사드리며,<br/>고객님의 말씀에 항상 귀기울이는 홈페이지코리아가 되겠습니다.</p>
		<div style="padding:20px; text-align:center; margin-top:50px;">
			<a href="{{ $appUrl }}/about/" target="_blank" rel="noopener noreferrer" style="padding:15px; border-radius:30px; margin:0 5px; font-size:15px; color:#fff; background:linear-gradient(90deg,#142B93,#0391E7);">회사소개서 바로가기</a>
			<a href="{{ $appUrl }}/ebook/index.html#page=1" target="_blank" rel="noopener noreferrer" style="padding:15px; border-radius:30px; margin:0 5px; font-size:15px; color:#fff; background:linear-gradient(90deg,#142B93,#0391E7);">AI 시대, 성과를 만드는 홈페이지의 비밀 Ebook 바로가기</a>
		</div>
	</section>
	<div style="width:100%; height:5px; background:linear-gradient(90deg,#142B93,#0391E7);" aria-hidden="true"></div>

	<section style="padding-top:50px;">
		<div style="font-size:15px; color:#040404; font-weight:700; margin-bottom:10px;">홈페이지코리아</div>
		<p style="font-size:14px; color:#555; line-height:1.4;"><strong style="padding-right:5px;">주소</strong>서울특별시 영등포구 경인로 775 에이스하이테크시티 2동 202호</p>
		<p style="font-size:14px; color:#555; line-height:1.4; margin-top:5px;"><strong style="padding-right:5px;">프로젝트 문의</strong>sales@homepagekorea.com</p>
		<p style="font-size:14px; color:#555; line-height:1.4; margin-top:5px;"><strong style="padding-right:5px;">유지보수 문의</strong>superweb@homepagerkorea.com</p>
	</section>
</section>
