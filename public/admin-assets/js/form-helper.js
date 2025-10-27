// ✅ Reset the entire form
function resetForm(formId) {
    const form = $('#'+formId);
    if (form.length) {
        form[0].reset();
        // ✅ Remove Dropzone files *only* in this form
        form.find('.dropzone').each(function(index, element) {
            const dz = Dropzone.forElement(element);
            if (dz) dz.removeAllFiles(true);
        });

        // ✅ Remove hidden image inputs only in this form
        form.find('input[type="hidden"][data-dz-image-id]').remove();
        hideValidationErrors(formId);
    }
}

// ✅ Show validation errors (expects Laravel-style error object)
function showValidationErrors(errors) {
    $.each(errors, function(field, message) {
        const input = $('#' + field);
        input.addClass('is-invalid');
        
        // Remove any existing validation message
        // input.next('.invalid-feedback').remove();

        // Add new one dynamically
        input.after('<p class="invalid-feedback">' + message + '</p>');
    });
}

// ✅ Hide all validation errors inside a form
function hideValidationErrors(formId) {
    $('#'+formId)
        .find('input, select, textarea')
        .each(function () {
            $(this)
                .removeClass('is-invalid')
                .next('.invalid-feedback')
                .remove();
        });
}