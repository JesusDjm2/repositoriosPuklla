@extends('layouts.padre')
@section('titulo', '- Kawsay - ' . $tema->titulo)

@section('meta')
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $tema->titulo }} — Kawsay">
    @if ($tema->descripcion)
        <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($tema->descripcion), 200) }}">
    @endif
    @if ($tema->imagen)
        <meta property="og:image" content="{{ asset($tema->imagen) }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif
    <meta property="og:url" content="{{ request()->url() }}">
@endsection

@section('contenido')
@php
    $areaColor = $area->color ?: '#6e7d1e';
    // Mapeo de nombres cortos para optimizar la fila única horizontal en PC
    $nombresCortos = [
        'ficha' => 'Fichas',
        'teoria' => 'Teoría',
        'video' => 'Videos',
        'presentacion' => 'Presentaciones',
        'bibliografia' => 'Bibliografía',
        'galeria' => 'Galería',
        'audio' => 'Audios',
        'experiencia' => 'Experiencias',
        'mapa' => 'Mapas',
    ];
@endphp

<section class="ktema-stage" style="--area-color: {{ $areaColor }};">
    <div class="container">
        {{-- Barra superior de navegación --}}
        <div class="ktema-stage__top">
            <a href="{{ route('kawsay.area', $areaSlug) }}" class="ktema-back-btn">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                <span>Volver a <strong>{{ $area->nombre }}</strong></span>
            </a>
        </div>

        {{-- Cuerpo principal dividido: Izquierda imagen, Derecha contenido --}}
        <div class="ktema-stage__body row g-4 g-xl-5 align-items-center">
            <div class="col-12 col-lg-5 col-xl-5">
                <div class="ktema-stage__media-wrap">
                    @if ($tema->imagen)
                        <figure class="ktema-stage__figure">
                            <img src="{{ asset($tema->imagen) }}" alt="{{ $tema->titulo }}" class="ktema-stage__img" fetchpriority="high">
                        </figure>
                    @else
                        <div class="ktema-stage__figure ktema-stage__figure--placeholder">
                            <div class="ktema-stage__ph-icon"><i class="fa fa-leaf" aria-hidden="true"></i></div>
                            <span class="ktema-stage__ph-area">{{ $area->nombre }}</span>
                            <span class="ktema-stage__ph-title">{{ $tema->titulo }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-lg-7 col-xl-7">
                <div class="ktema-stage__content">
                    <div class="ktema-stage__badge-row">
                        <span class="ktema-stage__area-badge">
                            <i class="fa fa-folder-open" aria-hidden="true"></i> {{ $area->nombre }}
                        </span>
                    </div>

                    <h1 class="ktema-stage__title">{{ $tema->titulo }}</h1>

                    @if ($tema->descripcion)
                        <div class="ktema-stage__desc-scroll">
                            <p class="ktema-stage__desc">{!! nl2br(e($tema->descripcion)) !!}</p>
                        </div>
                    @endif

                    <div class="ktema-stage__share-row">
                        <span class="ktema-stage__share-label">Compartir:</span>
                        <div class="kawsay-share">
                            <a href="https://wa.me/?text={{ urlencode($tema->titulo . ' - ' . request()->url()) }}" target="_blank" rel="noopener" aria-label="Compartir por WhatsApp" title="Compartir en WhatsApp">
                                <i class="fab fa-whatsapp" aria-hidden="true"></i>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" aria-label="Compartir en Facebook" title="Compartir en Facebook">
                                <i class="fab fa-facebook-f" aria-hidden="true"></i>
                            </a>
                            <button type="button" class="kawsay-share__copy" data-url="{{ request()->url() }}" aria-label="Copiar enlace" title="Copiar enlace al portapapeles">
                                <i class="fa fa-link" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Franja inferior: Formatos en una sola línea en PC --}}
        <div class="ktema-stage__dock-section">
            <div class="ktema-dock-header">
                <span class="ktema-dock-header__eyebrow">
                    <i class="fa fa-layer-group me-1" aria-hidden="true"></i> Formatos disponibles
                </span>

                @if ($catalogo->isNotEmpty() || $tema->comentarios->isNotEmpty())
                    <a href="#mas-contenido" class="ktema-scroll-cue d-none d-lg-inline-flex" aria-label="Ver catálogo y comentarios">
                        <span>Catálogo y comentarios</span>
                        <i class="fa fa-chevron-down" aria-hidden="true"></i>
                    </a>
                @endif
            </div>

            <div class="ktema-dock-scroll">
                <div class="ktema-dock-track" role="toolbar" aria-label="Formatos de contenido">
                    @foreach ($tiposContenido as $tipoSlug => $tipo)
                        @php
                            $total = $conteos[$tipoSlug] ?? 0;
                            $nombreCorto = $nombresCortos[$tipoSlug] ?? $tipo['label'];
                        @endphp

                        @if ($total > 0 && $tipoSlug === 'galeria' && $fotos->isNotEmpty())
                            {{-- Galería fotográfica: no lleva a otra página, abre el visor en popup. --}}
                            <button type="button" class="ktema-dock-pill ktema-dock-pill--active" id="kawsayGaleriaBtn" aria-haspopup="dialog" title="{{ $tipo['label'] }} ({{ $fotos->count() }} {{ $fotos->count() === 1 ? 'foto' : 'fotos' }})">
                                <span class="ktema-dock-pill__icon"><i class="fa {{ $tipo['icono'] }}" aria-hidden="true"></i></span>
                                <span class="ktema-dock-pill__name">{{ $nombreCorto }}</span>
                                <span class="ktema-dock-pill__count">{{ $fotos->count() }}</span>
                            </button>
                        @elseif ($total > 0)
                            <a href="{{ route('kawsay.contenido', [$areaSlug, $temaSlug, $tipoSlug]) }}" class="ktema-dock-pill ktema-dock-pill--active" title="{{ $tipo['label'] }} ({{ $total }} {{ $total === 1 ? 'archivo' : 'archivos' }})">
                                <span class="ktema-dock-pill__icon"><i class="fa {{ $tipo['icono'] }}" aria-hidden="true"></i></span>
                                <span class="ktema-dock-pill__name">{{ $nombreCorto }}</span>
                                <span class="ktema-dock-pill__count">{{ $total }}</span>
                            </a>
                        @else
                            <span class="ktema-dock-pill ktema-dock-pill--empty" title="{{ $tipo['label'] }} (Próximamente)">
                                <span class="ktema-dock-pill__icon"><i class="fa {{ $tipo['icono'] }}" aria-hidden="true"></i></span>
                                <span class="ktema-dock-pill__name">{{ $nombreCorto }}</span>
                                <span class="ktema-dock-pill__count">0</span>
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Sección secundaria accesible al hacer scroll: Catálogo y comentarios --}}
<div class="container py-4 py-md-5" id="mas-contenido">
    {{-- Libros y videos del catálogo asignados a este tema --}}
    @if ($catalogo->isNotEmpty())
        <section class="kcar ktema-catalogo mb-5" id="catalogo-tema" aria-labelledby="kcar-titulo-catalogo" data-kcar>
            <div class="kcar__head">
                <span class="kcar__icon" aria-hidden="true"><i class="fa fa-book-open"></i></span>
                <div class="kcar__heading">
                    <h2 class="kcar__title" id="kcar-titulo-catalogo">Del catálogo</h2>
                    <p class="kcar__sub">Libros y videos relacionados con este tema.</p>
                </div>
                <div class="kcar__controls">
                    <button type="button" class="kcar__btn" data-kcar-prev aria-controls="kcar-pista-catalogo" aria-label="Catálogo: anteriores">
                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="kcar__btn" data-kcar-next aria-controls="kcar-pista-catalogo" aria-label="Catálogo: siguientes">
                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="kcar__pista" id="kcar-pista-catalogo" data-kcar-pista tabindex="0"
                role="region" aria-roledescription="carrusel" aria-label="Del catálogo">
                @foreach ($catalogo as $item)
                    @include('kawsay.partials.carrusel-card', ['item' => $item, 'posicion' => $loop->iteration, 'total' => $loop->count])
                @endforeach
            </div>
        </section>
    @endif

    @include('kawsay.temas.partials.comentarios')
</div>

@if ($fotos->isNotEmpty())
    @include('kawsay.temas.partials.visor-galeria', ['fotos' => $fotos])
@endif

@include('kawsay.partials.estilos')
<script src="{{ asset('js/kawsay.js') }}?v={{ @filemtime(public_path('js/kawsay.js')) }}" defer></script>

<script>
    (function () {
        var copyBtn = document.querySelector('.kawsay-share__copy');
        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                var url = copyBtn.getAttribute('data-url');
                navigator.clipboard.writeText(url).then(function () {
                    var icon = copyBtn.querySelector('i');
                    copyBtn.classList.add('is-copiado');
                    icon.className = 'fa fa-check';
                    setTimeout(function () {
                        copyBtn.classList.remove('is-copiado');
                        icon.className = 'fa fa-link';
                    }, 1800);
                });
            });
        }
    })();
</script>
@endsection
