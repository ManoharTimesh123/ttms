(function($) {
    // always false because of Owl bugs
    var $ccnDirection = $("body").hasClass("dir-rtl") ? false : false;

    (function($) {
        "use strict";

        $(document).on('ready', function () {          
            $('.dashboard_sidebars').addClass('collapse-menu');  
            $('.left-menu-toggle, .left-menu-toggle-desktop').click(function () {
                $(".dashboard_sidebar").toggleClass("show");
                $(".dashboard_sidebars").toggleClass("collapse-menu");
                $(".dashboard_main_content").toggleClass("show");
                $(".mobile-menu-left-icon").toggleClass("flaticon-back");
            });   
            $('.filter-toggle').click(function () {
                $(".report-filter-form").toggleClass("report-form");
            });       
            // Announcement slider.
            if ($('.announcement_slider').length) {
                $('.announcement_slider').owlCarousel({
                    loop: false,
                    margin: 30,
                    dots: false,
                    nav: true,
                    rtl: $ccnDirection,
                    autoplayHoverPause: false,
                    autoplay: false,
                    singleItem: true,
                    smartSpeed: 1200,
                    items: 1,
                    navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],                    
                });
            }

            /*  News Slider  */
            if ($('.latest_news_slider').length) {
                $('.latest_news_slider').owlCarousel({
                    loop: false,
                    margin: 30,
                    dots: false,
                    nav: true,
                    rtl: $ccnDirection,
                    autoplayHoverPause: false,
                    autoplay: false,
                    singleItem: true,
                    smartSpeed: 1200,
                    navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
                    responsive: {
                        0: {
                            items: 1
                        },
                        600: {
                            items: 2,
                            center: false
                        },
                        992: {
                            items: 3
                        }
                    }
                });
            }
            $('.mform').on('submit', function() {
                setTimeout(function() {
                    var submitted = true;

                    if ($('.mform .is-invalid').length !== 0) {
                        submitted = false;
                    }

                    if ($('.mform .error').length !== 0) {
                        submitted = false;
                    }

                    if ($('#fitem_id_comment').hasClass('has-danger')) {
                        submitted = false;
                    }

                    if (submitted) {
                        disableSubmitButton();
                        disableOtherSubmitButton();
                    }

                }, 100);
            });
            function disableSubmitButton() {
                var submitButton = $('#id_submitbutton');
                submitButton.prop('disabled', true);
                submitButton.val('Submitting...');
            }

            function disableOtherSubmitButton() {
                var submitButton = $('#distribution_button');
                submitButton.prop('disabled', true);
                submitButton.val('Distributing users, please wait...');
            }

        });

    })(window.jQuery);
}(jQuery));

