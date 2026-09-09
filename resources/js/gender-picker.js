export function initGenderPicker() {
    const pickers = document.querySelectorAll('[data-gender-picker]');

    pickers.forEach((picker) => {
        const button = picker.querySelector('[data-gender-button]');
        const dropdown = picker.querySelector('[data-gender-dropdown]');
        const valueInput = picker.querySelector('[data-gender-value]');
        const label = picker.querySelector('[data-gender-label]');
        const arrow = picker.querySelector('[data-gender-arrow]');
        const options = picker.querySelectorAll('[data-gender-option]');

        if (!button || !dropdown || !valueInput || !label) {
            return;
        }

        const updateSelected = () => {
            const currentValue = valueInput.value;

            options.forEach((option) => {
                const isSelected = option.dataset.value === currentValue;
                const check = option.querySelector('[data-gender-check]');

                option.classList.toggle('bg-usc-50', isSelected);

                if (check) {
                    check.classList.toggle('hidden', !isSelected);
                }

                if (isSelected) {
                    label.textContent = option.dataset.label;
                    label.classList.remove('text-slate-400');
                    label.classList.add('text-[#02081C]');
                }
            });

            if (!currentValue) {
                label.textContent = 'Pilih jenis kelamin';
                label.classList.remove('text-[#02081C]');
                label.classList.add('text-slate-400');
            }
        };

        const openDropdown = () => {
            dropdown.classList.remove('hidden');
            button.setAttribute('aria-expanded', 'true');
            arrow?.classList.add('rotate-180');
        };

        const closeDropdown = () => {
            dropdown.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
            arrow?.classList.remove('rotate-180');
        };

        button.addEventListener('click', (event) => {
            event.stopPropagation();

            if (dropdown.classList.contains('hidden')) {
                openDropdown();
            } else {
                closeDropdown();
            }
        });

        options.forEach((option) => {
            option.addEventListener('click', () => {
                valueInput.value = option.dataset.value;
                updateSelected();
                closeDropdown();
            });
        });

        document.addEventListener('click', (event) => {
            if (!picker.contains(event.target)) {
                closeDropdown();
            }
        });

        updateSelected();
    });
}