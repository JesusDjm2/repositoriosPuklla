import { gsap } from 'gsap';
import Chart from 'chart.js/auto';
import Swal from 'sweetalert2';

// Nota: no se importa el JS de Bootstrap 5 aquí a propósito. El panel admin
// sigue usando Bootstrap 4 + jQuery vía CDN (ver layouts/app.blade.php) para
// no romper los modales/colapsables jQuery de las vistas admin existentes.

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Toast (esquina superior derecha, se autocierra) para las notificaciones de
// acción del panel: crear/actualizar/eliminar/error. Todas las vistas admin
// comparten este único punto de salida en vez de dibujar su propia alerta.
const notify = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    didOpen: (el) => {
        el.addEventListener('mouseenter', Swal.stopTimer);
        el.addEventListener('mouseleave', Swal.resumeTimer);
    },
});

document.addEventListener('DOMContentLoaded', () => {
    initDarkMode();
    initSidebarToggle();
    playEntranceAnimation();
    animateHeroGlow();
    animateCounters();
    initCharts();
    showFlashNotifications();
    initDeleteConfirm();
    initTableSearch();
    initSlugFields();
});

// Lee el data-island que layouts/app.blade.php escribe con session('success')
// / session('error') y los muestra como SweetAlert. Con esto, ninguna vista
// del panel necesita su propio bloque @if(session(...)) <div class="alert">.
function showFlashNotifications() {
    const flash = window.__flashMessages;
    if (!flash) return;

    if (flash.success) {
        notify.fire({ icon: 'success', title: flash.success });
    }

    if (flash.error) {
        notify.fire({ icon: 'error', title: flash.error });
    }
}

function initDarkMode() {
    const btn = document.getElementById('darkModeToggle');
    const icon = document.getElementById('darkModeIcon');
    if (!btn || !icon) return;

    const applyTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        icon.className = theme === 'dark' ? 'fa fa-sun-o' : 'fa fa-moon-o';
        btn.setAttribute('aria-label', theme === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
    };

    applyTheme(document.documentElement.getAttribute('data-theme') || 'light');

    btn.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('adminTheme', next);
        applyTheme(next);
    });
}

function initSidebarToggle() {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (!toggle || !sidebar || !backdrop) return;

    const isOpen = () => sidebar.classList.contains('active');

    const setOpen = (open) => {
        sidebar.classList.toggle('active', open);
        backdrop.classList.toggle('active', open);
        toggle.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('sidebar-open-lock', open);
    };

    toggle.addEventListener('click', () => setOpen(!isOpen()));
    backdrop.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen()) setOpen(false);
    });

    // Si el usuario agranda la ventana con el menú móvil abierto, lo cerramos
    // para no dejar el backdrop huérfano al pasar a layout de escritorio.
    window.addEventListener('resize', () => {
        if (window.innerWidth > 991.98 && isOpen()) setOpen(false);
    });
}

function playEntranceAnimation() {
    const hero = document.querySelector('[data-animate-hero]');
    const grids = document.querySelectorAll('[data-animate-stagger]');

    if (prefersReducedMotion) {
        return;
    }

    const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

    tl.from('.admin-topbar', { opacity: 0, y: -16, duration: 0.4 }, 0);
    tl.from('#sidebar', { opacity: 0, x: -24, duration: 0.45 }, 0);

    if (hero) {
        tl.from(hero, { opacity: 0, y: 20, duration: 0.5 }, 0.1);
    }

    grids.forEach((grid, i) => {
        tl.from(grid.children, {
            opacity: 0,
            y: 22,
            duration: 0.45,
            stagger: 0.07,
        }, hero ? 0.28 + i * 0.05 : 0.15 + i * 0.05);
    });
}

// Respiración muy sutil del resplandor dorado del hero: un detalle "vivo"
// que no distrae (amplitud pequeña, ritmo lento) y se apaga con
// prefers-reduced-motion.
function animateHeroGlow() {
    const decor = document.querySelector('.hero-banner__decor');
    if (!decor || prefersReducedMotion) return;

    gsap.to(decor, {
        opacity: 0.7,
        scale: 1.06,
        duration: 4.5,
        ease: 'sine.inOut',
        yoyo: true,
        repeat: -1,
        transformOrigin: '85% -10%',
    });
}

function animateCounters() {
    document.querySelectorAll('[data-counter]').forEach((el) => {
        const target = parseInt(el.dataset.counter, 10) || 0;

        if (prefersReducedMotion) {
            el.textContent = target.toLocaleString('es-PE');
            return;
        }

        const counter = { val: 0 };
        gsap.to(counter, {
            val: target,
            duration: 1.1,
            delay: 0.3,
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

// Único punto de confirmación de "eliminar" del panel: cualquier
// <form data-confirm-delete="..."> (ver x-data-table.actions) pasa por acá
// en vez de que cada vista dibuje su propio modal de Bootstrap + script.
function initDeleteConfirm() {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-confirm-delete]')) {
            return;
        }

        event.preventDefault();

        const accent = getComputedStyle(document.documentElement).getPropertyValue('--puk-maroon').trim() || '#ae0c21';

        Swal.fire({
            icon: 'warning',
            title: form.dataset.confirmDelete || '¿Eliminar este registro?',
            text: form.dataset.confirmDeleteText || 'Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: accent,
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
}

// Autogenera el slug ("url") de Categorías/Secciones a partir del campo
// nombre mientras se escribe: cualquier <input data-slug-source> alimenta a
// su <input data-slug-target> del mismo <form> (antes copiado y pegado en
// 4 vistas distintas).
function initSlugFields() {
    document.querySelectorAll('[data-slug-source]').forEach((origen) => {
        const destino = origen.closest('form')?.querySelector('[data-slug-target]');
        if (!destino) return;

        origen.addEventListener('input', () => {
            destino.value = toSlug(origen.value);
        });
    });
}

function toSlug(texto) {
    const mapaAcentos = { á: 'a', é: 'e', í: 'i', ó: 'o', ú: 'u', Á: 'A', É: 'E', Í: 'I', Ó: 'O', Ú: 'U', ñ: 'n', Ñ: 'N' };
    const sinAcentos = texto.replace(/[áéíóúÁÉÍÓÚñÑ]/g, (caracter) => mapaAcentos[caracter] || caracter);

    return sinAcentos
        .toLowerCase()
        .replace(/&/g, 'y')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

// Filtro de texto en cliente para tablas del admin: cualquier
// [data-table-search] filtra las filas de su [data-table-body] asociado por
// el texto que ya está en pantalla (mismo comportamiento que el filtro que
// varias vistas venían reimplementando cada una por su cuenta).
function initTableSearch() {
    document.querySelectorAll('[data-table-search]').forEach((input) => {
        const body = document.querySelector(`[data-table-body="${input.dataset.tableSearch}"]`);
        if (!body) return;

        input.addEventListener('input', () => {
            const texto = input.value.trim().toLowerCase();
            body.querySelectorAll(':scope > tr').forEach((fila) => {
                fila.style.display = fila.textContent.toLowerCase().includes(texto) ? '' : 'none';
            });
        });
    });
}
