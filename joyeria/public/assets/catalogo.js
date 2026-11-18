document.querySelectorAll('[data-catalog-filter]').forEach((form) => {
    form.querySelectorAll('select, input[type="checkbox"]').forEach((control) => {
        control.addEventListener('change', () => form.requestSubmit());
    });
});
