export function initMobileSidebar() {
    const container = document.getElementById('mobile-sidebar');
    const button = document.getElementById('mobile-sidebar-button');
    const menu = document.getElementById('mobile-sidebar-menu');
    const icon = document.getElementById('mobile-sidebar-icon');
    const loader = document.getElementById('mobile-sidebar-loader');

    if (!container || !button || !menu || !icon || !loader) {
        return;
    }

    const STORAGE_KEY = 'usc-mobile-sidebar-position';
    const BUTTON_SIZE = 56;
    const VIEWPORT_MARGIN = 16;
    const MENU_GAP = 12;

    let isOpen = false;
    let isDragging = false;
    let hasMoved = false;

    let startX = 0;
    let startY = 0;
    let startLeft = 0;
    let startTop = 0;

    // ==========================================
    // SPINNER
    // ==========================================

    const spinnerOne = loader.innerHTML;

    const spinnerTwo = `
        <svg
            version="1.1"
            id="cog9_1_"
            xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink"
            viewBox="-13 -13 45 45"
            class="h-7 w-7"
            aria-hidden="true"
        >
            <style>
                .box5631 {
                    transform-origin: 50% 50%;
                    fill: currentColor;
                }

                @keyframes moveBox5631-1 {
                    9.0909090909% {
                        transform: translate(-12px, 0);
                    }

                    18.1818181818% {
                        transform: translate(0px, 0);
                    }

                    27.2727272727% {
                        transform: translate(0px, 0);
                    }

                    36.3636363636% {
                        transform: translate(12px, 0);
                    }

                    45.4545454545% {
                        transform: translate(12px, 12px);
                    }

                    54.5454545455% {
                        transform: translate(12px, 12px);
                    }

                    63.6363636364% {
                        transform: translate(12px, 12px);
                    }

                    72.7272727273% {
                        transform: translate(12px, 0px);
                    }

                    81.8181818182% {
                        transform: translate(0px, 0px);
                    }

                    90.9090909091% {
                        transform: translate(-12px, 0px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .box5631:nth-child(1) {
                    animation: moveBox5631-1 4s infinite;
                }

                @keyframes moveBox5631-2 {
                    9.0909090909% {
                        transform: translate(0, 0);
                    }

                    18.1818181818% {
                        transform: translate(12px, 0);
                    }

                    27.2727272727% {
                        transform: translate(0px, 0);
                    }

                    36.3636363636% {
                        transform: translate(12px, 0);
                    }

                    45.4545454545% {
                        transform: translate(12px, 12px);
                    }

                    54.5454545455% {
                        transform: translate(12px, 12px);
                    }

                    63.6363636364% {
                        transform: translate(12px, 12px);
                    }

                    72.7272727273% {
                        transform: translate(12px, 12px);
                    }

                    81.8181818182% {
                        transform: translate(0px, 12px);
                    }

                    90.9090909091% {
                        transform: translate(0px, 12px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .box5631:nth-child(2) {
                    animation: moveBox5631-2 4s infinite;
                }

                @keyframes moveBox5631-3 {
                    9.0909090909% {
                        transform: translate(-12px, 0);
                    }

                    18.1818181818% {
                        transform: translate(-12px, 0);
                    }

                    27.2727272727% {
                        transform: translate(0px, 0);
                    }

                    36.3636363636% {
                        transform: translate(-12px, 0);
                    }

                    45.4545454545% {
                        transform: translate(-12px, 0);
                    }

                    54.5454545455% {
                        transform: translate(-12px, 0);
                    }

                    63.6363636364% {
                        transform: translate(-12px, 0);
                    }

                    72.7272727273% {
                        transform: translate(-12px, 0);
                    }

                    81.8181818182% {
                        transform: translate(-12px, -12px);
                    }

                    90.9090909091% {
                        transform: translate(0px, -12px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .box5631:nth-child(3) {
                    animation: moveBox5631-3 4s infinite;
                }

                @keyframes moveBox5631-4 {
                    9.0909090909% {
                        transform: translate(-12px, 0);
                    }

                    18.1818181818% {
                        transform: translate(-12px, 0);
                    }

                    27.2727272727% {
                        transform: translate(-12px, -12px);
                    }

                    36.3636363636% {
                        transform: translate(0px, -12px);
                    }

                    45.4545454545% {
                        transform: translate(0px, 0px);
                    }

                    54.5454545455% {
                        transform: translate(0px, -12px);
                    }

                    63.6363636364% {
                        transform: translate(0px, -12px);
                    }

                    72.7272727273% {
                        transform: translate(0px, -12px);
                    }

                    81.8181818182% {
                        transform: translate(-12px, -12px);
                    }

                    90.9090909091% {
                        transform: translate(-12px, 0px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .box5631:nth-child(4) {
                    animation: moveBox5631-4 4s infinite;
                }

                @keyframes moveBox5631-5 {
                    9.0909090909% {
                        transform: translate(0, 0);
                    }

                    18.1818181818% {
                        transform: translate(0, 0);
                    }

                    27.2727272727% {
                        transform: translate(0, 0);
                    }

                    36.3636363636% {
                        transform: translate(12px, 0);
                    }

                    45.4545454545% {
                        transform: translate(12px, 0);
                    }

                    54.5454545455% {
                        transform: translate(12px, 0);
                    }

                    63.6363636364% {
                        transform: translate(12px, 0);
                    }

                    72.7272727273% {
                        transform: translate(12px, 0);
                    }

                    81.8181818182% {
                        transform: translate(12px, -12px);
                    }

                    90.9090909091% {
                        transform: translate(0px, -12px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .box5631:nth-child(5) {
                    animation: moveBox5631-5 4s infinite;
                }

                @keyframes moveBox5631-6 {
                    9.0909090909% {
                        transform: translate(0, 0);
                    }

                    18.1818181818% {
                        transform: translate(-12px, 0);
                    }

                    27.2727272727% {
                        transform: translate(-12px, 0);
                    }

                    36.3636363636% {
                        transform: translate(0px, 0);
                    }

                    45.4545454545% {
                        transform: translate(0px, 0);
                    }

                    54.5454545455% {
                        transform: translate(0px, 0);
                    }

                    63.6363636364% {
                        transform: translate(0px, 0);
                    }

                    72.7272727273% {
                        transform: translate(0px, 12px);
                    }

                    81.8181818182% {
                        transform: translate(-12px, 12px);
                    }

                    90.9090909091% {
                        transform: translate(-12px, 0px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .box5631:nth-child(6) {
                    animation: moveBox5631-6 4s infinite;
                }

                @keyframes moveBox5631-7 {
                    9.0909090909% {
                        transform: translate(12px, 0);
                    }

                    18.1818181818% {
                        transform: translate(12px, 0);
                    }

                    27.2727272727% {
                        transform: translate(12px, 0);
                    }

                    36.3636363636% {
                        transform: translate(0px, 0);
                    }

                    45.4545454545% {
                        transform: translate(0px, -12px);
                    }

                    54.5454545455% {
                        transform: translate(12px, -12px);
                    }

                    63.6363636364% {
                        transform: translate(0px, -12px);
                    }

                    72.7272727273% {
                        transform: translate(0px, -12px);
                    }

                    81.8181818182% {
                        transform: translate(0px, 0px);
                    }

                    90.9090909091% {
                        transform: translate(12px, 0px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .box5631:nth-child(7) {
                    animation: moveBox5631-7 4s infinite;
                }

                @keyframes moveBox5631-8 {
                    9.0909090909% {
                        transform: translate(0, 0);
                    }

                    18.1818181818% {
                        transform: translate(-12px, 0);
                    }

                    27.2727272727% {
                        transform: translate(-12px, -12px);
                    }

                    36.3636363636% {
                        transform: translate(0px, -12px);
                    }

                    45.4545454545% {
                        transform: translate(0px, -12px);
                    }

                    54.5454545455% {
                        transform: translate(0px, -12px);
                    }

                    63.6363636364% {
                        transform: translate(0px, -12px);
                    }

                    72.7272727273% {
                        transform: translate(0px, -12px);
                    }

                    81.8181818182% {
                        transform: translate(12px, -12px);
                    }

                    90.9090909091% {
                        transform: translate(12px, 0px);
                    }

                    100% {
                        transform: translate(0px, 0px);
                    }
                }

                .box5631:nth-child(8) {
                    animation: moveBox5631-8 4s infinite;
                }

                @keyframes moveBox5631-9 {
                    9.0909090909% {
                        transform: translate(-12px, 0);
                    }

                    18.1818181818% {
                        transform: translate(-12px, 0);
                    }

                    27.2727272727% {
                        transform: translate(0px, 0);
                    }

                    36.3636363636% {
                        transform: translate(-12px, 0);
                    }

                    45.4545454545% {
                        transform: translate(0px, 0);
                    }

                    54.5454545455% {
                        transform: translate(0px, 0);
                    }

                    63.6363636364% {
                        transform: translate(-12px, 0);
                    }

                    72.7272727273% {
                        transform: translate(-12px, 0);
                    }

                    81.8181818182% {
                        transform: translate(-24px, 0);
                    }

                    90.9090909091% {
                        transform: translate(-12px, 0);
                    }

                    100% {
                        transform: translate(0, 0);
                    }
                }

                .box5631:nth-child(9) {
                    animation: moveBox5631-9 4s infinite;
                }
            </style>

            <g>
                <circle class="box5631" cx="13" cy="1" r="5"/>
                <circle class="box5631" cx="13" cy="1" r="5"/>
                <circle class="box5631" cx="25" cy="25" r="5"/>
                <circle class="box5631" cx="13" cy="13" r="5"/>
                <circle class="box5631" cx="13" cy="13" r="5"/>
                <circle class="box5631" cx="25" cy="13" r="5"/>
                <circle class="box5631" cx="1" cy="25" r="5"/>
                <circle class="box5631" cx="13" cy="25" r="5"/>
                <circle class="box5631" cx="25" cy="25" r="5"/>
            </g>
        </svg>
    `;

    const showSpinnerOne = () => {
        loader.innerHTML = spinnerOne;
        loader.classList.remove('hidden');
        loader.classList.add('flex');
    };

    const showSpinnerTwo = () => {
        loader.innerHTML = spinnerTwo;
        loader.classList.remove('hidden');
        loader.classList.add('flex');

        const svg = loader.querySelector('svg');

        if (svg) {
            svg.classList.add('-translate-x-0.5', '-translate-y-0.5');
        }
    };

    // ==========================================
    // BUTTON VISUAL STATE
    // ==========================================

    const setNormalState = () => {
        button.classList.remove(
            'from-usc-500/40',
            'to-emerald-500/40'
        );

        button.classList.add(
            'from-usc-500/25',
            'to-emerald-500/25'
        );

        showSpinnerOne();
    };

    const setActiveState = () => {
        button.classList.remove(
            'from-usc-500/25',
            'to-emerald-500/25'
        );

        button.classList.add(
            'from-usc-500/40',
            'to-emerald-500/40'
        );
    };

    // ==========================================
    // POSITION
    // ==========================================

    const clampPosition = (left, top) => {
        const maxLeft =
            window.innerWidth - BUTTON_SIZE - VIEWPORT_MARGIN;

        const maxTop =
            window.innerHeight - BUTTON_SIZE - VIEWPORT_MARGIN;

        return {
            left: Math.max(
                VIEWPORT_MARGIN,
                Math.min(left, maxLeft)
            ),

            top: Math.max(
                VIEWPORT_MARGIN,
                Math.min(top, maxTop)
            ),
        };
    };

    const setButtonPosition = (
        left,
        top,
        save = true
    ) => {
        const position = clampPosition(left, top);

        button.style.left = `${position.left}px`;
        button.style.top = `${position.top}px`;

        button.style.right = 'auto';
        button.style.bottom = 'auto';

        if (save) {
            localStorage.setItem(
                STORAGE_KEY,
                JSON.stringify(position)
            );
        }
    };

    // ==========================================
    // LOAD POSITION
    // ==========================================

    const loadPosition = () => {
        try {
            const saved =
                localStorage.getItem(STORAGE_KEY);

            if (saved) {
                const position = JSON.parse(saved);

                if (
                    typeof position.left === 'number' &&
                    typeof position.top === 'number'
                ) {
                    setButtonPosition(
                        position.left,
                        position.top,
                        false
                    );

                    return;
                }
            }
        } catch (error) {
            console.warn(
                'Failed to load sidebar position:',
                error
            );
        }

        setButtonPosition(
            window.innerWidth -
            BUTTON_SIZE -
            VIEWPORT_MARGIN,

            window.innerHeight -
            BUTTON_SIZE -
            24,

            false
        );
    };

    // ==========================================
    // OPEN
    // ==========================================

    const openMenu = () => {
        isOpen = true;

        container.classList.remove(
            'pointer-events-none'
        );

        container.classList.add(
            'pointer-events-auto'
        );

        menu.classList.remove('hidden');

        button.setAttribute(
            'aria-label',
            'Close menu'
        );

        // Tetap terang ketika popup terbuka
        setActiveState();

        // Spinner berubah ke spinner 2
        showSpinnerTwo();

        positionMenu();
    };

    // ==========================================
    // CLOSE
    // ==========================================

    const closeMenu = () => {
        isOpen = false;

        menu.classList.add('hidden');

        container.classList.remove(
            'pointer-events-auto'
        );

        container.classList.add(
            'pointer-events-none'
        );

        button.setAttribute(
            'aria-label',
            'Open menu'
        );

        // Kembali ke kondisi normal
        setNormalState();
    };

    // ==========================================
    // POPUP POSITION
    // ==========================================

    const positionMenu = () => {
        if (!isOpen) {
            return;
        }

        const buttonRect =
            button.getBoundingClientRect();

        const menuWidth =
            menu.offsetWidth;

        const menuHeight =
            menu.offsetHeight;

        const viewportWidth =
            window.innerWidth;

        const viewportHeight =
            window.innerHeight;

        const candidates = [
            {
                left:
                    buttonRect.right +
                    MENU_GAP,

                top:
                    buttonRect.top +
                    buttonRect.height / 2 -
                    menuHeight / 2,
            },

            {
                left:
                    buttonRect.left -
                    menuWidth -
                    MENU_GAP,

                top:
                    buttonRect.top +
                    buttonRect.height / 2 -
                    menuHeight / 2,
            },

            {
                left:
                    buttonRect.left +
                    buttonRect.width / 2 -
                    menuWidth / 2,

                top:
                    buttonRect.bottom +
                    MENU_GAP,
            },

            {
                left:
                    buttonRect.left +
                    buttonRect.width / 2 -
                    menuWidth / 2,

                top:
                    buttonRect.top -
                    menuHeight -
                    MENU_GAP,
            },
        ];

        const calculateOverflow = (position) => {
            let overflow = 0;

            if (
                position.left <
                VIEWPORT_MARGIN
            ) {
                overflow +=
                    VIEWPORT_MARGIN -
                    position.left;
            }

            if (
                position.left +
                menuWidth >
                viewportWidth -
                VIEWPORT_MARGIN
            ) {
                overflow +=
                    position.left +
                    menuWidth -
                    (
                        viewportWidth -
                        VIEWPORT_MARGIN
                    );
            }

            if (
                position.top <
                VIEWPORT_MARGIN
            ) {
                overflow +=
                    VIEWPORT_MARGIN -
                    position.top;
            }

            if (
                position.top +
                menuHeight >
                viewportHeight -
                VIEWPORT_MARGIN
            ) {
                overflow +=
                    position.top +
                    menuHeight -
                    (
                        viewportHeight -
                        VIEWPORT_MARGIN
                    );
            }

            return overflow;
        };

        const bestPosition =
            candidates.reduce(
                (best, current) => {
                    return calculateOverflow(
                        current
                    ) <
                        calculateOverflow(best)
                        ? current
                        : best;
                }
            );

        const finalLeft = Math.max(
            VIEWPORT_MARGIN,

            Math.min(
                bestPosition.left,

                viewportWidth -
                menuWidth -
                VIEWPORT_MARGIN
            )
        );

        const finalTop = Math.max(
            VIEWPORT_MARGIN,

            Math.min(
                bestPosition.top,

                viewportHeight -
                menuHeight -
                VIEWPORT_MARGIN
            )
        );

        menu.style.left =
            `${finalLeft}px`;

        menu.style.top =
            `${finalTop}px`;
    };

    // ==========================================
    // POINTER DOWN
    // ==========================================

    button.addEventListener(
        'pointerdown',
        (event) => {
            if (
                event.button !== undefined &&
                event.button !== 0
            ) {
                return;
            }

            isDragging = true;
            hasMoved = false;

            startX = event.clientX;
            startY = event.clientY;

            const rect =
                button.getBoundingClientRect();

            startLeft = rect.left;
            startTop = rect.top;

            button.style.transition = 'none';

            // Saat ditekan / mulai drag,
            // hijau sedikit lebih terang.
            setActiveState();

            button.setPointerCapture(
                event.pointerId
            );

            event.preventDefault();
        }
    );

    // ==========================================
    // POINTER MOVE
    // ==========================================

    button.addEventListener(
        'pointermove',
        (event) => {
            if (!isDragging) {
                return;
            }

            const deltaX =
                event.clientX - startX;

            const deltaY =
                event.clientY - startY;

            if (
                Math.abs(deltaX) > 5 ||
                Math.abs(deltaY) > 5
            ) {
                hasMoved = true;
            }

            // Kalau sedang drag,
            // popup otomatis ditutup.
            if (
                hasMoved &&
                isOpen
            ) {
                closeMenu();
            }

            const position =
                clampPosition(
                    startLeft + deltaX,
                    startTop + deltaY
                );

            button.style.left =
                `${position.left}px`;

            button.style.top =
                `${position.top}px`;

            button.style.right = 'auto';
            button.style.bottom = 'auto';

            // Selama drag tetap terang.
            setActiveState();

            event.preventDefault();
        }
    );

    // ==========================================
    // POINTER UP
    // ==========================================

    button.addEventListener(
        'pointerup',
        (event) => {
            if (!isDragging) {
                return;
            }

            isDragging = false;

            button.style.transition = '';

            const rect =
                button.getBoundingClientRect();

            setButtonPosition(
                rect.left,
                rect.top
            );

            if (
                button.hasPointerCapture(
                    event.pointerId
                )
            ) {
                button.releasePointerCapture(
                    event.pointerId
                );
            }

            /*
             * PENTING:
             *
             * Jangan setNormalState() di sini.
             *
             * Karena kalau menu sedang terbuka,
             * tombol harus tetap terang + spinner 2.
             *
             * Kalau belum terbuka, kondisi normal
             * akan ditentukan oleh click event.
             */
        }
    );

    // ==========================================
    // POINTER CANCEL
    // ==========================================

    button.addEventListener(
        'pointercancel',
        (event) => {
            isDragging = false;

            button.style.transition = '';

            if (
                button.hasPointerCapture(
                    event.pointerId
                )
            ) {
                button.releasePointerCapture(
                    event.pointerId
                );
            }

            // Kalau popup terbuka,
            // pertahankan state aktif.
            if (isOpen) {
                setActiveState();
                showSpinnerTwo();
            } else {
                setNormalState();
            }
        }
    );

    // ==========================================
    // CLICK
    // ==========================================

    button.addEventListener(
        'click',
        (event) => {
            event.stopPropagation();

            // Kalau habis drag,
            // jangan dianggap sebagai click.
            if (hasMoved) {
                hasMoved = false;

                // Setelah drag selesai:
                // kalau popup tidak terbuka,
                // kembali ke normal.
                if (!isOpen) {
                    setNormalState();
                }

                return;
            }

            // ==================================
            // TOGGLE MENU
            // ==================================

            if (isOpen) {
                /*
                 * Klik ketika popup terbuka:
                 *
                 * spinner 2
                 * ↓
                 * spinner 1
                 * ↓
                 * hijau kembali redup
                 */
                closeMenu();
            } else {
                /*
                 * Klik ketika popup tertutup:
                 *
                 * hijau terang
                 * ↓
                 * spinner 2
                 * ↓
                 * popup terbuka
                 */
                openMenu();
            }
        }
    );

    // ==========================================
    // CLICK OUTSIDE
    // ==========================================

    document.addEventListener(
        'click',
        (event) => {
            if (!isOpen) {
                return;
            }

            if (
                !menu.contains(event.target) &&
                !button.contains(event.target)
            ) {
                closeMenu();
            }
        }
    );

    // ==========================================
    // RESIZE
    // ==========================================

    window.addEventListener(
        'resize',
        () => {
            const rect =
                button.getBoundingClientRect();

            setButtonPosition(
                rect.left,
                rect.top
            );

            if (isOpen) {
                positionMenu();
            }
        }
    );

    // ==========================================
    // INITIALIZE
    // ==========================================

    loadPosition();

    // Kondisi awal:
    // hijau redup + spinner 1
    setNormalState();
}