document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[type="tel"]').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^0-9+ .-]/g, '');
        });
    });

    document.querySelectorAll('[type="number"]').forEach((input) => {
        input.addEventListener('input', () => {
            if (input.value && Number(input.value) < 0) {
                input.value = '';
            }
        });
    });

    document.querySelectorAll('.form-grid input, .form-grid select, .form-grid textarea').forEach((field) => {
        field.addEventListener('invalid', () => {
            field.setAttribute('aria-invalid', 'true');
        });
        field.addEventListener('input', () => {
            field.removeAttribute('aria-invalid');
        });
        field.addEventListener('change', () => {
            field.removeAttribute('aria-invalid');
        });
    });
});
