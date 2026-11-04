@extends('layouts.padre')
@section('titulo', '- ' . ($q !== '' ? 'Resultados para «' . $q . '»' : 'Buscar en Sisipedia'))

@php
    // Helpers estáticos (resaltar/ruta/nombre…); un `use` aquí rompería la vista compilada.
    $Busqueda = \App\Http\Controllers\sisipedia\BusquedaController::class;

    $total = array_sum($totales);
    $tabsMeta = [
        'registros' => ['label' => 'Temas', 'icon' => 'fa-folder-open'],
        'materiales' => ['label' => 'Materiales', 'icon' => 'fa-paperclip'],
        'aportes' => ['label' => 'Aportes', 'icon' => 'fa-comments'],
    ];
    // Formato de los materiales: ícono + palabra; el color lo pone el área, no el formato.
    $tipoMeta = [
        'pdf' => ['label' => 'PDF', 'icon' => 'fa-file-pdf'],
        'doc' => ['label' => 'Word', 'icon' => 'fa-file-word'],
        'audio' => ['label' => 'Audio', 'icon' => 'fa-music'],
        'video' => ['label' => 'Video', 'icon' => 'fa-video'],
        'imagen' => ['label' => 'Imagen', 'icon' => 'fa-image'],
    ];
    $areaActual = $area ? $areasPorId->get($area) : null;
    // Chip de área de un resultado (o null si no tiene, p. ej. un aporte general).
    $areaItem = fn ($id) => ($a = $areaDe[$id] ?? null) ? $areasPorId->get($a) : null;
@endphp

@section('contenido')
    {{-- Cabecera baja: reusa .lib-hero (el menú transparente la reconoce) sin animación --}}
    <div class="lib-hero sisi-sr-hero">
        <img src="{{ asset('img/fondo/Fondo-sisipedia.png') }}" alt="" class="lib-hero__bg" loading="eager">
        <div class="lib-hero__overlay"></div>
        <div class="sisi-sr-hero__content">
            <h1 class="sisi-sr-hero__title">Buscar en Sisipedia</h1>
            <form action="{{ route('sisipedia.buscar') }}" method="GET" role="search" class="sisipedia-hero-search sisi-sr-hero__form">
                <div class="input-group shadow">
                    <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                    <input type="search" name="q" class="form-control" value="{{ $q }}" data-sisi-suggest
                           aria-label="Buscar en Sisipedia" placeholder="Busca un tema: plantas medicinales, fiestas, tejido..."
                           minlength="2" maxlength="120" autocomplete="off" required @if ($q === '') autofocus @endif>
                    <button class="btn fw-semibold px-3 px-md-4 text-white" type="submit">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="container sisi-sr">
        @if ($q === '')
            {{-- Sin término: invitación a buscar + áreas --}}
            <section class="sisi-sr-empty">
                <i class="fa fa-search" aria-hidden="true"></i>
                <h2 class="h5 fw-bold">¿Qué quieres encontrar?</h2>
                <p>Escribe arriba una palabra, por ejemplo <em>agua</em>, <em>fiesta</em> o <em>chakra</em>.</p>
                @include('sisichakuna.partials.buscar-explorar', ['temas' => $temas])
            </section>
        @elseif ($total === 0 && ! $area)
            {{-- Sin resultados: nunca una página vacía --}}
            <section class="sisi-sr-empty">
                <i class="fa fa-binoculars" aria-hidden="true"></i>
                <h2 class="h5 fw-bold">No encontramos «{{ $q }}»</h2>
                <ul class="sisi-sr-tips">
                    <li>Revisa que la palabra esté bien escrita.</li>
                    <li>Prueba con una sola palabra o con una más general (por ejemplo <em>animales</em> en vez de <em>animales del río</em>).</li>
                    <li>También puedes explorar por formato o por área:</li>
                </ul>
                @include('sisichakuna.partials.buscar-explorar', ['temas' => $temas])
            </section>
        @else
            <p class="sisi-sr-count" role="status">
                <strong>{{ $total }}</strong> {{ $total === 1 ? 'resultado' : 'resultados' }} para «{{ $q }}»
                @if ($areaActual)
                    en <span style="--area: {{ $areaActual->color }}" class="sisi-sr-count__area">{{ $areaActual->nombre }}</span>
                @endif
            </p>

            {{-- Pestañas: Temas / Materiales / Aportes (solo las que tienen resultados) --}}
            <nav class="sisi-sr-tabs" aria-label="Tipo de resultado">
                @foreach ($tabsMeta as $key => $meta)
                    @if ($totales[$key] > 0)
                        <a href="{{ route('sisipedia.buscar', array_filter(['q' => $q, 'tab' => $key, 'area' => $area])) }}"
                           class="{{ $tab === $key ? 'is-active' : '' }}" @if ($tab === $key) aria-current="page" @endif>
                            <i class="fa {{ $meta['icon'] }}" aria-hidden="true"></i>{{ $meta['label'] }}
                            <span>{{ $totales[$key] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            {{-- ¿De qué área? Cada área del saber con su color; filtra sin perder la búsqueda --}}
            @if ($areas->count() > 1 || $area)
                <nav class="sisi-sr-areas" aria-label="Filtrar por área">
                    <span class="sisi-sr-areas__label" id="sisiSrAreasLabel">Área:</span>
                    <a href="{{ route('sisipedia.buscar', ['q' => $q, 'tab' => $tab]) }}"
                       class="{{ $area ? '' : 'is-active' }}" @unless ($area) aria-current="true" @endunless>
                        Todas
                    </a>
                    @foreach ($areas as $a)
                        <a href="{{ route('sisipedia.buscar', ['q' => $q, 'tab' => $tab, 'area' => $a->id]) }}"
                           style="--area: {{ $a->color }}" class="{{ $area === $a->id ? 'is-active' : '' }}"
                           @if ($area === $a->id) aria-current="true" @endif>
                            <i aria-hidden="true"></i>{{ $a->nombre }}<span>{{ $a->resultados }}</span>
                        </a>
                    @endforeach
                </nav>
            @endif

            @if ($total === 0)
                <div class="sisi-sr-empty sisi-sr-empty--inline">
                    <p class="mb-2">No hay resultados de «{{ $q }}» en <strong>{{ $areaActual?->nombre }}</strong>.</p>
                    <a href="{{ route('sisipedia.buscar', ['q' => $q]) }}" class="sisi-sr-btn">Ver todas las áreas</a>
                </div>
            @else
                @if ($tab === 'materiales')
                    <p class="sisi-sr-help">
                        Toca <strong>Escuchar</strong> o <strong>Ver</strong> para abrirlo aquí mismo,
                        o <i class="fa fa-download" aria-hidden="true"></i><span class="visually-hidden">Descargar</span> para guardarlo en tu celular.
                    </p>
                @endif

                <ul class="sisi-sr-list" data-sisi-results>
                    @foreach ($resultados as $item)
                        @php $ar = $areaItem($item->id); @endphp
                        @if ($tab === 'registros')
                            @php
                                $tipos = $item->files->pluck('tipo')->unique();
                                $ruta = $Busqueda::rutaSinArea($item->parent);
                            @endphp
                            <li class="sisi-sr-row" @if ($ar) style="--area: {{ $ar->color }}" @endif>
                                <a href="{{ route('sisipedia.categories.show', $item) }}" class="sisi-sr-item">
                                    <span class="sisi-sr-item__body">
                                        <span class="sisi-sr-item__title">{!! $Busqueda::resaltar($Busqueda::nombre($item->name, $item->parent_id === null), $palabras) !!}</span>
                                        <span class="sisi-sr-item__where">
                                            @if ($ar)
                                                <span class="sisi-sr-area">{{ $item->parent_id === null ? 'Área principal' : $ar->nombre }}</span>
                                            @endif
                                            @if ($ruta !== '')
                                                <span class="sisi-sr-item__path">{{ $ruta }}</span>
                                            @endif
                                        </span>
                                        @if ($tipos->isNotEmpty() || $item->children_count || $item->aportaciones_count)
                                            <span class="sisi-sr-item__tags">
                                                @foreach ($tipos as $t)
                                                    @if (isset($tipoMeta[$t]))
                                                        <span><i class="fa {{ $tipoMeta[$t]['icon'] }}" aria-hidden="true"></i>{{ $tipoMeta[$t]['label'] }}</span>
                                                    @endif
                                                @endforeach
                                                @if ($item->children_count)
                                                    <span><i class="fa fa-folder-open" aria-hidden="true"></i>{{ $item->children_count }} {{ $item->children_count === 1 ? 'subtema' : 'subtemas' }}</span>
                                                @endif
                                                @if ($item->aportaciones_count)
                                                    <span><i class="fa fa-comments" aria-hidden="true"></i>{{ $item->aportaciones_count }} {{ $item->aportaciones_count === 1 ? 'aporte' : 'aportes' }}</span>
                                                @endif
                                            </span>
                                        @endif
                                    </span>
                                    <span class="sisi-sr-item__go"><span>Abrir</span><i class="fa fa-chevron-right" aria-hidden="true"></i></span>
                                </a>
                            </li>
                        @elseif ($tab === 'materiales')
                            @php
                                $m = $tipoMeta[$item->tipo] ?? ['label' => ucfirst($item->tipo), 'icon' => 'fa-file'];
                                $nombre = $item->nombre_display;
                                // La extensión sobra: el formato ya se dice en palabras ("Audio", "PDF").
                                $nombreVisible = preg_replace('/\.(mp3|m4a|wav|ogg|pdf|docx?|jpe?g|png|webp|mp4|mov)$/i', '', $nombre) ?: $nombre;
                                $driveUrl = \App\Services\GoogleDriveService::getUrl($item->drive_id);
                                $downloadUrl = \App\Services\GoogleDriveService::getEmbedUrl($item->drive_id);
                                $ruta = $item->category ? $Busqueda::rutaSinArea($item->category) : '';
                            @endphp
                            <li class="sisi-sr-row sisi-sr-file" @if ($ar) style="--area: {{ $ar->color }}" @endif>
                                <div class="sisi-sr-item sisi-sr-item--static">
                                    <span class="sisi-sr-item__icon" aria-hidden="true"><i class="fa {{ $m['icon'] }}"></i></span>
                                    <span class="sisi-sr-item__body">
                                        <span class="sisi-sr-item__title">{!! $Busqueda::resaltar($nombreVisible, $palabras) !!}</span>
                                        <span class="sisi-sr-item__where">
                                            <span class="sisi-sr-item__format">{{ $m['label'] }}</span>
                                            @if ($item->category)
                                                <a href="{{ route('sisipedia.categories.show', $item->category_id) }}" class="sisi-sr-item__path sisi-sr-item__path--link">
                                                    en {{ $ruta !== '' ? $ruta : $Busqueda::nombre($item->category->name, true) }}
                                                </a>
                                            @endif
                                        </span>
                                    </span>
                                    <span class="sisi-sr-item__actions">
                                        @if ($item->tipo === 'audio')
                                            <button type="button" class="sisi-sr-btn" data-play aria-expanded="false" aria-label="Escuchar {{ $nombre }}">
                                                <i class="fa fa-play" aria-hidden="true"></i><span class="sisi-sr-lbl">Escuchar</span>
                                            </button>
                                        @elseif ($item->tipo === 'pdf')
                                            <button type="button" class="sisi-sr-btn sisipedia-open-preview-modal"
                                                    data-preview="{{ \App\Services\GoogleDriveService::getPreviewUrl($item->drive_id) }}"
                                                    data-nombre="{{ $nombre }}" data-drive="{{ $driveUrl }}" data-download="{{ $downloadUrl }}"
                                                    aria-label="Ver {{ $nombre }}">
                                                <i class="fa fa-eye" aria-hidden="true"></i><span class="sisi-sr-lbl">Ver</span>
                                            </button>
                                        @elseif ($item->tipo === 'imagen')
                                            <button type="button" class="sisi-sr-btn" data-img
                                                    data-full-url="{{ \App\Services\GoogleDriveService::getThumbnailUrl($item->drive_id, 1400) }}"
                                                    data-nombre="{{ $nombre }}" aria-label="Ver {{ $nombre }}">
                                                <i class="fa fa-eye" aria-hidden="true"></i><span class="sisi-sr-lbl">Ver</span>
                                            </button>
                                        @elseif ($item->tipo === 'video')
                                            <button type="button" class="sisi-sr-btn"
                                                    onclick="abrirVistaPreviaVideo({{ \Illuminate\Support\Js::from(route('sisipedia.video.stream', $item)) }}, {{ \Illuminate\Support\Js::from($nombre) }}, {{ \Illuminate\Support\Js::from($driveUrl) }})"
                                                    aria-label="Ver video {{ $nombre }}">
                                                <i class="fa fa-play" aria-hidden="true"></i><span class="sisi-sr-lbl">Ver</span>
                                            </button>
                                        @else
                                            <a href="{{ $driveUrl }}" target="_blank" rel="noopener" class="sisi-sr-btn" aria-label="Abrir {{ $nombre }} en Google Drive">
                                                <i class="fa fa-external-link-alt" aria-hidden="true"></i><span class="sisi-sr-lbl">Abrir</span>
                                            </a>
                                        @endif
                                        <a href="{{ $downloadUrl }}" class="sisi-sr-btn sisi-sr-btn--solid" title="Descargar" aria-label="Descargar {{ $nombre }}">
                                            <i class="fa fa-download" aria-hidden="true"></i>
                                        </a>
                                    </span>
                                </div>
                                @if ($item->tipo === 'audio')
                                    <div class="sisi-sr-player" hidden>
                                        @include('partials.audio-player', [
                                            'src' => route('sisipedia.audio.stream', $item),
                                            'title' => $nombre,
                                            'driveUrl' => $driveUrl,
                                            'downloadUrl' => $downloadUrl,
                                        ])
                                    </div>
                                @endif
                            </li>
                        @else
                            @php
                                $titulo = $item->titulo ?: 'Aporte de ' . $item->nombre_ol;
                                $tiposAp = $item->archivos->pluck('tipo')->unique();
                                $destino = $item->category_id
                                    ? route('sisipedia.categories.show', $item->category_id) . '#sisipedia-aportaciones-section'
                                    : route('public.sisi') . '#sisipedia-tree-aportaciones-section';
                                $ruta = $item->category ? $Busqueda::rutaSinArea($item->category) : '';
                            @endphp
                            <li class="sisi-sr-row" @if ($ar) style="--area: {{ $ar->color }}" @endif>
                                <a href="{{ $destino }}" class="sisi-sr-item">
                                    <span class="sisi-sr-item__body">
                                        <span class="sisi-sr-item__title">{!! $Busqueda::resaltar($titulo, $palabras) !!}</span>
                                        <span class="sisi-sr-item__who">
                                            Compartido por {!! $Busqueda::resaltar($item->nombre_ol, $palabras) !!}@if ($item->rol_nombre), {{ mb_strtolower(\App\Models\sisipedia\Aportacion::etiquetaRol($item->rol_nombre)) }}@endif
                                        </span>
                                        <span class="sisi-sr-item__where">
                                            <span class="sisi-sr-area">{{ $ar ? $ar->nombre : 'Aporte general' }}</span>
                                            @if ($ruta !== '')
                                                <span class="sisi-sr-item__path">{{ $ruta }}</span>
                                            @endif
                                        </span>
                                        @if ($item->detalle)
                                            <span class="sisi-sr-item__snippet">{!! $Busqueda::resaltar($Busqueda::fragmento($item->detalle, $palabras), $palabras) !!}</span>
                                        @endif
                                        @if ($tiposAp->isNotEmpty())
                                            <span class="sisi-sr-item__tags">
                                                @foreach ($tiposAp as $t)
                                                    @if (isset($tipoMeta[$t]))
                                                        <span><i class="fa {{ $tipoMeta[$t]['icon'] }}" aria-hidden="true"></i>{{ $tipoMeta[$t]['label'] }}</span>
                                                    @endif
                                                @endforeach
                                            </span>
                                        @endif
                                    </span>
                                    <span class="sisi-sr-item__go"><span>Ver aporte</span><i class="fa fa-chevron-right" aria-hidden="true"></i></span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>

                @if ($resultados->hasPages())
                    <nav class="sisi-sr-pages" aria-label="Más resultados">
                        @if ($resultados->onFirstPage())
                            <span class="sisi-sr-btn is-disabled" aria-disabled="true"><i class="fa fa-chevron-left" aria-hidden="true"></i>Anteriores</span>
                        @else
                            <a class="sisi-sr-btn" href="{{ $resultados->previousPageUrl() }}"><i class="fa fa-chevron-left" aria-hidden="true"></i>Anteriores</a>
                        @endif
                        <span class="sisi-sr-pages__info">Página {{ $resultados->currentPage() }} de {{ $resultados->lastPage() }}</span>
                        @if ($resultados->hasMorePages())
                            <a class="sisi-sr-btn sisi-sr-btn--solid" href="{{ $resultados->nextPageUrl() }}">Siguientes<i class="fa fa-chevron-right" aria-hidden="true"></i></a>
                        @else
                            <span class="sisi-sr-btn is-disabled" aria-disabled="true">Siguientes<i class="fa fa-chevron-right" aria-hidden="true"></i></span>
                        @endif
                    </nav>
                @endif
            @endif
        @endif
    </div>

    {{-- Modal de imagen (resultados de materiales) --}}
    <div class="modal fade" id="sisiSrImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title text-truncate"><i class="fa fa-image me-1"></i><span data-img-name></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-0 text-center bg-dark">
                    <img src="" alt="" class="img-fluid" style="max-height:80vh;" data-img-el>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var list = document.querySelector('[data-sisi-results]');
            if (!list) return;

            // "Escuchar": abre el reproductor de esa fila (uno a la vez) y lo inicia.
            list.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-play]');
                if (btn) {
                    var li = btn.closest('.sisi-sr-file');
                    var player = li.querySelector('.sisi-sr-player');
                    var toggle = player.querySelector('.audio-player-toggle');
                    if (!player.hidden) { if (toggle) toggle.click(); return; }
                    list.querySelectorAll('.sisi-sr-player:not([hidden])').forEach(function (p) {
                        var a = p.querySelector('audio'); if (a) a.pause();
                        p.hidden = true;
                        var b = p.closest('.sisi-sr-file').querySelector('[data-play]');
                        b.setAttribute('aria-expanded', 'false'); b.classList.remove('is-on');
                    });
                    player.hidden = false;
                    btn.setAttribute('aria-expanded', 'true'); btn.classList.add('is-on');
                    if (toggle) toggle.click();
                    return;
                }
                var img = e.target.closest('[data-img]');
                if (img) {
                    var modal = document.getElementById('sisiSrImageModal');
                    modal.querySelector('[data-img-el]').src = img.dataset.fullUrl;
                    modal.querySelector('[data-img-name]').textContent = img.dataset.nombre || 'Imagen';
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
            document.getElementById('sisiSrImageModal').addEventListener('hidden.bs.modal', function () {
                this.querySelector('[data-img-el]').src = '';
            });
        })();
    </script>

    <style>
        /* Tokens: el café de Sisipedia/Radio y una tinta neutra; cada fila recibe --area. */
        .sisi-sr, .sisi-sr-hero {
            --sr-accent: var(--project-accent, #6c3f17);
            --sr-ink: #2b2420;
            --sr-muted: #6b625a;
            --sr-line: #e7e1da;
        }

        /* ── Cabecera baja ─────────────────────────────────────── */
        .lib-hero.sisi-sr-hero { min-height: calc(160px + var(--puklla-nav-h)); }
        .sisi-sr-hero__content {
            position: absolute; inset: 0; z-index: 2; display: flex; flex-direction: column;
            align-items: center; justify-content: center; text-align: center; color: #fff;
            padding: calc(var(--puklla-nav-h) + .5rem) 1rem 1.25rem;
        }
        .sisi-sr-hero__title {
            font-size: clamp(1.15rem, 2.6vw, 1.5rem); font-weight: 700; margin: 0 0 .75rem;
            text-shadow: 0 1px 3px rgba(0,0,0,.45);
        }
        .sisi-sr-hero__form { width: 100%; max-width: 640px; margin: 0 auto; }
        .sisi-sr-hero__form .form-control { font-size: 1rem; } /* 16px: evita el zoom de iOS */
        @media (max-width: 575.98px) {
            /* En celular la cabecera solo sostiene el buscador: los resultados deben verse sin bajar */
            /* height: auto anula el alto fijo de .lib-hero en celular (estilos.css) */
            .lib-hero.sisi-sr-hero { height: auto; min-height: calc(118px + var(--puklla-nav-h)); }
            .sisi-sr-hero__title { font-size: 1.05rem; margin-bottom: .5rem; }
        }

        /* ── Contenido ─────────────────────────────────────────── */
        .sisi-sr { max-width: 880px; padding-top: 1.25rem; padding-bottom: 3rem; color: var(--sr-ink); }
        .sisi-sr-count { font-size: 1rem; margin: 0 0 .75rem; overflow-wrap: anywhere; }
        .sisi-sr-count__area { color: var(--area); font-weight: 700; }

        /* Pestañas (tipo de resultado) */
        .sisi-sr-tabs { display: flex; gap: .4rem; overflow-x: auto; scrollbar-width: none; padding-bottom: .25rem; margin-bottom: .6rem; }
        .sisi-sr-tabs::-webkit-scrollbar { display: none; }
        .sisi-sr-tabs > a {
            flex-shrink: 0; display: inline-flex; align-items: center; gap: .45rem; min-height: 2.75rem;
            padding: .45rem 1rem; border-radius: 99px; font-size: .95rem; font-weight: 600; text-decoration: none;
            background: #fff; color: var(--sr-ink); border: 1px solid var(--sr-line);
            transition: border-color .15s ease, background-color .15s ease, color .15s ease;
        }
        .sisi-sr-tabs > a > span { font-size: .78rem; padding: .05rem .5rem; border-radius: 99px; background: #f3efea; }
        .sisi-sr-tabs > a.is-active { background: var(--sr-accent); border-color: var(--sr-accent); color: #fff; }
        .sisi-sr-tabs > a.is-active > span { background: rgba(255,255,255,.22); color: #fff; }

        /* Áreas del saber: chips con el color de cada área */
        .sisi-sr-areas {
            display: flex; align-items: center; gap: .35rem; overflow-x: auto; scrollbar-width: none;
            padding: .15rem 0 .35rem; margin-bottom: .9rem;
            -webkit-mask-image: linear-gradient(90deg, #000 92%, transparent); mask-image: linear-gradient(90deg, #000 92%, transparent);
        }
        .sisi-sr-areas::-webkit-scrollbar { display: none; }
        /* Con espacio de sobra, los chips bajan de línea en vez de esconderse tras el difuminado */
        @media (min-width: 768px) {
            .sisi-sr-areas { flex-wrap: wrap; overflow: visible; -webkit-mask-image: none; mask-image: none; }
        }
        .sisi-sr-areas__label { flex-shrink: 0; font-size: .85rem; color: var(--sr-muted); margin-right: .15rem; }
        .sisi-sr-areas > a {
            --area: var(--sr-ink);
            flex-shrink: 0; display: inline-flex; align-items: center; gap: .4rem; min-height: 2.5rem;
            padding: .3rem .8rem; border-radius: 99px; font-size: .85rem; font-weight: 600; text-decoration: none;
            color: var(--sr-ink); background: #fff; border: 1px solid var(--sr-line);
            transition: border-color .15s ease, background-color .15s ease, color .15s ease;
        }
        .sisi-sr-areas > a > i { width: .6rem; height: .6rem; border-radius: 50%; background: var(--area); }
        .sisi-sr-areas > a > span { font-size: .75rem; color: var(--sr-muted); font-weight: 600; }
        .sisi-sr-areas > a.is-active { background: var(--area); border-color: var(--area); color: #fff; }
        .sisi-sr-areas > a.is-active > i { background: #fff; }
        .sisi-sr-areas > a.is-active > span { color: rgba(255,255,255,.85); }

        .sisi-sr-help { font-size: .9rem; color: var(--sr-muted); margin: 0 0 .75rem; }

        /* Lista: cada fila lleva el hilo de color de su área a la izquierda */
        .sisi-sr-list { list-style: none; margin: 0; padding: 0; background: #fff; border-radius: 1rem; overflow: hidden; border: 1px solid var(--sr-line); }
        .sisi-sr-row { --area: #9b9189; position: relative; }
        .sisi-sr-row::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--area); }
        .sisi-sr-row + .sisi-sr-row { border-top: 1px solid var(--sr-line); }

        .sisi-sr-item {
            display: flex; align-items: center; gap: .85rem; padding: .9rem 1rem .9rem 1.25rem;
            color: inherit; text-decoration: none; transition: background-color .15s ease;
        }
        a.sisi-sr-item:focus-visible { outline: 2px solid var(--sr-accent); outline-offset: -2px; }
        @media (hover: hover) {
            a.sisi-sr-item:hover { background: color-mix(in srgb, var(--area) 7%, #fff); color: inherit; }
            a.sisi-sr-item:hover .sisi-sr-item__go { color: var(--area); }
            a.sisi-sr-item:hover .sisi-sr-item__go i { transform: translateX(3px); }
        }
        a.sisi-sr-item:active { background: color-mix(in srgb, var(--area) 12%, #fff); }

        .sisi-sr-item__icon {
            flex-shrink: 0; width: 2.5rem; height: 2.5rem; border-radius: .7rem; display: inline-flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--area) 12%, #fff); color: var(--area);
        }
        .sisi-sr-item__body { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: .2rem; }
        .sisi-sr-item__title { font-weight: 700; font-size: 1.05rem; line-height: 1.3; overflow-wrap: anywhere; }
        .sisi-sr-item__where { display: flex; flex-wrap: wrap; align-items: baseline; gap: .15rem .5rem; font-size: .85rem; color: var(--sr-muted); min-width: 0; }
        .sisi-sr-area { color: var(--area); font-weight: 700; }
        .sisi-sr-item__format { font-weight: 700; color: var(--sr-ink); }
        .sisi-sr-item__path { overflow-wrap: anywhere; min-width: 0; }
        .sisi-sr-item__path--link { color: var(--sr-muted); text-decoration: underline; text-decoration-color: var(--sr-line); text-underline-offset: 3px; }
        .sisi-sr-item__path--link:hover { color: var(--area); text-decoration-color: currentColor; }
        .sisi-sr-item__who { font-size: .85rem; color: var(--sr-ink); overflow-wrap: anywhere; }
        .sisi-sr-item__snippet { font-size: .88rem; color: var(--sr-muted); overflow-wrap: anywhere; }
        .sisi-sr-item__tags { display: flex; flex-wrap: wrap; gap: .2rem .9rem; font-size: .8rem; color: var(--sr-muted); margin-top: .1rem; }
        .sisi-sr-item__tags i { margin-right: .3rem; }
        .sisi-sr-item__go {
            flex-shrink: 0; display: inline-flex; align-items: center; gap: .4rem;
            font-size: .85rem; font-weight: 600; color: var(--sr-muted); transition: color .15s ease;
        }
        .sisi-sr-item__go i { font-size: .75rem; transition: transform .15s ease; }

        /* Botones propios (sin los colores sueltos de Bootstrap) */
        .sisi-sr-item__actions { display: flex; gap: .4rem; flex-shrink: 0; }
        .sisi-sr-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
            min-height: 2.75rem; min-width: 2.75rem; padding: .4rem .9rem; border-radius: .7rem;
            font-size: .9rem; font-weight: 600; text-decoration: none; cursor: pointer;
            background: #fff; color: var(--sr-accent); border: 1.5px solid var(--sr-accent);
            transition: background-color .15s ease, color .15s ease, transform .15s ease;
        }
        .sisi-sr-btn--solid { background: var(--sr-accent); color: #fff; }
        .sisi-sr-btn:focus-visible { outline: 2px solid var(--sr-accent); outline-offset: 2px; }
        @media (hover: hover) {
            .sisi-sr-btn:hover { background: var(--sr-accent); color: #fff; transform: translateY(-1px); }
            .sisi-sr-btn--solid:hover { background: color-mix(in srgb, var(--sr-accent) 82%, #000); }
        }
        .sisi-sr-btn:active, .sisi-sr-btn.is-on { background: var(--sr-accent); color: #fff; transform: none; }
        .sisi-sr-btn.is-disabled { opacity: .45; pointer-events: none; }

        .sisi-sr-player { padding: 0 1rem .8rem 1.25rem; background: #faf8f5; }
        .sisi-sr-player[hidden] { display: none; }
        .sisi-sr-list mark { background: #fbe7a1; color: inherit; padding: 0 .1rem; border-radius: .2rem; }

        .sisi-sr-pages { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-top: 1rem; }
        .sisi-sr-pages__info { font-size: .9rem; color: var(--sr-muted); text-align: center; }

        @media (max-width: 575.98px) {
            .sisi-sr-item { padding: .85rem .85rem .85rem 1.1rem; gap: .65rem; }
            .sisi-sr-item__go > span { display: none; } /* en celular basta la flecha: la fila entera se toca */
            .sisi-sr-item--static { flex-wrap: wrap; }
            .sisi-sr-item--static .sisi-sr-item__body { flex-basis: calc(100% - 3.25rem); }
            .sisi-sr-item--static .sisi-sr-item__actions { margin-left: 3.15rem; }
        }

        /* ── Sin resultados / sin término ──────────────────────── */
        .sisi-sr-empty { background: #fff; border-radius: 1rem; padding: 2rem 1.25rem; text-align: center; border: 1px solid var(--sr-line); }
        .sisi-sr-empty--inline { padding: 1.5rem 1rem; }
        .sisi-sr-empty > i { font-size: 2.25rem; color: #b7aca2; margin-bottom: .75rem; }
        .sisi-sr-empty > p { color: var(--sr-muted); }
        .sisi-sr-tips { list-style: none; padding: 0; margin: 0 auto 1.25rem; max-width: 520px; font-size: .95rem; }
        .sisi-sr-tips li { padding: .25rem 0; }
        .sisi-sr-explore__label { font-size: .95rem; font-weight: 700; color: var(--sr-ink); margin: 1.25rem 0 .6rem; }
        .sisi-sr-explore__formats, .sisi-sr-explore__temas { display: flex; flex-wrap: wrap; justify-content: center; gap: .5rem; }
        .sisi-sr-explore__formats a, .sisi-sr-explore__temas a {
            --area: var(--sr-accent);
            display: inline-flex; align-items: center; gap: .45rem; min-height: 2.75rem; padding: .4rem .95rem;
            border-radius: 99px; border: 1px solid var(--sr-line); background: #fff; color: var(--sr-ink);
            font-size: .9rem; font-weight: 600; text-decoration: none; transition: border-color .15s ease, color .15s ease;
        }
        .sisi-sr-explore__formats a i, .sisi-sr-explore__temas a i { color: var(--area); }
        .sisi-sr-explore__temas a i { width: .6rem; height: .6rem; border-radius: 50%; background: var(--area); }
        .sisi-sr-explore__formats a:hover, .sisi-sr-explore__temas a:hover { border-color: var(--area); color: var(--area); }
    </style>

    @include('sisichakuna.partials.buscar-sugerencias')
    @include('partials.home-preview-modal')
@endsection
