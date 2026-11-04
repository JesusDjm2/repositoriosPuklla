@extends('layouts.padre')
@section('titulo', '- ' . ($q !== '' ? 'Resultados para «' . $q . '»' : 'Buscar en Sisipedia'))

@php
    // Helpers estáticos (resaltar/ruta/fragmento); un `use` aquí rompería la vista compilada.
    $Busqueda = \App\Http\Controllers\sisipedia\BusquedaController::class;

    $total = array_sum($totales);
    $tabsMeta = [
        'registros' => ['label' => 'Temas', 'icon' => 'fa-folder-open'],
        'materiales' => ['label' => 'Materiales', 'icon' => 'fa-paperclip'],
        'aportes' => ['label' => 'Aportes', 'icon' => 'fa-comments'],
    ];
    $tipoMeta = [
        'pdf' => ['label' => 'PDF', 'color' => 'danger', 'icon' => 'fa-file-pdf'],
        'doc' => ['label' => 'Word', 'color' => 'primary', 'icon' => 'fa-file-word'],
        'audio' => ['label' => 'Audio', 'color' => 'success', 'icon' => 'fa-music'],
        'video' => ['label' => 'Video', 'color' => 'warning', 'icon' => 'fa-video'],
        'imagen' => ['label' => 'Imagen', 'color' => 'info', 'icon' => 'fa-image'],
    ];
@endphp

@section('contenido')
    {{-- Hero compacto: reusa .lib-hero (el menú transparente lo reconoce) pero bajo y sin animación --}}
    <div class="lib-hero sisi-sr-hero">
        <img src="{{ asset('img/fondo/Fondo-sisipedia.png') }}" alt="" class="lib-hero__bg" loading="eager">
        <div class="lib-hero__overlay"></div>
        <div class="sisi-sr-hero__content">
            <p class="sisi-sr-hero__eyebrow">Buscar en Sisipedia</p>
            <form action="{{ route('sisipedia.buscar') }}" method="GET" role="search" class="sisipedia-hero-search sisi-sr-hero__form">
                <div class="input-group shadow">
                    <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                    <input type="search" name="q" class="form-control" value="{{ $q }}" data-sisi-suggest
                           aria-label="Buscar en Sisipedia" placeholder="Busca un tema: plantas medicinales, fiestas, tejido..."
                           minlength="2" maxlength="120" autocomplete="off" required @if ($q === '') autofocus @endif>
                    <button class="btn fw-semibold px-3 px-md-4 text-white" type="submit">Buscar</button>
                </div>
            </form>
            @if ($q !== '')
                <p class="sisi-sr-hero__count" role="status">
                    @if ($total > 0)
                        <strong>{{ $total }}</strong> {{ $total === 1 ? 'resultado' : 'resultados' }} para «{{ $q }}»
                    @else
                        No encontramos resultados para «{{ $q }}»
                    @endif
                </p>
            @endif
        </div>
    </div>

    <div class="container sisi-sr">
        @if ($q === '')
            {{-- Sin término: invitación a buscar + temas principales --}}
            <section class="sisi-sr-empty">
                <i class="fa fa-search" aria-hidden="true"></i>
                <h1 class="h5 fw-bold">¿Qué quieres encontrar?</h1>
                <p>Escribe arriba una palabra, por ejemplo <em>agua</em>, <em>fiesta</em> o <em>chakra</em>.</p>
                @include('sisichakuna.partials.buscar-explorar', ['temas' => $temas])
            </section>
        @elseif ($total === 0)
            {{-- Sin resultados: nunca una página vacía --}}
            <section class="sisi-sr-empty">
                <i class="fa fa-binoculars" aria-hidden="true"></i>
                <h1 class="h5 fw-bold">No encontramos «{{ $q }}»</h1>
                <ul class="sisi-sr-tips">
                    <li>Revisa que la palabra esté bien escrita.</li>
                    <li>Prueba con una sola palabra o con una más general (por ejemplo <em>animales</em> en vez de <em>animales del río</em>).</li>
                    <li>También puedes explorar por formato o por tema:</li>
                </ul>
                @include('sisichakuna.partials.buscar-explorar', ['temas' => $temas])
            </section>
        @else
            {{-- Pestañas: Temas / Materiales / Aportes, con su conteo --}}
            <nav class="sisi-sr-tabs" aria-label="Tipo de resultado">
                @foreach ($tabsMeta as $key => $meta)
                    {{-- Solo las pestañas con resultados: una pestaña en 0 no ayuda y en celular empuja las demás --}}
                    @if ($totales[$key] > 0)
                        <a href="{{ route('sisipedia.buscar', ['q' => $q, 'tab' => $key]) }}"
                           class="{{ $tab === $key ? 'is-active' : '' }}" @if ($tab === $key) aria-current="page" @endif>
                            <i class="fa {{ $meta['icon'] }}" aria-hidden="true"></i>{{ $meta['label'] }}
                            <span>{{ $totales[$key] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            @if ($tab === 'materiales')
                <p class="sisi-sr-help">
                    <i class="fa fa-play" aria-hidden="true"></i> Escuchar o ver ·
                    <i class="fa fa-eye" aria-hidden="true"></i> Ver documento ·
                    <i class="fa fa-download" aria-hidden="true"></i> Descargar ·
                    toca la carpeta gris para ir a su tema.
                </p>
            @endif
            <ul class="sisi-sr-list" data-sisi-results>
                @foreach ($resultados as $item)
                    @if ($tab === 'registros')
                        @php $tipos = $item->files->pluck('tipo')->unique(); @endphp
                        <li>
                            <a href="{{ route('sisipedia.categories.show', $item) }}" class="sisi-sr-item">
                                <span class="sisi-sr-item__icon bg-primary bg-opacity-10 text-primary" aria-hidden="true"><i class="fa fa-folder"></i></span>
                                <span class="sisi-sr-item__body">
                                    <span class="sisi-sr-item__title">{!! $Busqueda::resaltar($item->display_name, $palabras) !!}</span>
                                    @if ($item->parent)
                                        <span class="sisi-sr-item__path">{{ $Busqueda::ruta($item->parent) }}</span>
                                    @endif
                                    @if ($tipos->isNotEmpty() || $item->children_count || $item->aportaciones_count)
                                        <span class="sisi-sr-item__tags">
                                            @foreach ($tipos as $t)
                                                @if (isset($tipoMeta[$t]))
                                                    <span class="text-{{ $tipoMeta[$t]['color'] }}"><i class="fa {{ $tipoMeta[$t]['icon'] }}" aria-hidden="true"></i>{{ $tipoMeta[$t]['label'] }}</span>
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
                                <i class="fa fa-chevron-right sisi-sr-item__chev" aria-hidden="true"></i>
                            </a>
                        </li>
                    @elseif ($tab === 'materiales')
                        @php
                            $m = $tipoMeta[$item->tipo] ?? ['label' => ucfirst($item->tipo), 'color' => 'secondary', 'icon' => 'fa-file'];
                            $nombre = $item->nombre_display;
                            $driveUrl = \App\Services\GoogleDriveService::getUrl($item->drive_id);
                            $downloadUrl = \App\Services\GoogleDriveService::getEmbedUrl($item->drive_id);
                        @endphp
                        <li class="sisi-sr-file">
                            <div class="sisi-sr-item sisi-sr-item--static">
                                <span class="sisi-sr-item__icon bg-{{ $m['color'] }} bg-opacity-10 text-{{ $m['color'] }}" aria-hidden="true"><i class="fa {{ $m['icon'] }}"></i></span>
                                <span class="sisi-sr-item__body">
                                    <span class="sisi-sr-item__title">{!! $Busqueda::resaltar($nombre, $palabras) !!}</span>
                                    @if ($item->category)
                                        <a href="{{ route('sisipedia.categories.show', $item->category_id) }}" class="sisi-sr-item__path sisi-sr-item__path--link">
                                            <i class="fa fa-folder me-1" aria-hidden="true"></i>{{ $Busqueda::ruta($item->category) }}
                                        </a>
                                    @endif
                                </span>
                                <span class="sisi-sr-item__actions">
                                    @if ($item->tipo === 'audio')
                                        <button type="button" class="btn btn-sm btn-outline-success" data-play aria-expanded="false" aria-label="Escuchar {{ $nombre }}">
                                            <i class="fa fa-play" aria-hidden="true"></i><span class="sisi-sr-lbl">Escuchar</span>
                                        </button>
                                    @elseif ($item->tipo === 'pdf')
                                        <button type="button" class="btn btn-sm btn-outline-danger sisipedia-open-preview-modal"
                                                data-preview="{{ \App\Services\GoogleDriveService::getPreviewUrl($item->drive_id) }}"
                                                data-nombre="{{ $nombre }}" data-drive="{{ $driveUrl }}" data-download="{{ $downloadUrl }}"
                                                aria-label="Ver {{ $nombre }}">
                                            <i class="fa fa-eye" aria-hidden="true"></i><span class="sisi-sr-lbl">Ver</span>
                                        </button>
                                    @elseif ($item->tipo === 'imagen')
                                        <button type="button" class="btn btn-sm btn-outline-info" data-img
                                                data-full-url="{{ \App\Services\GoogleDriveService::getThumbnailUrl($item->drive_id, 1400) }}"
                                                data-nombre="{{ $nombre }}" aria-label="Ver {{ $nombre }}">
                                            <i class="fa fa-eye" aria-hidden="true"></i><span class="sisi-sr-lbl">Ver</span>
                                        </button>
                                    @elseif ($item->tipo === 'video')
                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                                onclick="abrirVistaPreviaVideo({{ \Illuminate\Support\Js::from(route('sisipedia.video.stream', $item)) }}, {{ \Illuminate\Support\Js::from($nombre) }}, {{ \Illuminate\Support\Js::from($driveUrl) }})"
                                                aria-label="Ver video {{ $nombre }}">
                                            <i class="fa fa-play" aria-hidden="true"></i><span class="sisi-sr-lbl">Ver</span>
                                        </button>
                                    @else
                                        <a href="{{ $driveUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" aria-label="Abrir {{ $nombre }}">
                                            <i class="fa fa-external-link-alt" aria-hidden="true"></i><span class="sisi-sr-lbl">Abrir</span>
                                        </a>
                                    @endif
                                    <a href="{{ $downloadUrl }}" class="btn btn-sm btn-{{ $m['color'] }}" title="Descargar" aria-label="Descargar {{ $nombre }}">
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
                        @endphp
                        <li>
                            <a href="{{ $destino }}" class="sisi-sr-item">
                                <span class="sisi-sr-item__icon bg-warning bg-opacity-10 text-warning" aria-hidden="true"><i class="fa fa-comments"></i></span>
                                <span class="sisi-sr-item__body">
                                    <span class="sisi-sr-item__title">{!! $Busqueda::resaltar($titulo, $palabras) !!}</span>
                                    <span class="sisi-sr-item__path">
                                        {!! $Busqueda::resaltar($item->nombre_ol, $palabras) !!}
                                        @if ($item->rol_nombre) · {{ \App\Models\sisipedia\Aportacion::etiquetaRol($item->rol_nombre) }} @endif
                                        · {{ $item->category ? $Busqueda::ruta($item->category) : 'Aporte general' }}
                                    </span>
                                    @if ($item->detalle)
                                        <span class="sisi-sr-item__snippet">{!! $Busqueda::resaltar($Busqueda::fragmento($item->detalle, $palabras), $palabras) !!}</span>
                                    @endif
                                    @if ($tiposAp->isNotEmpty())
                                        <span class="sisi-sr-item__tags">
                                            @foreach ($tiposAp as $t)
                                                @if (isset($tipoMeta[$t]))
                                                    <span class="text-{{ $tipoMeta[$t]['color'] }}"><i class="fa {{ $tipoMeta[$t]['icon'] }}" aria-hidden="true"></i>{{ $tipoMeta[$t]['label'] }}</span>
                                                @endif
                                            @endforeach
                                        </span>
                                    @endif
                                </span>
                                <i class="fa fa-chevron-right sisi-sr-item__chev" aria-hidden="true"></i>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>

            @if ($resultados->hasPages())
                <nav class="sisi-sr-pages" aria-label="Más resultados">
                    @if ($resultados->onFirstPage())
                        <span class="btn btn-light disabled"><i class="fa fa-chevron-left me-1"></i>Anteriores</span>
                    @else
                        <a class="btn btn-light" href="{{ $resultados->previousPageUrl() }}"><i class="fa fa-chevron-left me-1"></i>Anteriores</a>
                    @endif
                    <span class="sisi-sr-pages__info">Página {{ $resultados->currentPage() }} de {{ $resultados->lastPage() }}</span>
                    @if ($resultados->hasMorePages())
                        <a class="btn btn-primary" href="{{ $resultados->nextPageUrl() }}">Siguientes<i class="fa fa-chevron-right ms-1"></i></a>
                    @else
                        <span class="btn btn-light disabled">Siguientes<i class="fa fa-chevron-right ms-1"></i></span>
                    @endif
                </nav>
            @endif
        @endif
    </div>

    {{-- Modal de imagen (resultados de materiales) --}}
    <div class="modal fade" id="sisiSrImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 overflow-hidden">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title text-truncate"><i class="fa fa-image text-info me-1"></i><span data-img-name></span></h6>
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
                        b.setAttribute('aria-expanded', 'false'); b.classList.remove('active');
                    });
                    player.hidden = false;
                    btn.setAttribute('aria-expanded', 'true'); btn.classList.add('active');
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
        /* ── Hero compacto ─────────────────────────────────────── */
        .lib-hero.sisi-sr-hero { min-height: calc(210px + var(--puklla-nav-h)); }
        .sisi-sr-hero__content {
            position: absolute; inset: 0; z-index: 2; display: flex; flex-direction: column;
            align-items: center; justify-content: center; text-align: center; color: #fff;
            padding: calc(var(--puklla-nav-h) + .75rem) 1rem 1.25rem;
        }
        .sisi-sr-hero__eyebrow { font-size: .75rem; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; opacity: .85; margin-bottom: .6rem; }
        .sisi-sr-hero__form { width: 100%; max-width: 640px; margin: 0 auto; }
        .sisi-sr-hero__count { margin: .75rem 0 0; font-size: .95rem; text-shadow: 0 1px 3px rgba(0,0,0,.5); overflow-wrap: anywhere; }

        /* ── Contenido ─────────────────────────────────────────── */
        .sisi-sr { max-width: 860px; padding-top: 1.25rem; padding-bottom: 3rem; }
        .sisi-sr-tabs {
            display: flex; gap: .4rem; overflow-x: auto; scrollbar-width: none;
            padding-bottom: .25rem; margin-bottom: 1rem;
        }
        .sisi-sr-tabs::-webkit-scrollbar { display: none; }
        .sisi-sr-tabs > a, .sisi-sr-tabs > span {
            flex-shrink: 0; display: inline-flex; align-items: center; gap: .45rem; min-height: 2.6rem;
            padding: .45rem 1rem; border-radius: 99px; font-size: .9rem; font-weight: 600; text-decoration: none;
            background: #fff; color: #495057; border: 1px solid #dee2e6;
        }
        .sisi-sr-tabs > a > span, .sisi-sr-tabs > span > span {
            font-size: .75rem; padding: .05rem .5rem; border-radius: 99px; background: #f1f3f5; color: #495057;
        }
        .sisi-sr-tabs > a.is-active { background: #0d6efd; border-color: #0d6efd; color: #fff; }
        .sisi-sr-tabs > a.is-active > span { background: rgba(255,255,255,.25); color: #fff; }
        .sisi-sr-help { font-size: .82rem; color: #6c757d; margin: -.25rem 0 .75rem; }
        .sisi-sr-help i { color: #495057; margin: 0 .15rem; }

        .sisi-sr-list {
            list-style: none; margin: 0; padding: 0; background: #fff; border-radius: 1rem; overflow: hidden;
            box-shadow: 0 1px 2px rgba(16,24,40,.05), 0 4px 16px rgba(16,24,40,.06);
        }
        .sisi-sr-list > li + li { border-top: 1px solid #e9ecef; }
        .sisi-sr-item {
            display: flex; align-items: center; gap: .75rem; padding: .8rem 1rem;
            color: inherit; text-decoration: none; transition: background-color .15s ease;
        }
        a.sisi-sr-item:hover, a.sisi-sr-item:focus-visible { background: #f5f8ff; }
        a.sisi-sr-item:hover .sisi-sr-item__chev { transform: translateX(3px); color: #0d6efd; }
        .sisi-sr-item__icon {
            flex-shrink: 0; width: 2.5rem; height: 2.5rem; border-radius: .7rem;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .sisi-sr-item__body { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: .15rem; }
        .sisi-sr-item__title { font-weight: 700; color: #212529; font-size: .95rem; line-height: 1.3; overflow-wrap: anywhere; }
        .sisi-sr-item__path { font-size: .78rem; color: #6c757d; overflow-wrap: anywhere; }
        .sisi-sr-item__path--link { text-decoration: none; }
        .sisi-sr-item__path--link:hover { color: #0d6efd; text-decoration: underline; }
        .sisi-sr-item__snippet { font-size: .82rem; color: #495057; overflow-wrap: anywhere; }
        .sisi-sr-item__tags { display: flex; flex-wrap: wrap; gap: .2rem .8rem; font-size: .75rem; color: #6c757d; margin-top: .1rem; }
        .sisi-sr-item__tags i { margin-right: .3rem; }
        .sisi-sr-item__chev { flex-shrink: 0; font-size: .75rem; color: #adb5bd; transition: transform .15s ease, color .15s ease; }
        .sisi-sr-item__actions { display: flex; gap: .35rem; flex-shrink: 0; }
        .sisi-sr-item__actions .btn { min-height: 2.3rem; min-width: 2.3rem; display: inline-flex; align-items: center; justify-content: center; gap: .35rem; }
        @media (max-width: 400px) { .sisi-sr-lbl { display: none; } .sisi-sr-item { padding: .75rem; gap: .6rem; } }
        .sisi-sr-player { padding: 0 1rem .75rem; background: #f8f9fa; }
        .sisi-sr-player[hidden] { display: none; }
        .sisi-sr-list mark, .sisi-sr-hero mark { background: #fff3bf; color: inherit; padding: 0 .1rem; border-radius: .2rem; }

        .sisi-sr-pages { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-top: 1rem; }
        .sisi-sr-pages__info { font-size: .85rem; color: #6c757d; text-align: center; }

        /* ── Sin resultados / sin término ──────────────────────── */
        .sisi-sr-empty {
            background: #fff; border-radius: 1rem; padding: 2rem 1.25rem; text-align: center;
            box-shadow: 0 1px 2px rgba(16,24,40,.05), 0 4px 16px rgba(16,24,40,.06);
        }
        .sisi-sr-empty > i { font-size: 2.25rem; color: #adb5bd; margin-bottom: .75rem; }
        .sisi-sr-empty > p { color: #6c757d; }
        .sisi-sr-tips { list-style: none; padding: 0; margin: 0 auto 1.25rem; max-width: 520px; color: #495057; font-size: .92rem; }
        .sisi-sr-tips li { padding: .25rem 0; }
        .sisi-sr-explore__label { font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #868e96; margin: 1.25rem 0 .6rem; }
        .sisi-sr-explore__formats, .sisi-sr-explore__temas { display: flex; flex-wrap: wrap; justify-content: center; gap: .5rem; }
        .sisi-sr-explore__formats a, .sisi-sr-explore__temas a {
            display: inline-flex; align-items: center; gap: .4rem; min-height: 2.5rem; padding: .4rem .95rem;
            border-radius: 99px; border: 1px solid #dee2e6; background: #fff; color: #212529;
            font-size: .875rem; font-weight: 600; text-decoration: none;
        }
        .sisi-sr-explore__formats a:hover, .sisi-sr-explore__temas a:hover { border-color: #0d6efd; color: #0d6efd; }
    </style>

    @include('sisichakuna.partials.buscar-sugerencias')
    @include('partials.home-preview-modal')
@endsection
