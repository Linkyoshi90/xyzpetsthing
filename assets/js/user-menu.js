(function () {
    const toggle = document.getElementById('user-menu-toggle');
    const menu = document.getElementById('user-menu');
    if (!toggle || !menu) return;
    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('show');
    });
    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target) && !toggle.contains(e.target)) {
            menu.classList.remove('show');
        }
    });
})();