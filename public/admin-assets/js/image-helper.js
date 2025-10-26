Dropzone.autoDiscover = false;

$('.dropzone').each(function(index, element) {
    const folder = $(element).data('folder') || 'others';
    const maxFiles = $(element).data('max-files') || 1;
    const inputName = $(element).data('name') || 'image_id';

    const myDropzone = new Dropzone(element, {
        url: tempImageUploadUrl,
        paramName: 'images',
        uploadMultiple: true, // ✅ allows multiple uploads
        parallelUploads: maxFiles,
        maxFiles: maxFiles,
        addRemoveLinks: true,
        acceptedFiles: "image/gif",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },

        init: function() {
            this.on('addedfile', function(file) {
                if (this.files.length > maxFiles) {
                    this.removeFile(this.files[maxFiles - 1]);
                }

                // Clear previous error styles if new file added
                const dropzoneEl = $(element);
                dropzoneEl.removeClass('border border-danger');
                dropzoneEl.next('.dropzone-error').remove();
            });

            this.on('sending', function(file, xhr, formData) {
                formData.append('folder', folder);
            });

            // ✅ Handle successful uploads
            this.on('success', function(file, response) {
                const uploadedImagesIds = response.images_ids || [];

                const $form = $(element).closest('form');

                // Remove old hidden inputs if single-file Dropzone
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
            });

            // ✅ Handle errors
            this.on('error', function(file, errorMessage, xhr) {
                const dropzoneEl = $(element);

                // Remove any existing error messages
                dropzoneEl.next('.dropzone-error').remove();

                // Determine error message
                const message = typeof errorMessage === 'string'
                    ? errorMessage
                    : (errorMessage?.message || 'حدث خطأ أثناء رفع الصورة.');

                // Create error message element
                const $errorEl = $('<p class="dropzone-error text-danger mt-1"></p>');
                $errorEl.text(message);

                // Add red border and show message
                dropzoneEl.addClass('border border-danger');
                dropzoneEl.after($errorEl);

                console.error(`Dropzone error [${inputName}]:`, message);
            });
        }
    });
});
