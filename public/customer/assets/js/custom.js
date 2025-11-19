$(document).ready(function () {
    var lazyLoadInstance = new LazyLoad({ elements_selector: "img.lazy, video.lazy, div.lazy, section.lazy, header.lazy, footer.lazy,iframe.lazy" });
    let bannerHeight = $(window).height();
    $("#related-products").not('.slick-initialized').slick({
        centerMode: false,
        slidesToShow: 4,
        slidesToScroll: 1,
        arrows: true,
        prevArrow: '<i class="icon-left-arrow right-arrow arrow"></i>',
        nextArrow: '<i class="icon-right-arrow left-arrow arrow"></i>',
        responsive: [{
            breakpoint: 1200,
            settings: {
                centerMode: false,
                centerPadding: '0px',
                slidesToShow: 5,
                slidesToScroll: 1,

            }
        }, {
            breakpoint: 1300,
            settings: {
                centerMode: false,
                slidesToShow: 3,
                slidesToScroll: 1,
            }
        }, {
            breakpoint: 1200,
            settings: {
                centerMode: false,
                slidesToShow: 3,
                slidesToScroll: 1,
            }
        }, {
            breakpoint: 1024,
            settings: {
                centerMode: false,
                slidesToShow: 2,
                slidesToScroll: 1,
            }
        }, {
            breakpoint: 992,
            settings: {
                centerMode: false,
                slidesToShow: 2,
                slidesToScroll: 1,
            }
        }, {
            breakpoint: 576,
            settings: {
                centerMode: false,
                slidesToShow: 1,
                slidesToScroll: 1,
            }
        }]

    });
});

$("#isShippingDiffernt").click(function () {
    if ($(this).is(':checked') == true) {
        $("#shippingForm").removeClass('d-none');
    } else {
        $("#shippingForm").addClass('d-none');
    }
});

// Adding csrf-token to any ajax request headers
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

window.onscroll = function () { myFunction() };

var navbar = document.getElementById("navbar");
var sticky = navbar.offsetTop;

function myFunction() {
    if (window.pageYOffset >= sticky) {
        navbar.classList.add("sticky")
    } else {
        navbar.classList.remove("sticky");
    }
}

/**
 * Initialize reusable filters
 * @param {Object} options
 *  - checkboxSelectorMap: object → key = query param name, value = checkbox selector
 *      e.g., { brands: '.brand-label', categories: '.category-label' }
 *  - sliderSelectorMap: object → key = min/max query param name, value = slider selector
 *      e.g., { price_min: '.js-range-slider', price_max: '.js-range-slider' }
 *  - url: string → base URL for filtering
 */
function initFilters(options = {}) {
    var url = options.url || window.location.href.split('?')[0];
    var sliders = {};

    // Initialize sliders
    if (options.sliderSelectorMap) {
        $.each(options.sliderSelectorMap, function (paramName, selector) {

            var slider = $(selector).ionRangeSlider({
                type: 'double',
                min: options.sliderOptions?.min || priceMin,
                max: options.sliderOptions?.max || priceMax,
                from: options.sliderOptions.from,
                to: options.sliderOptions.to,
                step: options.sliderOptions?.step || 10,
                skin: 'round',
                max_postfix: '+',
                prefix: currency,
                onFinish: applyFilters
            }).data('ionRangeSlider');

            sliders[paramName] = slider;
        });
    }

    // Initialize checkboxes
    if (options.checkboxSelectorMap) {
        $.each(options.checkboxSelectorMap, function (paramName, selector) {
            $(selector).change(applyFilters);
        });
    }

    // Initialize selects
    if (options.selectSelectorMap) {
        $.each(options.selectSelectorMap, function (paramName, selector) {
            $(selector).change(applyFilters);
        });
    }

    // Apply filters
    function applyFilters() {
        var query = [];

        // Checkbox values
        $.each(options.checkboxSelectorMap || {}, function (paramName, selector) {
            var values = $(selector + ':checked').map(function () {
                return $(this).val();
            }).get();

            if (values.length) query.push(paramName + '=' + values.join(','));
        });

        // Slider values
        $.each(sliders, function (paramName, slider) {
            query.push(paramName + '_min=' + slider.result.from);
            query.push(paramName + '_max=' + slider.result.to);
        });

        // Select values
        $.each(options.selectSelectorMap || {}, function (paramName, selector) {
            var value = $(selector).val();
            if (value) query.push(paramName + '=' + value);
        });

        window.location.href = url + (query.length ? '?' + query.join('&') : '');
    }
}

function addToCart(id) {
    $.ajax({
        url: addToCartRoute,
        type: 'post',
        data: { id: id },
        dataType: 'json',
        success: function (response) {
            console.log(response);
            // alert(response.message);
            window.location.href = cartRoute;
        },
        error: function (xhr, status, error) {
            console.error(xhr);
            const message = xhr.responseJSON?.message || error;
            alert(message);
        }
    });
}