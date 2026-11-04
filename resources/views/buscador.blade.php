@extends('layouts.hub')
@section('titulo', '- Resultados de búsqueda')
@section('contenido')
    @php
        $CAP = 5;
        $hl = function (?string $text, string $q): string {
            $text = (string) ($text ?? '');
            if ($text === '' || $q === '') {
                return e($text);
            }
            $parts = preg_split('/(' . preg_quote($q, '/') . ')/iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            if ($parts === false || $parts === []) {
                return e($text);
            }
            $out = '';
            foreach ($parts as $i => $part) {
                $out .= $i % 2 === 1 ? '<mark class="buscador-hl">' . e($part) . '</mark>' : e($part);
            }
            return $out;
        };
    @endphp

    {{-- ═══ Encabezado plano: logo de vuelta al inicio, sin foto de fondo ═══ --}}
    <div class="buscador-topbar">
        <div class="buscador-topbar-inner">
            <a href="{{ route('hub') }}" class="buscador-topbar-logo-link" aria-label="Volver a la página de inicio">
                <img src="{{ asset('img/Asociacion-logo-rojo.png') }}" alt="Asociación Pukllasunchis"
                    class="buscador-topbar-logo">
            </a>

            <h1 class="buscador-topbar-title buscador-reveal">
                {{ $hasResults ? 'Resultados para' : 'Sin resultados para' }}
                <span class="buscador-topbar-query">“{{ $query }}”</span>
            </h1>

            @if ($hasResults)
                @php
                    $resultados = [];
                    if ($sisipediaCount > 0) {
                        $resultados[] = $sisipediaCount . ' ' . ($sisipediaCount === 1 ? 'registro Sisipedia' : 'registros Sisipedia');
                    }
                    if ($videosCount > 0) {
                        $resultados[] = $videosCount . ' ' . ($videosCount === 1 ? 'video' : 'videos');
                    }
                    if ($librosCount > 0) {
                        $resultados[] = $librosCount . ' ' . ($librosCount === 1 ? 'texto' : 'textos');
                    }
                    if ($kawsayLibrosCount > 0) {
                        $resultados[] = $kawsayLibrosCount . ' ' . ($kawsayLibrosCount === 1 ? 'texto Kawsay' : 'textos Kawsay');
                    }
                    if ($kawsayVideosCount > 0) {
                        $resultados[] = $kawsayVideosCount . ' ' . ($kawsayVideosCount === 1 ? 'video Kawsay' : 'videos Kawsay');
                    }
                    if ($aportacionesCount > 0) {
                        $resultados[] = $aportacionesCount . ' ' . ($aportacionesCount === 1 ? 'aportación' : 'aportaciones');
                    }
                    if ($cancionesCount > 0) {
                        $resultados[] = $cancionesCount . ' ' . ($cancionesCount === 1 ? 'canción' : 'canciones');
                    }

                    $proyectosActivos = [];
                    if ($sisipediaCount > 0) {
                        $proyectosActivos['resultados-sisipedia'] = $proyectosPorSlug['radio-pukllasunchis'] ?? 'Radio Pukllasunchis';
                    }
                    if ($videosCount > 0) {
                        $proyectosActivos['resultados-videos'] = $proyectosPorSlug['eesp-pukllasunchis'] ?? 'EESP Pukllasunchis';
                    }
                    if ($librosCount > 0) {
                        $proyectosActivos['resultados-biblioteca'] = $proyectosPorSlug['eesp-pukllasunchis'] ?? 'EESP Pukllasunchis';
                    }
                    if ($kawsayLibrosCount > 0) {
                        $proyectosActivos['resultados-kawsay-libros'] = $proyectosPorSlug['kawsay'] ?? 'Kawsay';
                    }
                    if ($kawsayVideosCount > 0) {
                        $proyectosActivos['resultados-kawsay-videos'] = $proyectosPorSlug['kawsay'] ?? 'Kawsay';
                    }
                    if ($aportacionesCount > 0) {
                        $proyectosActivos['resultados-aportaciones'] = $proyectosPorSlug['radio-pukllasunchis'] ?? 'Radio Pukllasunchis';
                    }
                    if ($cancionesCount > 0) {
                        $proyectosActivos['resultados-canciones'] = $proyectosPorSlug['radio-pukllasunchis'] ?? 'Radio Pukllasunchis';
                    }
                    // Un proyecto puede tener varias secciones; nos quedamos con la
                    // primera (array_unique conserva la primera clave por valor).
                    $proyectosUnicos = array_unique($proyectosActivos);
                @endphp
                <p class="buscador-topbar-summary buscador-reveal">
                    Se encontraron: {{ implode(' · ', $resultados) }}
                </p>
                @if (count($proyectosUnicos) > 1)
                    <p class="buscador-topbar-proyectos buscador-reveal">
                        En
                        @foreach ($proyectosUnicos as $targetId => $nombreProyecto)
                            <a href="#{{ $targetId }}" class="buscador-proyecto-link"
                                data-target="{{ $targetId }}">{{ $nombreProyecto }}</a>{{ ! $loop->last ? ' · ' : '' }}
                        @endforeach
                    </p>
                @endif
            @endif

            <form action="{{ route('busqueda') }}" method="GET" role="search"
                class="buscador-refine-form mx-auto mt-3 buscador-reveal">
                <label for="buscadorRefineInput" class="visually-hidden">
                    {{ $hasResults ? 'Refinar búsqueda' : 'Buscar de nuevo' }}
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                    <input type="search" id="buscadorRefineInput" name="query" class="form-control"
                        value="{{ $query }}"
                        placeholder="{{ $hasResults ? 'Refinar o buscar otro tema…' : 'Prueba con otras palabras…' }}"
                        minlength="3" maxlength="120" autocomplete="off" required>
                    <button class="btn" type="submit">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="container mt-4 {{ $hasResults ? 'mb-5' : 'mb-0' }}" id="buscadorResultados">

        {{-- ── Sisipedia ─────────────────────────────────────────────── --}}
        @if ($sisipediaHits->isNotEmpty())
            @php
                $sisipediaVisibles = $sisipediaHits->take($CAP);
                $sisipediaRestantes = $sisipediaCount - $sisipediaVisibles->count();
            @endphp
            <section class="buscador-section buscador-reveal" id="resultados-sisipedia">
                <div class="buscador-section-header">
                    <span class="badge rounded-pill" style="background:#6a0310;color:#fff;">{{ $sisipediaCount }}</span>
                    <h2 class="buscador-section-title">Registros Sisipedia</h2>
                    <a href="{{ $enlacesGrupos['sisipedia'] }}" class="badge buscador-badge-proyecto">
                        {{ $proyectosPorSlug['radio-pukllasunchis'] ?? 'Radio Pukllasunchis' }}
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="buscador-row-list">
                    @foreach ($sisipediaVisibles as $hit)
                        @php
                            $reg = $hit['category'];
                            $path = [];
                            $cur = $reg;
                            while ($cur) {
                                array_unshift($path, $cur->display_name);
                                $cur = $cur->parent ?? null;
                            }
                            $metaTexto = null;
                            if ($reg->description) {
                                $cleanDescription = \App\Models\sisipedia\Category::stripLegacyOntologyPrefix($reg->description);
                                $metaTexto = $hl(Str::limit($cleanDescription, 90), $query);
                            } elseif (count($path) > 1) {
                                $metaTexto = e(implode(' › ', array_slice($path, 0, -1)));
                            }
                        @endphp
                        @include('partials.buscador-row', [
                            'icon' => 'fa-sitemap',
                            'iconColor' => '#6a0310',
                            'href' => route('sisipedia.categories.show', $reg),
                            'title' => $hl($reg->display_name, $query),
                            'meta' => $metaTexto,
                        ])
                    @endforeach
                </div>
                @include('partials.buscador-vermas', [
                    'restantes' => $sisipediaRestantes,
                    'href' => $enlacesGrupos['sisipedia'],
                    'label' => $proyectosPorSlug['radio-pukllasunchis'] ?? 'Radio Pukllasunchis',
                ])
            </section>
        @endif

        {{-- ── Videos institucionales (Escuela) ─────────────────────────── --}}
        @if ($videosScored->isNotEmpty())
            @php
                $videosVisibles = $videosScored->take($CAP);
                $videosRestantes = $videosCount - $videosVisibles->count();
            @endphp
            <section class="buscador-section buscador-reveal" id="resultados-videos">
                <div class="buscador-section-header">
                    <span class="badge bg-danger text-white rounded-pill">{{ $videosCount }}</span>
                    <h2 class="buscador-section-title">Videos</h2>
                    <a href="{{ $enlacesGrupos['videos'] }}" class="badge buscador-badge-proyecto">
                        {{ $proyectosPorSlug['eesp-pukllasunchis'] ?? 'EESP Pukllasunchis' }}
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="buscador-row-list">
                    @foreach ($videosVisibles as $row)
                        @php
                            $video = $row['model'];
                            $vhref = $video->youtube
                                ? 'https://www.youtube.com/watch?v=' . $video->youtube
                                : ($video->drive ? 'https://drive.google.com/file/d/' . $video->drive . '/view' : null);
                        @endphp
                        @include('partials.buscador-row', [
                            'icon' => 'fa-video',
                            'iconColor' => '#dc3545',
                            'href' => $vhref,
                            'external' => true,
                            'title' => $hl($video->nombre, $query),
                            'meta' => $video->descripcion ? $hl(Str::limit($video->descripcion, 90), $query) : null,
                        ])
                    @endforeach
                </div>
                @include('partials.buscador-vermas', [
                    'restantes' => $videosRestantes,
                    'href' => $enlacesGrupos['videos'],
                    'label' => $proyectosPorSlug['eesp-pukllasunchis'] ?? 'EESP Pukllasunchis',
                ])
            </section>
        @endif

        {{-- ── Biblioteca (libros, Escuela) ─────────────────────────────── --}}
        @if ($libros->isNotEmpty())
            @php
                $librosVisibles = $libros->take($CAP);
                $librosRestantes = $librosCount - $librosVisibles->count();
            @endphp
            <section class="buscador-section buscador-reveal" id="resultados-biblioteca">
                <div class="buscador-section-header">
                    <span class="badge bg-success text-white rounded-pill">{{ $librosCount }}</span>
                    <h2 class="buscador-section-title">Biblioteca</h2>
                    <a href="{{ $enlacesGrupos['libros'] }}" class="badge buscador-badge-proyecto">
                        {{ $proyectosPorSlug['eesp-pukllasunchis'] ?? 'EESP Pukllasunchis' }}
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="buscador-row-list">
                    @foreach ($librosVisibles as $libro)
                        @include('partials.buscador-row', [
                            'icon' => 'fa-book',
                            'iconColor' => '#198754',
                            'href' => 'https://drive.google.com/file/d/' . $libro->identificador . '/preview',
                            'external' => true,
                            'title' => $hl($libro->nombre, $query),
                            'meta' => $libro->autor ? $hl($libro->autor, $query) : null,
                        ])
                    @endforeach
                </div>
                @include('partials.buscador-vermas', [
                    'restantes' => $librosRestantes,
                    'href' => $enlacesGrupos['libros'],
                    'label' => $proyectosPorSlug['eesp-pukllasunchis'] ?? 'EESP Pukllasunchis',
                ])
            </section>
        @endif

        {{-- ── Kawsay — Libros ───────────────────────────────────────────── --}}
        @if ($kawsayLibros->isNotEmpty())
            @php
                $kawsayLibrosVisibles = $kawsayLibros->take($CAP);
                $kawsayLibrosRestantes = $kawsayLibrosCount - $kawsayLibrosVisibles->count();
            @endphp
            <section class="buscador-section buscador-reveal" id="resultados-kawsay-libros">
                <div class="buscador-section-header">
                    <span class="badge bg-success text-white rounded-pill">{{ $kawsayLibrosCount }}</span>
                    <h2 class="buscador-section-title">Kawsay — Libros</h2>
                    <a href="{{ $enlacesGrupos['kawsayLibros'] }}" class="badge buscador-badge-proyecto">
                        {{ $proyectosPorSlug['kawsay'] ?? 'Kawsay' }}
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="buscador-row-list">
                    @foreach ($kawsayLibrosVisibles as $libro)
                        @include('partials.buscador-row', [
                            'icon' => 'fa-book',
                            'iconColor' => '#198754',
                            'href' => 'https://drive.google.com/file/d/' . $libro->identificador . '/preview',
                            'external' => true,
                            'title' => $hl($libro->nombre, $query),
                            'meta' => $libro->autor ? $hl($libro->autor, $query) : null,
                        ])
                    @endforeach
                </div>
                @include('partials.buscador-vermas', [
                    'restantes' => $kawsayLibrosRestantes,
                    'href' => $enlacesGrupos['kawsayLibros'],
                    'label' => $proyectosPorSlug['kawsay'] ?? 'Kawsay',
                ])
            </section>
        @endif
        
        {{-- ── Kawsay — Videos ───────────────────────────────────────────── --}}
        @if ($kawsayVideosScored->isNotEmpty())
            @php
                $kawsayVideosVisibles = $kawsayVideosScored->take($CAP);
                $kawsayVideosRestantes = $kawsayVideosCount - $kawsayVideosVisibles->count();
            @endphp
            <section class="buscador-section buscador-reveal" id="resultados-kawsay-videos">
                <div class="buscador-section-header">
                    <span class="badge bg-danger text-white rounded-pill">{{ $kawsayVideosCount }}</span>
                    <h2 class="buscador-section-title">Kawsay — Videos</h2>
                    <a href="{{ $enlacesGrupos['kawsayVideos'] }}" class="badge buscador-badge-proyecto">
                        {{ $proyectosPorSlug['kawsay'] ?? 'Kawsay' }}
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="buscador-row-list">
                    @foreach ($kawsayVideosVisibles as $row)
                        @php
                            $video = $row['model'];
                            $vhref = $video->youtube
                                ? 'https://www.youtube.com/watch?v=' . $video->youtube
                                : ($video->drive ? 'https://drive.google.com/file/d/' . $video->drive . '/view' : null);
                        @endphp
                        @include('partials.buscador-row', [
                            'icon' => 'fa-video',
                            'iconColor' => '#dc3545',
                            'href' => $vhref,
                            'external' => true,
                            'title' => $hl($video->nombre, $query),
                            'meta' => $video->descripcion ? $hl(Str::limit($video->descripcion, 90), $query) : null,
                        ])
                    @endforeach
                </div>
                @include('partials.buscador-vermas', [
                    'restantes' => $kawsayVideosRestantes,
                    'href' => $enlacesGrupos['kawsayVideos'],
                    'label' => $proyectosPorSlug['kawsay'] ?? 'Kawsay',
                ])
            </section>
        @endif

        {{-- ── Aportaciones ──────────────────────────────────────────────── --}}
        @if ($aportaciones->isNotEmpty())
            @php
                $rolColors = [
                    'Equipo Puklla' => '#6f42c1',
                    'Docente' => '#0d6efd',
                    'Líder' => '#198754',
                    'Niño/Estudiante' => '#fd7e14',
                ];
                $rolIcons = [
                    'Equipo Puklla' => 'fa-users',
                    'Docente' => 'fa-graduation-cap',
                    'Líder' => 'fa-star',
                    'Niño/Estudiante' => 'fa-child',
                ];
                $aportacionesVisibles = $aportaciones->take($CAP);
                $aportacionesRestantes = $aportacionesCount - $aportacionesVisibles->count();
            @endphp
            <section class="buscador-section buscador-reveal" id="resultados-aportaciones">
                <div class="buscador-section-header">
                    <span class="badge bg-warning text-dark rounded-pill">{{ $aportacionesCount }}</span>
                    <h2 class="buscador-section-title">Aportaciones</h2>
                    <a href="{{ $enlacesGrupos['aportaciones'] }}" class="badge buscador-badge-proyecto">
                        {{ $proyectosPorSlug['radio-pukllasunchis'] ?? 'Radio Pukllasunchis' }}
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="buscador-row-list">
                    @foreach ($aportacionesVisibles as $ap)
                        @php
                            $catPath = [];
                            $cur = $ap->category;
                            while ($cur) {
                                array_unshift($catPath, $cur->display_name);
                                $cur = $cur->parent ?? null;
                            }
                            $metaTexto = $ap->detalle
                                ? $hl(Str::limit($ap->detalle, 90), $query)
                                : (count($catPath) ? e(implode(' › ', $catPath)) : null);
                        @endphp
                        @include('partials.buscador-row', [
                            'icon' => $rolIcons[$ap->rol_nombre] ?? 'fa-user',
                            'iconColor' => $rolColors[$ap->rol_nombre] ?? '#6c757d',
                            'href' => $ap->category ? route('sisipedia.categories.show', $ap->category) : null,
                            'title' => $hl($ap->nombre_ol, $query),
                            'meta' => $metaTexto,
                        ])
                    @endforeach
                </div>
                @include('partials.buscador-vermas', [
                    'restantes' => $aportacionesRestantes,
                    'href' => $enlacesGrupos['aportaciones'],
                    'label' => $proyectosPorSlug['radio-pukllasunchis'] ?? 'Radio Pukllasunchis',
                ])
            </section>
        @endif

        {{-- ── Canciones ──────────────────────────────────────────────────── --}}
        @if ($canciones->isNotEmpty())
            @php
                $cancionesVisibles = $canciones->take($CAP);
                $cancionesRestantes = $cancionesCount - $cancionesVisibles->count();
            @endphp
            <section class="buscador-section buscador-reveal" id="resultados-canciones">
                <div class="buscador-section-header">
                    <span class="badge rounded-pill bg-dark text-white">{{ $cancionesCount }}</span>
                    <h2 class="buscador-section-title">Canciones</h2>
                    <a href="{{ $enlacesGrupos['canciones'] }}" class="badge buscador-badge-proyecto">
                        {{ $proyectosPorSlug['radio-pukllasunchis'] ?? 'Radio Pukllasunchis' }}
                        <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="buscador-row-list">
                    @foreach ($cancionesVisibles as $cancion)
                        @php
                            $chref = null;
                            if ($cancion->youtube) {
                                $chref = 'https://www.youtube.com/watch?v=' . $cancion->youtube;
                            } elseif ($cancion->drive) {
                                $chref = 'https://drive.google.com/file/d/' . $cancion->drive . '/view';
                            } elseif ($cancion->spotify) {
                                $chref = 'https://open.spotify.com/track/' . $cancion->spotify;
                            }
                        @endphp
                        @include('partials.buscador-row', [
                            'icon' => 'fa-music',
                            'iconColor' => '#212529',
                            'href' => $chref,
                            'external' => true,
                            'title' => $hl($cancion->nombre, $query),
                            'meta' => $cancion->autor ? $hl($cancion->autor, $query) : null,
                        ])
                    @endforeach
                </div>
                @include('partials.buscador-vermas', [
                    'restantes' => $cancionesRestantes,
                    'href' => $enlacesGrupos['canciones'],
                    'label' => $proyectosPorSlug['radio-pukllasunchis'] ?? 'Radio Pukllasunchis',
                ])
            </section>
        @endif
    </div>
    <style>
        /* ── Encabezado plano (sin foto de fondo) ── */
        .buscador-topbar {
            background: #f7f4ef;
            border-bottom: 1px solid rgba(106, 3, 16, .08);
            padding: 2.25rem 1.25rem 1.75rem;
            text-align: center;
        }

        .buscador-topbar-inner {
            max-width: 640px;
            margin: 0 auto;
        }

        .buscador-topbar-logo-link {
            display: inline-block;
        }

        .buscador-topbar-logo {
            width: 130px;
            max-width: 45vw;
        }

        .buscador-topbar-title {
            font-family: "EB Garamond", Garamond, "Times New Roman", serif;
            font-weight: 700;
            color: #2a2520;
            font-size: clamp(1.35rem, 3.2vw, 1.85rem);
            margin: 1rem 0 .35rem;
        }

        .buscador-topbar-query {
            color: #6a0310;
        }

        .buscador-topbar-summary {
            color: #8a8278;
            font-size: .88rem;
            margin: 0;
        }

        .buscador-topbar-proyectos {
            color: #8a8278;
            font-size: .82rem;
            margin: .3rem 0 0;
        }

        .buscador-proyecto-link {
            color: #6a0310;
            font-weight: 600;
            text-decoration: underline;
            text-decoration-color: rgba(106, 3, 16, .35);
            text-underline-offset: 2px;
            transition: text-decoration-color .2s ease;
        }

        .buscador-proyecto-link:hover {
            text-decoration-color: rgba(106, 3, 16, .8);
        }

        /* ── Secciones compactas ── */
        .buscador-section {
            margin-top: 2.25rem;
        }

        .buscador-section:first-child {
            margin-top: 2rem;
        }

        .buscador-section-header {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: .65rem;
        }

        .buscador-section-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
            color: #2a2520;
        }

        /* Breve resalte al llegar a una sección desde la línea de proyectos */
        .buscador-section.is-highlighted {
            animation: buscador-section-flash 1.1s ease-out;
            border-radius: .75rem;
        }

        @keyframes buscador-section-flash {
            0% {
                box-shadow: inset 0 0 0 2px rgba(106, 3, 16, 0);
            }

            25% {
                box-shadow: inset 0 0 0 2px rgba(106, 3, 16, .25);
            }

            100% {
                box-shadow: inset 0 0 0 2px rgba(106, 3, 16, 0);
            }
        }

        /* Etiqueta "a qué proyecto pertenece" cada sección: link discreto a la
           vista propia de ese proyecto, en el color de marca. */
        .buscador-badge-proyecto {
            background: rgba(106, 3, 16, .07);
            color: #6a0310;
            border: 1px solid rgba(106, 3, 16, .22);
            font-weight: 600;
            font-size: .68rem;
            letter-spacing: .02em;
            padding: .3rem .65rem;
            border-radius: 999px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            transition: background .2s ease, color .2s ease, border-color .2s ease;
        }

        .buscador-badge-proyecto i {
            font-size: .62rem;
            opacity: .75;
            transition: transform .2s ease;
        }

        .buscador-badge-proyecto:hover {
            background: #6a0310;
            color: #fff;
            border-color: #6a0310;
            text-decoration: none;
        }

        .buscador-badge-proyecto:hover i {
            transform: translateX(2px);
        }

        /* Fila compacta de resultado */
        .buscador-row-list {
            display: flex;
            flex-direction: column;
            gap: .4rem;
        }

        .buscador-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .55rem .8rem;
            border-radius: .6rem;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .06);
            text-decoration: none;
            color: inherit;
            transition: background .15s ease, border-color .15s ease, transform .15s ease;
        }

        .buscador-row:hover {
            background: rgba(106, 3, 16, .04);
            border-color: rgba(106, 3, 16, .18);
            text-decoration: none;
            color: inherit;
            transform: translateX(2px);
        }

        .buscador-row--nolink {
            cursor: default;
            pointer-events: none;
        }

        .buscador-row-icon {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: color-mix(in srgb, var(--row-icon-color, #6a0310) 12%, white);
            color: var(--row-icon-color, #6a0310);
            font-size: .78rem;
        }

        .buscador-row-body {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: .05rem;
        }

        .buscador-row-title {
            font-weight: 600;
            font-size: .86rem;
            color: #2a2520;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .buscador-row-meta {
            font-size: .74rem;
            color: #8a8278;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .buscador-row-arrow {
            flex-shrink: 0;
            font-size: .66rem;
            color: #b8a898;
            transition: transform .15s ease;
        }

        .buscador-row:hover .buscador-row-arrow {
            transform: translateX(3px);
            color: #6a0310;
        }

        .buscador-vermas {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .78rem;
            font-weight: 600;
            color: #6a0310;
            text-decoration: none;
            padding: .5rem 0 0;
        }

        .buscador-vermas:hover {
            text-decoration: underline;
            color: #6a0310;
        }

        .buscador-vermas i {
            font-size: .65rem;
            transition: transform .15s ease;
        }

        .buscador-vermas:hover i {
            transform: translateX(3px);
        }

        /* Resaltado de coincidencia */
        .buscador-hl {
            background: #fde68a;
            color: #1e293b;
            border-radius: 3px;
            padding: 0 2px;
            font-weight: 700;
            font-style: normal;
        }

        @media (max-width: 575.98px) {
            .buscador-topbar {
                padding: 1.75rem 1rem 1.4rem;
            }

            .buscador-row-title,
            .buscador-row-meta {
                white-space: normal;
            }
        }
    </style>

    <script>
        (function() {
            var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function revealFallback() {
                document.querySelectorAll('.buscador-reveal').forEach(function(el) {
                    el.style.opacity = 1;
                });
            }

            function animate() {
                if (typeof gsap === 'undefined') {
                    revealFallback();
                    return;
                }
                // Cada elemento recibe UN solo tween: las secciones también llevan .buscador-reveal y,
                // con dos from() encima, el segundo tomaba opacity:0 como valor final y las dejaba invisibles.
                gsap.from('.buscador-topbar .buscador-reveal', {
                    opacity: 0,
                    y: 16,
                    duration: 0.5,
                    stagger: 0.08,
                    ease: 'power2.out'
                });
                gsap.from('.buscador-section', {
                    opacity: 0,
                    y: 14,
                    duration: 0.4,
                    stagger: 0.06,
                    delay: 0.15,
                    ease: 'power2.out'
                });
            }

            if (reduceMotion) {
                revealFallback();
            } else if (typeof gsap !== 'undefined') {
                animate();
            } else {
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
                script.onload = animate;
                script.onerror = revealFallback;
                document.head.appendChild(script);
            }

            // ── Link sutil "En: [proyecto]": scroll suave + breve resalte ──
            // (esta vista no tiene nav fijo, así que solo un pequeño respiro)
            document.querySelectorAll('.buscador-proyecto-link').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    var target = document.getElementById(link.getAttribute('data-target'));
                    if (!target) return;
                    e.preventDefault();

                    var y = target.getBoundingClientRect().top + window.pageYOffset - 20;
                    window.scrollTo({
                        top: Math.max(y, 0),
                        behavior: reduceMotion ? 'auto' : 'smooth'
                    });

                    target.classList.remove('is-highlighted');
                    void target.offsetWidth; // reinicia la animación CSS si ya se había mostrado
                    target.classList.add('is-highlighted');
                });
            });
        })();
    </script>
@endsection
