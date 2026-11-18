
document.addEventListener('DOMContentLoaded', () => {

    // =====================================
    // MENÚ DE ESCRITORIO
    // =====================================

    const desktopMenu = document.querySelector('.python-account-menu');

    if (desktopMenu) {

        const trigger = desktopMenu.querySelector('.python-account-trigger');
        const dropdown = desktopMenu.querySelector('.python-account-dropdown');

        if (trigger && dropdown) {

            function closeDesktop() {
                dropdown.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
            }

            trigger.addEventListener('click', () => {
                const willOpen = dropdown.hidden;

                dropdown.hidden = !willOpen;
                trigger.setAttribute('aria-expanded', String(willOpen));
            });

            document.addEventListener('click', event => {
                if (!desktopMenu.contains(event.target)) {
                    closeDesktop();
                }
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && !dropdown.hidden) {
                    closeDesktop();
                    trigger.focus();
                }
            });
        }
    }

    // =====================================
    // MENÚ MÓVIL
    // =====================================

    const mobileTrigger = document.querySelector(
        '.python-mobile-account-trigger'
    );

    const mobileDropdown = document.querySelector(
        '#python-mobile-account-dropdown'
    );

    if (mobileTrigger && mobileDropdown) {

        function closeMobile() {
            mobileDropdown.hidden = true;
            mobileTrigger.setAttribute('aria-expanded', 'false');
        }

        mobileTrigger.addEventListener('click', () => {
            const willOpen = mobileDropdown.hidden;

            mobileDropdown.hidden = !willOpen;
            mobileTrigger.setAttribute('aria-expanded', String(willOpen));
        });

        document.addEventListener('click', event => {
            if (
                !mobileTrigger.contains(event.target) &&
                !mobileDropdown.contains(event.target)
            ) {
                closeMobile();
            }
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !mobileDropdown.hidden) {
                closeMobile();
                mobileTrigger.focus();
            }
        });

        // Cerrar el menú móvil al pasar a escritorio.
        const desktopBreakpoint = window.matchMedia('(min-width: 992px)');

        desktopBreakpoint.addEventListener('change', event => {
            if (event.matches) {
                closeMobile();
            }
        });
    }

});
