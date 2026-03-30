document.addEventListener('DOMContentLoaded', () => {
  if (typeof window.jQuery === 'undefined') {
  return;
}

const $ = window.jQuery;
let isFirstAnimated = false; // 🔥 최초 1회 애니메이션이 실행되었는지 여부
let lastWidth = $(window).width(); // 가로 너비 추적용

function drawLines() {
  const $wrap = $('.line_wrap');
  const $svg = $wrap.find('.line_svg');
  const pairs = [['t1', 'b1'], ['t2', 'b2'], ['t3', 'b3'], ['t4', 'b4']];
  const wrapOffset = $wrap.offset();
  if (!wrapOffset) return;

  $svg.empty(); // 기존 선 지우기

  pairs.forEach(([tClass, bClass], index) => {
    const $t = $wrap.find(`.${tClass}`);
    const $b = $wrap.find(`.${bClass}`);
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
    
    // 🔥 이미 최초 애니메이션이 끝난 후(리사이즈 상황)라면 dashoffset을 0으로 설정해 즉시 보이게 합니다.
    const offsetValue = isFirstAnimated ? 0 : length;

    $(line).attr({
      x1,
      y1,
      x2,
      y2,
      stroke: '#CDD1D5',
      'stroke-width': '1',
      'stroke-dasharray': length,
      'stroke-dashoffset': offsetValue, 
    });

    $(line).addClass('line_item').attr('data-delay', index * 0.15);
    
    // 🔥 리사이즈 시에는 CSS @keyframes 애니메이션이 다시 붙지 않도록 막습니다.
    if (isFirstAnimated) {
      $(line).css({ 'animation': 'none' }); 
    }

    $svg.append(line);
  });
}

function startLineAnimation() {
  if (isFirstAnimated) return; // 이미 실행됐다면 중복 실행 방지
  isFirstAnimated = true; // 최초 실행 플래그 On

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
  const target = document.querySelector('.portfolio_problem');
  if (target) observer.observe(target);
});

// ✅ 리사이즈 시점 방어 코드
$(window).on('resize', () => {
  const currentWidth = $(window).width();

  // 아이폰 스크롤 시 주소창 높이 변화는 무시하고, "가로 너비"가 변했을 때만 다시 그립니다.
  if (currentWidth !== lastWidth) {
    lastWidth = currentWidth;
    drawLines();
  }
});

  const speedFactor = 0.5;
  $('.production_setting_area li .txt .slide_txt').each(function makeMarquee() {
    const $this = $(this);
    const source = ($this.text() || '').trim();
    const textContent = source === '' ? 'PORTFOLIO ' : `${source.toUpperCase()} `;
    const $temp = $(`<span style="position:absolute; visibility:hidden; white-space:nowrap; font-size:100px; font-weight:900;">${textContent}</span>`).appendTo('body');
    const textWidth = $temp.outerWidth() || 1;
    $temp.remove();
    const windowWidth = $(window).width();
    const repeatCount = Math.ceil(windowWidth / textWidth);
    const repeatedText = textContent.repeat(repeatCount + 1);
    const totalWidth = textWidth * (repeatCount + 1);
    const duration = (totalWidth / 100) * speedFactor;
    const marqueeHtml = `<div class="marquee_inner" style="animation-duration: ${duration}s;">${repeatedText}</div><div class="marquee_inner" style="animation-duration: ${duration}s;">${repeatedText}</div>`;
    $this.html(marqueeHtml);
  });

  const observerContact = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        $('.page_contact').addClass('start');
      } else if (entry.boundingClientRect.top > window.innerHeight / 2) {
        $('.page_contact').removeClass('start');
      }
    });
  }, {
    threshold: 0.5,
  });

  const contactTarget = document.querySelector('.page_contact');
  if (contactTarget) observerContact.observe(contactTarget);

  const shareTitleEl = document.getElementById('portfolio-head-title');
  const shareText = shareTitleEl ? $(shareTitleEl).text().trim() : document.title;

  const fallbackCopyText = (text) => {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    let ok = false;
    try {
      ok = document.execCommand('copy');
    } catch (_e) {
      ok = false;
    }
    document.body.removeChild(ta);
    return ok;
  };

  $('.btn_link_copy').on('click', () => {
    const url = window.location.href;
    const done = () => alert('링크가 복사되었습니다.');
    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
      navigator.clipboard.writeText(url).then(done).catch(() => {
        if (fallbackCopyText(url)) done();
      });
    } else if (fallbackCopyText(url)) {
      done();
    }
  });

  $('.btn_share').on('click', async () => {
    const shareData = {
      title: document.title,
      text: shareText,
      url: window.location.href,
    };
    try {
      if (navigator.share) {
        await navigator.share(shareData);
      } else if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
        await navigator.clipboard.writeText(window.location.href);
      } else if (fallbackCopyText(window.location.href)) {
        alert('링크가 복사되었습니다.');
      }
    } catch (_error) {
      // Web Share 사용자 취소 등
    }
  });
});
