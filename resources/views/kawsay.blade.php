@extends('layouts.padre')
@section('titulo', '- Kawsay')
@section('estilos')
    <link rel="stylesheet" href="{{ asset('css/kawsay.css') }}?v={{ @filemtime(public_path('css/kawsay.css')) }}">
@endsection
@section('contenido')
    {{-- ═══ HERO EN MOSAICO ═══════════════════════════════════════════════════
         Izquierda: qué es Kawsay + las 3 formas de empezar (buscar, elegir un
         formato o un tema sugerido). Derecha: las 4 áreas como mosaico 2×2 con
         sus pinturas a color (sin el velo oscuro de antes) y el nombre sobre una
         franja sólida del color del área, para que siempre se lea.
         En celular: hero corto y las áreas en 2×2 compacto; el contenido empieza
         dentro de la primera pantalla y media. Estilos: public/css/kawsay.css (.khero) ═══ --}}
    <section class="khero" aria-labelledby="khero-titulo">
        <img class="khero__fondo" src="{{ asset('img/kawsay/fondo-inicial-kawsay.webp') }}" alt=""
            fetchpriority="high" decoding="async">
        <div class="container khero__grid">
            <div class="khero__texto">
                <p class="khero__eyebrow">Repositorio educativo</p>
                <h1 class="khero__titulo" id="khero-titulo">Kawsay</h1>
                <p class="khero__lema">Aprender haciendo para regenerar el territorio.</p>

                <form action="{{ route('kawsay.buscar') }}" method="GET" class="khero__buscar" role="search">
                    <label for="khero-buscar" class="visually-hidden">Buscar en Kawsay</label>
                    <i class="fa fa-search khero__buscar-icono" aria-hidden="true"></i>
                    <input type="search" id="khero-buscar" name="query" required minlength="3"
                        placeholder="Busca un tema, video o ficha…" autocomplete="off" enterkeyhint="search">
                    <button type="submit">Buscar</button>
                </form>

                @php $atajos = array_diff_key($carruseles, ['populares' => true]); @endphp
                @if (count($atajos))
                    <nav class="khero__atajos" aria-label="Ver por formato">
                        @foreach ($atajos as $clave => $grupo)
                            <a href="#formato-{{ $clave }}" class="khero__atajo">
                                <i class="fa {{ $grupo['icono'] }}" aria-hidden="true"></i> {{ $grupo['titulo'] }}
                            </a>
                        @endforeach
                    </nav>
                @endif

                @if ($temasSugeridos->isNotEmpty())
                    <p class="khero__sugerencias">
                        <span>Empieza por:</span>
                        @foreach ($temasSugeridos as $sugerido)
                            @if ($sugerido->area)
                                <a href="{{ route('kawsay.tema', [$sugerido->area->slug, $sugerido->slug]) }}">{{ $sugerido->titulo }}</a>
                            @endif
                        @endforeach
                    </p>
                @endif
            </div>

            @if ($areas->isNotEmpty())
                <nav class="khero__areas" aria-labelledby="khero-areas-titulo">
                    <h2 class="khero__areas-titulo" id="khero-areas-titulo">Explora por área</h2>
                    <ul class="khero__mosaico">
                        @foreach ($areas as $area)
                            <li>
                                <a href="{{ route('kawsay.area', $area->slug) }}" class="khero-area"
                                    style="--area-color: {{ $area->color ?: '#6e7d1e' }}">
                                    <span class="khero-area__media">
                                        @if ($area->imagen)
                                            <img src="{{ asset($area->imagen) }}" alt="" loading="lazy" decoding="async"
                                                @if ($area->slug === 'territorio-vivo') data-foco="abajo" @endif>
                                        @else
                                            <i class="fa {{ $area->icono ?: 'fa-leaf' }}" aria-hidden="true"></i>
                                        @endif
                                    </span>
                                    <span class="khero-area__pie">
                                        <span class="khero-area__nombre">{{ $area->nombre }}</span>
                                        <span class="khero-area__meta">
                                            {{ $area->temas->count() }} {{ $area->temas->count() === 1 ? 'tema' : 'temas' }}
                                            <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                        </span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </section>

    {{-- Botón flotante (celular): lleva al menú "Explorar", que ahí queda debajo del contenido --}}
    <button type="button" id="lib-sidebar-fab" class="lib-sidebar-fab" aria-label="Explorar áreas, temas y formatos">
        <i class="fa fa-compass" aria-hidden="true"></i> Explorar
    </button>


    <div class="container py-4 py-lg-5">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-8 order-1">

                {{-- ═══ ÚLTIMAS PUBLICACIONES: un carrusel por formato ═══
                     Datos: App\Services\KawsayPortada. Cada tarjeta abre su contenido en
                     el visor (PDF/libro), el reproductor (video) o la galería (foto)
                     sin salir de la portada. Estilos: public/css/kawsay.css --}}
                <header class="kport-head">
                    <p class="kport-head__eyebrow">Repositorio vivo</p>
                    <h2 class="kport-head__title">Últimas publicaciones</h2>
                    <p class="kport-head__sub">Lo más reciente de cada área, ordenado por formato. Toca una tarjeta para verla aquí mismo.</p>
                </header>

                {{-- ═══ TEMA DEL MES: lo elige el equipo en el panel (Kawsay-Temas → estrella) ═══ --}}
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
                    {{-- Los atajos por formato están en el hero (.khero__atajos): no se repiten aquí. --}}
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
                {{-- ═══ MENÚ LATERAL "Áreas y temas" (sticky en escritorio; en celular queda
                     debajo y el botón flotante "Explorar" lleva hasta aquí). Solo áreas y
                     temas: los formatos ya están en el hero y en los carruseles.
                     <details> nativo: funciona sin JS y con teclado. ═══ --}}
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

    {{-- Visor de fotos (mismo de la página de cada tema); lo abren las tarjetas del carrusel de fotos.
         partials.libro-preview-modal (PDF y video) ya lo incluye layouts.padre. --}}
    @if ($fotosVisor->isNotEmpty())
        @include('kawsay.temas.partials.visor-galeria', ['fotos' => $fotosVisor])
    @endif

    <script src="{{ asset('js/kawsay.js') }}?v={{ @filemtime(public_path('js/kawsay.js')) }}" defer></script>
@endsection
