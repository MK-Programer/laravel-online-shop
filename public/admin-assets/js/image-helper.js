Dropzone.autoDiscover = false;

$(function () {
    $('.dropzone').each(function (index, element) {
        initDropzone(element);
    });
});

/* -------------------------------------
   🔹 MAIN INITIALIZER
------------------------------------- */
function initDropzone(element) {
    const $el = $(element);
    const folder = $el.data('folder') || 'others';
    const maxFiles = $el.data('max-files') || 1;
    const inputName = $el.data('name') || 'image_id';

    const myDropzone = new Dropzone(element, {
        url: tempImageUploadUrl,
        paramName: 'images',
        uploadMultiple: true,
        parallelUploads: maxFiles,
        maxFiles: maxFiles,
        addRemoveLinks: true,
        acceptedFiles: "image/png,image/jpg,image/jpeg,image/gif",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        init: function () {
            setupDropzoneEvents(this, $el, folder, maxFiles, inputName);
        }
    });
}

/* -------------------------------------
   🔹 SETUP EVENT HANDLERS
------------------------------------- */
function setupDropzoneEvents(dropzone, $el, folder, maxFiles, inputName) {

    dropzone.on('addedfile', function (file) {
        handleAddedFile(dropzone, $el, maxFiles);
    });

    dropzone.on('removedfile', function (file) {
        handleRemovedFile($el);
    });

    dropzone.on('sending', function (file, xhr, formData) {
        formData.append('folder', folder);
    });

    dropzone.on('success', function (file, response) {
        handleSuccess($el, response, maxFiles, inputName);
    });

    dropzone.on('error', function (file, errorMessage, xhr) {
        handleError($el, errorMessage, inputName);
    });
}

/* -------------------------------------
   🔹 INDIVIDUAL HANDLERS
------------------------------------- */

// ✅ Handle file added
function handleAddedFile(dropzone, $el, maxFiles) {
    if (dropzone.files.length > maxFiles) {
        dropzone.removeFile(dropzone.files[maxFiles - 1]);
    }
    $el.removeClass('border border-danger');
    $el.next('.dropzone-error').remove();
}

// ✅ Handle file removed
function handleRemovedFile($el) {
    const $form = $el.closest('form');
    const imageId = $form.find(`input[data-dz-image-id]`).val();

    if (!imageId) return;

    $.ajax({
        url: tempImageDeleteUrl,
        type: 'DELETE',
        data: {
            image_id: imageId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            console.log('Image removed from temp:', response);
        },
        error: function (xhr, status, error) {
            console.error('Error removing temp image:', error);
        }
    });

    $form.find(`input[data-dz-image-id="${imageId}"]`).remove();
}

// ✅ Handle successful upload
function handleSuccess($el, response, maxFiles, inputName) {
    const uploadedImagesIds = response.images_ids || [];
    const $form = $el.closest('form');

    if (maxFiles === 1) {
        $form.find(`input[name="${inputName}"]`).remove();
    }

    uploadedImagesIds.forEach(id => {
        const inputHtml = (maxFiles === 1)
            ? `<input type="hidden" name="${inputName}" value="${id}" data-dz-image-id="${id}">`
            : `<input type="hidden" name="${inputName}[]" value="${id}" data-dz-image-id="${id}">`;

        $form.append(inputHtml);
    });

    console.log(`Uploaded for ${inputName}:`, uploadedImagesIds);
}

// ✅ Handle upload errors
function handleError($el, errorMessage, inputName) {
    $el.next('.dropzone-error').remove();

    const message = typeof errorMessage === 'string'
        ? errorMessage
        : (errorMessage?.message || 'حدث خطأ أثناء رفع الصورة.');

    const $errorEl = $('<p class="dropzone-error text-danger mt-1"></p>');
    $errorEl.text(message);

    $el.addClass('border border-danger');
    $el.after($errorEl);

    console.error(`Dropzone error [${inputName}]:`, message);
}
