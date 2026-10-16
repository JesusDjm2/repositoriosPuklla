{{-- Visor (lightbox) de la Galería fotográfica del tema. Requiere: $fotos (id, titulo, drive_id). --}}
@php
    $fotosJs = $fotos->map(fn ($f) => [
        'titulo' => $f->titulo,
        'url' => \App\Services\GoogleDriveService::getThumbnailUrl($f->drive_id, 1800),
        'mini' => \App\Services\GoogleDriveService::getThumbnailUrl($f->drive_id, 160),
        'ver' => \App\Services\GoogleDriveService::getUrl($f->drive_id),
    ])->values();
@endphp
<div id="kvis" class="kvis" role="dialog" aria-modal="true" aria-label="Galería fotográfica" hidden>
    <div class="kvis__bar">
        <div class="kvis__info">
            <span class="kvis__contador" id="kvisContador" aria-live="polite"></span>
            <span class="kvis__titulo" id="kvisTitulo"></span>
        </div>
        <div class="kvis__acciones">
            <a id="kvisAbrir" class="kvis__btn kvis__btn--texto" href="#" target="_blank" rel="noopener" title="Abrir en una nueva ventana">
                <i class="fa fa-external-link-alt" aria-hidden="true"></i> <span>Nueva ventana</span>
            </a>
            <button type="button" class="kvis__btn" id="kvisCerrar" aria-label="Cerrar galería"><i class="fa fa-xmark" aria-hidden="true"></i></button>
        </div>
    </div>

    <div class="kvis__escena" id="kvisEscena">
        <button type="button" class="kvis__nav kvis__nav--prev" id="kvisPrev" aria-label="Foto anterior"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>

        <figure class="kvis__figura">
            <div class="kvis__cargando" id="kvisCargando" aria-hidden="true"><span></span></div>
            <img id="kvisImg" alt="" draggable="false">
            <div class="kvis__error" id="kvisError" hidden>
                <i class="fa fa-image" aria-hidden="true"></i>
                <p>No se pudo cargar esta foto.</p>
            </div>
        </figure>

        <button type="button" class="kvis__nav kvis__nav--next" id="kvisNext" aria-label="Foto siguiente"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
    </div>

    <div class="kvis__tira" id="kvisTira" role="tablist" aria-label="Miniaturas"></div>
</div>

<style>
    .kvis { position: fixed; inset: 0; z-index: 10600; display: flex; flex-direction: column; background: rgba(12, 14, 9, .96); color: #fff; }
    .kvis[hidden] { display: none; }
    .kvis__bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .7rem 1rem; }
    .kvis__info { display: flex; align-items: baseline; gap: .8rem; min-width: 0; }
    .kvis__contador { font-weight: 800; font-variant-numeric: tabular-nums; padding: .2rem .65rem; border-radius: 999px; background: rgba(255, 255, 255, .14); font-size: .82rem; flex-shrink: 0; }
    .kvis__titulo { font-size: .95rem; opacity: .92; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .kvis__acciones { display: flex; align-items: center; gap: .5rem; flex-shrink: 0; }
    .kvis__btn {
        display: inline-flex; align-items: center; justify-content: center; gap: .45rem;
        min-width: 2.5rem; height: 2.5rem; padding: 0 .8rem; border: 0; border-radius: 999px; cursor: pointer;
        background: rgba(255, 255, 255, .14); color: #fff; font-size: .85rem; font-weight: 600; text-decoration: none;
        transition: background .15s ease;
    }
    .kvis__btn:hover { background: rgba(255, 255, 255, .28); color: #fff; text-decoration: none; }
    .kvis__btn:focus-visible, .kvis__nav:focus-visible, .kvis__mini:focus-visible { outline: 3px solid #fff; outline-offset: 2px; }

    .kvis__escena { position: relative; flex: 1; min-height: 0; display: flex; align-items: center; justify-content: center; padding: 0 4.2rem; touch-action: pan-y; }
    .kvis__figura { margin: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; position: relative; }
    .kvis__figura img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 6px; box-shadow: 0 10px 40px rgba(0, 0, 0, .5); user-select: none; transition: opacity .18s ease; }
    .kvis__figura img.is-cargando { opacity: 0; }
    .kvis__cargando { position: absolute; inset: 0; display: none; align-items: center; justify-content: center; pointer-events: none; }
    .kvis__cargando.is-visible { display: flex; }
    .kvis__cargando span { width: 2.6rem; height: 2.6rem; border-radius: 50%; border: 3px solid rgba(255, 255, 255, .25); border-top-color: #fff; animation: kvisGiro .8s linear infinite; }
    .kvis__error { text-align: center; opacity: .8; }
    .kvis__error i { font-size: 2.4rem; margin-bottom: .5rem; }
    .kvis__error[hidden] { display: none; }

    .kvis__nav {
        position: absolute; top: 50%; transform: translateY(-50%); z-index: 2;
        width: 3rem; height: 3rem; border: 0; border-radius: 50%; cursor: pointer;
        background: rgba(255, 255, 255, .16); color: #fff; font-size: 1.05rem;
        display: flex; align-items: center; justify-content: center; transition: background .15s ease, transform .15s ease;
    }
    .kvis__nav:hover { background: rgba(255, 255, 255, .32); }
    .kvis__nav--prev { left: .8rem; }
    .kvis__nav--next { right: .8rem; }
    .kvis__nav[hidden] { display: none; }

    .kvis__tira { display: flex; gap: .5rem; padding: .8rem 1rem 1rem; overflow-x: auto; justify-content: safe center; scrollbar-width: thin; }
    .kvis__mini { flex: 0 0 auto; width: 4.4rem; height: 3.2rem; padding: 0; border: 2px solid transparent; border-radius: 8px; overflow: hidden; cursor: pointer; opacity: .55; background: #222; transition: opacity .15s ease, border-color .15s ease; }
    .kvis__mini img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .kvis__mini:hover { opacity: .9; }
    .kvis__mini.is-activa { opacity: 1; border-color: #fff; }

    @keyframes kvisGiro { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) { .kvis__figura img, .kvis__cargando span { transition: none; animation-duration: 2s; } }
    @media (max-width: 767.98px) {
        .kvis__escena { padding: 0 .4rem; }
        .kvis__nav { width: 2.6rem; height: 2.6rem; background: rgba(0, 0, 0, .45); }
        .kvis__nav--prev { left: .4rem; } .kvis__nav--next { right: .4rem; }
        .kvis__btn--texto span { display: none; }
        .kvis__mini { width: 3.6rem; height: 2.7rem; }
    }
</style>

<script>
    (function () {
        var fotos = @json($fotosJs);

        var visor = document.getElementById('kvis');
        var abrirBtn = document.getElementById('kawsayGaleriaBtn');
        if (!visor || !abrirBtn || !fotos.length) return;

        var img = document.getElementById('kvisImg');
        var cargando = document.getElementById('kvisCargando');
        var error = document.getElementById('kvisError');
        var contador = document.getElementById('kvisContador');
        var titulo = document.getElementById('kvisTitulo');
        var abrirEnlace = document.getElementById('kvisAbrir');
        var prev = document.getElementById('kvisPrev');
        var next = document.getElementById('kvisNext');
        var cerrar = document.getElementById('kvisCerrar');
        var tira = document.getElementById('kvisTira');
        var actual = 0;
        var origen = null;

        // Miniaturas
        fotos.forEach(function (f, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'kvis__mini';
            b.setAttribute('role', 'tab');
            b.setAttribute('aria-label', 'Ir a la foto ' + (i + 1));
            var m = document.createElement('img');
            m.src = f.mini; m.alt = ''; m.loading = 'lazy';
            b.appendChild(m);
            b.addEventListener('click', function () { ir(i); });
            tira.appendChild(b);
        });
        var minis = tira.querySelectorAll('.kvis__mini');

        function precargar(i) {
            if (i < 0 || i >= fotos.length) return;
            (new Image()).src = fotos[i].url;
        }

        function ir(i) {
            if (i < 0 || i >= fotos.length) return;
            actual = i;
            var f = fotos[i];

            cargando.classList.add('is-visible');
            error.hidden = true;
            img.classList.add('is-cargando');
            img.onload = function () { cargando.classList.remove('is-visible'); img.classList.remove('is-cargando'); };
            var reintentado = false;
            img.onerror = function () {
                // Drive a veces rechaza una miniatura de forma momentánea: un reintento antes de rendirse.
                if (!reintentado && actual === i) {
                    reintentado = true;
                    setTimeout(function () { if (actual === i) img.src = f.url + '&r=1'; }, 700);
                    return;
                }
                cargando.classList.remove('is-visible');
                error.hidden = false;
            };
            img.alt = f.titulo || '';
            img.src = f.url;

            contador.textContent = (i + 1) + ' / ' + fotos.length;
            titulo.textContent = f.titulo || '';
            abrirEnlace.href = f.ver;
            prev.hidden = next.hidden = fotos.length < 2;
            prev.disabled = i === 0;
            next.disabled = i === fotos.length - 1;
            prev.style.opacity = i === 0 ? .35 : 1;
            next.style.opacity = i === fotos.length - 1 ? .35 : 1;

            minis.forEach(function (m, k) {
                m.classList.toggle('is-activa', k === i);
                m.setAttribute('aria-selected', k === i ? 'true' : 'false');
            });
            var activa = minis[i];
            if (activa && activa.scrollIntoView) activa.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });

            precargar(i + 1); precargar(i - 1);
        }

        function abrir() {
            origen = document.activeElement;
            visor.hidden = false;
            document.body.style.overflow = 'hidden';
            ir(0);
            cerrar.focus();
        }

        function cerrarVisor() {
            visor.hidden = true;
            document.body.style.overflow = '';
            img.removeAttribute('src');
            if (origen && origen.focus) origen.focus();
        }

        abrirBtn.addEventListener('click', abrir);
        cerrar.addEventListener('click', cerrarVisor);
        prev.addEventListener('click', function () { ir(actual - 1); });
        next.addEventListener('click', function () { ir(actual + 1); });

        // Clic en el fondo (fuera de foto y controles) cierra.
        document.getElementById('kvisEscena').addEventListener('click', function (e) {
            if (e.target === this || e.target.classList.contains('kvis__figura')) cerrarVisor();
        });

        document.addEventListener('keydown', function (e) {
            if (visor.hidden) return;
            if (e.key === 'Escape') { cerrarVisor(); }
            else if (e.key === 'ArrowLeft') { ir(actual - 1); }
            else if (e.key === 'ArrowRight') { ir(actual + 1); }
            else if (e.key === 'Home') { ir(0); }
            else if (e.key === 'End') { ir(fotos.length - 1); }
            else if (e.key === 'Tab') {
                // Mantiene el foco dentro del visor.
                var foco = visor.querySelectorAll('button:not([disabled]):not([hidden]), a[href]');
                if (!foco.length) return;
                var primero = foco[0], ultimo = foco[foco.length - 1];
                if (e.shiftKey && document.activeElement === primero) { e.preventDefault(); ultimo.focus(); }
                else if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primero.focus(); }
            }
        });

        // Deslizar en pantallas táctiles.
        var x0 = null;
        var escena = document.getElementById('kvisEscena');
        escena.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
        escena.addEventListener('touchend', function (e) {
            if (x0 === null) return;
            var dx = e.changedTouches[0].clientX - x0;
            x0 = null;
            if (Math.abs(dx) > 50) ir(actual + (dx < 0 ? 1 : -1));
        }, { passive: true });
    })();
</script>
