(function ($) {
//about01, about02
    /*$('.ani_title').each(function() {
        const $this = $(this);
        const nodes = $this.contents(); 
        let newHtml = '';

        nodes.each(function() {
            if (this.nodeType === 3) { 
                const chars = this.nodeValue.split('');
                $.each(chars, function(i, char) {
                    if (char.trim() === "") {
                        newHtml += char;
                    } else {
                        newHtml += '<span class="char">' + char + '</span>';
                    }
                });
            } else if (this.nodeName.toLowerCase() === 'strong') {
                const strongChars = $(this).text().split('');
                let strongInner = '';
                $.each(strongChars, function(i, char) {
                    if (char.trim() === "") {
                        strongInner += char;
                    } else {
                        strongInner += '<span class="char">' + char + '</span>';
                    }
                });
                newHtml += '<strong>' + strongInner + '</strong>';
            } else if (this.nodeName.toLowerCase() === 'br') {
                newHtml += '<br>';
            }
        });
        $this.html(newHtml);
    });

    $('.title_color_slide').each(function() {
        const $section = $(this);
        const $chars = $section.find('.ani_title .char');
        let animated = false;

        $(window).on('scroll.' + $section.index(), function() {
            if (animated) return;

            const scrollPos = $(window).scrollTop();
            const windowHeight = $(window).height();
            const targetPos = $section.offset().top;

            const isAbout01 = $section.hasClass('about01');
            const triggerPoint = isAbout01 ? 0 : targetPos - (windowHeight / 2);

            if (scrollPos >= triggerPoint) {
                animated = true;
                $(window).off('scroll.' + $section.index());
                
                $chars.each(function(index) {
                    setTimeout(function() {
                        $chars.eq(index).addClass('active');
                    }, index * 80);
                });
            }
        }).trigger('scroll.' + $section.index());
    });*/
	
	$(window).on('scroll', function () {
		var $mainVisual = $('.about01');
		var visualBottom = $mainVisual.offset().top + $mainVisual.outerHeight();
		var windowBottom = $(window).scrollTop() + $(window).height();
		if (windowBottom >= visualBottom) {
			$mainVisual.addClass('end').removeClass('start');
		} else {
			$mainVisual.removeClass('end').addClass('start');
		}
	});
	
	var $section = $('.about01');
    var $lines = $section.find('.ani_title > span');

    /* ABOUT01  */
	function fillLines01($lines, progress) {
		var numLines = $lines.length;
		$lines.each(function (i) {
			var lineStart    = i / numLines;
			var lineEnd      = (i + 1) / numLines;
			var lineProgress = (progress - lineStart) / (lineEnd - lineStart);
			lineProgress = Math.max(0, Math.min(1, lineProgress));
			var fillPct  = (lineProgress * 100).toFixed(2) + '%';
			this.style.setProperty('--fill', fillPct);
			var spanRect  = this.getBoundingClientRect();
			var spanWidth = spanRect.width;
			var fillPx    = lineProgress * spanWidth;
			$(this).find('strong.c_org').each(function () {
				var strongRect  = this.getBoundingClientRect();
				var strongLeft  = strongRect.left - spanRect.left;
				var strongWidth = strongRect.width;
				var strongFill  = (fillPx - strongLeft) / strongWidth * 100;
				strongFill = Math.max(0, Math.min(100, strongFill));
				this.style.setProperty('--fill', strongFill.toFixed(2) + '%');
			});
		});
	}

	/* ABOUT02 */
	function fillLines02($lines, progress) {
		var numLines = $lines.length;
		$lines.each(function (i) {
			var lineStart    = i / numLines;
			var lineEnd      = (i + 1) / numLines;
			var lineProgress = (progress - lineStart) / (lineEnd - lineStart);
			lineProgress = Math.max(0, Math.min(1, lineProgress));
			this.style.setProperty('--fill', (lineProgress * 100).toFixed(2) + '%');
			var $span = $(this);
			var fullText = $span.text();
			var $strong = $span.find('strong.c_org');
			$strong.each(function () {
				var strongText = $(this).text();
				var startIndex = fullText.indexOf(strongText);
				var startRatio = startIndex / fullText.length;
				var endRatio = (startIndex + strongText.length) / fullText.length;
				var strongProgress = (lineProgress - startRatio) / (endRatio - startRatio);
				strongProgress = Math.max(0, Math.min(1, strongProgress));
				this.style.setProperty('--fill', (strongProgress * 100).toFixed(2) + '%');
			});
		});
	}

	 /* ABOUT02 */
	function updateAbout01() {
		var $about01 = $('.about01');
		if (!$about01.length) return;
		var sectionTop = $about01.offset().top;
		var sectionBottom = sectionTop + $about01.outerHeight();
		var scrollTop = $(window).scrollTop();
		var winHeight = $(window).height();
		var rangeStart = sectionTop;
		var rangeEnd = sectionBottom - winHeight;
		var progress = rangeEnd <= rangeStart ? 0 : (scrollTop - rangeStart) / (rangeEnd - rangeStart);
		progress = Math.max(0, Math.min(1, progress));
		
		fillLines01($about01.find('.ani_title > span'), progress);
	}
    /* ABOUT02 */
    function updateAbout02() {
		var $about02 = $('.about02');
		if (!$about02.length) return;
		
		var sectionTop = $about02.offset().top;
		var scrollTop = $(window).scrollTop();
		var winHeight = $(window).height();
		var winWidth = $(window).width(); // 현재 너비 체크

		var cfg = {
			startRatio : 0.5,
			endRatio   : 0.4,
			startOffset: 0,
			endOffset  : 0
		};
		if (winWidth < 768) {
			cfg.startRatio = 0.3;
			cfg.endRatio = 0.2;
		} 
		else if (winWidth < 1024) {
			cfg.startRatio = 0.5;
			cfg.endRatio = 0.3;
		}

		var rangeStart = sectionTop - winHeight * (1 - cfg.startRatio) + cfg.startOffset;
		var rangeEnd = sectionTop + $about02.outerHeight() * cfg.endRatio - winHeight * 0.5 + cfg.endOffset;
		
		var progress = rangeEnd <= rangeStart ? 0 : (scrollTop - rangeStart) / (rangeEnd - rangeStart);
		progress = Math.max(0, Math.min(1, progress));
		
		fillLines02($about02.find('.ani_title > span'), progress);
	}

    /* 이벤트 바인딩 */
    function updateAll() {
        updateAbout01();
        updateAbout02();
    }

    updateAll();

    $(window).on('scroll', updateAll);

    var resizeTimer;
    $(window).on('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(updateAll, 200);
    });
//history, CONTACT
	document.addEventListener('DOMContentLoaded', () => {
	//line
		if (typeof window.jQuery === 'undefined') {
			return;
		}

		const $ = window.jQuery;
		let isFirstAnimated = false;
		let lastWidth = $(window).width();

		function drawLines() {
			$('.line_wrap').each(function() {
				const $wrap = $(this);
				const $svg = $wrap.find('.line_svg');
				
				// 이 wrap 안에 있는 t/b 쌍을 자동으로 감지
				const pairs = [['t1','b1'],['t2','b2'],['t3','b3'],['t4','b4']];
				const wrapOffset = $wrap.offset();
				if (!wrapOffset) return;
				
				$svg.empty();
				
				let lineIndex = 0;
				pairs.forEach(([tClass, bClass]) => {
					const $t = $wrap.find(`.${tClass}`);
					const $b = $wrap.find(`.${bClass}`);
					// 해당 쌍이 없으면 스킵 (about06은 t1/b1, t2/b2만 있으므로 t3~t4는 자동 스킵)
					if (!$t.length || !$b.length) return;
					
					const tOffset = $t.offset();
					const bOffset = $b.offset();
					if (!tOffset || !bOffset) return;
					
					const x1 = tOffset.left - wrapOffset.left + $t.outerWidth() / 2;
					const y1 = tOffset.top - wrapOffset.top + $t.outerHeight() / 2;
					const x2 = bOffset.left - wrapOffset.left + $b.outerWidth() / 2;
					const y2 = bOffset.top - wrapOffset.top + $b.outerHeight() / 2;
					const length = Math.hypot(x2 - x1, y2 - y1);
					
					const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
					const offsetValue = isFirstAnimated ? 0 : length;
					
					$(line).attr({
						x1, y1, x2, y2,
						stroke: '#CDD1D5',
						'stroke-width': '1',
						'stroke-dasharray': length,
						'stroke-dashoffset': offsetValue,
					});
					$(line).addClass('line_item').attr('data-delay', lineIndex * 0.15);
					if (isFirstAnimated) {
						$(line).css({ 'animation': 'none' });
					}
					$svg.append(line);
					lineIndex++;
				});
			});
		}

		function startLineAnimation() {
			if (isFirstAnimated) return;
			isFirstAnimated = true;

			$('.line_wrap .line_item').each(function start() {
				const delay = $(this).attr('data-delay');
				$(this).css({
					animation: 'drawLine 1.2s ease forwards',
					'animation-delay': `${delay}s`,
				});
			});
		}

		const observer = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					startLineAnimation();
					observer.unobserve(entry.target);
				}
			});
		}, {
			rootMargin: '-50% 0px -50% 0px',
		});

		$(window).on('load', () => {
			drawLines();
			
			const target04 = document.querySelector('.about04');
			if (target04) observer.observe(target04);
			
			// about06도 관찰 대상 추가
			const target06 = document.querySelector('.about06');
			if (target06) observer.observe(target06);
		});

		$(window).on('resize', () => {
			const currentWidth = $(window).width();
			if (currentWidth !== lastWidth) {
				lastWidth = currentWidth;
				drawLines();
			}
		});
	//history accordion
		$(".history_list h3 button").click(function(){
			const $list = $(this).parent().next(".list");
			const $parent = $(this).parent().parent();
			$list
				.stop(true, true)
				.slideToggle({
					duration: "fast",
					step: function() { drawLines(); },
					complete: function() { drawLines(); }
				});

			$parent
				.stop(true, true)
				.toggleClass("active")
				.siblings()
				.removeClass("active")
				.children(".list")
				.slideUp({
					duration: "fast",
					step: function() { drawLines(); },
					complete: function() { drawLines(); }
				});
		});
	});	
//카카오 지도
	kakao.maps.load(function () {
		const DEFAULT_LAT  = 37.51498728668106;
		const DEFAULT_LNG  = 126.89966380731899;
		const DEFAULT_LEVEL = 3;

		const container = document.getElementById('map');
		const options = {
			center: new kakao.maps.LatLng(DEFAULT_LAT, DEFAULT_LNG),
			level: DEFAULT_LEVEL,
			scrollwheel: false,
		};
		const map = new kakao.maps.Map(container, options);
		function handleZoomControl() {
			const width = window.innerWidth;
			if (width <= 767) {
				map.setZoomable(true);
			} else {
				map.setZoomable(false);
			}
		}

		handleZoomControl();
		window.addEventListener('resize', handleZoomControl);
		const markerPosition = new kakao.maps.LatLng(DEFAULT_LAT, DEFAULT_LNG);
		const markerImage = new kakao.maps.MarkerImage(
			'/images/icon_map_marker.svg',
			new kakao.maps.Size(33, 42),
			{ offset: new kakao.maps.Point(16, 42) }
		);
		const marker = new kakao.maps.Marker({
			position: markerPosition,
			image: markerImage,
		});
		marker.setMap(map);
		document.querySelector('.btn_zoom_in').addEventListener('click', () => {
			map.setLevel(map.getLevel() - 1, { animate: true });
		});
		document.querySelector('.btn_zoom_out').addEventListener('click', () => {
			map.setLevel(map.getLevel() + 1, { animate: true });
		});
		document.querySelector('.btn_reset').addEventListener('click', () => {
			map.setCenter(new kakao.maps.LatLng(DEFAULT_LAT, DEFAULT_LNG));
			map.setLevel(DEFAULT_LEVEL, { animate: true });
		});
	});
//페이지 위치계산
	updateAll(); 
    $(window).trigger('scroll');
})(jQuery);