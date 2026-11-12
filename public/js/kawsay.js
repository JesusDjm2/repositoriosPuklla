/*
 * Kawsay — JS del sitio público (portada, página de tema y de contenido).
 * Estilos: public/css/kawsay.css
 *
 * Sin dependencias obligatorias. Cada bloque se activa solo si su HTML existe:
 * - Contador de vistas: [data-kawsay-vista="url"] avisa al abrir un archivo.
 * - Entrada del hero (GSAP, opcional) en la portada.
 * - Carruseles [data-kcar]: scroll nativo (funcionan sin JS); aquí solo se
 *   agregan botones, teclado y entrada suave.
 * - Menú "Explorar": marca el formato visible.
 */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ── Contador de vistas: un aviso al servidor al abrir un archivo ──────
       sendBeacon no retrasa la apertura del visor y se envía aunque la
       persona cierre la página. El servidor cuenta una vez por sesión. */
    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    function avisarVista(el) {
        if (!el || !navigator.sendBeacon || el.__kawsayVistaEnviada) return;
        el.__kawsayVistaEnviada = true; // una vez por elemento y página (el servidor además deduplica por sesión)
        var datos = new FormData();
        if (tokenMeta) datos.append('_token', tokenMeta.content);
        navigator.sendBeacon(el.getAttribute('data-kawsay-vista'), datos);
    }
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-kawsay-vista]');
        if (el && el.tagName !== 'VIDEO') avisarVista(el);
    });
    // Videos con controles nativos: se cuenta al empezar a reproducir ("play" no burbujea: captura).
    // Audios: el reproductor crea su <audio> dentro de [data-kawsay-vista-al-reproducir].
    document.addEventListener('play', function (e) {
        var t = e.target;
        if (!t.matches) return;
        if (t.matches('video[data-kawsay-vista]')) avisarVista(t);
        else if (t.tagName === 'AUDIO') {
            var caja = t.closest('[data-kawsay-vista-al-reproducir]');
            if (caja && !caja.__kawsayVistaEnviada && navigator.sendBeacon) {
                caja.__kawsayVistaEnviada = true;
                var datos = new FormData();
                if (tokenMeta) datos.append('_token', tokenMeta.content);
                navigator.sendBeacon(caja.getAttribute('data-kawsay-vista-al-reproducir'), datos);
            }
        }
    }, true);

    /* ── Video de YouTube: miniatura liviana; el reproductor (≈1 MB) se carga
       recién al pulsar, para no gastar datos de quien solo está mirando. ── */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-kyt]');
        if (!btn) return;
        var marco = document.createElement('iframe');
        marco.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(btn.getAttribute('data-kyt')) + '?autoplay=1&rel=0&playsinline=1';
        marco.title = btn.getAttribute('data-kyt-titulo') || 'Video de YouTube';
        marco.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen';
        marco.allowFullscreen = true;
        marco.className = 'kawsay-yt__frame';
        btn.replaceWith(marco);
    });

    /* ── Entrada del hero: UN solo momento orquestado (título → frase →
       buscador → áreas en cascada). gsap.from() parte del estado final, así
       que si GSAP no carga o falla, todo queda visible igual. ──────────── */
    function entradaHero() {
        var texto = document.querySelector('.khero__texto');
        if (!texto || typeof gsap === 'undefined') return;
        gsap.matchMedia().add('(prefers-reduced-motion: no-preference)', function () {
            var tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
            tl.from(texto.children, { y: 22, opacity: 0, duration: 0.6, stagger: 0.08 })
              .from('.khero__areas-titulo', { opacity: 0, duration: 0.4 }, '-=0.45')
              .from('.khero-area', {
                  y: 26, opacity: 0, scale: 0.96, duration: 0.6, stagger: 0.08, ease: 'back.out(1.4)',
              }, '-=0.4');
        });
    }

    // La entrada se lanza cuando se retira la pantalla de carga del sitio
    // (#puklla-loader, layouts/padre): animar detrás de ella no lo vería nadie.
    function cuandoSeVayaElCargador(fn) {
        var cargador = document.getElementById('puklla-loader');
        if (!cargador) { fn(); return; }
        var listo = false;
        function una() { if (!listo) { listo = true; fn(); } }
        new MutationObserver(function (cambios, obs) {
            if (!document.getElementById('puklla-loader') || cargador.classList.contains('out')) { obs.disconnect(); una(); }
        }).observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
        setTimeout(una, 3500); // tope: nunca esperar más
    }

    if (!reduceMotion) {
        if (typeof gsap !== 'undefined') {
            cuandoSeVayaElCargador(entradaHero);
        } else {
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
            s.onload = function () { cuandoSeVayaElCargador(entradaHero); };
            document.head.appendChild(s);
        }
    }

    /* ── Carruseles ─────────────────────────────────────────────────────── */
    document.querySelectorAll('[data-kcar]').forEach(function (carrusel) {
        var pista = carrusel.querySelector('[data-kcar-pista]');
        var prev = carrusel.querySelector('[data-kcar-prev]');
        var next = carrusel.querySelector('[data-kcar-next]');
        if (!pista) return;

        function paso() {
            // Avanza casi una "pantalla" de tarjetas, dejando ver la última como referencia.
            return Math.max(pista.clientWidth * 0.85, 200);
        }

        function mover(dir) {
            pista.scrollBy({ left: dir * paso(), behavior: reduceMotion ? 'auto' : 'smooth' });
        }

        var pendiente = false;
        function actualizar() {
            pendiente = false;
            var max = pista.scrollWidth - pista.clientWidth;
            var hayMas = max > 4;
            carrusel.classList.toggle('has-overflow', hayMas);
            var alInicio = pista.scrollLeft <= 4;
            var alFinal = pista.scrollLeft >= max - 4;
            carrusel.classList.toggle('is-at-end', !hayMas || alFinal);
            if (prev) prev.disabled = alInicio;
            if (next) next.disabled = alFinal;
        }

        function programar() {
            if (pendiente) return;
            pendiente = true;
            requestAnimationFrame(actualizar);
        }

        if (prev) prev.addEventListener('click', function () { mover(-1); });
        if (next) next.addEventListener('click', function () { mover(1); });
        pista.addEventListener('scroll', programar, { passive: true });
        window.addEventListener('resize', programar);

        // Flechas del teclado cuando la pista tiene el foco.
        pista.addEventListener('keydown', function (e) {
            if (e.target !== pista) return;
            if (e.key === 'ArrowRight') { e.preventDefault(); mover(1); }
            if (e.key === 'ArrowLeft') { e.preventDefault(); mover(-1); }
        });

        // Las miniaturas cambian el ancho al cargar: recalcular cuando lleguen.
        pista.querySelectorAll('img').forEach(function (img) {
            if (!img.complete) img.addEventListener('load', programar, { once: true });
        });

        actualizar();
    });

    /* ── Botón flotante (celular): llevar al menú "Explorar" ────────────── */
    var fab = document.getElementById('lib-sidebar-fab');
    var menu = document.getElementById('lib-sidebar');
    if (fab && menu) {
        fab.addEventListener('click', function () {
            menu.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
        });
        // Se oculta mientras se ve el hero (ya ofrece buscar, formatos y áreas; además
        // tapaba una tarjeta del mosaico) o el propio menú "Explorar".
        var hero = document.querySelector('.khero');
        var aLaVista = new Map();
        if ('IntersectionObserver' in window) {
            var obs = new IntersectionObserver(function (entradas) {
                entradas.forEach(function (e) { aLaVista.set(e.target, e.isIntersecting); });
                var ocultar = Array.from(aLaVista.values()).some(Boolean);
                fab.style.opacity = ocultar ? '0' : '1';
                fab.style.pointerEvents = ocultar ? 'none' : 'auto';
            });
            obs.observe(menu);
            if (hero) obs.observe(hero);
        }
    }
    var secciones = Array.prototype.slice.call(document.querySelectorAll('[data-kcar]'));
    if (!('IntersectionObserver' in window) || !secciones.length) return;

    /* ── Entrada suave: solo para carruseles que aún no están en pantalla ── */
    if (!reduceMotion) {
        var revelar = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('is-visible');
                    revelar.unobserve(e.target);
                }
            });
        }, { rootMargin: '0px 0px -8% 0px' });

        secciones.forEach(function (s) {
            if (s.getBoundingClientRect().top > window.innerHeight) {
                s.classList.add('is-pending');
                revelar.observe(s);
            }
        });

        // Red de seguridad: el contenido NUNCA debe quedar oculto. Si con un
        // scroll muy rápido el observador no alcanzó a marcar una sección que
        // ya está (o pasó) por la pantalla, se revela aquí.
        var revisando = false;
        function revisarPendientes() {
            revisando = false;
            var quedan = 0;
            secciones.forEach(function (s) {
                if (!s.classList.contains('is-pending') || s.classList.contains('is-visible')) return;
                if (s.getBoundingClientRect().top < window.innerHeight) s.classList.add('is-visible');
                else quedan++;
            });
            if (!quedan) window.removeEventListener('scroll', alHacerScroll);
        }
        function alHacerScroll() {
            if (revisando) return;
            revisando = true;
            requestAnimationFrame(revisarPendientes);
        }
        window.addEventListener('scroll', alHacerScroll, { passive: true });
    }

})();
