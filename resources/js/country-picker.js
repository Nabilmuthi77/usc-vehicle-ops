export function initCountryPicker() {

    const countryPickers = document.querySelectorAll('[data-country-picker]');

    countryPickers.forEach(function (picker) {

        const button = picker.querySelector('[data-country-button]');
        const dropdown = picker.querySelector('[data-country-dropdown]');
        const codeInput = picker.querySelector('[data-country-code]');
        const selectedCode = picker.querySelector('[data-selected-code]');
        const options = picker.querySelectorAll('[data-country-option]');

        if (!button || !dropdown || !codeInput || !selectedCode) {
            return;
        }


        /*
         * Buka / Tutup Dropdown
         */
        button.addEventListener('click', function (event) {

            event.stopPropagation();

            const isOpen = !dropdown.classList.contains('hidden');

            dropdown.classList.toggle('hidden');

            button.setAttribute(
                'aria-expanded',
                String(!isOpen)
            );

        });


        /*
         * Pilih Negara
         */
        options.forEach(function (option) {

            option.addEventListener('click', function () {

                const code = this.dataset.code;

                codeInput.value = code;
                selectedCode.textContent = code;

                dropdown.classList.add('hidden');

                button.setAttribute(
                    'aria-expanded',
                    'false'
                );

            });

        });


        /*
         * Tutup Saat Klik di Luar
         */
        document.addEventListener('click', function (event) {

            if (!picker.contains(event.target)) {

                dropdown.classList.add('hidden');

                button.setAttribute(
                    'aria-expanded',
                    'false'
                );

            }

        });


        /*
         * Restore Value Setelah Validation Error
         */
        const currentCode = codeInput.value;

        const selectedOption = picker.querySelector(
            `[data-country-option][data-code="${currentCode}"]`
        );

        if (selectedOption) {

            selectedCode.textContent =
                selectedOption.dataset.code;

        }

    });

}