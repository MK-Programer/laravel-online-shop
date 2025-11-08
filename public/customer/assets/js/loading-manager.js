(function($) {
    const $loader = $('#page_loader');
    const fadeInDuration = 200;
    const fadeOutDuration = 400;
    const minDisplayTime = 300; // minimum time loader is visible in ms

    let loaderStartTime;

    function showLoader() {
        loaderStartTime = new Date().getTime();
        $loader.stop(true, true).fadeIn(fadeInDuration);
    }

    function hideLoader() {
        const elapsed = new Date().getTime() - loaderStartTime;
        const delay = elapsed < minDisplayTime ? minDisplayTime - elapsed : 0;
        setTimeout(() => {
            $loader.fadeOut(fadeOutDuration);
        }, delay);
    }

    // Show loader immediately when script runs (page load start)
    $loader.show();

    // Hide loader when page fully loaded
    $(window).on('load', hideLoader);

    // Handle bfcache restore (back/forward buttons)
    $(window).on('pageshow', function(event) {
        if (event.originalEvent.persisted) {
            $loader.hide();
        } else {
            hideLoader();
        }
    });

    // Show loader on internal link clicks
    $(document).on('click', 'a', function(e) {
        const href = $(this).attr('href');
        const target = $(this).attr('target');
        if (href && !href.startsWith('#') && !href.startsWith('javascript') && target !== '_blank') {
            showLoader();
        }
    });

    // Show loader on form submission
    $(document).on('submit', 'form', function() {
        showLoader();
    });

    // Show/hide loader on AJAX requests
    $(document).ajaxStart(showLoader);
    $(document).ajaxStop(hideLoader);
    $(document).ajaxError(hideLoader);

})(jQuery);
