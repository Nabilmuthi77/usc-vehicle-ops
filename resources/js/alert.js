export function initAlert() {

    const alerts = document.querySelectorAll('[data-alert]');

    alerts.forEach(function (alert) {

        const closeAlert = function () {

            alert.style.transition =
                'opacity 0.3s ease, transform 0.3s ease';

            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-5px)';

            setTimeout(function () {
                alert.remove();
            }, 300);

        };


        // Auto close setelah 10 detik
        setTimeout(closeAlert, 10000);


        // Manual close
        const closeButton = alert.querySelector('[data-alert-close]');

        if (closeButton) {
            closeButton.addEventListener('click', closeAlert);
        }

    });

}