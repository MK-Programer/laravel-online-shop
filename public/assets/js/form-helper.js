// Reset the entire form
function resetForm(formId) {
    const form = $('#' + formId);
    if (form.length) {
        form[0].reset();
        if ($('.summernote').length) {
            $('.summernote').summernote('code', '');
        }

        // Remove Dropzone files *only* in this form
        form.find('.dropzone').each(function (index, element) {
            const dz = Dropzone.forElement(element);
            if (dz) dz.removeAllFiles(true);
        });

        // Remove hidden image inputs only in this form
        form.find('input[type="hidden"][data-dz-image-id]').remove();
        hideValidationErrors(formId);
    }
}

// Show validation errors (supports multiple messages per field)
function showValidationErrors(errors) {
    $.each(errors, function (field, messages) {
        // Convert "images_order.123" → "images_order[123]"
        const bracketName = field.replace(/\.(\w+)/g, '[$1]');

        // Escape brackets for jQuery selector
        const safeName = bracketName.replace(/([:[\].])/g, '\\$1');

        // Try to find element by id, name, or data-name
        let input = $('#' + field.replace(/\./g, '\\.'));
        if (!input.length) input = $('[name="' + bracketName + '"]');
        if (!input.length) input = $('[data-name="' + field + '"]');

        const parentDiv = input.closest('.mb-3');
        const messageArray = Array.isArray(messages) ? messages : [messages];
        const allMessages = messageArray.join('<br>');

        // Remove any previous messages for this field
        parentDiv.find('.invalid-feedback').remove();

        // Add invalid class for styling (applies to Dropzones too)
        input.addClass('is-invalid');

        // Handle Dropzone specifically
        if (input.hasClass('dropzone')) {
            input.after(
                '<p class="invalid-feedback">' +
                allMessages +
                '</p>'
            );
        } else {
            input.after(
                '<p class="invalid-feedback">' +
                allMessages +
                '</p>'
            );
        }
    });
}

// Hide all validation errors inside a specific form
function hideValidationErrors(formId) {
    $('#' + formId)
        .find('input, select, textarea, .dropzone')
        .each(function () {
            $(this)
                .removeClass('is-invalid')
                .nextAll('.invalid-feedback') // remove all feedbacks (in case multiple exist)
                .remove();
        });
}

function submitFormUsingAjax(formId, resetFormOnSuccess = true, onSuccess = null) {
    $('#'+formId).submit(function (event) {
        event.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: $(this).serializeArray(),
            dataType: 'json',
            beforeSend: function () {
                removeAlert();
                hideValidationErrors(formId);
            },
            success: function (response) {
                if(resetFormOnSuccess) resetForm(formId);
                if(typeof onSuccess === 'function'){
                    onSuccess(response);
                }else{
                    showSuccess(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error(xhr);
                if (xhr.status == 422) {
                    showValidationErrors(xhr.responseJSON.errors);
                } else {
                    const message = xhr.responseJSON?.error || error;
                    showError(message);
                }
            },
        });
    });
}