(function(){
  const storageKey = 'theme';
  const html = document.documentElement;
  function applyTheme(theme){
    html.setAttribute('data-theme', theme);
    localStorage.setItem(storageKey, theme);
  }
  const current = localStorage.getItem(storageKey) || 'light';
  applyTheme(current);
  document.getElementById('theme-toggle')?.addEventListener('click', () => {
    const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    applyTheme(next);
  });
})();