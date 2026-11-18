
document.addEventListener('DOMContentLoaded', () => {
    const menu = document.querySelector('.python-account-menu');
    if (!menu) return;

    const trigger = menu.querySelector('.python-account-trigger');
    const dropdown = menu.querySelector('.python-account-dropdown');

    function closeMenu() {
        dropdown.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
    }

    trigger.addEventListener('click', () => {
        const willOpen = dropdown.hidden;

        dropdown.hidden = !willOpen;
        trigger.setAttribute('aria-expanded', String(willOpen));
    });

    document.addEventListener('click', event => {
        if (!menu.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !dropdown.hidden) {
            closeMenu();
            trigger.focus();
        }
    });
});
