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

// ✅ Show validation errors (supports multiple messages per field)
function showValidationErrors(errors) {
    $.each(errors, function(field, messages) {
        const input = $('#' + field);
        input.addClass('is-invalid');
        
        // Remove any previous feedback messages for this input
        // input.nextAll('.invalid-feedback').remove();

        // Ensure messages is always treated as an array
        const messageArray = Array.isArray(messages) ? messages : [messages];

        // Append all messages
        const allMessages = messageArray.join('<br>');
        input.after('<p class="invalid-feedback">' + allMessages + '</p>');
    });
}

// ✅ Hide all validation errors inside a specific form
function hideValidationErrors(formId) {
    $('#' + formId)
        .find('input, select, textarea')
        .each(function () {
            $(this)
                .removeClass('is-invalid')
                .nextAll('.invalid-feedback') // remove all feedbacks (in case multiple exist)
                .remove();
        });
}