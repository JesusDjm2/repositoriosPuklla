@extends('layouts.padre')
@section('titulo', '- Kawsay')
@section('contenido')
    <div class="fixed-image-container kawsay-hero">
        <img src="{{ asset('img/kawsay/fondo-inicial-kawsay.webp') }}" alt="Fondo Kawsay" fetchpriority="high"
            decoding="async">
        <div class="overlay"></div>
        <div class="centered-content">
            <h2>Repositorio Educativo Kawsay</h2>
            <p>
                Aprender haciendo para regenerar el territorio.
            </p>
            <form action="{{ route('kawsay.buscar') }}" method="GET" class="search-container">
                <input type="text" name="query" placeholder="Buscar en Kawsay: temas, contenido, libros, videos..."
                    class="search-input" required minlength="3">
                <button type="submit" class="search-button">🔍 Buscar</button>
            </form>
        </div>
    </div>

    {{-- FAB: acceso rápido al menú de categorías en mobile --}}
    <button id="lib-sidebar-fab" class="lib-sidebar-fab" aria-label="Ver categorías">
        <i class="fa fa-layer-group"></i> Categorías
    </button>

    {{-- ═══ ÁREAS DEL REPOSITORIO EDUCATIVO: sección propia justo debajo del
         hero (ya no óvalos flotando encima de la foto estirada — ese patrón
         obligaba a estirar el fondo con hacks de alto/márgenes negativos por
         breakpoint y a poner parches de contraste detrás del texto). Ahora
         son tarjetas normales sobre superficie blanca: la foto de cada área
         queda como avatar circular (mismo detalle visual que antes), el
         color de KawsayArea se usa como acento del avatar en vez de teñir
         toda la tarjeta, y el texto no necesita ningún truco de contraste
         porque siempre está sobre blanco. Administrable desde Kawsay-Áreas. ═══ --}}
    <section class="kawsay-areas">
        <div class="container">
            <p class="kawsay-areas__heading text-center">Áreas de trabajo</p>
            <div class="row g-3 g-lg-4 justify-content-center">
                @foreach ($areas as $area)
                    <div class="col-6 col-md-3">
                        <a href="{{ route('kawsay.area', $area->slug) }}" class="kawsay-area-item"
                            style="--area-color: {{ $area->color ?: '#6e7d1e' }};">
                            <span class="kawsay-area-item__avatar">
                                @if ($area->imagen)
                                    <img src="{{ asset($area->imagen) }}" alt=""
                                        style="object-position: {{ $area->slug === 'territorio-vivo' ? 'center 78%' : 'center' }};"
                                        loading="lazy">
                                @else
                                    <i class="fa {{ $area->icono ?: 'fa-leaf' }}"></i>
                                @endif
                                <span class="kawsay-area-item__scrim"></span>
                                <span class="kawsay-area-item__title">{{ $area->nombre }}</span>
                            </span>
                            @if ($area->descripcion)
                                <span class="kawsay-area-item__desc">{{ $area->descripcion }}</span>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @include('kawsay.partials.estilos')

    <div class="container py-4 py-lg-5">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-8 order-1">

                {{-- ═══ ÚLTIMOS LIBROS ═══ --}}
                <div class="lib-content-head">
                    <h2 class="lib-content-head__title">Últimos libros</h2>
                    <p class="lib-content-head__sub">Páginas que siembran conciencia y respeto por la naturaleza.</p>
                </div>
                <div class="row g-3" id="grid-libros">
                    @foreach ($libros as $libro)
                        @php
                            $urlVistaPrevia = "https://drive.google.com/thumbnail?id={$libro->identificador}&sz=w600";
                        @endphp
                        <div class="col-6 col-md-4 col-lg-3 lib-book-col">
                            <div class="lib-book-card">
                                <div class="lib-book-card__cover">
                                    <img src="{{ $urlVistaPrevia }}" alt="{{ $libro->nombre }}" loading="lazy">
                                    <div class="lib-book-card__overlay">
                                        <button type="button" class="lib-book-card__btn"
                                            onclick="abrirVistaPreviaLibro({{ \Illuminate\Support\Js::from($libro->identificador) }}, {{ \Illuminate\Support\Js::from($libro->nombre) }})">
                                            <i class="fa fa-eye"></i> Vista previa
                                        </button>
                                    </div>
                                </div>
                                <div class="lib-book-card__info">
                                    <h3 class="lib-book-card__title" title="{{ $libro->nombre }}">{{ $libro->nombre }}</h3>
                                    <p class="lib-book-card__author">
                                        <i class="fa fa-user"></i> {{ $libro->autor }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 mb-4">
                    <a href="{{ route('kawsaylibros.lista') }}" class="lib-sidebar-anchor">
                        <i class="fa fa-arrow-right"></i> Ver todos los libros
                    </a>
                </div>

                {{-- ═══ ÚLTIMOS VIDEOS ═══ --}}
                <div class="lib-content-head mt-5">
                    <h2 class="lib-content-head__title">Últimos videos</h2>
                    <p class="lib-content-head__sub">Historias que inspiran a cuidar la vida que nos rodea.</p>
                </div>
                <div class="row g-3" id="grid-videos">
                    @foreach ($videos as $video)
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="video-card-h">
                                <div class="video-card-h__thumb">
                                    @if ($video->youtube)
                                        <a href="https://www.youtube.com/watch?v={{ $video->youtube }}" target="_blank"
                                            rel="noopener" class="video-card-h__link">
                                            <img src="https://img.youtube.com/vi/{{ $video->youtube }}/hqdefault.jpg"
                                                alt="{{ $video->nombre }}" loading="lazy">
                                            <span class="video-card-h__play"><i class="fab fa-youtube"></i></span>
                                        </a>
                                    @elseif ($video->drive)
                                        {{-- Miniatura + play que abre el popup con <video controls> real
                                             (streaming propio), igual que la portada de Radio; no el iframe de Drive. --}}
                                        <button type="button" class="video-card-h__link border-0 p-0 bg-transparent"
                                            aria-label="Reproducir {{ $video->nombre }}"
                                            onclick="abrirVistaPreviaVideo({{ \Illuminate\Support\Js::from(route('kawsayvideos.stream', $video)) }}, {{ \Illuminate\Support\Js::from($video->nombre) }}, {{ \Illuminate\Support\Js::from(\App\Services\GoogleDriveService::getUrl($video->drive)) }})">
                                            <img src="{{ \App\Services\GoogleDriveService::getThumbnailUrl($video->drive, 600) }}"
                                                alt="{{ $video->nombre }}" loading="lazy" style="object-fit:contain;"
                                                onerror="this.style.display='none'">
                                            <span class="video-card-h__play">
                                                <span class="btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                                    style="width:46px;height:46px;padding:0;">
                                                    <i class="fa fa-play text-dark" style="font-size:1rem;margin-left:2px;"></i>
                                                </span>
                                            </span>
                                        </button>
                                    @endif
                                </div>
                                <div class="video-card-h__body">
                                    <h3 class="video-card-h__title" title="{{ $video->nombre }}">{{ $video->nombre }}</h3>
                                    @if ($video->descripcion)
                                        <p class="video-card-h__desc">{{ Str::limit($video->descripcion, 60) }}</p>
                                    @endif
                                    @if ($video->categoria)
                                        <a href="{{ route('kawsayvideoscat.show', $video->categoria->url) }}"
                                            class="video-card-h__cat">
                                            <i class="fa fa-folder-open"></i> {{ $video->categoria->nombre }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 mb-4">
                    <a href="{{ route('kawsayvideos.lista') }}" class="lib-sidebar-anchor">
                        <i class="fa fa-arrow-right"></i> Ver todos los videos
                    </a>
                </div>

            </div>{{-- /.col-lg-8 --}}

            <div class="col-lg-4 order-2">
                <aside class="lib-sidebar" id="lib-sidebar">
                    <h4 class="lib-sidebar__title">
                        <i class="fa fa-compass"></i> Explorar el repositorio
                    </h4>

                    <div class="lib-sidebar__section">
                        <button type="button" class="lib-sidebar__section-btn">
                            <span class="lib-sidebar__section-icon"><i class="fa fa-book"></i></span>
                            <span class="lib-sidebar__section-name">Libros</span>
                            <span class="lib-sidebar__meta">
                                <span class="sidebar-badge sidebar-badge-cat"
                                    title="{{ $categoriasVirtuales->count() }} categorías">{{ $categoriasVirtuales->count() }}</span>
                                <span class="sidebar-badge sidebar-badge-book"
                                    title="{{ $categoriasVirtuales->sum('libros_count') }} textos">{{ $categoriasVirtuales->sum('libros_count') }}</span>
                            </span>
                            <span class="lib-sidebar__chevron">▼</span>
                        </button>
                        <div class="lib-sidebar__submenu">
                            @foreach ($categoriasVirtuales as $cat)
                                <a href="{{ route('kawsaycategorias.show', $cat->url) }}" class="lib-sidebar__cat-link">
                                    <span class="lib-sidebar__cat-name">{{ $cat->nombre }}</span>
                                    <span class="lib-sidebar__cat-count">{{ $cat->libros_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="lib-sidebar__section">
                        <button type="button" class="lib-sidebar__section-btn">
                            <span class="lib-sidebar__section-icon"><i class="fa fa-video"></i></span>
                            <span class="lib-sidebar__section-name">Videos</span>
                            <span class="lib-sidebar__meta">
                                <span class="sidebar-badge sidebar-badge-cat"
                                    title="{{ $categoriasVideos->count() }} categorías">{{ $categoriasVideos->count() }}</span>
                                <span class="sidebar-badge sidebar-badge-book"
                                    title="{{ $categoriasVideos->sum('videos_count') }} videos">{{ $categoriasVideos->sum('videos_count') }}</span>
                            </span>
                            <span class="lib-sidebar__chevron">▼</span>
                        </button>
                        <div class="lib-sidebar__submenu">
                            @foreach ($categoriasVideos as $cat)
                                <a href="{{ route('kawsayvideoscat.show', $cat->url) }}" class="lib-sidebar__cat-link">
                                    <span class="lib-sidebar__cat-name">{{ $cat->nombre }}</span>
                                    <span class="lib-sidebar__cat-count">{{ $cat->videos_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </div>

        </div>{{-- /.row --}}
    </div>

    {{-- partials.libro-preview-modal ya lo incluye layouts.padre una sola vez --}}

    <script>
        (function() {
            function loadGsapThen(fn) {
                if (typeof gsap !== 'undefined') { fn(); return; }
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
                script.onload = fn;
                document.head.appendChild(script);
            }

            loadGsapThen(function() {
                document.querySelectorAll('#grid-libros, #grid-videos').forEach(function(grid) {
                    if (grid) {
                        gsap.from(grid.querySelectorAll('.lib-book-col, .col-6'), {
                            y: 26, duration: .4, stagger: .045, ease: 'power2.out'
                        });
                    }
                });

                document.querySelectorAll('.lib-sidebar__section-btn').forEach(function(btn) {
                    var sub = btn.nextElementSibling;
                    gsap.set(sub, { height: 0, overflow: 'hidden' });

                    btn.addEventListener('click', function() {
                        var isOpen = btn.classList.toggle('is-open');
                        gsap.to(sub, {
                            height: isOpen ? 'auto' : 0,
                            duration: isOpen ? .38 : .26,
                            ease: isOpen ? 'power2.out' : 'power2.in'
                        });
                    });
                });
            });

            var fab = document.getElementById('lib-sidebar-fab');
            var sidebar = document.getElementById('lib-sidebar');
            if (fab && sidebar) {
                fab.addEventListener('click', function() {
                    sidebar.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
                new IntersectionObserver(function(entries) {
                    var e = entries[0];
                    fab.style.opacity = e.isIntersecting ? '0' : '1';
                    fab.style.pointerEvents = e.isIntersecting ? 'none' : 'auto';
                }).observe(sidebar);
            }
        })();
    </script>
@endsection
