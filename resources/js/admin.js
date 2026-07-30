import { gsap } from 'gsap';
import Chart from 'chart.js/auto';

// Nota: no se importa el JS de Bootstrap 5 aquí a propósito. El panel admin
// sigue usando Bootstrap 4 + jQuery vía CDN (ver layouts/app.blade.php) para
// no romper los modales/colapsables jQuery de las vistas admin existentes.

document.addEventListener('DOMContentLoaded', () => {
    initDarkMode();
    animateStaggerGrids();
    animateCounters();
    initCharts();
});

function initDarkMode() {
    const btn = document.getElementById('darkModeToggle');
    const icon = document.getElementById('darkModeIcon');
    const text = document.getElementById('darkModeText');
    if (!btn) return;

    const applyTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        if (theme === 'dark') {
            icon.className = 'fa fa-sun-o';
            text.textContent = ' Modo claro';
        } else {
            icon.className = 'fa fa-moon-o';
            text.textContent = ' Modo oscuro';
        }
    };

    applyTheme(document.documentElement.getAttribute('data-theme') || 'light');

    btn.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('adminTheme', next);
        applyTheme(next);
    });
}

function animateStaggerGrids() {
    document.querySelectorAll('[data-animate-stagger]').forEach((grid) => {
        gsap.from(grid.children, {
            opacity: 0,
            y: 24,
            duration: 0.5,
            stagger: 0.08,
            ease: 'power2.out',
        });
    });
}

function animateCounters() {
    document.querySelectorAll('[data-counter]').forEach((el) => {
        const target = parseInt(el.dataset.counter, 10) || 0;
        const counter = { val: 0 };
        gsap.to(counter, {
            val: target,
            duration: 1.1,
            ease: 'power1.out',
            onUpdate: () => {
                el.textContent = Math.round(counter.val).toLocaleString('es-PE');
            },
        });
    });
}

function initCharts() {
    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        const config = JSON.parse(canvas.dataset.chart);
        new Chart(canvas, config);
    });
}
