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

// Convert text to slug
function slugify(text) {
    return text
        .toString()                      // make sure it's a string
        .normalize('NFD')                // split accented characters (e.g. é → e +  ́)
        .replace(/[\u0300-\u036f]/g, '') // remove accents
        .toLowerCase()                   // convert to lowercase
        .trim()                          // remove leading/trailing spaces
        .replace(/[^a-z0-9\s-]/g, '')    // remove invalid chars
        .replace(/\s+/g, '-')            // replace spaces with hyphens
        .replace(/-+/g, '-');            // collapse multiple hyphens
}

function addSlugifyEvent(sourceId, destinationId) {
    $('#' + sourceId).change(function () {
        let name = $(this).val();
        let slug = slugify(name);
        $('#' + destinationId).val(slug);
    });
}

function loadDropdown(triggerSelectorId, targetSelectorId, url, dataFunction, processResponse, clearBefore = true) {
    $('#' + triggerSelectorId).change(function () {
        var value = $(this).val();

        if (!value) {
            if (clearBefore) $('#' + targetSelectorId).find('option').not(':first').remove();
            return;
        }

        $.ajax({
            url: url,
            type: 'get',
            data: dataFunction(value), // dynamically generate data based on selected value
            dataType: 'json',
            success: function (response) {
                if (clearBefore) $('#' + targetSelectorId).find('option').not(':first').remove();

                const items = processResponse(response); // get items to populate dropdown

                $.each(items, function (id, name) {
                    $('#' + targetSelectorId).append('<option value="' + id + '">' + name + '</option>');
                });
            },
            error: function (xhr, status, error) {
                const message = xhr.responseJSON?.error || error;
                showError(message);
            }
        });
    });
}

function initSelect2(selectId, ajaxParams = {}){
    let config = {
        minimumInputLength: ajaxParams.minimumInputLength ?? 3,
        tags: ajaxParams.tags ?? true,
        multiple: ajaxParams.multiple ?? true,
        closeOnSelect: ajaxParams.closeOnSelect ?? false
    };

    if(ajaxParams.url){
        config.ajax = {
            url: ajaxParams.url,
            dataType: 'json',
            delay: 250,
            processResults: ajaxParams.processResults ?? function (data) {
                return {
                    results: data.tags ?? data
                };
            }
        };
    }

    const select = $('#' + selectId).select2(config);

    select.on('select2:select', function () {
        if (config.closeOnSelect === true) {
            $(this).select2('close');
        }
    });
}

