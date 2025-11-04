/* -------------------------------------
   Dropzone Image Upload Handler
   Refactored (same logic, cleaner structure)
------------------------------------- */

// ===============================
// Global Setup & Styles
// ===============================
Dropzone.autoDiscover = false;

$('<style>')
    .prop('type', 'text/css')
    .html(`
      .dz-preview {
          display: inline-block;
          margin: 10px;
      }
      .dz-preview .dz-image {
          width: 200px;
          height: 200px;
          max-width: 200px;
          max-height: 200px;
          overflow: hidden;
          border-radius: 8px;
      }
      .dz-image img {
          width: 100%;
          height: 100%;
          object-fit: cover;
          border-radius: 8px;
      }
    `)
    .appendTo('head');

// ===============================
// Utility Helpers
// ===============================

function makeUploadUid() {
    return `${Date.now()}_${Math.random().toString(36).substring(2, 9)}`;
}

function extractIdFromResponse(response) {
    if (!response) return null;

    if (response.id) return response.id;
    if (response.image_id) return response.image_id;
    if (response.imageId) return response.imageId;

    if (Array.isArray(response.images_id) && response.images_id.length === 1)
        return response.images_id[0];

    if (response.images_id && response.images_id.length > 0)
        return response.images_id[0];

    if (response.image_ids && response.image_ids.length === 1)
        return response.image_ids[0];

    if (typeof response === 'object') {
        for (const k in response) {
            const v = response[k];
            if (typeof v === 'number' || (typeof v === 'string' && /^\d+$/.test(v))) return v;
            if (Array.isArray(v) && v.length === 1) return v[0];
        }
    }
    return null;
}

function reorderImageOrders($form) {
    const $orderInputs = $form.find('input[name^="images_order["]');
    let order = 1;
    $orderInputs.each(function () {
        $(this).val(order++);
    });
}

function getNextAvailableOrder($form) {
    const usedOrders = $form
        .find('input[name^="images_order["]')
        .map(function () {
            return parseInt($(this).val());
        })
        .get()
        .filter(v => !isNaN(v));

    if (usedOrders.length === 0) return 1;

    let order = 1;
    while (usedOrders.includes(order)) order++;
    return order;
}

function sortDropzonePreviews(dz) {
    const $dropzone = $(dz.element);
    const $previews = $dropzone.find('.dz-preview');

    $previews.sort(function (a, b) {
        const orderA = parseInt($(a).find('.dz-order-input').val()) || 9999;
        const orderB = parseInt($(b).find('.dz-order-input').val()) || 9999;
        return orderA - orderB;
    });

    $dropzone.append($previews);
}

function attachOrderChangeListener(dz) {
    const $dropzone = $(dz.element);

    // Listen for any change in order inputs inside this Dropzone
    $dropzone.on('change', '.dz-order-input', function () {
        // Re-sort previews when any order value changes
        sortDropzonePreviews(dz);
    });
}

// ===============================
// Main Initialization
// ===============================
$(function () {
    $('.dropzone').each(function (_, el) {
        initDropzone(el);
    });
});

function initDropzone(element) {
    const $el = $(element);
    const folder = $el.data('folder') || 'others';
    const maxFiles = parseInt($el.data('max-files') || defaultMaxFiles);
    const inputName = $el.data('name') || 'images_id';

    const dz = new Dropzone(element, {
        url: tempImageUploadUrl,
        paramName: 'images',
        uploadMultiple: false,
        parallelUploads: Math.max(1, Math.min(5, maxFiles)),
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

// ===============================
// Preload Existing Images
// ===============================
function preloadExistingImages(dz, $el, inputName, maxFiles) {
    const existingImages = $el.data('existing-images');
    const existingNames = $el.data('existing-names');
    const existingSorts = $el.data('existing-sorts');

    if (!existingImages) return;

    let images = Array.isArray(existingImages) ? existingImages : [existingImages];
    let names = Array.isArray(existingNames) ? existingNames : (existingNames ? [existingNames] : []);
    let sorts = Array.isArray(existingSorts) ? existingSorts : (existingSorts ? [existingSorts] : []);

    let combined = images.map((img, i) => ({
        img: img,
        name: names[i] || `existing_${i}`,
        sort: maxFiles > 1 ? (sorts[i] || i + 1) : null
    }));

    if (maxFiles > 1) combined.sort((a, b) => a.sort - b.sort);

    combined.forEach((item, i) => {
        const uploadUid = makeUploadUid();
        const mockFile = {
            name: item.name,
            size: 12345,
            accepted: true,
            uploadUid: uploadUid,
            existing: true
        };

        dz.emit("addedfile", mockFile);
        dz.emit("thumbnail", mockFile, item.img);
        dz.emit("complete", mockFile);
        dz.files.push(mockFile);

        const inputHtml = maxFiles > 1
            ? `<input type="hidden" name="${inputName}[]" value="${item.name}"
                   data-existing="true" data-name="${item.name}"
                   data-dz-upload-uid="${uploadUid}"
                   data-sort="${item.sort}">`
            : `<input type="hidden" name="${inputName}" value="${item.name}"
                   data-existing="true" data-name="${item.name}"
                   data-dz-upload-uid="${uploadUid}">`;

        $el.closest('form').append(inputHtml);

        if (maxFiles > 1) {
            const $previewEl = $(mockFile.previewElement);
            const $orderInput = $(`
                <div class="text-center mt-1">
                    <label class="small d-block">Order</label>
                    <input type="number"
                        class="form-control form-control-sm dz-order-input"
                        name="images_order[${uploadUid}]"
                        value="${item.sort}"
                        min="1"
                        style="width: 80px; margin: 0 auto;">
                </div>
            `);
            $previewEl.append($orderInput);
        }
    });
}

// ===============================
// 🔹 Event Binding
// ===============================
function setupDropzoneEvents(dz, $el, folder, maxFiles, inputName) {
    dz.on('addedfile', function (file) {
        if (!file.uploadUid) file.uploadUid = makeUploadUid();

        if (dz.files.length > maxFiles) dz.removeFile(dz.files[0]);

        $el.removeClass('border border-danger');
        $el.next('.dropzone-error').remove();

        if (maxFiles > 1 && !file.existing) {
            const $form = $el.closest('form');
            const nextOrder = getNextAvailableOrder($form);
            const $previewEl = $(file.previewElement);
            const $orderInput = $(`
                <div class="text-center mt-1">
                    <label class="small d-block">Order</label>
                    <input type="number"
                        class="form-control form-control-sm dz-order-input"
                        name="images_order[${file.uploadUid}]"
                        value="${nextOrder}"
                        min="1"
                        style="width: 80px; margin: 0 auto;">
                </div>
            `);
            $previewEl.append($orderInput);
        }

        sortDropzonePreviews(dz);
        attachOrderChangeListener(dz);
    });

    dz.on('removedfile', function (file) {
        handleRemovedFile($el, file, inputName);
        sortDropzonePreviews(dz);
        attachOrderChangeListener(dz);
    });

    dz.on('sending', function (file, xhr, formData) {
        formData.append('folder', folder);
        formData.append('upload_uid', file.uploadUid);
    });

    dz.on('success', function (file, response) {
        handleSuccess($el, response, maxFiles, inputName, file);
    });

    dz.on('error', function (file, errorMessage) {
        handleError($el, errorMessage, inputName);
    });
}

// ===============================
// File Removal
// ===============================
function handleRemovedFile($el, file, inputName) {
    const $form = $el.closest('form');
    const uploadUid = file.uploadUid;
    const fileName = file.name;

    if (uploadUid) {
        const $byUid = $form.find(`input[data-dz-upload-uid="${uploadUid}"]`);
        if ($byUid.length) {
            if ($byUid.is('[data-existing="true"]')) {
                const existingVal = $byUid.val();
                $form.append(`<input type="hidden" name="remove_existing_image[]" value="${existingVal}">`);
            } else {
                const imageId = $byUid.val();
                if (imageId) {
                    $.ajax({
                        url: tempImageDeleteUrl,
                        type: 'DELETE',
                        data: {
                            images_id: [imageId],
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: () => console.log('Temp image removed:', imageId),
                        error: () => console.error('Failed to remove temp image:', imageId)
                    });
                }
            }
            $byUid.remove();
            reorderImageOrders($form);
            return;
        }
    }

    const existingInput = $form.find(`input[data-name="${fileName}"][data-existing="true"]`);
    if (existingInput.length) {
        const imageId = existingInput.val();
        existingInput.remove();
        $form.append(`<input type="hidden" name="remove_existing_image[]" value="${imageId}">`);
        reorderImageOrders($form);
        return;
    }

    const tempByName = $form.find(`input[data-dz-image-name="${fileName}"]`);
    if (tempByName.length) {
        const imageId = tempByName.val();
        if (imageId) {
            $.ajax({
                url: tempImageDeleteUrl,
                type: 'DELETE',
                data: {
                    images_id: [imageId],
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: () => console.log('Temp image removed (fallback):', imageId),
                error: () => console.error('Failed to remove temp image (fallback):', imageId)
            });
        }
        tempByName.remove();
    }

    $(`input[name="images_order[${file.uploadUid}]"]`).closest('.text-center').remove();
    reorderImageOrders($form);
}

// ===============================
// Upload Handlers
// ===============================
function handleSuccess($el, response, maxFiles, inputName, file) {
    const $form = $el.closest('form');
    const id = extractIdFromResponse(response);

    if (!id) {
        console.warn('Dropzone: could not extract uploaded image ID', response);
        return;
    }

    const isMultiple = maxFiles > 1;
    const inputHtml = isMultiple
        ? `<input type="hidden" name="${inputName}[]" value="${id}"
              data-dz-image-id="${id}"
              data-dz-upload-uid="${file.uploadUid}"
              data-dz-image-name="${file.name}">`
        : `<input type="hidden" name="${inputName}" value="${id}"
              data-dz-image-id="${id}"
              data-dz-upload-uid="${file.uploadUid}"
              data-dz-image-name="${file.name}">`;

    $form.append(inputHtml);
}

function handleError($el, errorMessage, inputName) {
    $el.next('.dropzone-error').remove();

    const message = typeof errorMessage === 'string'
        ? errorMessage
        : (errorMessage?.message || 'An error occurred while uploading the image.');

    const $errorEl = $('<p class="dropzone-error text-danger mt-1"></p>').text(message);

    $el.addClass('border border-danger').after($errorEl);
    console.error(`Dropzone error [${inputName}]:`, message);
}
