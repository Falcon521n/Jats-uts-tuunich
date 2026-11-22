document.querySelectorAll('[data-show-password]').forEach((toggle) => {
    toggle.addEventListener('change', () => {
        const password = toggle.closest('.password-control')?.querySelector('input[type="password"], input[type="text"]');
        if (password) {
            password.type = toggle.checked ? 'text' : 'password';
        }
    });
});

document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!form.reportValidity()) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('input[type="file"][accept*="image"]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (file && file.size > 5 * 1024 * 1024) {
            input.setCustomValidity('La imagen debe pesar 5 MB o menos.');
        } else {
            input.setCustomValidity('');
        }
    });
});
