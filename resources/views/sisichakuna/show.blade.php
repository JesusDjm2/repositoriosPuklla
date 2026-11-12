@extends('layouts.padre')
@php
    $heroPorTipo = [
        'audio' => 'Audios',
        'video' => 'Videos',
        'imagen' => 'Imágenes',
        'pdf' => 'Documentos PDF',
    ];
    $heroSubtitulo = $heroPorTipo[$tipo ?? ''] ?? 'Repositorio de Trabajos';

    // Una foto de fondo distinta por sección (carpeta img/hormigas/), en vez de
    // compartir todas la misma Fondo-sisipedia.png genérica.
    $heroBgPorTipo = [
        '' => 'Sisipedia-01.jpeg',
        'audio' => 'Sisipedia-02.jpeg',
        'pdf' => 'Sisipedia-03.jpeg',
        'imagen' => 'Sisipedia-05.jpeg',
        'video' => 'Sisipedia-06.jpeg',
    ];
    $heroBg = asset('img/hormigas/' . ($heroBgPorTipo[$tipo ?? ''] ?? $heroBgPorTipo['']));
@endphp
@section('titulo', '- ' . $heroSubtitulo)

@section('estilos')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
@endsection

@section('contenido')
@include('partials.lib-hero', [
    'heroBg' => $heroBg,
    'heroBgAlt' => 'Fondo Sisipedia',
    'heroEyebrow' => 'Sisipedia',
    'heroTitle' => $heroSubtitulo,
    'heroFormatButtons' => true,
])
<div class="container py-2 py-md-3">

{{-- ── Navegación por tipo de archivo ───────────────────────────
     Solo en el árbol (sin ?tipo): en las galerías por tipo esto mismo ya
     vive en el hero (heroFormatButtons), repetirlo acá era redundante. ── --}}
@if(request()->routeIs('public.sisi') && !isset($tipo))
@php
    $acCounts = $archivoCounts ?? [];
@endphp
<div class="row mt-2 mt-md-4">
    <div class="col-12 mb-2">
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 sisipedia-tipo-nav">
            <a href="{{ route('public.sisi') }}"
               class="sisipedia-tipo-pill {{ !isset($tipo) ? 'sisipedia-tipo-pill--active' : '' }}">
                <i class="fa fa-sitemap me-1"></i>Todos los registros
            </a>
            <a href="{{ route('public.sisi', ['tipo' => 'imagen']) }}"
               class="sisipedia-tipo-pill sisipedia-tipo-pill--imagen {{ ($tipo ?? '') === 'imagen' ? 'sisipedia-tipo-pill--active' : '' }}">
                <i class="fa fa-image me-1"></i>Imágenes
                @if(!empty($acCounts['imagen']))
                    <span class="sisipedia-tipo-badge">{{ $acCounts['imagen'] }}</span>
                @endif
            </a>
            <a href="{{ route('public.sisi', ['tipo' => 'audio']) }}"
               class="sisipedia-tipo-pill sisipedia-tipo-pill--audio {{ ($tipo ?? '') === 'audio' ? 'sisipedia-tipo-pill--active' : '' }}">
                <i class="fa fa-music me-1"></i>Audios
                @if(!empty($acCounts['audio']))
                    <span class="sisipedia-tipo-badge">{{ $acCounts['audio'] }}</span>
                @endif
            </a>
            <a href="{{ route('public.sisi', ['tipo' => 'video']) }}"
               class="sisipedia-tipo-pill sisipedia-tipo-pill--video {{ ($tipo ?? '') === 'video' ? 'sisipedia-tipo-pill--active' : '' }}">
                <i class="fa fa-video me-1"></i>Videos
                @if(!empty($acCounts['video']))
                    <span class="sisipedia-tipo-badge">{{ $acCounts['video'] }}</span>
                @endif
            </a>
            <a href="{{ route('public.sisi', ['tipo' => 'pdf']) }}"
               class="sisipedia-tipo-pill sisipedia-tipo-pill--pdf {{ ($tipo ?? '') === 'pdf' ? 'sisipedia-tipo-pill--active' : '' }}">
                <i class="fa fa-file-pdf me-1"></i>PDF
                @if(!empty($acCounts['pdf']))
                    <span class="sisipedia-tipo-badge">{{ $acCounts['pdf'] }}</span>
                @endif
            </a>
        </div>
    </div>
</div>
@endif

@if(!isset($tipo))
{{-- ════════════════════════════════════════════════════════════
     VISTA ÁRBOL (existente)
     ════════════════════════════════════════════════════════════ --}}
    <div class="row mt-2 mt-md-4">
        <div class="col-12 mb-3">
            {{-- Buscar ya no filtra este árbol en el navegador: va a la página de resultados. --}}
            <div class="d-flex flex-column flex-sm-row gap-2">
                <form action="{{ route('sisipedia.buscar') }}" method="GET" role="search" class="input-group flex-grow-1">
                    <span class="input-group-text bg-white">
                        <i class="fa fa-search text-muted"></i>
                    </span>
                    <input type="search" name="q" class="form-control" data-sisi-suggest
                        aria-label="Buscar en Sisipedia" placeholder="Buscar un tema, audio o documento…"
                        minlength="2" maxlength="120" autocomplete="off" required>
                    <button class="btn btn-primary" type="submit">Buscar</button>
                </form>
                <button class="btn btn-sm btn-outline-secondary flex-shrink-0" id="collapseAll" type="button" title="Cerrar todas las carpetas">
                    <i class="fa fa-compress me-1"></i>
                    <span class="d-none d-sm-inline">Cerrar todo</span>
                </button>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <small class="text-muted me-1">Contiene:</small>
                <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger" style="font-size:.65rem;">
                    <i class="fa fa-file-pdf me-1"></i>PDF
                </span>
                <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success" style="font-size:.65rem;">
                    <i class="fa fa-music me-1"></i>Audio
                </span>
                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary" style="font-size:.65rem;">
                    <i class="fa fa-video me-1"></i>Video
                </span>
                <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning" style="font-size:.65rem;">
                    <i class="fa fa-users me-1"></i>Aportes
                </span>
                <span class="badge rounded-pill bg-light text-secondary border" style="font-size:.65rem;">
                    <i class="fa fa-folder-open me-1"></i>Subtemas
                </span>
            </div>
            <small class="text-muted mt-1 d-block">
                <i class="fa fa-info-circle"></i> Toca la flecha <i class="fa fa-chevron-right"></i> para ver los subtemas de cada tema.
            </small>
        </div>
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4" id="treeCard">
                <div class="card-body p-2 p-md-3">
                    <div id="categoryTree">
                        @if(isset($tree) && $tree->count())
                            <ul class="list-unstyled mb-0">
                                @foreach ($tree as $category)
                                    @include('sisichakuna.tree', [
                                        'category' => $category,
                                        'level' => 0,
                                    ])
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-folder-open fa-4x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No hay categorías disponibles</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Aportaciones recientes de visitantes ────────────── --}}
    @if(isset($aportacionesSueltas) && $aportacionesSueltas->isNotEmpty())
        <div class="row mt-4" id="sisipedia-tree-aportaciones-section">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-0 pt-3 pb-0 px-3 px-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa fa-globe text-primary"></i>
                            <h2 class="h5 mb-0">Aportaciones recientes</h2>
                            <span class="badge bg-primary ms-1">{{ $aportacionesSueltas->count() }}</span>
                        </div>
                        <p class="text-muted small mb-0 mt-1">
                            <i class="fa fa-info-circle me-1"></i>
                            Contribuciones de la comunidad. Cada aportación puede contener múltiples archivos (imágenes, PDF, Word, audio, video).
                        </p>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            @foreach($aportacionesSueltas as $aportacion)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="card border-0 shadow-sm rounded-3 h-100 sisipedia-aportacion-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start gap-2 mb-2">
                                                @php
                                                    $rolColor = match($aportacion->rol_nombre) {
                                                        'Equipo Puklla'   => 'info',
                                                        'Docente'         => 'primary',
                                                        'Líder'           => 'success',
                                                        'Niño/Estudiante' => 'warning',
                                                        default           => 'secondary',
                                                    };
                                                    $rolIcono = match($aportacion->rol_nombre) {
                                                        'Equipo Puklla'   => 'fa-users',
                                                        'Docente'         => 'fa-graduation-cap',
                                                        'Líder'           => 'fa-star',
                                                        'Niño/Estudiante' => 'fa-child',
                                                        default           => 'fa-user',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $rolColor }} bg-opacity-10 text-{{ $rolColor }} border border-{{ $rolColor }} flex-shrink-0">
                                                    <i class="fa {{ $rolIcono }} me-1"></i>{{ \App\Models\sisipedia\Aportacion::etiquetaRol($aportacion->rol_nombre) }}
                                                </span>
                                            </div>
                                            <h3 class="h6 fw-bold mb-1">{{ $aportacion->nombre_ol }}</h3>
                                            <div class="d-flex flex-wrap gap-1 mb-2">
                                                @if($aportacion->institucion)
                                                    <span class="badge bg-light text-dark border" style="font-size:.65rem;">
                                                        <i class="fa fa-building me-1"></i>{{ $aportacion->institucion }}
                                                    </span>
                                                @endif
                                                @if($aportacion->ubicacion)
                                                    <span class="badge bg-light text-dark border" style="font-size:.65rem;">
                                                        <i class="fa fa-map-marker me-1"></i>{{ $aportacion->ubicacion }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($aportacion->detalle)
                                                <p class="text-muted small mb-2">{{ Str::limit(html_entity_decode(strip_tags($aportacion->detalle)), 100) }}</p>
                                            @endif
                                            @php
                                                $archSueltos = $aportacion->relationLoaded('archivos') ? $aportacion->archivos : $aportacion->archivos()->get();
                                            @endphp
                                            @if($archSueltos->isNotEmpty())
                                                <div class="d-flex flex-wrap gap-1 mt-2">
                                                    @foreach($archSueltos->take(4) as $archS)
                                                        @php
                                                            $colorS = match($archS->tipo) {
                                                                'imagen' => 'info', 'pdf' => 'danger', 'doc' => 'primary', 'audio' => 'success', 'video' => 'warning',
                                                                default => 'secondary',
                                                            };
                                                            $iconoS = match($archS->tipo) {
                                                                'imagen' => 'fa-image', 'pdf' => 'fa-file-pdf', 'doc' => 'fa-file-word', 'audio' => 'fa-music', 'video' => 'fa-video',
                                                                default => 'fa-file',
                                                            };
                                                        @endphp
                                                        <span class="badge bg-{{ $colorS }} bg-opacity-10 text-{{ $colorS }} border border-{{ $colorS }}" style="font-size:.6rem;">
                                                            <i class="fa {{ $iconoS }} me-1"></i>{{ ucfirst($archS->tipo) }}
                                                        </span>
                                                    @endforeach
                                                    @if($archSueltos->count() > 4)
                                                        <span class="badge bg-light text-muted border" style="font-size:.6rem;">+{{ $archSueltos->count() - 4 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-footer bg-white border-0 pt-0 px-3 pb-3">
                                            <a href="{{ route('public.sisi') }}"
                                               class="btn btn-sm btn-outline-primary w-100">
                                                <i class="fa fa-eye me-1"></i>Ver todos los registros
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@else
{{-- ════════════════════════════════════════════════════════════
     VISTA GALERÍA POR TIPO
     ════════════════════════════════════════════════════════════ --}}
@php
    $tipoConfig = [
        'audio'  => ['icon' => 'fa-music',     'label' => 'Audios',   'color' => 'success', 'rgb' => '25,135,84'],
        'video'  => ['icon' => 'fa-video',     'label' => 'Videos',   'color' => 'warning', 'rgb' => '255,193,7'],
        'imagen' => ['icon' => 'fa-image',     'label' => 'Imágenes', 'color' => 'info',    'rgb' => '13,202,240'],
        'pdf'    => ['icon' => 'fa-file-pdf',  'label' => 'PDF',      'color' => 'danger',  'rgb' => '220,53,69'],
    ];
    $tc = $tipoConfig[$tipo] ?? ['icon' => 'fa-file', 'label' => ucfirst($tipo), 'color' => 'secondary', 'rgb' => '108,117,125'];
@endphp

<div class="row mt-2 mt-md-3" id="sisipedia-gallery">
    <div class="col-12">

        {{-- ── Buscador (mismo comportamiento que el árbol: filtra por nombre de archivo o por categoría) ── --}}
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="fa fa-search text-muted"></i>
                </span>
                <input type="text" id="sisipediaGallerySearch" class="form-control"
                       placeholder="Buscar por nombre de archivo o por categoría..."
                       value="{{ $q ?? '' }}">
                <button class="btn btn-outline-secondary" type="button" id="sisipediaGallerySearchClear">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <small class="text-muted mt-1 d-block">
                <i class="fa fa-info-circle"></i> Busca por nombre de archivo o por el registro/categoría al que pertenece.
            </small>
        </div>

        {{-- ── Encabezado de galería ─────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="sisipedia-gallery-icon-wrap d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                      style="width:42px;height:42px;background:rgba({{ $tc['rgb'] }},.12);">
                    <i class="fa {{ $tc['icon'] }} text-{{ $tc['color'] }}" style="font-size:1.1rem;"></i>
                </span>
                <div>
                    <h2 class="h5 fw-bold mb-0">{{ $tc['label'] }}</h2>
                    <small class="text-muted">
                        {{ $archivos->total() }} {{ $archivos->total() === 1 ? 'archivo' : 'archivos' }} encontrados
                        @if(!empty($q))
                            para «{{ $q }}»
                        @endif
                    </small>
                </div>
            </div>
            <a href="{{ route('public.sisi') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i>Ver árbol
            </a>
        </div>

        @if($archivos->isEmpty())
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <i class="fa {{ !empty($q) ? 'fa-search' : $tc['icon'] }} fa-3x text-muted mb-3 d-block opacity-50"></i>
                    @if(!empty($q))
                        <p class="text-muted mb-2">No se encontraron {{ strtolower($tc['label']) }} para «{{ $q }}».</p>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="sisipediaGallerySearchReset">
                            <i class="fa fa-times me-1"></i>Quitar búsqueda
                        </button>
                    @else
                        <p class="text-muted mb-2">No hay {{ strtolower($tc['label']) }} disponibles aún.</p>
                        <a href="{{ route('public.sisi') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-sitemap me-1"></i>Ver todos los registros
                        </a>
                    @endif
                </div>
            </div>
        @else

        {{-- ════ AUDIOS (CategoryFile de sisicha) ══════════════ --}}
        @if($tipo === 'audio')
        <div class="row g-3" id="mediaGrid">
            @foreach($archivos as $archivo)
            @php
                $cat = $archivo->category;
                $pathParts = [];
                $cur = $cat;
                while ($cur) { array_unshift($pathParts, $cur->display_name); $cur = $cur->parent ?? null; }
                $lastName       = !empty($pathParts) ? end($pathParts) : null;
                $ancestorParts  = count($pathParts) > 1 ? array_slice($pathParts, 0, -1) : [];
            @endphp
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 media-item">
                <div class="card border-0 shadow-sm rounded-3 h-100 sisipedia-audio-card">
                    <div class="sisipedia-audio-stripe rounded-top-3"></div>
                    <div class="card-body p-3 pt-2">
                        {{-- Ícono + nombre archivo --}}
                        <div class="d-flex align-items-start gap-2 mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1"
                                 style="width:40px;height:40px;background:rgba(25,135,84,.1);">
                                <i class="fa fa-music text-success"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <h6 class="fw-semibold mb-0 text-truncate" style="font-size:.875rem;"
                                    title="{{ $archivo->nombre_original ?? 'Audio' }}">
                                    {{ $archivo->nombre_original ?? 'Audio sin nombre' }}
                                </h6>
                            </div>
                        </div>

                        {{-- Registro al que pertenece (jerarquía completa) --}}
                        <div class="sisipedia-reg-section mb-3 p-2 rounded-2" style="background:rgba(0,0,0,.03);">
                            @if($cat)
                                @if(!empty($ancestorParts))
                                    <div class="sisipedia-breadcrumb mb-1" title="{{ implode(' › ', $ancestorParts) }}">
                                        @foreach($ancestorParts as $part)
                                            <span class="sisipedia-breadcrumb-item">{{ $part }}</span><i class="fa fa-angle-right sisipedia-breadcrumb-sep"></i>
                                        @endforeach
                                    </div>
                                @endif
                                <a href="{{ route('sisipedia.categories.show', $cat) }}"
                                   class="d-block text-truncate text-decoration-none fw-semibold text-dark sisipedia-reg-link"
                                   style="font-size:.8rem;" title="{{ implode(' › ', $pathParts) }}">
                                    <i class="fa fa-folder me-1 text-muted"></i>{{ $lastName }}
                                </a>
                            @else
                                <span class="text-muted fst-italic" style="font-size:.75rem;">Sin registro</span>
                            @endif
                        </div>

                        {{-- Reproductor de audio propio (streaming vía servidor, no iframe de Drive) --}}
                        @include('partials.audio-player', [
                            'src' => route('sisipedia.audio.stream', $archivo),
                            'title' => $archivo->nombre_original ?? 'Audio sin nombre',
                            'driveUrl' => \App\Services\GoogleDriveService::getUrl($archivo->drive_id),
                            'downloadUrl' => route('sisipedia.descargar.material', $archivo),
                        ])
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ════ VIDEOS (CategoryFile de sisicha) ══════════════ --}}
        @if($tipo === 'video')
        <div class="row g-3" id="mediaGrid">
            @foreach($archivos as $archivo)
            @php
                $cat = $archivo->category;
                $pathParts = [];
                $cur = $cat;
                while ($cur) { array_unshift($pathParts, $cur->display_name); $cur = $cur->parent ?? null; }
                $lastName    = !empty($pathParts) ? end($pathParts) : null;
                $ancestorPath = count($pathParts) > 1 ? implode(' › ', array_slice($pathParts, 0, -1)) : null;
                $previewUrl  = \App\Services\GoogleDriveService::getPreviewUrl($archivo->drive_id);
                $thumbUrl    = \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 600);
            @endphp
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 media-item">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 sisipedia-video-card">
                    {{-- Miniatura + play: abre popup con <video controls> real --}}
                    <div class="sisipedia-video-thumb-wrap position-relative" role="button" tabindex="0"
                         aria-label="Reproducir {{ $archivo->nombre_original ?? 'Video' }}"
                         onclick="abrirVistaPreviaVideo({{ \Illuminate\Support\Js::from(route('sisipedia.video.stream', $archivo)) }}, {{ \Illuminate\Support\Js::from($archivo->nombre_original ?? 'Video') }}, {{ \Illuminate\Support\Js::from(\App\Services\GoogleDriveService::getUrl($archivo->drive_id)) }})"
                         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}"
                         style="cursor:pointer;">
                        <div class="ratio ratio-16x9 bg-dark">
                            <img src="{{ $thumbUrl }}"
                                 alt="{{ $archivo->nombre_original ?? 'Video' }}"
                                 class="w-100 h-100"
                                 style="object-fit:contain;opacity:.88;"
                                 loading="lazy"
                                 onerror="this.style.display='none'">
                        </div>
                        <span class="position-absolute top-0 end-0 m-2 badge bg-danger" style="font-size:.65rem;z-index:2;">
                            <i class="fa fa-video me-1"></i>Video
                        </span>
                        <button type="button" tabindex="-1"
                                class="sisipedia-video-play-btn position-absolute top-50 start-50 translate-middle btn btn-light rounded-circle shadow"
                                style="width:52px;height:52px;z-index:2;">
                            <i class="fa fa-play text-dark" style="margin-left:2px;"></i>
                        </button>
                    </div>
                    {{-- Info --}}
                    <div class="card-body p-3">
                        <div class="sisipedia-reg-section mb-2 p-2 rounded-2" style="background:rgba(0,0,0,.03);">
                            @if($cat)
                                @if($ancestorPath)
                                    <div class="text-muted text-truncate" style="font-size:.7rem;">{{ $ancestorPath }}</div>
                                @endif
                                <a href="{{ route('sisipedia.categories.show', $cat) }}"
                                   class="d-block text-truncate text-decoration-none fw-semibold text-dark sisipedia-reg-link"
                                   style="font-size:.8rem;" title="{{ implode(' › ', $pathParts) }}">
                                    <i class="fa fa-folder me-1 text-muted"></i>{{ $lastName }}
                                </a>
                            @else
                                <span class="text-muted fst-italic" style="font-size:.75rem;">Sin registro</span>
                            @endif
                        </div>
                        @if($archivo->nombre_original)
                            <p class="text-muted text-truncate mb-0" style="font-size:.75rem;"
                               title="{{ $archivo->nombre_original }}">
                                <i class="fa fa-file-video me-1 opacity-75"></i>{{ $archivo->nombre_original }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ════ IMÁGENES (CategoryFile de sisicha) ════════════ --}}
        @if($tipo === 'imagen')
        <div class="row g-3" id="mediaGrid">
            @foreach($archivos as $archivo)
            @php
                $cat = $archivo->category;
                $pathParts = [];
                $cur = $cat;
                while ($cur) { array_unshift($pathParts, $cur->display_name); $cur = $cur->parent ?? null; }
                $lastName    = !empty($pathParts) ? end($pathParts) : null;
                $ancestorPath = count($pathParts) > 1 ? implode(' › ', array_slice($pathParts, 0, -1)) : null;
                $previewUrl  = \App\Services\GoogleDriveService::getPreviewUrl($archivo->drive_id);
                $driveUrl    = \App\Services\GoogleDriveService::getUrl($archivo->drive_id);
                $downloadUrl = route('sisipedia.descargar.material', $archivo);
                $thumbUrl    = \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 400);
            @endphp
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 media-item">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 sisipedia-image-card">
                    {{-- Imagen --}}
                    <div class="sisipedia-image-thumb-wrap position-relative" style="background:#f8f9fa;">
                        <div class="ratio ratio-4x3 bg-light">
                            <img src="{{ $thumbUrl }}"
                                 alt="{{ $archivo->nombre_original ?? 'Imagen' }}"
                                 class="w-100 h-100"
                                 style="object-fit:cover;"
                                 loading="lazy"
                                 onerror="this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center h-100 text-muted\'><i class=\'fa fa-image fa-3x opacity-25\'></i></div>'">
                        </div>
                        <span class="position-absolute top-0 end-0 m-2 badge bg-info" style="font-size:.65rem;z-index:2;">
                            <i class="fa fa-image me-1"></i>Imagen
                        </span>
                        {{-- Botón vista previa (popup compartido, ver partials/home-preview-modal) --}}
                        <button class="sisipedia-open-preview-modal position-absolute top-50 start-50 translate-middle btn btn-light rounded-circle shadow"
                                data-preview="{{ $previewUrl }}"
                                data-nombre="{{ $archivo->nombre_original ?? 'Imagen' }}"
                                data-drive="{{ $driveUrl }}"
                                data-download="{{ $downloadUrl }}"
                                style="width:48px;height:48px;z-index:2;opacity:.9;">
                            <i class="fa fa-expand text-dark"></i>
                        </button>
                    </div>
                    {{-- Info --}}
                    <div class="card-body p-3">
                        <div class="sisipedia-reg-section mb-2 p-2 rounded-2" style="background:rgba(0,0,0,.03);">
                            @if($cat)
                                @if($ancestorPath)
                                    <div class="text-muted text-truncate" style="font-size:.7rem;">{{ $ancestorPath }}</div>
                                @endif
                                <a href="{{ route('sisipedia.categories.show', $cat) }}"
                                   class="d-block text-truncate text-decoration-none fw-semibold text-dark sisipedia-reg-link"
                                   style="font-size:.8rem;" title="{{ implode(' › ', $pathParts) }}">
                                    <i class="fa fa-folder me-1 text-muted"></i>{{ $lastName }}
                                </a>
                            @else
                                <span class="text-muted fst-italic" style="font-size:.75rem;">Sin registro</span>
                            @endif
                        </div>
                        @if($archivo->nombre_original)
                            <p class="text-muted text-truncate mb-0" style="font-size:.75rem;"
                               title="{{ $archivo->nombre_original }}">
                                <i class="fa fa-file-image me-1 opacity-75"></i>{{ $archivo->nombre_original }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @endif

        {{-- ════ PDF (CategoryFile de sisicha) ══════════════════ --}}
        @if($tipo === 'pdf')
        <div class="row g-3" id="mediaGrid">
            @foreach($archivos as $archivo)
            @php
                $cat = $archivo->category;
                $pathParts = [];
                $cur = $cat;
                while ($cur) { array_unshift($pathParts, $cur->display_name); $cur = $cur->parent ?? null; }
                $lastName    = !empty($pathParts) ? end($pathParts) : null;
                $ancestorPath = count($pathParts) > 1 ? implode(' › ', array_slice($pathParts, 0, -1)) : null;
                $driveUrl    = \App\Services\GoogleDriveService::getUrl($archivo->drive_id);
                $previewUrl  = \App\Services\GoogleDriveService::getPreviewUrl($archivo->drive_id);
                $downloadUrl = route('sisipedia.descargar.material', $archivo);
            @endphp
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 media-item">
                <div class="card border-0 shadow-sm rounded-3 h-100 sisipedia-pdf-card">
                    <div class="sisipedia-pdf-stripe rounded-top-3"></div>
                    <div class="card-body p-3 pt-2">
                        <div class="d-flex align-items-start gap-2 mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1"
                                 style="width:40px;height:40px;background:rgba(220,53,69,.1);">
                                <i class="fa fa-file-pdf text-danger"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <h6 class="fw-semibold mb-0 text-truncate" style="font-size:.875rem;"
                                    title="{{ $archivo->nombre_original ?? 'PDF' }}">
                                    {{ $archivo->nombre_original ?? 'PDF sin nombre' }}
                                </h6>
                            </div>
                        </div>

                        <div class="sisipedia-reg-section mb-3 p-2 rounded-2" style="background:rgba(0,0,0,.03);">
                            @if($cat)
                                @if($ancestorPath)
                                    <div class="text-muted text-truncate" style="font-size:.7rem;">{{ $ancestorPath }}</div>
                                @endif
                                <a href="{{ route('sisipedia.categories.show', $cat) }}"
                                   class="d-block text-truncate text-decoration-none fw-semibold text-dark sisipedia-reg-link"
                                   style="font-size:.8rem;" title="{{ implode(' › ', $pathParts) }}">
                                    <i class="fa fa-folder me-1 text-muted"></i>{{ $lastName }}
                                </a>
                            @else
                                <span class="text-muted fst-italic" style="font-size:.75rem;">Sin registro</span>
                            @endif
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger flex-grow-1 sisipedia-open-preview-modal"
                                    data-preview="{{ $previewUrl }}"
                                    data-nombre="{{ $archivo->nombre_original ?? 'PDF' }}"
                                    data-drive="{{ $driveUrl }}"
                                    data-download="{{ $downloadUrl }}">
                                <i class="fa fa-eye me-1"></i>Vista previa
                            </button>
                            <a href="{{ $downloadUrl }}" class="btn btn-sm btn-danger flex-shrink-0"
                               title="Descargar" aria-label="Descargar {{ $archivo->nombre_original ?? 'PDF' }}">
                                <i class="fa fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Paginación mejorada --}}
        @if($archivos->hasPages())
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
                    <div class="text-muted small text-center text-sm-start">
                        Mostrando
                        <span class="fw-semibold">{{ $archivos->firstItem() }}</span>
                        –
                        <span class="fw-semibold">{{ $archivos->lastItem() }}</span>
                        de
                        <span class="fw-semibold">{{ $archivos->total() }}</span>
                        {{ strtolower($tc['label']) }}
                    </div>
                    <div class="sisipedia-pagination">
                        {{ $archivos->onEachSide(2)->links() }}
                    </div>
                </div>
            </div>
        @endif

        @endif {{-- /isEmpty --}}
    </div>
</div>
@endif
{{-- /container --}}
</div>

@include('partials.home-preview-modal')

{{-- ══════════════════════════════════════════════════════════
     ESTILOS
     ══════════════════════════════════════════════════════════ --}}
<style>
/* ── Tabs de tipo ─────────────────────────────────────────── */
.sisipedia-tipo-nav { padding: .5rem 0 .25rem; }
.sisipedia-tipo-pill {
    display: inline-flex;
    align-items: center;
    padding: .45rem 1rem;
    border-radius: 2rem;
    font-size: .84rem;
    font-weight: 500;
    text-decoration: none !important;
    color: #495057;
    background: #fff;
    border: 1.5px solid #dee2e6;
    transition: border-color .18s, color .18s, background .18s, box-shadow .18s;
    white-space: nowrap;
    cursor: pointer;
}
.sisipedia-tipo-pill:hover {
    color: #212529;
    border-color: #adb5bd;
    background: #f8f9fa;
    text-decoration: none;
}
.sisipedia-tipo-pill--active              { color: #fff !important; background: #1a6e3c; border-color: #1a6e3c; box-shadow: 0 3px 10px rgba(26,110,60,.28); }
.sisipedia-tipo-pill--imagen.sisipedia-tipo-pill--active { background: #0dcaf0; border-color: #0dcaf0; box-shadow: 0 3px 10px rgba(13,202,240,.28); }
.sisipedia-tipo-pill--audio.sisipedia-tipo-pill--active  { background: #198754; border-color: #198754; box-shadow: 0 3px 10px rgba(25,135,84,.28);  }
.sisipedia-tipo-pill--video.sisipedia-tipo-pill--active  { color: #212529 !important; background: #ffc107; border-color: #ffc107; box-shadow: 0 3px 10px rgba(255,193,7,.28); }
.sisipedia-tipo-pill--pdf.sisipedia-tipo-pill--active    { background: #dc3545; border-color: #dc3545; box-shadow: 0 3px 10px rgba(220,53,69,.28);  }
.sisipedia-tipo-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 1.5em; height: 1.5em; padding: 0 .3em;
    border-radius: 1em; font-size: .72em; font-weight: 700;
    line-height: 1; margin-left: .35rem;
    background: rgba(0,0,0,.12); color: inherit;
}
.sisipedia-tipo-pill--active .sisipedia-tipo-badge { background: rgba(255,255,255,.28); color: #fff; }
.sisipedia-tipo-pill--video.sisipedia-tipo-pill--active .sisipedia-tipo-badge { background: rgba(0,0,0,.16); color: #212529; }
.sisipedia-tipo-pill:not(.sisipedia-tipo-pill--active) .sisipedia-tipo-badge { background: #e9ecef; color: #495057; }

/* ── Fix: evita que nombres largos rompan el layout de la tarjeta ──
   (Bootstrap .card es flex-column; sin min-width:0 los hijos no se
   encogen y el contenido fuerza el ancho de la columna). ────────── */
.media-item,
.sisipedia-audio-card,
.sisipedia-video-card,
.sisipedia-image-card,
.sisipedia-pdf-card,
.sisipedia-audio-card .card-body,
.sisipedia-video-card .card-body,
.sisipedia-image-card .card-body,
.sisipedia-pdf-card .card-body {
    min-width: 0;
}

/* ── Breadcrumb de jerarquía dentro de cada tarjeta ──────── */
.sisipedia-breadcrumb {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .2rem;
    font-size: .68rem;
    line-height: 1.35;
    color: #868e96;
    overflow: hidden;
    max-height: 2.7em;
}
.sisipedia-breadcrumb-item {
    max-width: 100%;
    overflow-wrap: anywhere;
}
.sisipedia-breadcrumb-sep {
    font-size: .55rem;
    opacity: .55;
    flex-shrink: 0;
}
.sisipedia-breadcrumb-sep:last-child { display: none; }

/* ── Audio cards ─────────────────────────────────────────── */
.sisipedia-audio-stripe {
    height: 4px;
    background: linear-gradient(90deg, #198754, #20c997);
}

/* ── PDF cards ────────────────────────────────────────────── */
.sisipedia-pdf-stripe {
    height: 4px;
    background: linear-gradient(90deg, #dc3545, #f1707a);
}

/* ── Video cards ─────────────────────────────────────────── */
.sisipedia-video-play-btn {
    transition: transform .18s, box-shadow .18s;
    border: none;
}
.sisipedia-video-thumb-wrap:hover .sisipedia-video-play-btn {
    transform: translate(-50%, -50%) scale(1.12) !important;
    box-shadow: 0 6px 20px rgba(0,0,0,.3) !important;
}

/* ── Registro section dentro de cards ────────────────────── */
.sisipedia-reg-section { border-left: 3px solid #dee2e6; }
.sisipedia-reg-link { transition: color .15s; }
.sisipedia-reg-link:hover { color: #0d6efd !important; }

/* ── Image cards ──────────────────────────────────────────── */
.sisipedia-image-thumb-wrap { border-radius: .5rem .5rem 0 0; overflow: hidden; }
.sisipedia-image-thumb-wrap .sisipedia-open-preview-modal {
    transition: transform .18s, opacity .18s, box-shadow .18s;
    border: none;
    opacity: 0 !important;
}
.sisipedia-image-thumb-wrap:hover .sisipedia-open-preview-modal {
    opacity: .92 !important;
    transform: translate(-50%, -50%) scale(1.08);
    box-shadow: 0 6px 20px rgba(0,0,0,.25) !important;
}
.sisipedia-image-card:hover .sisipedia-image-thumb-wrap img {
    transform: scale(1.05);
}
.sisipedia-image-thumb-wrap img {
    transition: transform .3s ease;
}

/* ── Pagination en galería (mejorada) ────────────────────── */
.sisipedia-pagination .pagination {
    flex-wrap: wrap;
    gap: .2rem;
    margin-bottom: 0;
}
.sisipedia-pagination .page-link {
    border-radius: .45rem !important;
    padding: .35rem .7rem;
    font-size: .84rem;
    border: 1px solid #dee2e6;
    color: #495057;
    transition: all .15s ease;
}
.sisipedia-pagination .page-link:hover {
    background: #f0f0f0;
    border-color: #ced4da;
    color: #212529;
}
.sisipedia-pagination .page-item.active .page-link {
    background: #1a6e3c;
    border-color: #1a6e3c;
    color: #fff;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(26,110,60,.25);
}
.sisipedia-pagination .page-item.disabled .page-link {
    opacity: .5;
    pointer-events: none;
}
.sisipedia-pagination .page-item:not(.active):not(.disabled) .page-link:active {
    transform: scale(.95);
}

/* ── Responsive: paginación compacta en móvil ───────────── */
@media (max-width: 575.98px) {
    .sisipedia-pagination .page-link {
        padding: .25rem .55rem;
        font-size: .78rem;
    }
    .sisipedia-pagination .page-item:not(.active):not(.disabled) .page-link {
        background: #fff;
    }
}
</style>

{{-- ══════════════════════════════════════════════════════════
     SCRIPTS
     ══════════════════════════════════════════════════════════ --}}
@if(request()->routeIs('public.sisi'))
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof gsap === 'undefined') return;

    // ── Entrada de pills de tipo ──────────────────────────
    gsap.from('.sisipedia-tipo-pill', {
        opacity: 0, x: -12, stagger: 0.07, duration: 0.4, ease: 'power2.out'
    });

    @if(isset($tipo))
    // ── Buscador de la galería (recarga con ?q=… y resetea la página) ──
    (function () {
        var input = document.getElementById('sisipediaGallerySearch');
        if (!input) return;
        var clearBtn = document.getElementById('sisipediaGallerySearchClear');
        var resetBtn = document.getElementById('sisipediaGallerySearchReset');

        function search(term) {
            var url = new URL(window.location.href);
            term = term.trim();
            if (term) {
                url.searchParams.set('q', term);
            } else {
                url.searchParams.delete('q');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        var debounceTimer;
        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            var term = input.value;
            debounceTimer = setTimeout(function () { search(term); }, 600);
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                clearTimeout(debounceTimer);
                search(input.value);
            }
        });
        if (clearBtn) clearBtn.addEventListener('click', function () { search(''); });
        if (resetBtn) resetBtn.addEventListener('click', function () { search(''); });
    })();

    // ── Entrada escalonada de tarjetas ───────────────────
    var items = document.querySelectorAll('.media-item');
    if (items.length) {
        gsap.fromTo(items,
            { opacity: 0, y: 28 },
            { opacity: 1, y: 0, stagger: 0.045, duration: 0.48, ease: 'power2.out', clearProps: 'all', delay: 0.15 }
        );
    }

    // ── Entrada del encabezado de galería ────────────────
    gsap.from('.sisipedia-gallery-icon-wrap, #sisipedia-gallery h2, #sisipedia-gallery small', {
        opacity: 0, y: 10, stagger: 0.08, duration: 0.4, ease: 'power2.out'
    });

    // ── Hover lift para cards de audio/video/imagen ──────
    document.querySelectorAll('.sisipedia-audio-card, .sisipedia-video-card, .sisipedia-image-card, .sisipedia-pdf-card').forEach(function (card) {
        card.addEventListener('mouseenter', function () {
            gsap.to(this, { y: -6, duration: 0.2, ease: 'power2.out',
                boxShadow: '0 14px 30px rgba(0,0,0,.14)' });
        });
        card.addEventListener('mouseleave', function () {
            gsap.to(this, { y: 0, duration: 0.2, ease: 'power2.out',
                boxShadow: '0 2px 8px rgba(0,0,0,.07)' });
        });
    });

    // Click play en video: el onclick del wrap abre el popup con <video controls>
    // real (abrirVistaPreviaVideo, ver partials.libro-preview-modal) — ya no hay
    // iframe inline que activar aquí.

    @else
    // ── Entrada del árbol de categorías ──────────────────
    gsap.from('#treeCard', {
        opacity: 0, y: 16, duration: 0.5, ease: 'power2.out', delay: 0.1
    });

    // ── Aportaciones recientes: revelado al hacer scroll (queda bajo el árbol) ──
    (function () {
        var container = document.getElementById('sisipedia-tree-aportaciones-section');
        if (!container) return;
        var cards = container.querySelectorAll('.sisipedia-aportacion-card');
        if (!cards.length) return;

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            cards.forEach(function (c) { c.style.opacity = 1; });
            return;
        }

        function animate() {
            gsap.from(cards, { opacity: 0, y: 24, duration: 0.5, stagger: 0.08, ease: 'power2.out' });
        }

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        animate();
                        obs.disconnect();
                    }
                });
            }, { threshold: 0.12 });
            observer.observe(container);
        } else {
            animate();
        }
    })();
    @endif
});
</script>
@endif

@if(!isset($tipo))
{{-- ── Scripts del árbol ──────────────────────────────────────
     Los subtemas profundos llegan vacíos (data-lazy-url) y se piden al abrirlos.
     Delegado en document: también cubre los nodos insertados después. ── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    function setToggleIcon(list, expanded) {
        const btn = document.querySelector('[data-bs-target="#' + list.id + '"]');
        const icon = btn ? btn.querySelector('.toggle-icon') : null;
        if (!icon) return;
        icon.classList.toggle('fa-chevron-right', !expanded);
        icon.classList.toggle('fa-chevron-down', expanded);
    }

    function cargarHijos(list) {
        const url = list.dataset.lazyUrl;
        if (!url || list.dataset.loaded) return;
        list.dataset.loaded = '1';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
            .then(function (html) { list.innerHTML = html; })
            .catch(function () {
                delete list.dataset.loaded;
                list.innerHTML = '<li class="text-danger small ps-4 py-2">No se pudo cargar. ' +
                    '<button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-tree-retry>Intentar de nuevo</button></li>';
            });
    }

    document.addEventListener('show.bs.collapse', function (e) {
        if (!e.target.matches('[data-tree-children]')) return;
        setToggleIcon(e.target, true);
        cargarHijos(e.target);
    });
    document.addEventListener('hide.bs.collapse', function (e) {
        if (e.target.matches('[data-tree-children]')) setToggleIcon(e.target, false);
    });
    document.addEventListener('click', function (e) {
        const retry = e.target.closest('[data-tree-retry]');
        if (retry) cargarHijos(retry.closest('[data-tree-children]'));
    });

    const collapseAllBtn = document.getElementById('collapseAll');
    if (collapseAllBtn) collapseAllBtn.addEventListener('click', function () {
        document.querySelectorAll('[data-tree-children].show').forEach(function (list) {
            bootstrap.Collapse.getOrCreateInstance(list, { toggle: false }).hide();
        });
    });
});
</script>
@endif
@endsection
