document.addEventListener('DOMContentLoaded', () => {
  if (typeof window.jQuery === 'undefined') {
    return;
  }

  const $ = window.jQuery;
  const root = document.getElementById('blog_view_root');
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
  const eventUrl = root && root.dataset ? root.dataset.eventUrl : '';
  const likeUrl = root && root.dataset ? root.dataset.likeUrl : '';
  const postId = root && root.dataset ? root.dataset.postId : '';

  const getSessionKey = () => {
    const key = 'blog_session_key';
    let value = localStorage.getItem(key);
    if (!value) {
      value = `blog-${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
      localStorage.setItem(key, value);
    }
    return value;
  };

  const sendPostJson = (url, payload) => {
    if (!url || !csrfToken) return Promise.resolve();
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(payload),
    }).catch(() => null);
  };

  const sessionKey = getSessionKey();
  const likeStorageKey = `blog_like_done_${postId}`;
  const viewStorageKey = `blog_view_logged_${postId}_${new Date().toISOString().slice(0, 10)}`;

  if (!localStorage.getItem(viewStorageKey)) {
    sendPostJson(eventUrl, {
      event_type: 'view',
      session_key: sessionKey,
    }).then(() => {
      localStorage.setItem(viewStorageKey, '1');
    });
  }

  const startedAt = Date.now();
  let maxScrollDepth = 0;
  let lastScrollSentAt = 0;
  let hasSentExitMetrics = false;

  const getScrollDepth = () => {
    const doc = document.documentElement;
    const scrollTop = window.scrollY || doc.scrollTop || 0;
    const scrollHeight = Math.max(doc.scrollHeight, document.body.scrollHeight) - window.innerHeight;
    if (scrollHeight <= 0) return 100;
    return Math.min(100, Math.round((scrollTop / scrollHeight) * 100));
  };

  const sendScroll = (force = false) => {
    const now = Date.now();
    if (!force && now - lastScrollSentAt < 10000) return;
    lastScrollSentAt = now;
    sendPostJson(eventUrl, {
      event_type: 'scroll',
      session_key: sessionKey,
      scroll_depth: maxScrollDepth,
    });
  };

  $(window).on('scroll', () => {
    maxScrollDepth = Math.max(maxScrollDepth, getScrollDepth());
    sendScroll(false);
  });

  const sendDwellAndScroll = () => {
    if (hasSentExitMetrics) return;
    hasSentExitMetrics = true;
    const dwellSeconds = Math.max(0, Math.round((Date.now() - startedAt) / 1000));
    sendPostJson(eventUrl, {
      event_type: 'dwell',
      session_key: sessionKey,
      dwell_seconds: dwellSeconds,
    });
    sendScroll(true);
  };

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') {
      sendDwellAndScroll();
    }
  });
  window.addEventListener('beforeunload', sendDwellAndScroll);

  $('.index_list a[href^="#"]').on('click', function (e) {
    e.preventDefault();
    const target = $(this).attr('href');
    const $target = $(target);
    if ($target.length === 0) return;
    const headerHeight = $('header').outerHeight() || 0;
    const offset = $target.offset().top - headerHeight;
    $('html, body').animate({ scrollTop: offset }, 500, 'swing');
  });

  const updateIndexActive = () => {
    const headerHeight = $('header').outerHeight() || 0;
    const scrollTop = $(window).scrollTop();
    const threshold = 10;
    let currentTarget = null;
    $('.index_list a[href^="#"]').each(function () {
      const target = $(this).attr('href');
      const $target = $(target);
      if ($target.length === 0) return;
      const targetTop = $target.offset().top - headerHeight - threshold;
      if (scrollTop >= targetTop) {
        currentTarget = $(this).closest('li');
      }
    });
    $('.index_list li').removeClass('on');
    if (currentTarget) {
      currentTarget.addClass('on');
    }
  };
  $(window).on('scroll', updateIndexActive);
  updateIndexActive();

  let indexFixedTop = 0;
  let recommendedFixedTop = 0;

  const cacheFixedTops = () => {
    const $index = $('.index_area');
    const $recommended = $('.recommended_area');
    $index.removeClass('unfixed');
    $recommended.removeClass('unfixed');
    indexFixedTop = parseInt($index.css('top'), 10) || 0;
    recommendedFixedTop = parseInt($recommended.css('top'), 10) || 0;
  };

  const checkUnfixed = () => {
    const scrollTop = $(window).scrollTop();
    const $wrap = $('.blog_view_wrap');
    const wrapBottom = $wrap.offset().top + $wrap.outerHeight();
    const $index = $('.index_area');
    if ($index.length > 0) {
      const indexBottom = scrollTop + indexFixedTop + $index.outerHeight();
      if (indexBottom >= wrapBottom) {
        $index.addClass('unfixed');
      } else {
        $index.removeClass('unfixed');
      }
    }

    const $recommended = $('.recommended_area');
    const recommendedBottom = scrollTop + recommendedFixedTop + $recommended.outerHeight();
    if (recommendedBottom >= wrapBottom) {
      $recommended.addClass('unfixed');
    } else {
      $recommended.removeClass('unfixed');
    }
  };

  cacheFixedTops();
  checkUnfixed();
  $(window).on('scroll', checkUnfixed);
  $(window).on('resize', () => {
    cacheFixedTops();
    checkUnfixed();
  });

  const likeCountEls = document.querySelectorAll('.blog_like_count');
  const likedAlready = localStorage.getItem(likeStorageKey) === '1';
  if (likedAlready) {
    $('.like').attr('aria-pressed', true).addClass('checked');
  }

  $('.like').on('click', function () {
    const $btn = $(this);
    sendPostJson(likeUrl, {
      session_key: sessionKey,
    })
      .then((response) => {
        if (!response || typeof response.json !== 'function') return null;
        return response.json();
      })
      .then((json) => {
        const liked = Boolean(json && json.liked);
        if (liked) {
          localStorage.setItem(likeStorageKey, '1');
          $btn.attr('aria-pressed', true).addClass('checked');
        } else {
          localStorage.removeItem(likeStorageKey);
          $btn.attr('aria-pressed', false).removeClass('checked');
        }

        if (json && typeof json.like_count !== 'undefined' && likeCountEls.length > 0) {
          const formattedCount = Number(json.like_count).toLocaleString();
          likeCountEls.forEach((el) => {
            el.textContent = formattedCount;
          });
        }
      })
      .catch(() => null);
  });

  $('.btn_link_copy').on('click', () => {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
      sendPostJson(eventUrl, { event_type: 'link_copy', session_key: sessionKey });
      alert('링크가 복사되었습니다.');
    }).catch(() => null);
  });

  $('.btn_share').on('click', async () => {
    const shareData = {
      title: document.title,
      text: $('#blog-view-title').text(),
      url: window.location.href,
    };
    try {
      if (navigator.share) {
        await navigator.share(shareData);
        sendPostJson(eventUrl, { event_type: 'share', session_key: sessionKey });
      } else if (navigator.clipboard) {
        await navigator.clipboard.writeText(window.location.href);
        sendPostJson(eventUrl, { event_type: 'share', session_key: sessionKey });
      }
    } catch (_error) {
      // 사용자 취소 무시
    }
  });
});
