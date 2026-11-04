{{--
    Hero unificado del sistema "lib-*": misma altura, misma animación
    (parallax + reveal con GSAP/ScrollTrigger) y misma estructura para
    cualquier vista de listado o detalle, sin importar el proyecto
    (Escuela, Kawsay, Radio, Sisipedia). Las páginas de inicio de cada
    proyecto (eesppukllasunchis, kawsay, welcome) tienen su propio hero
    "rico" (logo + buscador + accesos) y no usan este parcial.

    La vista que lo incluye debe cargar gsap + ScrollTrigger antes de
    este include (@section('estilos') con los <script> del CDN), igual
    que ya hace resources/views/libros/lista.blade.php.

    Variables:
    - $heroBg         (string)          ruta de la imagen de fondo (asset())
    - $heroBgAlt      (string)          alt de la imagen
    - $heroEyebrow    (string)          texto pequeño sobre el título
    - $heroTitle      (string)          título principal
    - $heroTitleSmall (bool, opcional)  usa el tamaño reducido (para nombres de categoría/registro)
    - $heroMeta       (array, opcional) [['icon' => 'fa-book-open', 'text' => '12 textos'], ...]
    - $heroCta        (array|null, opcional) ['href' => ..., 'icon' => ..., 'label' => ...]
    - $heroSearchRoute (string|null, opcional) URL de un buscador simple en el hero (ej. la
                          búsqueda propia de Kawsay); $heroSearchPlaceholder su texto de ayuda.
    - $heroFormatButtons (bool, opcional) buscador + accesos por formato (Audios/PDF/
                          Imágenes/Videos) + link al árbol completo, igual que en el
                          hero de /repositorio/sisipedia (welcome.blade.php). Uso
                          exclusivo de las vistas públicas de Sisipedia — no activar
                          en el resto de proyectos (Biblioteca/Kawsay/Radio-canciones)
                          que también usan este parcial.
--}}
@php
    $heroTitleSmall = $heroTitleSmall ?? false;
    $heroMeta = $heroMeta ?? [];
    $heroCta = $heroCta ?? null;
    $heroFormatButtons = $heroFormatButtons ?? false;
    $heroSearchRoute = $heroSearchRoute ?? null;
@endphp
<div class="lib-hero {{ $heroFormatButtons ? 'lib-hero--formats' : '' }}">
    <img src="{{ $heroBg }}" alt="{{ $heroBgAlt }}" class="lib-hero__bg" loading="eager">
    <div class="lib-hero__overlay"></div>
    <div class="lib-hero__content">
        <p class="lib-hero__eyebrow">{{ $heroEyebrow }}</p>
        <h1 class="lib-hero__title {{ $heroTitleSmall ? 'lib-hero__title--cat' : '' }}">{{ $heroTitle }}</h1>
        <div class="lib-hero__divider"></div>
        @if (count($heroMeta))
            <div class="lib-hero__meta">
                @foreach ($heroMeta as $item)
                    <span><i class="fa {{ $item['icon'] }}"></i> {{ $item['text'] }}</span>
                @endforeach
            </div>
        @endif
        @if ($heroSearchRoute && ! $heroFormatButtons)
            <form action="{{ $heroSearchRoute }}" method="GET" role="search" class="sisipedia-hero-search kawsay-hero-search mx-auto mt-3">
                <div class="input-group shadow">
                    <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                    <input type="search" name="query" class="form-control" aria-label="Buscar"
                        placeholder="{{ $heroSearchPlaceholder ?? 'Buscar...' }}" value="{{ request('query') }}"
                        minlength="3" maxlength="120" autocomplete="off" required>
                    <button class="btn fw-semibold px-4 text-white" type="submit">Buscar</button>
                </div>
            </form>
        @endif
        @if ($heroFormatButtons)
            {{-- Busca solo dentro de Sisipedia (BusquedaController). La búsqueda global de
                 toda la Asociación es la del pie de página (route('busqueda')). --}}
            <form action="{{ route('sisipedia.buscar') }}" method="GET" role="search" class="sisipedia-hero-search mx-auto mt-3">
                <div class="input-group shadow">
                    <span class="input-group-text bg-white">
                        <i class="fa fa-search"></i>
                    </span>
                    <input type="search" name="q" class="form-control" aria-label="Buscar en Sisipedia" data-sisi-suggest
                        placeholder="Busca un tema: plantas medicinales, fiestas, tejido..." value="{{ request('q') }}"
                        minlength="2" maxlength="120" autocomplete="off" required>
                    <button class="btn fw-semibold px-4 text-white" type="submit">
                        Buscar
                    </button>
                </div>
            </form>

            <p class="sisipedia-hero-formats-label">O explora por formato</p>
            <div class="sisipedia-hero-formats">
                <a href="{{ route('public.sisi', ['tipo' => 'audio']) }}"
                    class="sisipedia-hero-format-btn sisipedia-hero-format-btn--audio">
                    <i class="fa fa-music"></i>Audios
                </a>
                <a href="{{ route('public.sisi', ['tipo' => 'pdf']) }}"
                    class="sisipedia-hero-format-btn sisipedia-hero-format-btn--pdf">
                    <i class="fa fa-file-pdf"></i>PDF
                </a>
                <a href="{{ route('public.sisi', ['tipo' => 'imagen']) }}"
                    class="sisipedia-hero-format-btn sisipedia-hero-format-btn--imagen">
                    <i class="fa fa-image"></i>Imágenes
                </a>
                <a href="{{ route('public.sisi', ['tipo' => 'video']) }}"
                    class="sisipedia-hero-format-btn sisipedia-hero-format-btn--video">
                    <i class="fa fa-video"></i>Videos
                </a>
            </div>

            <a href="{{ route('public.sisi') }}" class="sisipedia-hero-explore-link">
                <i class="fa fa-sitemap me-1"></i>Ver el árbol completo de registros
            </a>
            @include('sisichakuna.partials.buscar-sugerencias')
        @endif
        @if ($heroCta)
            <div class="mt-3">
                <a href="{{ $heroCta['href'] }}"
                   style="display:inline-flex;align-items:center;gap:.45rem;
                          background:rgba(255,255,255,.15);backdrop-filter:blur(6px);
                          border:1px solid rgba(255,255,255,.35);border-radius:999px;
                          color:#fff;font-size:.82rem;font-weight:600;
                          padding:.45rem 1.1rem;text-decoration:none;
                          transition:background .2s,transform .18s;"
                   onmouseover="this.style.background='rgba(255,255,255,.28)';this.style.transform='translateY(-2px)'"
                   onmouseout="this.style.background='rgba(255,255,255,.15)';this.style.transform=''">
                    <i class="fa {{ $heroCta['icon'] ?? 'fa-arrow-right' }}"></i> {{ $heroCta['label'] }}
                    <i class="fa fa-arrow-right" style="font-size:.72rem;opacity:.8;"></i>
                </a>
            </div>
        @endif
    </div>
</div>

<script>
    (function() {
        function initHero() {
            if (typeof gsap === 'undefined') return;

            if (typeof ScrollTrigger !== 'undefined') {
                gsap.registerPlugin(ScrollTrigger);
                gsap.to('.lib-hero__bg', {
                    y: '20%',
                    ease: 'none',
                    scrollTrigger: {
                        trigger: '.lib-hero',
                        start: 'top top',
                        end: 'bottom top',
                        scrub: 1.2
                    }
                });
            }

            gsap.set('.lib-hero__eyebrow', { y: 24 });
            gsap.set('.lib-hero__title', { y: 32 });
            gsap.set('.lib-hero__meta', { y: 18 });
            gsap.set('.lib-hero__content .mt-3', { opacity: 0, y: 14 });

            gsap.timeline({ defaults: { ease: 'power3.out' } })
                .to('.lib-hero__eyebrow', { opacity: 1, y: 0, duration: .8 }, .25)
                .to('.lib-hero__title', { opacity: 1, y: 0, duration: .9 }, .45)
                .to('.lib-hero__divider', { width: 56, duration: .55 }, .85)
                .to('.lib-hero__meta', { opacity: 1, y: 0, duration: .6 }, 1.05)
                .to('.lib-hero__content .mt-3', { opacity: 1, y: 0, duration: .5 }, 1.25);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initHero);
        } else {
            initHero();
        }
    })();
</script>
