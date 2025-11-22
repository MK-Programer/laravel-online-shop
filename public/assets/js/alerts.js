function ensureAlertContainer() {
    if ($('#alert-container').length === 0) {

        // Map of targets with selector + action
        const targets = [
            { selector: '.container-fluid', action: 'after' },       // insert after this if exists
            { selector: '.section-9.pt-4 .container', action: 'inside' }, // insert inside container
            { selector: '.section-10 .container .login-form', action: 'inside' }
        ];

        for (const target of targets) {
            const $el = $(target.selector);
            if ($el.length > 0) {

                const $alert = $('<div id="alert-container">This is an alert!</div>');

                if (target.action === 'after') {
                    $alert.insertAfter($el.first());
                } else if (target.action === 'inside') {
                    $alert.prependTo($el.first()); // inserts **inside the container**
                }

                return; // stop after inserting once
            }
        }
    }
}

function showSuccess(message) {
    ensureAlertContainer();
    const alert = `
        <div class="alert alert-success alert-dismissible fade show text-start" role="alert">
           <a href="javascript:void(0);" class="close text-decoration-none" style="float:right;" aria-hidden="true">×</a>
            <h4><i class="icon fa fa-check"></i> Success!</h4>
            ${message}
        </div>`;
    $('#alert-container').html(alert);
}

function showError(message) {
    ensureAlertContainer();
    const alert = `
        <div class="alert alert-danger alert-dismissible fade show text-start" role="alert">
            <a href="javascript:void(0);" class="close text-decoration-none" style="float:right;" aria-hidden="true">×</a>
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
