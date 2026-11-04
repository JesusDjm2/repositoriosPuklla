@extends('layouts.padre')
@section('titulo', '- Sisipedia')
@section('contenido')
    @if (session('busqueda_error'))
        <div class="container pt-4">
            <div class="alert alert-warning alert-dismissible fade show shadow-sm rounded-3 border-0" role="alert">
                <i class="fas fa-search me-2"></i>{{ session('busqueda_error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    @endif
    <div class="fixed-image-container">
        <img src="{{ asset('img/hormigas/Sisipedia-04.jpeg') }}" alt="Fondo Sisipedia" fetchpriority="high"
            decoding="async" style="object-position: 50% 0%;">
        <div class="overlay"></div>
        <div class="centered-content">
            <h2 class="sisipedia-hero-anim">SISIPEDIA</h2>
            <p class="sisipedia-hero-anim">
                Repositorio con investigaciones de 20 años del programa de Radio Pukllasunchis, realizadas con escuelas y
                líderes, útiles para tareas y proyectos académicos.
            </p>

            {{-- Busca solo dentro de Sisipedia; la búsqueda de toda la Asociación es la del pie de página. --}}
            <form action="{{ route('sisipedia.buscar') }}" method="GET" role="search"
                class="sisipedia-hero-search mx-auto sisipedia-hero-anim">
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

            @include('sisichakuna.partials.buscar-sugerencias')

            <p class="sisipedia-hero-formats-label sisipedia-hero-anim">O explora por formato</p>
            <div class="sisipedia-hero-formats sisipedia-hero-anim">
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

            <a href="{{ route('public.sisi') }}" class="sisipedia-hero-explore-link sisipedia-hero-anim">
                <i class="fa fa-sitemap me-1"></i>Ver el árbol completo de registros
            </a>
        </div>
    </div>

    <script>
        (function() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            function animate() {
                if (typeof gsap === 'undefined') return;
                gsap.from('.sisipedia-hero-anim', {
                    opacity: 0,
                    y: 16,
                    duration: 0.55,
                    stagger: 0.1,
                    ease: 'power2.out'
                });
            }

            if (typeof gsap !== 'undefined') {
                animate();
            } else {
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
                script.onload = animate;
                document.head.appendChild(script);
            }
        })();
    </script>
    <div class="container mt-4 contenedorContenido">
        {{--  <div class="row cardIndex g-4 justify-content-center row-cols-1 row-cols-md-2 row-cols-xl-4">
            <div class="col">
                <div class="contenedor">
                    <img src="{{ asset('img/min/radio-Pukllasunchis.webp') }}" alt="" class="img-fluid">
                    <h2 class="position-absolute text-center text-white px-2">Sisichakunaq Pukllaynin</h2>
                    <a href="{{ route('public.sisi') }}">Ver registros</a>
                </div>
            </div>
            <div class="col">
                <div class="contenedor">
                    <img src="{{ asset('img/min/formacion-continua-pukllasunchis-01.webp') }}" alt="" class="img-fluid">
                    <h2 class="position-absolute text-center text-white">Videos</h2>
                    <a href="{{ route('videos') }}">Ver registros</a>
                </div>
            </div>
            <div class="col">
                <div class="contenedor">
                    <img src="{{ asset('img/min/libros-Pukllasunchis-min.webp') }}" alt="" class="img-fluid">
                    <h2 class="position-absolute text-center text-white px-2">Textos<br><small style="font-size: 12px;">(PDF / Word)</small></h2>
                    <a href="{{ route('libros') }}">Ver registros</a>
                </div>
            </div>
            <div class="col">
                <div class="contenedor contenedor-placeholder opacity-75">
                    <img src="{{ asset('img/min/libros-Pukllasunchis-min.webp') }}" alt="" class="img-fluid">
                    <h2 class="position-absolute text-center text-white">Fotos</h2>
                    <span class="position-absolute bottom-0 start-50 translate-middle-x mb-3 badge bg-secondary bg-opacity-75 rounded-pill">Próximamente</span>
                </div>
            </div>
        </div> --}}


        @include('partials.home-aporte-form')

        @include('partials.home-latest-archivos', ['tipo' => 'audio', 'items' => $ultimosAudios])
        @include('partials.home-latest-archivos', ['tipo' => 'pdf', 'items' => $ultimosPdf])
        @include('partials.home-latest-archivos', ['tipo' => 'imagen', 'items' => $ultimosImagenes])
        @include('partials.home-latest-archivos', ['tipo' => 'video', 'items' => $ultimosVideos])
        @include('partials.home-preview-modal')

        {{-- <div class="container mt-4">
            <div class="row">
                <div class="col-lg-4">
                    <h2 style="font-size:20px" class="mt-3 mb-4 titulosDos">Publicaciones EESP Pukllasunchis<a
                            href="https://www.facebook.com/eesp.pukllasunchis" target="_blank" class="verMas">→ Ver Fan
                            page </a></h2>
                    <div class="facebook" style="width: 100%;">
                        <div class="fb-page" data-href="https://www.facebook.com/eesp.pukllasunchis" data-tabs="timeline"
                            data-width="" data-height="" data-small-header="false" data-adapt-container-width="true"
                            data-hide-cover="false" data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/eesp.pukllasunchis" class="fb-xfbml-parse-ignore">
                                <a href="https://www.facebook.com/eesp.pukllasunchis">EESP Pukllasunchis</a>
                            </blockquote>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h2 style="font-size:20px" class="mt-3 mb-4 titulosDos">Publicaciones Colegio Pukllasunchis<a
                            href="https://www.facebook.com/colegio.pukllasunchis" target="_blank" class="verMas">→ Ver
                            Fan page </a></h2>
                    <div class="facebook" style="width: 100%;">
                        <div class="fb-page" data-href="https://www.facebook.com/colegio.pukllasunchis"
                            data-tabs="timeline" data-width="" data-height="" data-small-header="false"
                            data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/colegio.pukllasunchis"
                                class="fb-xfbml-parse-ignore"><a
                                    href="https://www.facebook.com/colegio.pukllasunchis">Colegio Pukllasunchis</a>
                            </blockquote>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h2 style="font-size:20px" class="mt-3 mb-4 titulosDos">Publicaciones Centro Cultural<a
                            href="https://www.facebook.com/CentroCulturalPukllasunchis" target="_blank" class="verMas">→
                            Ver
                            Fan page </a></h2>
                    <div class="facebook" style="width: 100%;">
                        <div class="fb-page" data-href="https://www.facebook.com/CentroCulturalPukllasunchis"
                            data-tabs="timeline" data-width="" data-height="" data-small-header="false"
                            data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/CentroCulturalPukllasunchis"
                                class="fb-xfbml-parse-ignore"><a
                                    href="https://www.facebook.com/CentroCulturalPukllasunchis">Centro Cultural
                                    Pukllasunchis</a></blockquote>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
@endsection
