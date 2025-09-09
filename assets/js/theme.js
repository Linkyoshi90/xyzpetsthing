(function () {
    const storageKey = 'theme';
    const html = document.documentElement;
    const banner = document.querySelector('.site-banner');
    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem(storageKey, theme);
        if (banner) {
            banner.classList.toggle('dark', theme === 'dark');
        }
    }
    const current = localStorage.getItem(storageKey) || 'light';
    applyTheme(current);
    const toggle = document.getElementById('theme-toggle');
    if (toggle) {
        toggle.addEventListener('click', () => {
            const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            applyTheme(next);
        });
    }
})();