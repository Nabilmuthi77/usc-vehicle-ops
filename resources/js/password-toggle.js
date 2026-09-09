export function initPasswordToggle() {

    const buttons = document.querySelectorAll('[data-password-toggle]');

    buttons.forEach(function (button) {

        const inputId = button.dataset.passwordToggle;
        const input = document.getElementById(inputId);

        if (!input) {
            return;
        }

        button.addEventListener('click', function () {

            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';

            button.textContent = isPassword
                ? 'Sembunyikan'
                : 'Lihat';

        });

    });

}