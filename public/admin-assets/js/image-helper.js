// Inject CSS styling
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

Dropzone.autoDiscover = false;

// Helper: extract id from a typical server response
function extractIdFromResponse(response) {
    if (!response) return null;
    // Common keys your backend might use:
    if (response.id) return response.id;
    if (response.image_id) return response.image_id;
    if (response.imageId) return response.imageId;
    if (Array.isArray(response.images_id) && response.images_id.length === 1) return response.images_id[0];
    if (response.images_id && response.images_id.length > 0) return response.images_id[0];
    if (response.image_ids && response.image_ids.length === 1) return response.image_ids[0];
    // If server returns a map keyed by filename, try to find by filename keys:
    if (typeof response === 'object') {
        // find first numeric-ish value
        for (const k in response) {
            const v = response[k];
            if (typeof v === 'number' || (typeof v === 'string' && /^\d+$/.test(v))) {
                return v;
            }
            if (Array.isArray(v) && v.length === 1) return v[0];
        }
    }
    return null;
}

// Generates a unique upload UID per file
function makeUploadUid() {
    return `${Date.now()}_${Math.random().toString(36).substring(2, 9)}`;
}

// Initialize all Dropzone elements
$(function () {
    $('.dropzone').each(function (_, el) {
        initDropzone(el);
    });
});

/* -------------------------------------
    MAIN INITIALIZER
------------------------------------- */
function initDropzone(element) {
    const $el = $(element);
    const folder = $el.data('folder') || 'others';
    const maxFiles = parseInt($el.data('max-files') || defaultMaxFiles);
    const inputName = $el.data('name') || 'image_id';

    const dz = new Dropzone(element, {
        url: tempImageUploadUrl,
        paramName: 'images',
        uploadMultiple: false,            // <<< IMPORTANT: single-file per request
        parallelUploads: Math.max(1, Math.min(5, maxFiles)), // keep parallel but not multiple
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
   LOAD EXISTING IMAGES (Edit Form)
   - Ensure each mock file has uploadUid and the hidden input includes it
------------------------------------- */
function preloadExistingImages(dz, $el, inputName, maxFiles) {
    const existingImages = $el.data('existing-images');
    const existingNames = $el.data('existing-names');

    if (!existingImages) return;

    const images = Array.isArray(existingImages) ? existingImages : [existingImages];
    const names = Array.isArray(existingNames) ? existingNames : (existingNames ? [existingNames] : []);

    for (let i = 0; i < images.length; i++) {
        const imgUrl = images[i];
        const imgName = names[i] || `existing_${i}`;
        const uploadUid = makeUploadUid();

        // mock file with uploadUid so removal can match
        const mockFile = { name: imgName, size: 12345, accepted: true, uploadUid: uploadUid, existing: true };
        dz.emit("addedfile", mockFile);
        dz.emit("thumbnail", mockFile, imgUrl);
        dz.emit("complete", mockFile);
        dz.files.push(mockFile);

        // Append hidden input with existing flag and upload UID
        const isMultiple = maxFiles > 1;
        const inputHtml = isMultiple
            ? `<input type="hidden" name="${inputName}[]" value="${imgName}" data-existing="true" data-name="${imgName}" data-dz-upload-uid="${uploadUid}">`
            : `<input type="hidden" name="${inputName}" value="${imgName}" data-existing="true" data-name="${imgName}" data-dz-upload-uid="${uploadUid}">`;

        $el.closest('form').append(inputHtml);
    }
}

/* -------------------------------------
    SETUP EVENTS (with unique ID tracking)
------------------------------------- */
function setupDropzoneEvents(dz, $el, folder, maxFiles, inputName) {
    dz.on('addedfile', function (file) {
        // If this mock already has uploadUid (preloaded), keep it; otherwise create one
        if (!file.uploadUid) file.uploadUid = makeUploadUid();

        if (dz.files.length > maxFiles) {
            // remove the earliest non-existing file (Dropzone handles file objects)
            dz.removeFile(dz.files[0]);
        }

        $el.removeClass('border border-danger');
        $el.next('.dropzone-error').remove();

        // 🆕 Add order input below image preview if multiple images allowed
        if (maxFiles > 1) {
            const $previewEl = $(file.previewElement);
            const $orderInput = $(`
                <div class="text-center mt-1">
                    <label class="small d-block">Order</label>
                    <input type="number" 
                        class="form-control form-control-sm dz-order-input" 
                        name="images_order[${file.uploadUid}]" 
                        value="${dz.files.length}" 
                        min="1" 
                        style="width: 80px; margin: 0 auto;">
                </div>
            `);
            $previewEl.append($orderInput);
        }
    });

    dz.on('removedfile', function (file) {
        handleRemovedFile($el, file, inputName);
    });

    dz.on('sending', function (file, xhr, formData) {
        formData.append('folder', folder);
        // append the uid so backend could (optionally) return mapping by uid if needed
        formData.append('upload_uid', file.uploadUid);
    });

    dz.on('success', function (file, response) {
        handleSuccess($el, response, maxFiles, inputName, file);
    });

    dz.on('error', function (file, errorMessage) {
        handleError($el, errorMessage, inputName);
    });
}

/* -------------------------------------
   SUCCESS HANDLER
   - Stores the returned DB id in a hidden input tied to uploadUid
------------------------------------- */
function handleSuccess($el, response, maxFiles, inputName, file) {
    const $form = $el.closest('form');
    const id = extractIdFromResponse(response);

    // If we couldn't find an ID, show a console warning and try to continue gracefully
    if (!id) {
        console.warn('Dropzone: could not extract uploaded image ID from response', response);
        return;
    }

    // Create hidden input mapped to this file's uploadUid
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

/* -------------------------------------
   HANDLE REMOVED FILE
   - Prefers to remove by data-dz-upload-uid (reliable)
   - Falls back to existing/file-name detection as a backup
------------------------------------- */
function handleRemovedFile($el, file, inputName) {
    const $form = $el.closest('form');
    const uploadUid = file.uploadUid;
    const fileName = file.name;

    // 1) If this was a preloaded existing file (we added data-existing=true), find it by upload UID first
    if (uploadUid) {
        const $byUid = $form.find(`input[data-dz-upload-uid="${uploadUid}"]`);
        if ($byUid.length) {
            // if this input is flagged as existing, mark for deletion in DB
            if ($byUid.is('[data-existing="true"]')) {
                const existingVal = $byUid.val();
                // add remove marker (backend should handle this)
                $form.append(`<input type="hidden" name="remove_existing_image[]" value="${existingVal}">`);
            } else {
                // temporary uploaded file -> delete temp record via AJAX
                const imageId = $byUid.val();
                if (imageId) {
                    $.ajax({
                        url: tempImageDeleteUrl,
                        type: 'DELETE',
                        data: {
                            images_id: [imageId],
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function () {
                            console.log('Temp image removed:', imageId);
                        },
                        error: function () {
                            console.error('Failed to remove temp image:', imageId);
                        }
                    });
                }
            }
            // Remove the hidden input(s) matched by UID
            $byUid.remove();
            reorderImageOrders($form);
            return;
        }
    }

    // 2) Fallback: try to find by existing flag + data-name (older preloaded method)
    const existingInput = $form.find(`input[data-name="${fileName}"][data-existing="true"]`);
    if (existingInput.length) {
        const imageId = existingInput.val();
        existingInput.remove();
        $form.append(`<input type="hidden" name="remove_existing_image[]" value="${imageId}">`);
        console.log('Marked existing image for removal (fallback):', imageId);
        reorderImageOrders($form);
        return;
    }

    // 3) Final fallback: try to find any temp input matching the file name
    // (this is less reliable but keeps backwards compatibility)
    const tempByName = $form.find(`input[data-dz-image-name="${fileName}"]`);
    if (tempByName.length) {
        const imageId = tempByName.val();
        // attempt deletion
        if (imageId) {
            $.ajax({
                url: tempImageDeleteUrl,
                type: 'DELETE',
                data: {
                    images_id: [imageId],
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function () {
                    console.log('Temp image removed (fallback by name):', imageId);
                },
                error: function () {
                    console.error('Failed to remove temp image (fallback by name):', imageId);
                }
            });
        }
        tempByName.remove();
    }

    // Remove associated order input
    $(`input[name="images_order[${file.uploadUid}]"]`).closest('.text-center').remove();
    reorderImageOrders($form);
}

function reorderImageOrders($form) {
    const $orderInputs = $form.find('input[name^="images_order["]');
    let order = 1;

    $orderInputs.each(function () {
        $(this).val(order++);
    });
}

/* -------------------------------------
   ERROR HANDLER
------------------------------------- */
function handleError($el, errorMessage, inputName) {
    $el.next('.dropzone-error').remove();

    const message = typeof errorMessage === 'string'
        ? errorMessage
        : (errorMessage?.message || 'An error occurred while uploading the image.');

    const $errorEl = $('<p class="dropzone-error text-danger mt-1"></p>').text(message);

    $el.addClass('border border-danger').after($errorEl);
    console.error(`Dropzone error [${inputName}]:`, message);
}
