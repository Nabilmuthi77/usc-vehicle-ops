export function initPhoneFormat() {

    const phoneInputs = document.querySelectorAll('[data-phone-format]');

    phoneInputs.forEach(function (input) {

        input.addEventListener('input', function () {

            let value = input.value.replace(/\D/g, '');

            // Hilangkan angka 0 di awal
            if (value.startsWith('0')) {
                value = value.substring(1);
            }

            // Maksimal 11 digit
            value = value.substring(0, 11);

            // Format 812-3456-7890
            if (value.length > 7) {
                value =
                    value.substring(0, 3) +
                    '-' +
                    value.substring(3, 7) +
                    '-' +
                    value.substring(7);
            } else if (value.length > 3) {
                value =
                    value.substring(0, 3) +
                    '-' +
                    value.substring(3);
            }

            input.value = value;

        });

    });

}