'use strict';
!(function ($) {
    $(() => {
        $('.menu-item.has-submenu .menu-link').on('click', function (e) {
            e.preventDefault();
            $(this).next('.submenu').is(':hidden') &&
            $(this)
                .parent('.has-submenu')
                .siblings()
                .find('.submenu')
                .slideUp(200);
            $(this).next('.submenu').slideToggle(200);
        });
        $('[data-trigger]').on('click', function (s) {
            s.preventDefault(), s.stopPropagation();
            var n = $(this).attr('data-trigger');
            $(n).toggleClass('show'),
                $('body').toggleClass('offcanvas-active'),
                $('.screen-overlay').toggleClass('show');
        });
        $('.screen-overlay, .btn-close').on('click', function (s) {
            $('.screen-overlay').removeClass('show');
            $('.mobile-offcanvas, .show').removeClass('show');
            $('body').removeClass('offcanvas-active');
        });
        $('.btn-aside-minimize').on('click', function () {
            window.innerWidth < 768
                ? (e('body').removeClass('aside-mini'),
                    $('.screen-overlay').removeClass('show'),
                    $('.navbar-aside').removeClass('show'),
                    $('body').removeClass('offcanvas-active'))
                : $('body').toggleClass('aside-mini');
        });
        $('.select-nice').length && $('.select-nice').select2();

        if ($('#offcanvas_aside').length) {
            const e = document.querySelector('#offcanvas_aside');
            new PerfectScrollbar(e);
        }

        $('.darkmode').on('click', function () {
            $('body').toggleClass('dark');
        });

        $('.custom-select-image').on('click', function (event) {
            event.preventDefault();
            $(this).closest('.image-box').find('.image_input').trigger('click');
        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $(input)
                        .closest('.image-box')
                        .find('.preview_image')
                        .prop('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        $('.image_input').on('change', function () {
            readURL(this);
        });

        $(document).on('click', '.btn_remove_image', (event) => {
            event.preventDefault();
            let $img = $(event.currentTarget)
                .closest('.image-box')
                .find('.preview-image-wrapper .preview_image');
            $img.attr('src', $img.data('default-image'));
            $(event.currentTarget)
                .closest('.image-box')
                .find('.image-data')
                .val('');
        });

        if (window.noticeMessages && window.noticeMessages.length) {
            window.noticeMessages.map((x, k) => {
                Botble.showNotice(x.type, x.message, '');
            });
            window.noticeMessages = [];
        }
    });
})(jQuery);
