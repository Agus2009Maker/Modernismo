document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;

    themeToggleBtn.addEventListener('click', () => {
        if (htmlElement.getAttribute('data-theme') === 'dark') {
            htmlElement.setAttribute('data-theme', 'light');
            themeToggleBtn.innerHTML = 'Modo Nocturno 🌙';
        } else {
            htmlElement.setAttribute('data-theme', 'dark');
            themeToggleBtn.innerHTML = 'Modo Diurno ☀️';
        }
    });
});
