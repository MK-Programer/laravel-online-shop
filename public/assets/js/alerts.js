function ensureAlertContainer() {
    if ($('#alert-container').length === 0) {
        $('<div id="alert-container"></div>').insertAfter('.container-fluid');
    }
}

function showSuccess(message) {
    ensureAlertContainer();
    const alert = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4><i class="icon fa fa-check"></i> Success!</h4>
            ${message}
        </div>`;
    $('#alert-container').html(alert);
}

function showError(message) {
    ensureAlertContainer();
    const alert = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4><i class="icon fa fa-ban"></i> Error!</h4>
            ${message}
        </div>`;
    $('#alert-container').html(alert);
}

function removeAlert() {
    $('#alert-container').html('');
}

document.addEventListener('click', function (e) {
    const closeBtn = e.target.closest('.close');
    if (closeBtn) {
        const alert = closeBtn.closest('.alert');
        if (alert) {
            alert.remove();
        }
    }
});
