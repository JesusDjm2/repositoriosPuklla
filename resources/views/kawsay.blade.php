@extends('layouts.padre')
@section('titulo', '- Kawsay')
@section('bodyClass', 'is-kawsay kawsay-snap-page')
@section('estilos')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/kawsay.css') }}?v={{ @filemtime(public_path('css/kawsay.css')) }}">
@endsection

@section('contenido')
    {{-- ═══ SECCIÓN 1: INICIO (100vh / Scroll Snap) ══════════════════════════════
         Fondo blanco (#ffffff), sin imagen de fondo ni degradados oscuros.
         Contenido centrado: Texto "Repositorio Educativo" → Logo Kawsay → Buscador.
         Sin badges de formatos ni texto "Empieza por:". ═══ --}}
    <section class="kawsay-seccion-snap kawsay-seccion-inicio" id="seccion-inicio" aria-labelledby="kawsay-hero-title">
        <div class="kawsay-hero-center">
            <div class="kawsay-hero-eyebrow-wrap">
                <img src="{{ asset('img/kawsay/rama-costados.png') }}" class="kawsay-branch-decor kawsay-branch-decor--eyebrow kawsay-branch-left"
                    alt="" aria-hidden="true">
                <p class="kawsay-hero-eyebrow">Repositorio Educativo</p>
                <img src="{{ asset('img/kawsay/rama-costados.png') }}" class="kawsay-branch-decor kawsay-branch-decor--eyebrow kawsay-branch-right"
                    alt="" aria-hidden="true">
            </div>
            <div class="kawsay-hero-logo-box">
                <img src="{{ asset('img/kawsay.png') }}" alt="Kawsay" class="kawsay-hero-logo-img">
            </div>
            <h1 class="visually-hidden" id="kawsay-hero-title">Kawsay - Repositorio Educativo</h1>
            <p class="kawsay-hero-lema">Aprender haciendo para regenerar el territorio</p>

            <form action="{{ route('kawsay.buscar') }}" method="GET" class="kawsay-hero-search-form" role="search">
                <label for="khero-buscar" class="visually-hidden">Buscar en Kawsay</label>
                <i class="fa fa-search kawsay-hero-search-icon" aria-hidden="true"></i>
                <input type="search" id="khero-buscar" name="query" required minlength="3"
                    placeholder="Busca un tema, video o ficha…" autocomplete="off" enterkeyhint="search">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <a href="#seccion-areas" class="kawsay-scroll-hint" aria-label="Ir a la sección Explora Nuestras Áreas de Trabajo">
            <span class="kawsay-scroll-hint-label">Explorar áreas</span>
            <i class="fa fa-chevron-down kawsay-scroll-hint-arrow" aria-hidden="true"></i>
        </a>
    </section>

    {{-- ═══ SECCIÓN 2: ÁREAS DE TRABAJO (100vh / Scroll Snap) ═════════════════════
         Título: "Explora Nuestras Áreas de Trabajo" decorado a ambos lados sutilmente con ramas.
         Las 4 áreas en una misma línea.
         Tarjetas con animación 3D de volteo (flip card) al pasar el mouse (hover):
         - Frente: imagen, título debajo, contador de temas.
         - Reverso (volteado): contador de temas, texto descriptivo, botón "Ir a Temas". ═══ --}}
    <section class="kawsay-seccion-snap kawsay-seccion-areas" id="seccion-areas" aria-labelledby="kawsay-areas-heading">
        <div class="container-fluid px-3 px-md-4 px-xl-5 kawsay-areas-wrapper">
            <header class="kawsay-areas-header">
                <div class="kawsay-decor-heading">
                    <img src="{{ asset('img/kawsay/rama-costados.png') }}" class="kawsay-branch-decor kawsay-branch-left"
                        alt="" aria-hidden="true">
                    <h2 class="kawsay-section-h2" id="kawsay-areas-heading">Explora Nuestras Áreas de Trabajo</h2>
                    <img src="{{ asset('img/kawsay/rama-costados.png') }}" class="kawsay-branch-decor kawsay-branch-right"
                        alt="" aria-hidden="true">
                </div>
            </header>

            @if ($areas->isNotEmpty())
                <div class="kawsay-flip-grid" role="list">
                    @foreach ($areas as $area)
                        <a href="{{ route('kawsay.area', $area->slug) }}" class="kawsay-flip-card" role="listitem"
                            style="--area-color: {{ $area->color ?: '#6e7d1e' }}"
                            aria-label="{{ $area->nombre }} - Ir a Temas">
                            <div class="kawsay-flip-card-inner">
                                {{-- CARA FRONTAL --}}
                                <div class="kawsay-flip-card-front">
                                    <div class="kawsay-flip-card-media">
                                        @if ($area->imagen)
                                            <img src="{{ asset($area->imagen) }}" alt="{{ $area->nombre }}"
                                                loading="lazy" decoding="async"
                                                @if ($area->slug === 'territorio-vivo') data-foco="abajo" @endif>
                                        @else
                                            <div class="kawsay-flip-card-fallback">
                                                <i class="fa {{ $area->icono ?: 'fa-leaf' }}" aria-hidden="true"></i>
                                            </div>
                                        @endif
                                        <span class="kawsay-card-count-badge">
                                            <i class="fa fa-folder-open" aria-hidden="true"></i>
                                            <span>{{ $area->temas->count() }} {{ $area->temas->count() === 1 ? 'tema' : 'temas' }}</span>
                                        </span>
                                    </div>
                                    <div class="kawsay-flip-card-foot">
                                        <h3 class="kawsay-card-title">{{ $area->nombre }}</h3>
                                    </div>
                                </div>

                                {{-- CARA TRASERA (VOLTEADA EN HOVER) --}}
                                <div class="kawsay-flip-card-back">
                                    <div class="kawsay-flip-card-back-body">
                                        <span class="kawsay-card-back-badge">
                                            <i class="fa fa-layer-group" aria-hidden="true"></i>
                                            <span>{{ $area->temas->count() }} {{ $area->temas->count() === 1 ? 'tema' : 'temas' }}</span>
                                        </span>
                                        <h3 class="kawsay-card-back-title">{{ $area->nombre }}</h3>
                                        <p class="kawsay-card-back-desc">
                                            {{ $area->descripcion ?: 'Descubre temas, guías y recursos de esta área de trabajo.' }}
                                        </p>
                                        <span class="kawsay-card-back-btn">
                                            <span>Ir a Temas</span>
                                            <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ═══ SECCIÓN 3: PUBLICACIONES Y RECURSOS (OCULTADA TEMPORALMENTE) ═══════════
         Se oculta por ahora toda la sección de últimas publicaciones a pedido del usuario.
         Inmediatamente después de la Sección 2 se muestra el pie de página. ═══ --}}
    @if(false)
    <section class="kawsay-seccion-snap kawsay-seccion-publicaciones" id="seccion-publicaciones">
        {{-- Botón flotante (celular): lleva al menú "Explorar" --}}
        <button type="button" id="lib-sidebar-fab" class="lib-sidebar-fab" aria-label="Explorar áreas, temas y formatos">
            <i class="fa fa-compass" aria-hidden="true"></i> Explorar
        </button>

        <div class="container py-4 py-lg-5">
            <div class="row g-4 g-lg-5">
                <div class="col-lg-8 order-1">

                    <header class="kport-head">
                        <p class="kport-head__eyebrow">Repositorio vivo</p>
                        <h2 class="kport-head__title">Últimas publicaciones</h2>
                        <p class="kport-head__sub">Lo más reciente de cada área, ordenado por formato. Toca una tarjeta para verla aquí mismo.</p>
                    </header>

                    {{-- TEMA DEL MES --}}
                    @if ($temaDelMes)
                        <a href="{{ $temaDelMes->url }}" class="kdest" style="--area-color: {{ $temaDelMes->area->color ?: '#6e7d1e' }}">
                            <span class="kdest__media">
                                @if ($temaDelMes->imagen)
                                    <img src="{{ $temaDelMes->imagen }}" alt="" loading="lazy" decoding="async">
                                @else
                                    <i class="fa fa-seedling" aria-hidden="true"></i>
                                @endif
                            </span>
                            <span class="kdest__body">
                                <span class="kdest__eyebrow"><i class="fa fa-star" aria-hidden="true"></i> Tema del mes · {{ $temaDelMes->area->nombre }}</span>
                                <span class="kdest__title">{{ $temaDelMes->tema->titulo }}</span>
                                @if ($temaDelMes->tema->descripcion)
                                    <span class="kdest__desc">{{ \Illuminate\Support\Str::limit($temaDelMes->tema->descripcion, 170) }}</span>
                                @endif
                                @if ($temaDelMes->formatos->isNotEmpty())
                                    <span class="kdest__formatos">
                                        @foreach ($temaDelMes->formatos as $formato)
                                            <span class="kdest__chip"><i class="fa {{ $formato->icono }}" aria-hidden="true"></i> {{ $formato->total }} {{ $formato->label }}</span>
                                        @endforeach
                                    </span>
                                @endif
                                <span class="kdest__cta">Explorar el tema <i class="fa fa-arrow-right" aria-hidden="true"></i></span>
                            </span>
                        </a>
                    @endif

                    @if (count($carruseles))
                        @foreach ($carruseles as $clave => $grupo)
                            <section class="kcar kcar--{{ $clave }}" id="formato-{{ $clave }}" aria-labelledby="kcar-titulo-{{ $clave }}"
                                data-kcar>
                                <div class="kcar__head">
                                    <span class="kcar__icon" aria-hidden="true"><i class="fa {{ $grupo['icono'] }}"></i></span>
                                    <div class="kcar__heading">
                                        <h3 class="kcar__title" id="kcar-titulo-{{ $clave }}">{{ $grupo['titulo'] }}</h3>
                                        <p class="kcar__sub">{{ $grupo['subtitulo'] }}</p>
                                    </div>
                                    <div class="kcar__controls">
                                        <button type="button" class="kcar__btn" data-kcar-prev aria-controls="kcar-pista-{{ $clave }}"
                                            aria-label="{{ $grupo['titulo'] }}: anteriores">
                                            <i class="fa fa-chevron-left" aria-hidden="true"></i>
                                        </button>
                                        <button type="button" class="kcar__btn" data-kcar-next aria-controls="kcar-pista-{{ $clave }}"
                                            aria-label="{{ $grupo['titulo'] }}: siguientes">
                                            <i class="fa fa-chevron-right" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="kcar__pista" id="kcar-pista-{{ $clave }}" data-kcar-pista tabindex="0"
                                    role="region" aria-roledescription="carrusel" aria-label="{{ $grupo['titulo'] }} recientes">
                                    @foreach ($grupo['items'] as $item)
                                        @include('kawsay.partials.carrusel-card', [
                                            'item' => $item,
                                            'posicion' => $loop->iteration,
                                            'total' => $loop->count,
                                        ])
                                    @endforeach
                                </div>

                                @if ($grupo['verTodo'])
                                    <a href="{{ $grupo['verTodo']['url'] }}" class="kcar__more">
                                        {{ $grupo['verTodo']['label'] }} <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </section>
                        @endforeach
                    @else
                        <div class="kport-vacio">
                            <i class="fa fa-seedling" aria-hidden="true"></i>
                            <p>Aún no hay publicaciones. Pronto encontrarás aquí videos, fotos y fichas de cada área.</p>
                        </div>
                    @endif

                </div>{{-- /.col-lg-8 --}}

                <div class="col-lg-4 order-2">
                    <aside class="kexp" id="lib-sidebar" aria-labelledby="kexp-titulo">
                        <h2 class="kexp__title" id="kexp-titulo"><i class="fa fa-compass" aria-hidden="true"></i> Áreas y temas</h2>

                        @foreach ($areas as $area)
                            <details class="kexp__grupo" style="--area-color: {{ $area->color ?: '#6e7d1e' }}">
                                <summary class="kexp__summary">
                                    <span class="kexp__avatar" aria-hidden="true">
                                        @if ($area->imagen)
                                            <img src="{{ asset($area->imagen) }}" alt="" loading="lazy">
                                        @else
                                            <i class="fa {{ $area->icono ?: 'fa-leaf' }}"></i>
                                        @endif
                                    </span>
                                    <span class="kexp__summary-name">{{ $area->nombre }}</span>
                                    <span class="kexp__count" title="{{ $area->temas->count() }} temas">{{ $area->temas->count() }}</span>
                                    <i class="fa fa-chevron-down kexp__chevron" aria-hidden="true"></i>
                                </summary>
                                <ul class="kexp__lista">
                                    @foreach ($area->temas as $tema)
                                        <li><a href="{{ route('kawsay.tema', [$area->slug, $tema->slug]) }}">{{ $tema->titulo }}</a></li>
                                    @endforeach
                                    <li class="kexp__lista-todo">
                                        <a href="{{ route('kawsay.area', $area->slug) }}">Ver toda el área <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                                    </li>
                                </ul>
                            </details>
                        @endforeach
                    </aside>
                </div>

            </div>{{-- /.row --}}
        </div>
    </section>
    @endif

    {{-- Visor de fotos --}}
    @if ($fotosVisor->isNotEmpty())
        @include('kawsay.temas.partials.visor-galeria', ['fotos' => $fotosVisor])
    @endif

    <script src="{{ asset('js/kawsay.js') }}?v={{ @filemtime(public_path('js/kawsay.js')) }}" defer></script>
@endsection
