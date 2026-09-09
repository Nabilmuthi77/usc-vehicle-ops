/**
 * USC Role Picker
 * Reusable custom dropdown untuk pilihan role.
 */

export function initRolePicker() {
    const pickers = document.querySelectorAll('[data-role-picker]');

    pickers.forEach((picker) => {
        const select = picker.querySelector('select');
        const trigger = picker.querySelector('[data-role-trigger]');
        const menu = picker.querySelector('[data-role-menu]');
        const label = picker.querySelector('[data-role-label]');
        const arrow = picker.querySelector('[data-role-arrow]');
        const options = picker.querySelectorAll('[data-role-option]');

        if (!select || !trigger || !menu || !label) {
            return;
        }

        const closeMenu = () => {
            menu.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');

            if (arrow) {
                arrow.classList.remove('rotate-180');
            }
        };

        const openMenu = () => {
            menu.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');

            if (arrow) {
                arrow.classList.add('rotate-180');
            }
        };

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();

            if (menu.classList.contains('hidden')) {
                openMenu();
            } else {
                closeMenu();
            }
        });

        options.forEach((option) => {
            option.addEventListener('click', () => {
                const value = option.dataset.value;
                const text = option.dataset.label;

                select.value = value;
                label.textContent = text;

                options.forEach((item) => {
                    item.classList.remove(
                        'bg-usc-50',
                        'text-usc-700'
                    );

                    item.querySelector('[data-role-check]')
                        ?.classList.add('hidden');
                });

                option.classList.add(
                    'bg-usc-50',
                    'text-usc-700'
                );

                option.querySelector('[data-role-check]')
                    ?.classList.remove('hidden');

                closeMenu();
            });
        });

        document.addEventListener('click', (event) => {
            if (!picker.contains(event.target)) {
                closeMenu();
            }
        });
    });
}