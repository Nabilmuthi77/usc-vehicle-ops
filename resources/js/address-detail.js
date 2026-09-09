/**
 * USC Address Toggle
 *
 * Klik field alamat untuk menampilkan / menyembunyikan alamat lengkap.
 */
export function initAddressDetail() {
    const containers = document.querySelectorAll('[data-address-container]');

    containers.forEach((container) => {
        const preview = container.querySelector('[data-address-preview]');
        const full = container.querySelector('[data-address-full]');

        if (!preview || !full) {
            return;
        }

        preview.addEventListener('click', () => {
            preview.classList.add('hidden');
            full.classList.remove('hidden');
        });

        full.addEventListener('click', () => {
            full.classList.add('hidden');
            preview.classList.remove('hidden');
        });
    });
}