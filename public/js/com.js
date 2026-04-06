$(document).ready(function(){
//헤더
	function checkHeader() {
		const $intro      = $(".intro");
		const $mainVisual = $(".main_visual");
		const isMain      = $intro.length > 0;

		let threshold = 50;

		if (isMain) {
			const introH      = $intro.outerHeight()      || 0;
			const mainVisualH = $mainVisual.outerHeight()  || 0;
			threshold = introH + mainVisualH;
		}

		if ($(window).scrollTop() > threshold) {
			$(".header").addClass("fixed");
		} else {
			$(".header").removeClass("fixed");
		}
	}
	$(window).on('scroll', checkHeader);
	checkHeader();
	$(".header").mouseover(function(){
		$(".header").stop(false,true).addClass("hover");
	});
	$(".header").mouseleave(function(){
		$(".header").stop(false,true).removeClass("hover");
	});
//헤더 색상별 클래스
	function checkHeaderBg() {
		const headerH = $(".header").outerHeight();
		const checkY  = headerH / 2;
		let matched   = false;
		const $stickyWrap = $(".sticky_wrap");
		if ($stickyWrap.length) {
			const wrapRect = $stickyWrap[0].getBoundingClientRect();
			if (checkY >= wrapRect.top && checkY <= wrapRect.bottom) {
				const scrollTop    = $(window).scrollTop();
				const windowHeight = $(window).height();
				const wrapTop      = $stickyWrap.offset().top;
				const scrollInWrap = scrollTop - wrapTop;
				const $rotates     = $(".page_rotate");
				const index        = Math.floor(scrollInWrap / windowHeight);
				const activeIndex  = Math.min(Math.max(index, 0), $rotates.length - 1);
				const mode         = $rotates.eq(activeIndex).data("header");
				$(".header").removeClass("bg_white bg_black");
				if (mode === "light") $(".header").addClass("bg_white");
				if (mode === "dark")  $(".header").addClass("bg_black");
				matched = true;
			}
		}
		if (!matched) {
			$("section:not(.page_rotate), div[data-header]").each(function () {
				const rect = this.getBoundingClientRect();
				if (checkY >= rect.top && checkY <= rect.bottom) {
					const mode = $(this).data("header");
					$(".header").removeClass("bg_white bg_black");
					if (mode === "light") $(".header").addClass("bg_white");
					if (mode === "dark")  $(".header").addClass("bg_black");
					matched = true;
					return false;
				}
			});
		}
	}
	$(window).on("scroll", checkHeaderBg);
	checkHeaderBg();
//접근성 헤더 메뉴
    const $menuItems = $('.gnb .menu');
    function openSnb($menu) {
        $menu.addClass('on_focus');
        $menu.find('> a').attr('aria-expanded', 'true');
    }
    function closeSnb($menu) {
        $menu.removeClass('on_focus');
        if (!$menu.hasClass('on')) $menu.find('> a').attr('aria-expanded', 'false');
    }
    function closeAllSnb() {
        $menuItems.each(function () { closeSnb($(this)); });
    }
    $menuItems.each(function () {
        const $menu     = $(this);
        const $link     = $menu.find('> a');
        const $snb      = $menu.find('.snb');
        const $snbLinks = $snb.find('a');
        $link.on('focus', function () { closeAllSnb(); if ($snb.length) openSnb($menu); });
        $snbLinks.on('focus', function () { openSnb($menu); });
        $snbLinks.last().on('keydown', function (e) { if (!e.shiftKey && e.key === 'Tab') closeSnb($menu); });
        $link.on('keydown', function (e) {
            if (e.shiftKey && e.key === 'Tab') closeAllSnb();
            if ((e.key === 'Enter' || e.key === ' ') && $snb.length) {
                e.preventDefault();
                $menu.hasClass('on_focus') ? closeSnb($menu) : (openSnb($menu), $snbLinks.first().focus());
            }
            if (e.key === 'Escape') { closeSnb($menu); $link.focus(); }
        });
        $snbLinks.on('keydown', function (e) { if (e.key === 'Escape') { closeSnb($menu); $link.focus(); } });
    });
    $(document).on('focusin', function (e) { if (!$(e.target).closest('.gnb').length) closeAllSnb(); });
//header_mobile
	$(".btn_menu").click(function(){
		$("html,body").stop(false,true).toggleClass("over_h");
		$(".header").stop(false,true).toggleClass("on");
		var isOpen = $(".header").hasClass("on");
		$(this).attr("aria-expanded", isOpen);
		if (isOpen) {
			$(this).attr("aria-label", "전체 메뉴 닫기");
		} else {
			$(this).attr("aria-label", "전체 메뉴 열기");
		}
	});
	$(".header .gnb .menu button.mo_vw").click(function(){
		$(this).next(".snb").stop(false,true).slideToggle("fast").parent().stop(false,true).toggleClass("open").siblings().removeClass("open").removeClass("on").children(".snb").slideUp("fast");
	});
//gotop
	var speed = 500;
	var gNum = "main";
	$(".gotop").css("cursor", "pointer").click(function() {
		var $target = $('#main_visual'); // 타겟 아이디 지정
		if (gNum === "main" && $target.length) {
			var targetOffset = $target.offset().top; // #main_visual의 Y축 위치값 계산
			$('body, html').animate({ scrollTop: targetOffset }, speed);
		} 
		else {
			$('body, html').animate({ scrollTop: 0 }, speed);
		}
	});

	$(window).scroll(function() {
		const isIndexPage = window.location.pathname === '/';

		if (isIndexPage) {
			const $mainService = $(".main_service");
			if ($mainService.length > 0) {
				const serviceTop = $mainService.offset().top;
				if ($(window).scrollTop() >= serviceTop) {
					$(".footer").addClass("fixed");
				} else {
					$(".footer").removeClass("fixed");
				}
			}
		} else {
			if ($(window).scrollTop() > 100) {
				$(".footer").addClass("fixed");
			} else {
				$(".footer").removeClass("fixed");
			}
		}
	});

	$(window).on("scroll resize", function () {
		let scrollTop = Math.max(0, $(window).scrollTop());
		let windowHeight = window.innerHeight;
		let windowBottom = scrollTop + windowHeight;
		let $point = $(".footer .point");
		if ($point.length > 0) {
			let pointTop = $point.offset().top;
			if (windowBottom >= pointTop) {
				$(".footer").addClass("unfixed");
			} else {
				$(".footer").removeClass("unfixed");
			}
		}
	});
//aside
	function asideToggle() {
		if ($(window).width() <= 767) {
			// 767 이하: 아코디언 기능 활성화
			$(".aside dt").off("click").on("click", function(event) {
				$(this).next("dd").stop(false,true).slideToggle("fast")
					.parent().stop(false,true).toggleClass("on")
					.siblings().removeClass("on").children("dd").slideUp("fast");
				event.stopPropagation();
			});

			$(document).off("click.aside").on("click.aside", function(event) {
				if (!$(event.target).closest('.aside dl').length) {
					$(".aside dl").removeClass("on").children("dd").slideUp("fast");
				}
			});

		} else {
			// 768 이상: 아코디언 기능 해제
			$(".aside dt").off("click");
			$(document).off("click.aside");
			$(".aside dl").removeClass("on").children("dd").removeAttr("style");
		}
	}

	// 페이지 로드 시 실행
	asideToggle();

	// 브라우저 크기 변경 시 자동 실행
	$(window).on("resize", function() {
		asideToggle();
	});

//브라우저 사이즈
	let vh = window.innerHeight * 0.01; 
	document.documentElement.style.setProperty('--vh', `${vh}px`);
//화면 리사이즈시 변경 
	window.addEventListener('resize', () => {
		let vh = window.innerHeight * 0.01; 
		document.documentElement.style.setProperty('--vh', `${vh}px`);
	});
	window.addEventListener('touchend', () => {
		let vh = window.innerHeight * 0.01;
		document.documentElement.style.setProperty('--vh', `${vh}px`);
	});
//IOS
	function isApple() {
		return /iPhone|iPad|iPod/i.test(navigator.userAgent);
	}
	const isAppleDevice = isApple();
	if (isAppleDevice) {
		$("body").addClass("ios_safe");
	}

});