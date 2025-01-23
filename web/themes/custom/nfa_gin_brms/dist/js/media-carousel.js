(function ($) {
  'use strict';
  Drupal.behaviors.mediaCarousel = {
    attach: function (context) {
      once('media-carousel', '.swiper', context).forEach(function (element) {
        var swiper = new Swiper(element, {
          slidesPerView: 1,
          spaceBetween: 10,
          pagination: {
            el: ".swiper-pagination",
            clickable: true,
          },
          navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
          },
          breakpoints: {
            576: {
              slidesPerView: 2,
            },
            768: {
              slidesPerView: 3,
            },
            992: {
              slidesPerView: 4,
            },
          },
        });
      });
    },
  };
}(jQuery));
