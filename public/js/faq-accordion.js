/**
 * FAQ 아코디언 (.faq_list) — 공개 홈·서비스 공통
 */
(function ($) {
	'use strict';

	function refreshAria($list) {
		$list.find('li').not('.faq_empty').each(function () {
			var $li = $(this);
			var expanded = $li.hasClass('on');
			$li.find('> h3 button').attr('aria-expanded', expanded ? 'true' : 'false');
		});
	}

	function initFaqList($list) {
		if (!$list.length) {
			return;
		}

		$list.find('li.faq_empty .con').remove();

		$list.off('click.hkFaq', 'button').on('click.hkFaq', 'button', function () {
			var $btn = $(this);
			var $h3 = $btn.parent();
			var $li = $h3.parent();
			$h3.next('.con').stop(true, true).slideToggle('fast');
			$li.stop(true, true).toggleClass('on').siblings().removeClass('on').children('.con').slideUp('fast');
			$list.find('li').removeClass('on_before');
			$list.find('li.on').prev('li').addClass('on_before');
			refreshAria($list);
		});

		var $first = $list.children('li').not('.faq_empty').first();
		$list.children('li').removeClass('on on_before');
		$list.find('.con').hide();
		if ($first.length) {
			$first.addClass('on').children('.con').show();
			$first.prev('li').addClass('on_before');
		}
		refreshAria($list);
	}

	$(function () {
		$('.faq_list').each(function () {
			initFaqList($(this));
		});
	});
})(jQuery);
