function loader() {
    $('.status').fadeOut(); 
    $('.preloader').delay(1000).fadeOut('slow');
}

function centeredHeader() {
    var windowHeight = $(window).height();

    $('.full-screen').css('min-height', windowHeight);

    var centerContent = ($(window).height() / 2) - ($('.header-content').height());
    $('.primary-row').css('padding-top', centerContent);
}

function wowAnimation() {
    new WOW().init();
}

function ie10Fix() {
    if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
      var msViewportStyle = document.createElement('style')
      msViewportStyle.appendChild(
        document.createTextNode(
          '@-ms-viewport{width:auto!important}'
        )
      )
      document.querySelector('head').appendChild(msViewportStyle)
    }
}

function smoothScroll() {
	$('a').smoothScroll({speed: 1200});
}

$(document).ready(function () {
    'use strict';

    centeredHeader();
    wowAnimation();
    ie10Fix();
	smoothScroll();
});

$(window).load(function () {
    'use strict';

    loader();

});

$(window).resize(function(){
    centeredHeader();
});