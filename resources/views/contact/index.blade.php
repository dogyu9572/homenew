@extends('layouts.app')
@section('title', $sName)
@section('sName', $sName)
@section('description', '홈페이지 기획부터 SEO 최적화, 사용자 경험 개선까지, 성공적인 온라인 비즈니스를 위한 유용한 인사이트를 만나보세요.')
@section('sga_plus')
,"mainEntity": {
    "@@type": "Organization",
    "name": "홈페이지코리아",
    "url": "https://homepagekorea.com",
    "email": "sales@homepagekorea.com",
    "address": {
        "@@type": "PostalAddress",
        "addressCountry": "KR",
        "addressRegion": "서울특별시",
        "streetAddress": "영등포구 경인로 775 에이스하이테크시티 2동 202호"
    },
    "contactPoint": {
        "@@type": "ContactPoint",
        "contactType": "customer service",
        "email": "sales@homepagekorea.com",
        "availableLanguage": "Korean",
        "hoursAvailable": {
            "@@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
            "opens": "09:00",
            "closes": "18:00"
        }
    }
}
@endsection

@section('content')
<main class="sub_contents_wrap">

	<section class="svisual g{{ $gNum }}" aria-labelledby="sub-visual-title">
		<div class="inner">
			{{-- 현재 위치 정보를 제공하는 내비게이션 --}}
			<nav class="location" aria-label="현재 위치">
				<a href="/" class="home" aria-label="홈으로 이동">HOME</a>
				<span>{{ $gName }}</span>
				@if(isset($gNum) && ($gNum == '01' || $gNum == '02'))<span aria-current="location">{{ $sName }}</span>@endif
			</nav>
			<h1 class="sound_only">홈페이지코리아 문의하기</h1>
			<div id="sub-visual-title" class="title" aria-hidden="true">{{ $sName ?? '' }}</div>
			<h2 class="h2">25년 노하우로 최적의 방향을 제시해드립니다.</h2>
		</div>
	</section>

	<section class="contact_us_wrap" aria-labelledby="contact-us-title">
		<div class="inner">
			<div class="contact_us_inputs">
				<div class="tit">
					<h2 id="contact-us-title">궁금하신 사항을 남겨주시면<br>최대 2일 내 연락 드립니다.</h2>
					<p>어떤 프로젝트를 계획중이신가요?</p>
					<ul class="imgs">
						<li class="i1"><img src="/images/img_contact_us01.png" alt="" aria-hidden="true"><span>UI/UX Design</span></li>
						<li class="i2"><img src="/images/img_contact_us02.png" alt="" aria-hidden="true"><span>Development</span></li>
						<li class="i3"><img src="/images/img_contact_us03.png" alt="" aria-hidden="true"><span>Solution</span></li>
					</ul>
				</div>
				<div class="con">
					<form action="#" method="post" novalidate id="contact_form">
						<div class="flex">
							<dl class="w100p">
								<dt><label for="input1">소속<i aria-hidden="true">*</i><span class="sound_only">(필수)</span></label></dt>
								<dd><input type="text" id="input1" name="company" class="text w100p" placeholder="소속을 입력해주세요." required aria-required="true"></dd>
							</dl>
							<dl>
								<dt><label for="input2">담당자 성함<i aria-hidden="true">*</i><span class="sound_only">(필수)</span></label></dt>
								<dd><input type="text" id="input2" name="name" class="text w100p" placeholder="담당자 성함을 입력해주세요." required aria-required="true"></dd>
							</dl>
							<dl>
								<dt><label for="input3">이메일<i aria-hidden="true">*</i><span class="sound_only">(필수)</span></label></dt>
								<dd><input type="email" id="input3" name="email" class="text w100p" placeholder="이메일을 입력해주세요." required aria-required="true"></dd>
							</dl>
							<fieldset class="w100p">
								<legend class="dt_tit">관심 서비스 <span>(중복선택)</span> <i aria-hidden="true">*</i><span class="sound_only">(필수)</span></legend>
								<div class="checks">
									<label class="check"><input type="checkbox" name="service[]" value="홈페이지 제작"><i aria-hidden="true"></i>홈페이지 제작</label>
									<label class="check"><input type="checkbox" name="service[]" value="홈페이지 유지보수"><i aria-hidden="true"></i>홈페이지 유지보수</label>
									<label class="check"><input type="checkbox" name="service[]" value="온라인 쇼핑몰 제작"><i aria-hidden="true"></i>온라인 쇼핑몰 제작</label>
									<label class="check"><input type="checkbox" name="service[]" value="시스템 개발"><i aria-hidden="true"></i>시스템 개발(예약/ERP/CMS 등)</label>
									<label class="check"><input type="checkbox" name="service[]" value="앱 개발"><i aria-hidden="true"></i>앱 개발</label>
									<label class="check"><input type="checkbox" name="service[]" value="맞춤형 AI 솔루션"><i aria-hidden="true"></i>맞춤형 AI 솔루션</label>
								</div>
							</fieldset>
							<dl class="w100p">
								<dt><label for="input5">현재 사이트가 있는 경우 알려주세요.</label></dt>
								<dd><input type="url" id="input5" name="current_site" class="text w100p" placeholder="사이트주소를 입력해주세요."></dd>
							</dl>
							<dl class="w100p">
								<dt><label for="input6">어떤 문의가 있으신가요?</label></dt>
								<dd><textarea name="" id="" cols="30" rows="10" class="text w100p" placeholder="문의 내용을 입력해주세요."></textarea></dd>
								<!-- (에디터) -->
							</dl>
							<dl class="w100p">
								<dt><label for="input7">첨부파일<span>(최대 3개까지 첨부 가능)</span></label><label class="btn_file"><input type="file" name="btn_file" id="input7" multiple><span>파일선택</span></label></dt>
								<dd class="input_files"></dd>
							</dl>
							<dl class="w100p">
								<dt><label for="input8">프로젝트 예산</label></dt>
								<dd><input type="text" id="input7" name="budget" class="text w100p" placeholder="프로젝트 예산을 입력해주세요."></dd>
							</dl>
						</div>
						<button type="submit" class="btn_submit">문의 접수하기</button>
					</form>
				</div>
			</div>
		</div>
	</section>
	
	<div class="popup" id="popup_complete" role="dialog" aria-modal="true" aria-labelledby="popup-title" aria-describedby="popup-desc" hidden>
		<div class="dm" aria-hidden="true"></div>
		<div class="inbox">
			<button type="button" class="btn_close" aria-label="팝업 닫기"></button>
			<h2 class="tit" id="popup-title">감사합니다.<br/>접수가 완료되었습니다.</h2>
			<p id="popup-desc">컨설턴트가 확인하는 동안 최신 뉴스 구경하세요!</p>
			<a href="/blog/" class="btn_link slim">블로그 바로가기</a>
		</div>
	</div>

</main>

<script>
//첨부파일
	(function () {
		const MAX_FILES = 3;
		let attachedFiles = [];
		$('#input7').on('change', function () {
			const newFiles = Array.from(this.files);
			const available = MAX_FILES - attachedFiles.length;
			if (available <= 0) {
				alert('파일은 최대 3개까지 첨부 가능합니다.');
				$(this).val('');
				return;
			}
			const addFiles = newFiles.slice(0, available);
			if (newFiles.length > available) {
				alert(`최대 ${MAX_FILES}개까지 첨부 가능합니다. ${available}개만 추가됩니다.`);
			}
			addFiles.forEach(function (file) {
				const isDuplicate = attachedFiles.some(f => f.name === file.name);
				if (isDuplicate) return;
				attachedFiles.push(file);
				renderFile(file);
			});
			$(this).val('');
		});
		function renderFile(file) {
			const $btn = $('<button>', {
				type: 'button',
				class: 'file',
				text: file.name,
				'aria-label': file.name + ' 삭제'
			});
			$btn.on('click', function () {
				attachedFiles = attachedFiles.filter(f => f.name !== file.name);
				$(this).remove();
			});

			$('.input_files').append($btn);
		}
	})();
//접수 완료 팝업
	$('#contact_form').on('submit', function(e) {
		e.preventDefault();
		openPopup('popup_complete').addClass("on");
	});

	let $lastFocus;
	function openPopup(id) {
		$lastFocus = $(document.activeElement);
		const $popup = $('#' + id);
		$popup.removeAttr('hidden').attr('aria-hidden', 'false');
		$('body').attr('aria-hidden', 'true');
		$popup.find('.btn_close').focus();
	}
	function closePopup(id) {
		const $popup = $('#' + id);
		$popup.attr('hidden', true).attr('aria-hidden', 'true');
		$('body').removeAttr('aria-hidden');
		if ($lastFocus) $lastFocus.focus();
	}
	$(document).on('click', '.popup .btn_close', function () {
		closePopup($(this).closest('.popup').attr('id'));
	});
	$(document).on('click', '.popup .dm', function () {
		closePopup($(this).closest('.popup').attr('id'));
	});
	$(document).on('keydown', function (e) {
		if (e.key === 'Escape') {
			const $openPopup = $('.popup:not([hidden])');
			if ($openPopup.length) closePopup($openPopup.attr('id'));
		}
	});
	$(document).on('keydown', '.popup', function (e) {
		if (e.key !== 'Tab') return;
		const $focusable = $(this).find('button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])').filter(':visible');
		const $first = $focusable.first();
		const $last  = $focusable.last();
		if (e.shiftKey) {
			if ($(document.activeElement).is($first)) {
				$last.focus();
				e.preventDefault();
			}
		} else {
			if ($(document.activeElement).is($last)) {
				$first.focus();
				e.preventDefault();
			}
		}
	});
</script>

@endsection