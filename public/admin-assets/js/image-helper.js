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
    const maxFiles = parseInt($el.data('max-files') || 1);
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
            preloadExistingImages(this, $el, inputName, maxFiles);
        }
    });
}

/* -------------------------------------
   🔹 LOAD EXISTING IMAGES
------------------------------------- */
function preloadExistingImages(dropzone, $el, inputName, maxFiles) {
    const existingImages = $el.data('existing-images');
    const existingNames = $el.data('existing-names');

    // If no existing data, stop early
    if (!existingImages || !existingNames) return;

    // Convert to arrays if single values
    const images = Array.isArray(existingImages) ? existingImages : [existingImages];
    const names = Array.isArray(existingNames) ? existingNames : [existingNames];

    // ✅ Loop through both arrays together
    for (let i = 0; i < images.length; i++) {
        const imgUrl = images[i];
        const imgName = names[i] || `image_${i}`;

        const mockFile = {
            name: imgName,
            size: 12345,
            accepted: true
        };

        // Add image preview in Dropzone
        dropzone.emit("addedfile", mockFile);
        dropzone.emit("thumbnail", mockFile, imgUrl);
        dropzone.emit("complete", mockFile);
        dropzone.files.push(mockFile);

        // Append hidden input for form submission
        const isMultiple = maxFiles > 1;
        const inputHtml = isMultiple
            ? `<input type="hidden" name="${inputName}[]" value="${imgName}" data-existing="true" data-name="${imgName}">`
            : `<input type="hidden" name="${inputName}" value="${imgName}" data-existing="true" data-name="${imgName}">`;

        $el.closest('form').append(inputHtml);
    }
}

/* -------------------------------------
   🔹 SETUP EVENTS
------------------------------------- */
function setupDropzoneEvents(dropzone, $el, folder, maxFiles, inputName) {

    dropzone.on('addedfile', function (file) {
        if (dropzone.files.length > maxFiles) {
            dropzone.removeFile(dropzone.files[0]);
        }
        $el.removeClass('border border-danger');
        $el.next('.dropzone-error').remove();
    });

    dropzone.on('removedfile', function (file) {
        handleRemovedFile($el, file, inputName);
    });

    dropzone.on('sending', function (file, xhr, formData) {
        formData.append('folder', folder);
    });

    dropzone.on('success', function (file, response) {
        handleSuccess($el, response, maxFiles, inputName, file);
    });

    dropzone.on('error', function (file, errorMessage) {
        handleError($el, errorMessage, inputName);
    });
}

/* -------------------------------------
   🔹 HANDLERS
------------------------------------- */
function handleRemovedFile($el, file, inputName) {
    const $form = $el.closest('form');
    const fileName = file.name;

    // Find if file is existing
    const existingInput = $(`input[data-name="${file.name}"][data-existing="true"]`);
    if (existingInput.length) {
        const imageId = existingInput.val();
        const inputName = existingInput.attr('name').replace(/\[\]$/, ''); // remove [] if exists

        // Add remove marker
        const inputHtml = `<input type="hidden" name="remove_existing_image[]" value="${imageId}">`;
        $el.closest('form').append(inputHtml);

        // Remove the old hidden input (to avoid resending it)
        existingInput.remove();
    }

    // Temp image removed
    const $tempInput = $form.find(`input[data-dz-image-name="${fileName}"]`);
    if ($tempInput.length) {
        const imageId = $tempInput.val();
        $.ajax({
            url: tempImageDeleteUrl,
            type: 'DELETE',
            data: {
                image_id: imageId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                console.log('Temp image removed:', response);
            }
        });
        $tempInput.remove();
    }
}

function handleSuccess($el, response, maxFiles, inputName, file) {
    const uploadedImagesIds = response.images_ids || [];
    const $form = $el.closest('form');

    uploadedImagesIds.forEach(id => {
        const inputHtml = (maxFiles > 1)
            ? `<input type="hidden" name="${inputName}[]" value="${id}" data-dz-image-id="${id}" data-dz-image-name="${file.name}">`
            : `<input type="hidden" name="${inputName}" value="${id}" data-dz-image-id="${id}" data-dz-image-name="${file.name}">`;

        $form.append(inputHtml);
    });
}

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
