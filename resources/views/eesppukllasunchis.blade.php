@extends('layouts.padre')
@section('titulo', '- Biblioteca EESP Pukllasunchis')
@section('contenido')
    <div class="fixed-image-container">
        <img src="{{ asset('img/fondo/tramites-2026.webp') }}" alt="Fondo Biblioteca EESP Pukllasunchis"
            fetchpriority="high" decoding="async">
        <div class="overlay"></div>
        <div class="centered-content">
            <img src="{{ asset('img/logo-iesp-pukllasunchis.png') }}" alt="Logo EESP Pukllasunchis"
                class="project-hero-logo">
            <h2>Biblioteca EESP Pukllasunchis</h2>
            <p>
                Encuentra en esta biblioteca libros virtuales que podrán ayudarte en tus trabajos de Investigación o tesis.
            </p>
            <form action="{{ route('busqueda') }}" method="GET" class="search-container">
                <input type="text" name="query" placeholder="Buscar libros por título, autor o tema..."
                    class="search-input" required minlength="3">
                <button type="submit" class="search-button">🔍 Buscar</button>
            </form>
            <div class="buttons-container">
                <a href="{{ route('libros') }}" class="btn-action">📚 Libros virtuales</a>
                <a href="{{ route('catalogo-fisico') }}" class="btn-action">📖 Libros físicos</a>
                <a href="{{ route('videos') }}" class="btn-action">🎥 Videos</a>
            </div>
        </div>
    </div>

    {{-- FAB: acceso rápido al menú de secciones en mobile --}}
    <button id="lib-sidebar-fab" class="lib-sidebar-fab" aria-label="Ver secciones">
        <i class="fa fa-layer-group"></i> Secciones
    </button>

    <div class="container py-4 py-lg-5">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-8 order-1">
                {{-- ═══ ÚLTIMOS TEXTOS (LIBROS VIRTUALES) ═══ --}}
                <div class="lib-content-head">
                    <h2 class="lib-content-head__title">Últimos Libros virtuales</h2>
                    <p class="lib-content-head__sub">Los {{ $libros->count() }} textos más recientes</p>
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
                    <a href="{{ route('libros') }}" class="lib-sidebar-anchor">
                        <i class="fa fa-arrow-right"></i> Ver todos los textos
                    </a>
                </div>
                @if ($librosFisicos->isNotEmpty())
                    <div class="lib-content-head mt-5">
                        <h2 class="lib-content-head__title">Últimos libros físicos</h2>
                        <p class="lib-content-head__sub">{{ $librosFisicos->count() }} libros incorporados recientemente</p>
                    </div>
                    <div class="row g-3" id="grid-fisicos">
                        @foreach ($librosFisicos as $libroFisico)
                            @php
                                $primeraCategoria = $libroFisico->categoriasFisicas->first();
                                $colorCategoria = \App\Models\CategoriaFisica::colorForCodigo($primeraCategoria->codigo ?? null);
                            @endphp
                            <div class="col-6 col-md-4 col-lg-3 lib-book-col">
                                <div class="lib-book-card cf-book-card--flat" style="--cat-color: {{ $colorCategoria }};">
                                    <div class="cf-book-card__accent"></div>
                                    <div class="lib-book-card__info">
                                        <h3 class="lib-book-card__title" title="{{ $libroFisico->nombre }}">{{ $libroFisico->nombre }}</h3>
                                        <p class="lib-book-card__author">
                                            <i class="fa fa-user"></i> {{ $libroFisico->autor }}
                                        </p>
                                        <div class="cf-book-card__meta">
                                            @if ($libroFisico->anio_publicacion)
                                                <span class="cf-meta-pill">
                                                    <i class="fa fa-calendar-alt"></i> {{ $libroFisico->anio_publicacion }}
                                                </span>
                                            @endif
                                            @if ($primeraCategoria)
                                                <span class="cf-cat-pill">
                                                    <i class="fa fa-tag"></i>
                                                    {{ \Illuminate\Support\Str::limit($primeraCategoria->nombre, 20) }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($libroFisico->codigo)
                                            <div class="cf-book-card__code">
                                                <i class="fa fa-barcode"></i> {{ $libroFisico->codigo }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 mb-4">
                        <a href="{{ route('catalogo-fisico') }}" class="lib-sidebar-anchor">
                            <i class="fa fa-arrow-right"></i> Ver todos los libros físicos
                        </a>
                    </div>
                @endif
                <div class="lib-content-head mt-5">
                    <h2 class="lib-content-head__title">Últimos videos</h2>
                    <p class="lib-content-head__sub">{{ $videos->count() }} videos recientes</p>
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
                                        <div class="video-card-h__iframe-wrap">
                                            <iframe src="https://drive.google.com/file/d/{{ $video->drive }}/preview"
                                                loading="lazy" allowfullscreen></iframe>
                                        </div>
                                    @endif
                                </div>
                                <div class="video-card-h__body">
                                    <h3 class="video-card-h__title" title="{{ $video->nombre }}">{{ $video->nombre }}</h3>
                                    @if ($video->descripcion)
                                        <p class="video-card-h__desc">{{ Str::limit($video->descripcion, 60) }}</p>
                                    @endif
                                    @if ($video->categoria)
                                        <a href="{{ route('videoscat.show', $video->categoria->url) }}"
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
                    <a href="{{ route('videos') }}" class="lib-sidebar-anchor">
                        <i class="fa fa-arrow-right"></i> Ver todos los videos
                    </a>
                </div>
            </div>

            <div class="col-lg-4 order-2">
                <aside class="lib-sidebar" id="lib-sidebar">
                    <h4 class="lib-sidebar__title">
                        <i class="fa fa-compass"></i> Explorar el repositorio
                    </h4>

                    <div class="lib-sidebar__section">
                        <button type="button" class="lib-sidebar__section-btn">
                            <span class="lib-sidebar__section-name">Libros virtuales</span>
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
                                <a href="{{ route('categorias.show', $cat->url) }}" class="lib-sidebar__cat-link">
                                    <span class="lib-sidebar__cat-name">{{ $cat->nombre }}</span>
                                    <span class="lib-sidebar__cat-count">{{ $cat->libros_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="lib-sidebar__section">
                        <button type="button" class="lib-sidebar__section-btn">
                            <span class="lib-sidebar__section-name">Libros físicos</span>
                            <span class="lib-sidebar__meta">
                                <span class="sidebar-badge sidebar-badge-cat"
                                    title="{{ $categoriasFisicas->count() }} categorías">{{ $categoriasFisicas->count() }}</span>
                                <span class="sidebar-badge sidebar-badge-book"
                                    title="{{ $categoriasFisicas->sum('libros_fisicos_count') }} libros">{{ $categoriasFisicas->sum('libros_fisicos_count') }}</span>
                            </span>
                            <span class="lib-sidebar__chevron">▼</span>
                        </button>
                        <div class="lib-sidebar__submenu">
                            @foreach ($categoriasFisicas as $cat)
                                <a href="{{ route('catalogo-fisico', ['cat' => $cat->id]) }}" class="lib-sidebar__cat-link">
                                    <span class="lib-sidebar__cat-name">{{ $cat->nombre }}</span>
                                    <span class="lib-sidebar__cat-count">{{ $cat->libros_fisicos_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="lib-sidebar__section">
                        <button type="button" class="lib-sidebar__section-btn">
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
                                <a href="{{ route('videoscat.show', $cat->url) }}" class="lib-sidebar__cat-link">
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
                document.querySelectorAll('#grid-libros, #grid-fisicos, #grid-videos').forEach(function(grid) {
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
