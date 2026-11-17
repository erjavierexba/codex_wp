(() => {
    const button = document.querySelector('.menu-toggle');
    const nav = document.getElementById('site-nav');
    if (button && nav) {
        button.hidden = false;
        document.documentElement.classList.add('has-js');
        const setOpen = (open) => {
            button.setAttribute('aria-expanded', String(open));
            nav.classList.toggle('is-open', open);
        };
        button.addEventListener('click', () => setOpen(button.getAttribute('aria-expanded') !== 'true'));
        nav.addEventListener('click', (event) => { if (event.target.closest('a')) setOpen(false); });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') { setOpen(false); button.focus(); }
        });
        window.matchMedia('(min-width: 801px)').addEventListener('change', () => setOpen(false));
    }
    const status = document.getElementById('oldbear-contact-status');
    if (status) { status.focus({preventScroll: true}); }
})();

