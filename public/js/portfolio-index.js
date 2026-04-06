document.addEventListener('DOMContentLoaded', () => {
	if (typeof window.AOS !== 'undefined') {
		window.AOS.init({ duration: 1000 });
	}
	//탭
	var $tabs = $('.tabs');
    var $activeLi = $tabs.find('li.on');

    if ($activeLi.length) {
        var scrollLeft = $activeLi.position().left + $tabs.scrollLeft();
        $tabs.scrollLeft(scrollLeft);
    }
	//팝업
	if (sessionStorage.getItem('hideNotice') === 'true') {
        $('.pop_notice').remove();
    } else {
        $('.pop_notice').addClass('first');
    }
    $('.pop_notice .btn_close').on('click', function() {
        sessionStorage.setItem('hideNotice', 'true');
        $(this).closest('.pop_notice').fadeOut(300);
    });
});
