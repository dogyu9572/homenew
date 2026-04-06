document.addEventListener('DOMContentLoaded', () => {
	if (typeof AOS !== 'undefined' && typeof AOS.init === 'function') {
		AOS.init({ duration: 1000, });
	}
	//탭
	var $tabs = $('.tabs');
    var $activeLi = $tabs.find('li.on');

    if ($activeLi.length) {
        var liLeft = $activeLi[0].getBoundingClientRect().left;
        var tabsLeft = $tabs[0].getBoundingClientRect().left;
        $tabs.scrollLeft($tabs.scrollLeft() + (liLeft - tabsLeft));
    }
});
