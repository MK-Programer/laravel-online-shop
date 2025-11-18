// Hide loader when page fully loaded
$(window).on('load', function () {
    $('#page_loader').fadeOut(400);
});

// Show loader on normal navigation
$(document).on('click', 'a', function (e) {
    const link = $(this).attr('href');
    if (link && !link.startsWith('#') && !link.startsWith('javascript')) {
        $('#page_loader').fadeIn(200);
    }
});

// Show loader on form submit
$(document).on('submit', 'form', function () {
    $('#page_loader').fadeIn(200);
});

// Handle back/forward navigation (bfcache restore)
$(window).on('pageshow', function (event) {
    // If the page was restored from bfcache, hide the loader
    if (event.originalEvent.persisted) {
        $('#page_loader').fadeOut(0);
    } else {
        // Also ensure it's hidden just in case
        $('#page_loader').fadeOut(0);
    }
});

// Show loader when any AJAX request starts
$(document).ajaxStart(function () {
    $('#page_loader').fadeIn(200);
});

// Hide loader when all AJAX requests complete
$(document).ajaxStop(function () {
    $('#page_loader').fadeOut(400);
});

$(document).ajaxError(function () {
    $('#page_loader').fadeOut(400);
});

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
                const message = xhr.responseJSON?.error || 'An unexpected error occurred.';
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

