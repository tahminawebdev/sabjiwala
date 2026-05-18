(function ($) {
	"use strict";

	jQuery(window).on('scroll', function() {
		if (jQuery(window).scrollTop() > 250) {
			jQuery('.gr-header-section').addClass('sticky-on')
		} else {
			jQuery('.gr-header-section').removeClass('sticky-on')
		}
	});
	$('.or-canvas-cart-trigger').on("click", function() {
		$('.or-ofcanvas-cart-wrapper').toggleClass("or-canvas-cart-on");
	});

	$('.open_mobile_menu').on("click", function() {
		$('.mobile_menu_wrap').toggleClass("mobile_menu_on");
	});
	$('.open_mobile_menu').on('click', function () {
		$('body').toggleClass('mobile_menu_overlay_on');
	});
	if($('.mobile_menu li.dropdown ul').length){
		$('.mobile_menu li.dropdown').append('<div class="dropdown-btn"><span class="fas fa-arrow-right"></span></div>');
		$('.mobile_menu li.dropdown .dropdown-btn').on('click', function() {
			$(this).prev('ul').slideToggle(500);
		});
	}
	$(".dropdown-btn").on("click", function () {
		$(this).toggleClass("toggle-open");
	});

	// Preloader start
	$(window).on('load', function () {
		$(".preloder_part").fadeOut();
		$(".spinner").delay(1000).fadeOut("slow");
	});
	// preloader end

	// back to top start
	$(window).scroll(function() {
		if ($(this).scrollTop() > 200) {
		$('.backtotop:hidden').stop(true, true).fadeIn();
		} else {
		$('.backtotop').stop(true, true).fadeOut();
		}
	});
	$(function() {
		$(".scroll").on('click', function() {
		$("html,body").animate({scrollTop: 0}, "slow");
		return false
		});
	});

	  /*================================== start Utilities ========================================*/

	  window.onload = function () {
		// Dark
		const toggleSwitch = document.querySelector(
		  '.theme-switch-box__input'
		);
		const currentTheme = localStorage.getItem("theme");
		if (currentTheme) {
		  document.documentElement.setAttribute("data-theme", currentTheme);
		  if (currentTheme === "dark") {
			toggleSwitch.checked = true;
		  }
		}
		function switchTheme(e) {
		  if (e.target.checked) {
			document.documentElement.setAttribute("data-theme", "dark");
			localStorage.setItem("theme", "dark");
		  } else {
			document.documentElement.setAttribute("data-theme", "light");
			localStorage.setItem("theme", "light");
		  }
		}
		if (toggleSwitch) {
		  toggleSwitch.addEventListener("change", switchTheme, false);
		}
	  };

	// mobile menu start
	$('#mobile-menu-active').metisMenu();

	$('#mobile-menu-active .dropdown > a').on('click', function (e) {
		e.preventDefault();
	});

	$(".hamburger_menu > a").on("click", function (e) {
		e.preventDefault();
		$(".slide-bar").toggleClass("show");
		$("body").addClass("on-side");
		$('.body-overlay').addClass('active');
		$(this).addClass('active');
	});

	$(".close-mobile-menu > a").on("click", function (e) {
		e.preventDefault();
		$(".slide-bar").removeClass("show");
		$("body").removeClass("on-side");
		$('.body-overlay').removeClass('active');
		$('.hamburger_menu > a').removeClass('active');
	});

	$('.body-overlay').on('click', function () {
		$(this).removeClass('active');
		$(".slide-bar").removeClass("show");
		$("body").removeClass("on-side");
		$('.hamburger-menu > a').removeClass('active');
	});
	// mobile menu end

	// Off Canvas - Start
	// --------------------------------------------------
	$(document).ready(function () {
		$('.cart_close_btn, .body-overlay').on('click', function () {
		$('.cart_sidebar').removeClass('active');
		$('.body-overlay').removeClass('active');
		});

		$('.cart_btn').on('click', function () {
		$('.cart_sidebar').addClass('active');
		$('.body-overlay').addClass('active');
		});
	});


	//data background
	$("[data-background]").each(function () {
		$(this).css("background-image", "url(" + $(this).attr("data-background") + ") ")
	})

	// data bg color
	$("[data-bg-color]").each(function () {
		$(this).css("background-color", $(this).attr("data-bg-color"));

	});

	// category slide
	function cateSliderActive($scope, $) {
		$('.category__slide').slick({
			infinite: true,
			speed: 500,
			slidesToShow: 8,
			autoplay: true,
			autoplaySpeed: 3000,
			slidesToScroll: 1,
			dots: false,
			arrows: true,
			prevArrow: '<i class="slick-arrow slick-prev fas fa-angle-left"></i>',
			nextArrow: '<i class="slick-arrow slick-next far fas fa-angle-right"></i>',
			responsive: [
				{
				breakpoint: 1025,
				settings: {
					slidesToShow: 8,
				}
				},
				{
				breakpoint: 769,
				settings: {
					slidesToShow: 5,
				}
				},
				{
				breakpoint: 480,
				settings: {
					slidesToShow: 2,
				}
				}
			]
		});
	}
	function bannerSliderActive($scope, $) {
		// banner carousel active
		$('.banner-slide__active').slick({
			infinite: true,
			speed: 500,
			slidesToShow: 3,
			autoplay: true,
			autoplaySpeed: 3000,
			slidesToScroll: 1,
			dots: false,
			arrows: true,
			prevArrow: '<i class="slick-arrow slick-prev far fa-long-arrow-left"></i>',
			nextArrow: '<i class="slick-arrow slick-next far fa-long-arrow-right"></i>',
			responsive: [
				{
				breakpoint: 1025,
				settings: {
					slidesToShow: 3,
				}
				},
				{
				breakpoint: 769,
				settings: {
					slidesToShow: 2,
				}
				},
				{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
				}
				}
			]
		});
	}
	function sideProductSliderActive($scope, $) {
		// side product carousel active
		$('.side-product__slide').slick({
			infinite: true,
			speed: 500,
			slidesToShow: 1,
			autoplay: true,
			autoplaySpeed: 3000,
			slidesToScroll: 1,
			dots: false,
			arrows: true,
			prevArrow: '<i class="slick-arrow slick-prev far fas fa-angle-left"></i>',
			nextArrow: '<i class="slick-arrow slick-next far fas fa-angle-right"></i>',
		});
	}
	function productSingleSlider($scope, $) {
		$('.product__slider').slick({
			infinite: true,
			speed: 300,
			slidesToShow: 1,
			slidesToScroll: 1,
			dots: false,
			arrows: true,
			prevArrow: '<i class="slick-arrow slick-prev fas fa-angle-left"></i>',
			nextArrow: '<i class="slick-arrow slick-next far fas fa-angle-right"></i>',
		});
	}

	$('.tx-widget__product-slide').slick({
		infinite: true,
		speed: 300,
		slidesToShow: 1,
		slidesToScroll: 1,
		dots: false,
		arrows: true,
		prevArrow: '<i class="slick-arrow slick-prev fas fa-angle-left"></i>',
		nextArrow: '<i class="slick-arrow slick-next far fas fa-angle-right"></i>',
	});

	// brand
	function brandActiveSlide($scope, $) {
		$('.brand__slide').slick({
			infinite: true,
			speed: 500,
			slidesToShow: 5,
			autoplay: true,
			autoplaySpeed: 3000,
			slidesToScroll: 1,
			dots: false,
			arrows: false,
			responsive: [
				{
				breakpoint: 1024,
				settings: {
					slidesToShow: 4,
					slidesToScroll: 4,
				}
				},
				{
				breakpoint: 600,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3
				}
				},
				{
				breakpoint: 480,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				}
				}
			]
		});
	}

	// news
	function viNewsSlider($scope, $) {
		$('.news__slide').slick({
			infinite: true,
			speed: 500,
			slidesToShow: 3,
			autoplay: true,
			autoplaySpeed: 3000,
			slidesToScroll: 1,
			dots: false,
			arrows: true,
			prevArrow: '<i class="slick-arrow slick-prev fas fa-angle-left"></i>',
			nextArrow: '<i class="slick-arrow slick-next far fas fa-angle-right"></i>',
			responsive: [
				{
				breakpoint: 1025,
				settings: {
					slidesToShow: 2,
				}
				},
				{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
				}
				}
			]
		});
	}

	// cat product
	function productCateSlider($scope, $) {
		$('.cat-product__slide').slick({
			infinite: true,
			speed: 500,
			slidesToShow: 5,
			autoplay: true,
			autoplaySpeed: 3000,
			slidesToScroll: 1,
			dots: false,
			arrows: true,
			prevArrow: '<i class="slick-arrow slick-prev fas fa-angle-left"></i>',
			nextArrow: '<i class="slick-arrow slick-next far fas fa-angle-right"></i>',
			responsive: [
				{
				breakpoint: 1025,
				settings: {
					slidesToShow: 3,
				}
				},
				{
				breakpoint: 769,
				settings: {
					slidesToShow: 2,
				}
				},
				{
				breakpoint: 480,
				settings: {
					slidesToShow: 1,
				}
				}
			]
		});
	}
	// tab product
	$('.tab-product__slide').slick({
		infinite: true,
		speed: 500,
		slidesToShow: 4,
		autoplay: true,
  		autoplaySpeed: 3000,
		slidesToScroll: 1,
		dots: false,
		arrows: true,
		prevArrow: '<i class="slick-arrow slick-prev fas fa-angle-left"></i>',
		nextArrow: '<i class="slick-arrow slick-next far fas fa-angle-right"></i>',
		responsive: [
			{
			  breakpoint: 1025,
			  settings: {
				slidesToShow: 3,
			  }
			},
			{
			  breakpoint: 769,
			  settings: {
				slidesToShow: 2,
			  }
			},
			{
			  breakpoint: 480,
			  settings: {
				slidesToShow: 1,
			  }
			}
		  ]
	});

	// category product
	$('.tab-product__slide2').slick({
		infinite: true,
		speed: 500,
		slidesToShow: 6,
		autoplay: true,
  		autoplaySpeed: 3000,
		slidesToScroll: 1,
		dots: false,
		arrows: true,
		prevArrow: '<i class="slick-arrow slick-prev fas fa-angle-left"></i>',
		nextArrow: '<i class="slick-arrow slick-next far fas fa-angle-right"></i>',
		responsive: [
			{
			  breakpoint: 1025,
			  settings: {
				slidesToShow: 4,
			  }
			},
			{
			  breakpoint: 769,
			  settings: {
				slidesToShow: 3,
			  }
			},
			{
			  breakpoint: 480,
			  settings: {
				slidesToShow: 1,
			  }
			}
		  ]
	});

	// category product
	$('.testimonial__active').slick({
		infinite: true,
		speed: 500,
		slidesToShow: 3,
		autoplay: true,
  		autoplaySpeed: 3000,
		slidesToScroll: 1,
		dots: false,
		arrows: true,
		prevArrow: '<i class="slick-arrow slick-prev fas fa-angle-left"></i>',
		nextArrow: '<i class="slick-arrow slick-next far fas fa-angle-right"></i>',
		responsive: [
			{
			  breakpoint: 1025,
			  settings: {
				slidesToShow: 3,
			  }
			},
			{
			  breakpoint: 769,
			  settings: {
				slidesToShow: 2,
			  }
			},
			{
			  breakpoint: 480,
			  settings: {
				slidesToShow: 1,
			  }
			}
		  ]
	});


	// feedback
	function feedbackCarousel($scope, $) {
		$('.feedback__slider').slick({
			infinite: true,
			speed: 500,
			slidesToShow: 1,
			autoplay: true,
			autoplaySpeed: 3000,
			slidesToScroll: 1,
			dots: false,
			arrows: false,
		});
	}

	// widget post
	$('.widget__post-slide').slick({
		infinite: true,
		speed: 500,
		slidesToShow: 1,
		autoplay: true,
  		autoplaySpeed: 3000,
		slidesToScroll: 1,
		dots: false,
		arrows: true,
		prevArrow: '<i class="slick-arrow slick-prev fas fa-angle-left"></i>',
		nextArrow: '<i class="slick-arrow slick-next far fas fa-angle-right"></i>',
	});

	/* magnificPopup img view */
	$('.popup-image').magnificPopup({
		type: 'image',
		gallery: {
			enabled: true
		}
	});

	/* magnificPopup video view */
	$('.popup-video').magnificPopup({
		type: 'iframe'
	});

	  /*-------------------------------------
    theiaStickySidebar
    -------------------------------------*/
	if (typeof $.fn.theiaStickySidebar !== "undefined") {
		$(".sticky-coloum-wrap .sticky-coloum-item").theiaStickySidebar({
		  additionalMarginTop: 130,
		});
	  }

	  //  Countdown
	// $('[data-countdown]').each(function () {

	// 	var $this = $(this),
	// 		finalDate = $(this).data('countdown');
	// 	if (!$this.hasClass('countdown-full-format')) {
	// 		$this.countdown(finalDate, function (event) {
	// 			$this.html(event.strftime('<div class="single"><h1>%D</h1><p>Days</p></div> <div class="single"><h1>%H</h1><p>Hours</p></div> <div class="single"><h1>%M</h1><p>Mins</p></div> <div class="single"><h1>%S</h1><p>Sec</p></div>'));
	// 		});
	// 	} else {
	// 		$this.countdown(finalDate, function (event) {
	// 			$this.html(event.strftime('<div class="single"><h1>%Y</h1><p>Years</p></div> <div class="single"><h1>%m</h1><p>Months</p></div> <div class="single"><h1>%W</h1><p>Weeks</p></div> <div class="single"><h1>%d</h1><p>Days</p></div> <div class="single"><h1>%H</h1><p>Hours</p></div> <div class="single"><h1>%M</h1><p>Mins</p></div> <div class="single"><h1>%S</h1><p>Sec</p></div>'));
	// 		});
	// 	}
	// });


	$('.main-menu nav > ul > li').slice(-2).addClass('menu-last');

	// modell single
	$(function () {
		$('.model-option li').on('click', function () {
			var active = $('.model-option li.active');
			active.removeClass('active');
			$(this).addClass('active');
		});
	});



	/*------------------------------------------
        = NEWSLETTER POPUP
    -------------------------------------------*/
    function newsletterPopup() {
        var newsletter = $(".newsletter-popup-area-section");
        var newsletterClose = $(".newsletter-close-btn");

        var test = localStorage.input === 'true'? true: false;
        $(".show-message").prop('checked', test || false);

        var localValue = localStorage.getItem("input");

        //console.log(localValue);

        if(localValue === "true") {
            newsletter.css({
                "display": "none"
            });
        }

        newsletter.addClass("active-newsletter-popup");

        newsletterClose.on("click", function(e) {
            newsletter.removeClass("active-newsletter-popup");
            return false;
        })

        $(".show-message").on('change', function() {
            localStorage.input = $(this).is(':checked');
        });
    }

	/*------------------------------------------
        = SHOP PAGE GRID VIEW TOGGLE
    -------------------------------------------*/
    if($(".woocommerce-toolbar-top").length) {
        var products = $(".products"),
            allButton = $(".products-sizes a"),
            grid4 = $(".grid-4"),
            grid3 = $(".grid-3"),
            listView = $(".list-view");

        allButton.each(function() {
            var $this = $(this);
            $this.on("click", function(e) {
                e.preventDefault();
                $this.addClass("active").siblings().removeClass('active');
                e.stopPropagation();
            })
        });

        grid4.on("click", function(f) {
            products.removeClass("list-view three-column");
            products.addClass("default-column");
            f.stopPropagation();
        });

        grid3.on("click", function(g) {
            products.removeClass("default-column list-view");
            products.addClass("three-column");
            g.stopPropagation();
        });

        listView.on("click", function(h) {
            products.removeClass("default-column three-column");
            products.addClass("list-view");
            h.stopPropagation();
        });
    }

	/*----------------------------
	= SHOP PRICE SLIDER
    ------------------------------ */
    if($("#slider-range").length) {
        $("#slider-range").slider({
            range: true,
            min: 12,
            max: 200,
            values: [0, 100],
            slide: function(event, ui) {
                $("#amount").val("$" + ui.values[0] + " - $" + ui.values[1]);
            }
        });

        $("#amount").val("$" + $("#slider-range").slider("values", 0) + " - $" + $("#slider-range").slider("values", 1));
    }

	/*------------------------------------------
    = TOUCHSPIN FOR PRODUCT SINGLE PAGE
    -------------------------------------------*/
    if ($("input.product-count").length) {
        $("input.product-count").TouchSpin({
            min: 1,
            max: 1000,
            step: 1,
            buttondown_class: "btn btn-link",
            buttonup_class: "btn btn-link",
        });
    }



    /*------------------------------------------
        = VERTICAL MENU FOR HEADER CAT
    -------------------------------------------*/
    if($(".vertical-menu").length) {
		var $verticalMenu = $(".vertical-menu");
		var $btn = $(".vertical-menu > button");
		var $menu = $(".category-nav__list");

		$menu.hide();

		$btn.on("click", function(e) {
			$menu.slideToggle();
			$verticalMenu.toggleClass("rotate-arrow");
		});
	}

	/*------------------------------------------
        = woocommerce
    -------------------------------------------*/
    if($(".checkout-section").length) {
        var showLogInBtn = $(".woocommerce-info > a");
        var showCouponBtn = $(".showcoupon");
        var shipDifferentAddressBtn = $("#ship-to-different-address");
        var loginForm = $("form.login");
        var couponForm = $(".checkout_coupon");
        var shippingAddress = $(".shipping_address");

        loginForm.hide();
        couponForm.hide();
        shippingAddress.hide();

        showLogInBtn.on("click", function(event) {
            event.preventDefault();
            loginForm.slideToggle();
            event.stopPropagation();
        });

        showCouponBtn.on("click", function(event2) {
            event2.preventDefault();
            couponForm.slideToggle();
            event2.stopPropagation();
        })

        shipDifferentAddressBtn.on("click", function(event3) {
            shippingAddress.slideToggle();
            event3.stopPropagation();
        })
    }

	// Accordion Box start
	if ($(".accordion_box").length) {
		$(".accordion_box").on("click", ".acc-btn", function () {
			var outerBox = $(this).parents(".accordion_box");
			var target = $(this).parents(".accordion");

			if ($(this).next(".acc_body").is(":visible")) {
				$(this).removeClass("active");
				$(this).next(".acc_body").slideUp(300);
				$(outerBox).children(".accordion").removeClass("active-block");
			} else {
				$(outerBox).find(".accordion .acc-btn").removeClass("active");
				$(this).addClass("active");
				$(outerBox).children(".accordion").removeClass("active-block");
				$(outerBox).find(".accordion").children(".acc_body").slideUp(300);
				target.addClass("active-block");
				$(this).next(".acc_body").slideDown(300);
			}
		});
	}
	// Accordion Box end

	$(".contact-info__item").on('mouseenter', function () {
		$(".contact-info__item").removeClass("active");
		$(this).addClass("active");
	});

    /*------------------------------------------
        = COOKIES AREA CLOSE
    -------------------------------------------*/
    function cookiesClose() {
        var cookieBox = $(".cookies-area");
        var acceptBtn = $(".cookies-area .cookie-btn");

        var localValue = localStorage.getItem("button");

        if(localValue === "true") {
            cookieBox.css({
                "display": "none"
            });
        }

        acceptBtn.on("click", function(e) {
            cookieBox.addClass("remove-cookies-area");
            localStorage.button = true;
            return false;
        })
    }

	/*==========================================================================
        WHEN DOCUMENT LOADING
    ==========================================================================*/
	$(window).on('load', function() {



		if($(".newsletter-popup-area-section").length) {

			setTimeout(function() {
				newsletterPopup();
			},"2500");
		}

		if($(".cookies-area").length) {
			cookiesClose();
		}

	});
	function brandActive($scope, $) {
		$('.brand__slide').slick({
			infinite: true,
			speed: 500,
			slidesToShow: 5,
			autoplay: true,
			autoplaySpeed: 3000,
			slidesToScroll: 1,
			dots: false,
			arrows: false,
			responsive: [
				{
				breakpoint: 1024,
				settings: {
					slidesToShow: 4,
					slidesToScroll: 4,
				}
				},
				{
				breakpoint: 600,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 3
				}
				},
				{
				breakpoint: 480,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 2
				}
				}
			]
		});
	}

	function categoryActiveSLider($scope, $) {
		$('.services-carousel').owlCarousel({
			//animateOut: 'fadeOut',
    		//animateIn: 'fadeIn',
			loop:true,
			margin:30,
			nav:true,
			//autoHeight: true,
			smartSpeed: 500,
			autoplay: 6000,
			navText: [ '<span class="fa-solid fa-arrow-left fa-fw"></span>', '<span class="fa-solid fa-arrow-right fa-fw"></span>' ],
			responsive:{
				0:{
					items:1
				},
				600:{
					items:2
				},
				800:{
					items:3
				},
				1024:{
					items:4
				},
				1200:{
					items:5
				},
				1400:{
					items:6
				}
			}
		});
	}

	function productSliderFilter($scope, $) {
		$(".gr-product-store-slider-1").slick({
			slidesToShow: 4,
			arrows: true,
			dots: false,
			slidesToScroll: 1,
			prevArrow: ".store_left_arrow",
			nextArrow: ".store_right_arrow",
			responsive: [
			{
				breakpoint: 1025,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 1,
					infinite: true,
				}
			},
			{
				breakpoint: 800,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 600,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 500,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}
			]
		});
		$(".filter li").on('click', function(){
			var filter = $(this).data('filter');
			$(".gr-product-store-slider-1").slick('slickUnfilter');

			if(filter == 'one'){
				$(".gr-product-store-slider-1").slick('slickFilter','.one');
			}
			else if(filter == 'two'){
				$(".gr-product-store-slider-1").slick('slickFilter','.two');
			}
			else if(filter == 'three'){
				$(".gr-product-store-slider-1").slick('slickFilter','.three');
			}
			else if(filter == 'four'){
				$(".gr-product-store-slider-1").slick('slickFilter','.four');
			}
			else if(filter == 'five'){
				$(".gr-product-store-slider-1").slick('slickFilter','.five');
			}
			else if(filter == 'six'){
				$(".gr-product-store-slider-1").slick('slickFilter','.six');
			}
			else if(filter == 'all'){

				$(".gr-product-store-slider-1").slick('slickUnfilter');
			}
		});
		$('.gr-product-filter-tab-btn ul li').on('click', function() {

			$('.gr-product-filter-tab-btn ul li.active').removeClass('active');
			$(this).addClass('active');
		});
	}
	function productActiveCarousel($scope, $) {
		$('.gr-condiments-slider').slick({
			arrow: true,
			dots: false,
			loop: true,
			infinite: true,
			slidesToShow: 3,
			autoplay: false,
			slidesToScroll: 1,
			prevArrow: ".cond_left_arrow_1",
			nextArrow: ".cond_right_arrow_1",
			speed: 1000,
			responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 1,
					infinite: true,
				}
			},
			{
				breakpoint: 1000,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 800,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 600,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 500,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}

			]
		});
	}
	function testimonialSlideActive($scope, $) {
		$('.gr-testimonial-slider').slick({
			arrow: true,
			dots: false,
			loop: true,
			infinite: true,
			slidesToShow: 2,
			autoplay: false,
			slidesToScroll: 1,
			prevArrow: ".testi_left_arrow_1",
			nextArrow: ".testi_right_arrow_1",
			speed: 1000,
			responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 1,
					infinite: false,
				}
			},
			{
				breakpoint: 1000,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 800,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 600,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 500,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}

			]
		});
	}
	/**
	 * Data Counter Active
	 *
	 * @param   {[type]}  $scope  [$scope description]
	 * @param   {[type]}  $       [$ description]
	 *
	 * @return  {[type]}          [return description]
	 */
	$('.countdown_timer').each(function(){
		$('[data-countdown]').each(function() {
			var $this = $(this), finalDate = $(this).data('countdown');
			$this.countdown(finalDate, function(event) {
				var $this = $(this).html(event.strftime(''
					+ '<li class="days_count"><strong>%D</strong><span>Days</span></li>'
					+ '<li class="hours_count"><strong>%H</strong><span>Hours</span></li>'
					+ '<li class="minutes_count"><strong>%M</strong><span>Mins</span></li>'
					+ '<li class="seconds_count"><strong>%S</strong><span>Secs</span></li>'));
			});
		});
	});

	function grProductActiveSlide($scope, $) {
		$('.gr-product-store-slider-2').slick({
			arrow: true,
			dots: false,
			loop: true,
			infinite: true,
			slidesToShow: 3,
			autoplay: false,
			slidesToScroll: 1,
			prevArrow: ".store_left_arrow_1",
			nextArrow: ".store_right_arrow_1",
			speed: 1000,
			responsive: [
			{
				breakpoint: 1024,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 1,
					infinite: false,
				}
			},
			{
				breakpoint: 1000,
				settings: {
					slidesToShow: 3,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 800,
				settings: {
					slidesToShow: 2,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 600,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			},
			{
				breakpoint: 500,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1
				}
			}

			]
		});
	}

	$('.gr-footer-sponsor-slider').slick({
		arrow: true,
		dots: false,
		loop: true,
		speed: 1000,
		infinite: true,
		slidesToShow: 5,
		autoplay: true,
		slidesToScroll: 1,
		prevArrow: ".spon_left_arrow_1",
		nextArrow: ".spon_right_arrow_1",
		responsive: [
		{
			breakpoint: 1024,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 1,
				infinite: true,
			}
		},
		{
			breakpoint: 1000,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 1
			}
		},
		{
			breakpoint: 800,
			settings: {
				slidesToShow: 3,
				slidesToScroll: 1
			}
		},
		{
			breakpoint: 600,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 2
			}
		},
		{
			breakpoint: 500,
			settings: {
				slidesToShow: 2,
				slidesToScroll: 1
			}
		},

		]
	});

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-cate-slider-id.default', cateSliderActive);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-banner-carousel-id.default', bannerSliderActive);
		//elementorFrontend.hooks.addAction('frontend/element_ready/vi-product-list-car-id.default', slideWidgetActive);
		elementorFrontend.hooks.addAction('frontend/element_ready/blog-post-widget.default', viNewsSlider);
		elementorFrontend.hooks.addAction('frontend/element_ready/blog-post-grid-v2-widget.default', viNewsSlider);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-feedback-carousel-id.default', feedbackCarousel);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-product-featured-id.default', productSingleSlider);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-cate-slider-v2-id.default', productCateSlider);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-product-tab-id.default', sideProductSliderActive);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-product-single-carousel-id.default', sideProductSliderActive);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-product-latest-carousel-id.default', sideProductSliderActive);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-product-featured-id.default', brandActiveSlide);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-brand-logo-id.default', brandActive);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-cate-slider-v3-id.default', categoryActiveSLider);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-product-filter-slide-id.default', productSliderFilter);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-product-carousel-id.default', productActiveCarousel);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-testimonial-item-id.default', testimonialSlideActive);
		elementorFrontend.hooks.addAction('frontend/element_ready/vi-product-c2-id.default', grProductActiveSlide);
	});



})(jQuery);
