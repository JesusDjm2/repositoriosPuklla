@extends('layouts.padre')
@section('titulo', '- Detalle de registro')

@section('estilos')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
@endsection

@section('contenido')
    @include('partials.lib-hero', [
        'heroBg' => asset('img/fondo/Fondo-sisipedia.png'),
        'heroBgAlt' => 'Fondo Sisipedia',
        'heroEyebrow' => 'Sisipedia',
        'heroTitle' => $category->display_name ?? 'Detalle del registro',
        'heroTitleSmall' => true,
        'heroFormatButtons' => true,
    ])
    <div class="container">
        <div class="row mt-2 mt-md-5">
            <div class="col-lg-12">
                <nav aria-label="breadcrumb" class="mb-3 sisipedia-reveal-onload">
                    <ol class="breadcrumb bg-white shadow-sm rounded-3 px-3 py-2 mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('public.sisi') }}" class="text-decoration-none">
                                <i class="fa fa-home me-1"></i>Registros
                            </a>
                        </li>
                        @foreach ($breadcrumbs as $item)
                            @if ($loop->last)
                                <li class="breadcrumb-item active" aria-current="page">{{ $item->display_name }}</li>
                            @else
                                <li class="breadcrumb-item">
                                    <a href="{{ route('sisipedia.categories.show', $item) }}"
                                        class="text-decoration-none">{{ $item->display_name }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>

                {{-- Layout principal: contenido izquierda + sidebar sticky derecha --}}
                <div class="row g-4 align-items-start">

                    {{-- ── Columna principal (info + multimedia) ────────────── --}}
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden sisipedia-reveal-onload">

                            {{-- Imagen --}}
                            @if ($category->image)
                                <img src="{{ asset($category->image) }}" alt="{{ $category->display_name }}"
                                    class="img-fluid w-100" style="max-height: 340px; object-fit: cover;">
                            @else
                                <div class="d-flex align-items-center justify-content-center bg-light"
                                     style="min-height: 160px;">
                                    <i class="fa fa-folder-open fa-3x text-primary opacity-50"></i>
                                </div>
                            @endif

                            <div class="card-body p-3 p-md-4">
                                {{-- Breadcrumb de herencia (sin numeración) --}}
                                @if ($category->parent)
                                    <p class="text-muted small mb-2">
                                        <i class="fa fa-level-up-alt me-1 fa-rotate-90"></i>Pertenece a:
                                        <a href="{{ route('sisipedia.categories.show', $category->parent) }}"
                                           class="text-decoration-none fw-semibold">{{ $category->parent->display_name }}</a>
                                    </p>
                                @endif

                                <h1 class="h3 fw-bold mb-2">{{ $category->display_name }}</h1>
                                <p class="text-muted mb-0">
                                    {{ $category->description ?: 'Este registro no tiene descripción adicional.' }}
                                </p>
                            </div>

                            {{-- ── Archivos adjuntos: grid 3 columnas, por formato ──────── --}}
                            @if ($category->files->isNotEmpty())
                                <div class="border-top px-3 px-md-4 py-3 py-md-4">
                                    <h2 class="h6 fw-bold mb-3 text-secondary">
                                        <i class="fa fa-paperclip me-2 text-primary"></i>Archivos adjuntos
                                    </h2>
                                    <div class="row g-3">
                                        @foreach ($category->files as $archivo)
                                            @php
                                                $tipoMeta = [
                                                    'pdf' => [
                                                        'label' => 'PDF',
                                                        'color' => 'danger',
                                                        'icon' => 'fa-file-pdf',
                                                        'preview' => 'iframe',
                                                        'iframe_h' => 340,
                                                    ],
                                                    'doc' => [
                                                        'label' => 'Word',
                                                        'color' => 'primary',
                                                        'icon' => 'fa-file-word',
                                                        'preview' => 'none',
                                                        'iframe_h' => null,
                                                    ],
                                                    'audio' => [
                                                        'label' => 'Audio',
                                                        'color' => 'success',
                                                        'icon' => 'fa-music',
                                                        'preview' => 'iframe',
                                                        'iframe_h' => 120,
                                                    ],
                                                    'video' => [
                                                        'label' => 'Video',
                                                        'color' => 'warning',
                                                        'icon' => 'fa-video',
                                                        'preview' => 'video',
                                                        'iframe_h' => null,
                                                    ],
                                                    'imagen' => [
                                                        'label' => 'Imagen',
                                                        'color' => 'info',
                                                        'icon' => 'fa-image',
                                                        'preview' => 'imagen',
                                                        'iframe_h' => null,
                                                    ],
                                                ];
                                                $m = $tipoMeta[$archivo->tipo] ?? [
                                                    'label' => ucfirst($archivo->tipo),
                                                    'color' => 'secondary',
                                                    'icon' => 'fa-file',
                                                    'preview' => 'iframe',
                                                    'iframe_h' => 200,
                                                ];
                                                $driveUrl = \App\Services\GoogleDriveService::getUrl($archivo->drive_id);
                                                $downloadUrl = \App\Services\GoogleDriveService::getEmbedUrl($archivo->drive_id);
                                                $previewUrl = \App\Services\GoogleDriveService::getPreviewUrl($archivo->drive_id);
                                                $thumbUrl = $archivo->tipo === 'imagen'
                                                    ? \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 500)
                                                    : ($archivo->tipo === 'video'
                                                        ? \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 400)
                                                        : null);
                                                $fullImgUrl = $archivo->tipo === 'imagen'
                                                    ? \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 1400)
                                                    : null;
                                            @endphp
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <div class="card h-100 shadow-sm rounded-3 overflow-hidden sisipedia-file-card border-0">
                                                    <div class="rounded-top bg-{{ $m['color'] }}" style="height: 4px;" aria-hidden="true"></div>
                                                    <div class="card-header bg-white border-bottom pt-3 pb-2 px-3">
                                                        <div class="d-flex align-items-start justify-content-between gap-2">
                                                            <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                                                                <span class="badge bg-{{ $m['color'] }} bg-opacity-10 text-{{ $m['color'] }} border border-{{ $m['color'] }} mb-2">
                                                                    <i class="fa {{ $m['icon'] }} me-1"></i>{{ $m['label'] }}
                                                                </span>
                                                                <div class="fw-semibold text-dark small text-break" title="{{ $archivo->nombre_display }}">
                                                                    {{ $archivo->nombre_display }}
                                                                </div>
                                                            </div>
                                                            <a href="{{ $downloadUrl }}"
                                                               class="btn btn-sm btn-{{ $m['color'] }} flex-shrink-0">
                                                                <i class="fa fa-download me-1"></i>Descargar
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-0 bg-light">
                                                        @if ($archivo->tipo === 'audio')
                                                            <div class="p-2">
                                                                @include('partials.audio-player', [
                                                                    'src' => route('sisipedia.audio.stream', $archivo),
                                                                    'title' => $archivo->nombre_display,
                                                                    'driveUrl' => $driveUrl,
                                                                    'downloadUrl' => $downloadUrl,
                                                                ])
                                                            </div>
                                                        @elseif ($m['preview'] === 'iframe' && !empty($m['iframe_h']))
                                                            {{-- PDF (y tipos desconocidos): vista previa en popup, no incrustada
                                                                 en la tarjeta — mismo modal que el resto de vistas públicas
                                                                 de Sisipedia (ver partials/home-preview-modal). --}}
                                                            <div class="d-flex flex-column align-items-center justify-content-center text-center px-3 py-4"
                                                                 style="min-height: {{ max(140, min($m['iframe_h'], 200)) }}px;">
                                                                <button type="button"
                                                                        class="btn btn-sm btn-{{ $m['color'] }} sisipedia-open-preview-modal"
                                                                        data-preview="{{ $previewUrl }}"
                                                                        data-nombre="{{ $archivo->nombre_display }}"
                                                                        data-drive="{{ $driveUrl }}"
                                                                        data-download="{{ $downloadUrl }}">
                                                                    <i class="fa fa-eye me-1"></i>Ver vista previa
                                                                </button>
                                                            </div>
                                                        @elseif ($m['preview'] === 'video')
                                                            <div class="sisipedia-file-video-wrap position-relative"
                                                                 role="button" tabindex="0"
                                                                 aria-label="Reproducir {{ $archivo->nombre_display }}"
                                                                 onclick="abrirVistaPreviaVideo({{ \Illuminate\Support\Js::from(route('sisipedia.video.stream', $archivo)) }}, {{ \Illuminate\Support\Js::from($archivo->nombre_display) }}, {{ \Illuminate\Support\Js::from($driveUrl) }})"
                                                                 onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
                                                                <div class="ratio ratio-16x9 bg-dark">
                                                                    <img src="{{ $thumbUrl }}" alt="{{ $archivo->nombre_display }}"
                                                                         class="w-100 h-100" style="object-fit:contain;opacity:.85;"
                                                                         loading="lazy" onerror="this.style.display='none'">
                                                                </div>
                                                                <button type="button" tabindex="-1"
                                                                        class="sisipedia-load-category-video position-absolute top-50 start-50 translate-middle btn btn-light rounded-circle shadow"
                                                                        style="width:52px;height:52px;">
                                                                    <i class="fa fa-play text-dark" style="margin-left:2px;"></i>
                                                                </button>
                                                            </div>
                                                        @elseif ($m['preview'] === 'imagen')
                                                            <button type="button"
                                                                    class="w-100 p-0 border-0 bg-transparent sisipedia-img-preview-trigger"
                                                                    data-full-url="{{ $fullImgUrl }}"
                                                                    data-nombre="{{ $archivo->nombre_display }}">
                                                                <div class="ratio ratio-4x3 bg-light">
                                                                    <img src="{{ $thumbUrl }}" alt="{{ $archivo->nombre_display }}"
                                                                         class="w-100 h-100" style="object-fit:cover;"
                                                                         loading="lazy"
                                                                         onerror="this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center h-100 text-muted\'><i class=\'fa fa-image fa-3x opacity-25\'></i></div>'">
                                                                </div>
                                                            </button>
                                                        @else
                                                            <div class="d-flex flex-column align-items-center justify-content-center text-center px-3 py-5">
                                                                <span class="rounded-circle bg-{{ $m['color'] }} bg-opacity-10 text-{{ $m['color'] }} d-inline-flex align-items-center justify-content-center mb-3"
                                                                      style="width: 64px; height: 64px;">
                                                                    <i class="fa {{ $m['icon'] }} fa-2x"></i>
                                                                </span>
                                                                <p class="small text-muted mb-3 mb-md-0">
                                                                    @if ($archivo->tipo === 'doc')
                                                                        Vista previa no disponible aquí; descarga el archivo o ábrelo en Drive.
                                                                    @else
                                                                        Abre o descarga el archivo para verlo.
                                                                    @endif
                                                                </p>
                                                                <a href="{{ $driveUrl }}" target="_blank" rel="noopener"
                                                                   class="btn btn-outline-{{ $m['color'] }} btn-sm">
                                                                    <i class="fa fa-external-link-alt me-1"></i>Abrir en Google Drive
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- ── Sidebar sticky: registros heredados ──────────────── --}}
                    @php $publicChildren = $category->children->where('is_active', true); @endphp
                    <div class="col-lg-4" style="position: sticky; top: 5.5rem;">
                        <div class="card border-0 rounded-4 overflow-hidden sisipedia-reveal-onload"
                             style="box-shadow: 0 4px 20px rgba(13,110,253,.13); border-top: 3px solid #0d6efd !important;">

                            {{-- Header destacado --}}
                            <div class="px-3 px-md-4 py-3"
                                 style="background: linear-gradient(135deg,#0d6efd 0%,#6610f2 100%);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h2 class="h6 mb-0 text-white fw-bold">
                                        <i class="fa fa-sitemap me-2"></i>Registros heredados
                                    </h2>
                                    @if ($publicChildren->count())
                                        <span class="badge bg-white text-primary fw-bold rounded-pill"
                                              style="font-size:.75rem;">
                                            {{ $publicChildren->count() }}
                                            {{ $publicChildren->count() === 1 ? 'registro' : 'registros' }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="card-body p-2 p-md-3">
                                @if ($publicChildren->count())
                                    <div class="d-grid gap-2">
                                        @foreach ($publicChildren as $child)
                                            <a href="{{ route('sisipedia.categories.show', $child) }}"
                                               class="card border text-decoration-none hover-shadow sisipedia-child-card"
                                               style="transition: box-shadow .15s, border-color .15s;">
                                                <div class="d-flex gap-2 align-items-start p-2">
                                                    {{-- Imagen o ícono --}}
                                                    @if ($child->image)
                                                        <img src="{{ asset($child->image) }}" alt="{{ $child->display_name }}"
                                                            class="rounded flex-shrink-0 mt-1" width="40" height="40"
                                                            style="object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0 mt-1"
                                                            style="width:40px; height:40px; min-width:40px;">
                                                            <i class="fa fa-bookmark"></i>
                                                        </div>
                                                    @endif

                                                    <div class="overflow-hidden flex-grow-1">
                                                        <div class="fw-semibold text-dark text-truncate" style="font-size:.9rem;">
                                                            {{ $child->display_name }}
                                                        </div>
                                                        @if ($child->description)
                                                            <small class="text-muted d-block text-truncate" style="font-size:.75rem;">
                                                                {{ Str::limit($child->description, 60) }}
                                                            </small>
                                                        @endif
                                                        {{-- Badges de archivos del hijo --}}
                                                        @php $childFiles = $child->relationLoaded('files') ? $child->files : collect(); @endphp
                                                        @if ($childFiles->isNotEmpty())
                                                            <div class="d-flex flex-wrap mt-1" style="gap:.2rem;">
                                                                @if ($childFiles->where('tipo','pdf')->isNotEmpty())
                                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"
                                                                          style="font-size:.6rem; padding:.2rem .4rem;">
                                                                        <i class="fa fa-file-pdf me-1"></i>PDF
                                                                    </span>
                                                                @endif
                                                                @if ($childFiles->where('tipo','doc')->isNotEmpty())
                                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary"
                                                                          style="font-size:.6rem; padding:.2rem .4rem;">
                                                                        <i class="fa fa-file-word me-1"></i>Doc
                                                                    </span>
                                                                @endif
                                                                @if ($childFiles->where('tipo','audio')->isNotEmpty())
                                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success"
                                                                          style="font-size:.6rem; padding:.2rem .4rem;">
                                                                        <i class="fa fa-music me-1"></i>Audio
                                                                    </span>
                                                                @endif
                                                                @if ($childFiles->where('tipo','video')->isNotEmpty())
                                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary"
                                                                          style="font-size:.6rem; padding:.2rem .4rem;">
                                                                        <i class="fa fa-video me-1"></i>Video
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Flecha indicadora --}}
                                                    <i class="fa fa-chevron-right text-muted flex-shrink-0 mt-2"
                                                       style="font-size:.75rem;"></i>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="fa fa-folder-open fa-2x mb-2 d-block opacity-50"></i>
                                        <small>No hay sub-registros en este nivel.</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ── Aportaciones ─────────────────────────────────────────── --}}
                <div class="row g-4 mt-1" id="sisipedia-aportaciones-section">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white border-0 pt-3 pb-0 px-3 px-md-4">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <h2 class="h5 mb-0">
                                        <i class="fa fa-layer-group me-2 text-primary"></i>
                                        Aportaciones
                                        @if($category->aportaciones->count())
                                            <span class="badge bg-primary ms-1">{{ $category->aportaciones->count() }}</span>
                                        @endif
                                    </h2>
                                    <button class="btn btn-primary px-4 py-2 fw-semibold shadow-sm" id="btnAbrirRoles"
                                            onclick="mostrarRoles()"
                                            style="border-radius:2rem; background:linear-gradient(135deg,#0d6efd,#6610f2); border:none; letter-spacing:.03em;">
                                        <i class="fa fa-plus-circle me-2"></i>Agregar aportación
                                    </button>
                                </div>
                                <p class="text-muted small mb-0 mt-2">
                                    <i class="fa fa-info-circle me-1"></i>¿Conoces algo sobre este tema? ¡Comparte tu conocimiento con la comunidad!
                                </p>
                            </div>

                            {{-- Paso 1: Selector de rol --}}
                            <div id="rolSelector" class="px-3 px-md-4 pt-3 pb-2" style="display:none;">
                                <p class="fw-semibold mb-3 text-center">¿Cómo deseas aportar?</p>
                                <div class="row row-cols-2 row-cols-lg-4 g-3 justify-content-center mb-3">
                                    {{-- Equipo Puklla --}}
                                    <div class="col">
                                        <div class="card border-2 rounded-4 text-center p-3 h-100 rol-card"
                                             style="cursor:pointer; border-color:#6f42c1 !important; transition: transform .15s, box-shadow .15s;"
                                             onclick="seleccionarRol('Equipo Puklla')">
                                            <div class="mb-2">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white"
                                                      style="width:56px;height:56px;background:#6f42c1;">
                                                    <i class="fa fa-users fa-lg"></i>
                                                </span>
                                            </div>
                                            <div class="fw-bold fs-6">Equipo Puklla</div>
                                            <small class="text-muted">Institucional</small>
                                        </div>
                                    </div>
                                    {{-- Docente --}}
                                    <div class="col">
                                        <div class="card border-2 rounded-4 text-center p-3 h-100 rol-card"
                                             style="cursor:pointer; border-color:#0d6efd !important; transition: transform .15s, box-shadow .15s;"
                                             onclick="seleccionarRol('Docente')">
                                            <div class="mb-2">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
                                                      style="width:56px;height:56px;">
                                                    <i class="fa fa-chalkboard-teacher fa-lg"></i>
                                                </span>
                                            </div>
                                            <div class="fw-bold fs-6">Docente</div>
                                            <small class="text-muted">Aporto como educador</small>
                                        </div>
                                    </div>
                                    {{-- Lidereza/Líder --}}
                                    <div class="col">
                                        <div class="card border-2 rounded-4 text-center p-3 h-100 rol-card"
                                             style="cursor:pointer; border-color:#198754 !important; transition: transform .15s, box-shadow .15s;"
                                             onclick="seleccionarRol('Líder')">
                                            <div class="mb-2">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white"
                                                      style="width:56px;height:56px;">
                                                    <i class="fa fa-star fa-lg"></i>
                                                </span>
                                            </div>
                                            <div class="fw-bold fs-6">{{ \App\Models\sisipedia\Aportacion::ROL_LIDER_ETIQUETA }}</div>
                                            <small class="text-muted">Comunidad</small>
                                        </div>
                                    </div>
                                    {{-- Niño/Estudiante --}}
                                    <div class="col">
                                        <div class="card border-2 rounded-4 text-center p-3 h-100 rol-card"
                                             style="cursor:pointer; border-color:#fd7e14 !important; transition: transform .15s, box-shadow .15s;"
                                             onclick="seleccionarRol('Niño/Estudiante')">
                                            <div class="mb-2">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white"
                                                      style="width:56px;height:56px;background:#fd7e14;">
                                                    <i class="fa fa-child fa-lg"></i>
                                                </span>
                                            </div>
                                            <div class="fw-bold fs-6">Niño / Estudiante</div>
                                            <small class="text-muted">Aporto como estudiante</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="cancelarAportacion()">
                                        <i class="fa fa-times me-1"></i>Cancelar
                                    </button>
                                </div>
                            </div>

                            {{-- Paso 2: Formulario de aportación --}}
                            <div id="formAportacionWrap" class="px-3 px-md-4 pt-3" style="display:none;">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif
                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                                    </div>
                                @endif

                                {{-- Badge con rol elegido --}}
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge fs-6 px-3 py-2" id="rolBadge" style="background:#0d6efd;">
                                        <i class="fa fa-user me-1"></i><span id="rolTexto"></span>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-link text-muted p-0"
                                            onclick="volverARoles()">
                                        <i class="fa fa-arrow-left me-1"></i>Cambiar rol
                                    </button>
                                </div>

                                <form action="{{ route('sisipedia.categories.aportaciones.store', $category) }}"
                                      method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="rol_nombre" id="inputRolNombre"
                                           value="{{ old('rol_nombre') }}">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Nombre completo:<span class="text-danger">*</span></label>
                                            <input type="text" name="nombre_ol" class="form-control @error('nombre_ol') is-invalid @enderror"
                                                   value="{{ old('nombre_ol') }}" required>
                                            @error('nombre_ol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Institución:</label>
                                            <input type="text" name="institucion" class="form-control @error('institucion') is-invalid @enderror"
                                                   value="{{ old('institucion') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Ubicación:</label>
                                            <input type="text" name="ubicacion" class="form-control @error('ubicacion') is-invalid @enderror"
                                                   value="{{ old('ubicacion') }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Título:</label>
                                            <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror"
                                                   value="{{ old('titulo') }}">
                                            @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Detalle:</label>
                                            <textarea name="detalle" rows="3"
                                                      class="form-control summernote-editor @error('detalle') is-invalid @enderror">{{ old('detalle') }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">
                                                <i class="fa fa-upload text-primary me-1"></i>Archivos (puedes subir varios)
                                            </label>
                                            <input type="file" name="archivos[]" multiple
                                                   accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.mp3,.wav,.ogg,.mp4,.webm,.mov,.m4v"
                                                   class="form-control @error('archivos') is-invalid @enderror @error('archivos.*') is-invalid @enderror"
                                                   id="archivosInput">
                                            <small class="text-muted">
                                                <i class="fa fa-info-circle me-1"></i>
                                                Límites: Imagen 10MB · PDF/Word 100MB · Audio 150MB · Video 300MB
                                            </small>
                                            @error('archivos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            @error('archivos.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <div id="archivosPreview" class="mt-2 d-none">
                                                <div class="d-flex flex-wrap gap-2" id="archivosPreviewList"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mb-3">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-save me-1"></i>Guardar aportación
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="cancelarAportacion()">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                                <hr>
                            </div>

                            {{-- Lista de aportaciones --}}
                            <div class="card-body p-3 p-md-4">
                                @forelse($category->aportaciones as $aportacion)
                                    <div class="card border rounded-3 mb-3 sisipedia-aportacion-item">
                                        <div class="card-body p-3">
                                            @php $archivos = $aportacion->relationLoaded('archivos') ? $aportacion->archivos : $aportacion->archivos()->get(); @endphp
                                            @include('sisichakuna.partials.aportacion-card', ['aportacion' => $aportacion, 'archivos' => $archivos])
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                        No hay aportaciones para este registro aún.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    const rolColors = {
                        'Equipo Puklla':   '#6f42c1',
                        'Docente':         '#0d6efd',
                        'Líder':           '#198754',
                        'Niño/Estudiante': '#fd7e14',
                    };
                    const rolTextoMostrar = {
                        'Equipo Puklla': 'Equipo Puklla',
                        'Docente': 'Docente',
                        'Líder': @json(\App\Models\sisipedia\Aportacion::ROL_LIDER_ETIQUETA),
                        'Niño/Estudiante': 'Niño/Estudiante',
                    };

                    function mostrarRoles() {
                        document.getElementById('rolSelector').style.display = 'block';
                        document.getElementById('formAportacionWrap').style.display = 'none';
                        document.getElementById('btnAbrirRoles').style.display = 'none';
                    }

                    function seleccionarRol(rol) {
                        document.getElementById('inputRolNombre').value = rol;
                        document.getElementById('rolTexto').textContent  = rolTextoMostrar[rol] ?? rol;
                        document.getElementById('rolBadge').style.background = rolColors[rol] ?? '#6c757d';
                        document.getElementById('rolSelector').style.display = 'none';
                        document.getElementById('formAportacionWrap').style.display = 'block';
                    }

                    function volverARoles() {
                        document.getElementById('formAportacionWrap').style.display = 'none';
                        document.getElementById('rolSelector').style.display = 'block';
                    }

                    function cancelarAportacion() {
                        document.getElementById('rolSelector').style.display = 'none';
                        document.getElementById('formAportacionWrap').style.display = 'none';
                        document.getElementById('btnAbrirRoles').style.display = '';
                    }

                    @if($errors->any() && old('rol_nombre'))
                        window.addEventListener('DOMContentLoaded', function() {
                            seleccionarRol('{{ old('rol_nombre') }}');
                        });
                    @endif

                    document.addEventListener('DOMContentLoaded', function() {
                        var input = document.getElementById('archivosInput');
                        if (input) {
                            input.addEventListener('change', function() {
                                var preview = document.getElementById('archivosPreview');
                                var list = document.getElementById('archivosPreviewList');
                                list.innerHTML = '';
                                if (this.files.length > 0) {
                                    preview.classList.remove('d-none');
                                    Array.from(this.files).forEach(function(file) {
                                        var icon = 'fa-file';
                                        var color = 'secondary';
                                        if (file.type.startsWith('image/')) { icon = 'fa-image'; color = 'info'; }
                                        else if (file.type === 'application/pdf') { icon = 'fa-file-pdf'; color = 'danger'; }
                                        else if (file.type.includes('word')) { icon = 'fa-file-word'; color = 'primary'; }
                                        else if (file.type.startsWith('audio/')) { icon = 'fa-music'; color = 'success'; }
                                        else if (file.type.startsWith('video/')) { icon = 'fa-video'; color = 'warning'; }
                                        var sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                                        var badge = document.createElement('span');
                                        badge.className = 'badge bg-' + color + ' bg-opacity-10 text-' + color + ' border border-' + color;
                                        badge.innerHTML = '<i class="fa ' + icon + ' me-1"></i>' + file.name + ' <small>(' + sizeMB + 'MB)</small>';
                                        list.appendChild(badge);
                                    });
                                } else {
                                    preview.classList.add('d-none');
                                }
                            });
                        }
                    });

                    function initSummernote() {
                        if (window.jQuery && $('.summernote-editor').length && !$('.summernote-editor').hasClass('summernote-initialized')) {
                            $('.summernote-editor').summernote({
                                height: 120,
                                toolbar: [
                                    ['style', ['style']],
                                    ['font', ['bold', 'italic', 'underline', 'clear']],
                                    ['para', ['ul', 'ol', 'paragraph']],
                                    ['insert', ['link']],
                                    ['view', ['fullscreen', 'codeview']]
                                ],
                                placeholder: 'Describe los detalles de esta aportación...',
                                callbacks: {
                                    onInit: function() {
                                        $('.note-editable').css('font-size', '14px');
                                        $('.summernote-editor').addClass('summernote-initialized');
                                    }
                                }
                            });
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        initSummernote();
                        const originalSeleccionarRol = window.seleccionarRol;
                        window.seleccionarRol = function(rol) {
                            originalSeleccionarRol(rol);
                            setTimeout(initSummernote, 100);
                        };
                    });

                    {{-- Video (categoría y aportaciones): la miniatura solo abre el popup con
                         <video controls> real (ver abrirVistaPreviaVideo en libro-preview-modal),
                         ya no carga un iframe inline — el click va directo en el onclick del wrap. --}}

                    /** Imágenes (categoría y aportaciones): siempre miniatura + modal, nunca iframe */
                    document.addEventListener('click', function (e) {
                        var trigger = e.target.closest('.sisipedia-img-preview-trigger');
                        if (!trigger) return;
                        e.preventDefault();
                        var modalEl = document.getElementById('sisipediaDetalleImageModal');
                        if (!modalEl) return;
                        var modalImg = document.getElementById('sisipediaDetalleImageModalImg');
                        var modalName = document.getElementById('sisipediaDetalleImageModalName');
                        var url = trigger.getAttribute('data-full-url');
                        var nombre = trigger.getAttribute('data-nombre') || 'Imagen';
                        if (modalImg) modalImg.src = url;
                        if (modalName) modalName.textContent = nombre;
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    });

                    document.addEventListener('hidden.bs.modal', function (e) {
                        if (e.target && e.target.id === 'sisipediaDetalleImageModal') {
                            var modalImg = document.getElementById('sisipediaDetalleImageModalImg');
                            if (modalImg) modalImg.src = '';
                        }
                    });
                </script>

                {{-- ══════════════════════════════════════════════════════════
                     Animaciones GSAP: entrada al cargar + revelado al hacer scroll
                     ══════════════════════════════════════════════════════════ --}}
                <script>
                    (function () {
                        var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                        function ensureGsap(callback) {
                            if (typeof gsap !== 'undefined') { callback(); return; }
                            var script = document.createElement('script');
                            script.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
                            script.onload = callback;
                            script.onerror = function () {};
                            document.head.appendChild(script);
                        }

                        function revealInstantly(nodeList) {
                            nodeList.forEach(function (el) {
                                el.style.opacity = 1;
                                el.style.transform = 'none';
                            });
                        }

                        // ── Entrada al cargar la página ──────────────────────
                        var onloadTargets = document.querySelectorAll('.sisipedia-reveal-onload');
                        if (onloadTargets.length) {
                            if (prefersReducedMotion) {
                                revealInstantly(onloadTargets);
                            } else {
                                ensureGsap(function () {
                                    gsap.from(onloadTargets, {
                                        opacity: 0, y: 18, duration: 0.55, stagger: 0.1, ease: 'power2.out'
                                    });
                                    var fileCards = document.querySelectorAll('.sisipedia-file-card');
                                    if (fileCards.length) {
                                        gsap.from(fileCards, {
                                            opacity: 0, y: 14, duration: 0.4, stagger: 0.06,
                                            ease: 'power2.out', delay: 0.25, clearProps: 'all'
                                        });
                                    }
                                    var childCards = document.querySelectorAll('.sisipedia-child-card');
                                    if (childCards.length) {
                                        gsap.from(childCards, {
                                            opacity: 0, y: 14, duration: 0.4, stagger: 0.06,
                                            ease: 'power2.out', delay: 0.3, clearProps: 'all'
                                        });
                                    }
                                });
                            }
                        }

                        // ── Revelado al hacer scroll para secciones bajo el pliegue ──
                        function revealOnScroll(containerSelector, itemSelector) {
                            var container = document.querySelector(containerSelector);
                            if (!container) return;
                            var items = container.querySelectorAll(itemSelector);
                            if (!items.length) return;

                            if (prefersReducedMotion) {
                                revealInstantly(items);
                                return;
                            }

                            function animate() {
                                gsap.from(items, {
                                    opacity: 0, y: 24, duration: 0.5, stagger: 0.08, ease: 'power2.out'
                                });
                            }

                            if ('IntersectionObserver' in window) {
                                var observer = new IntersectionObserver(function (entries, obs) {
                                    entries.forEach(function (entry) {
                                        if (entry.isIntersecting) {
                                            ensureGsap(animate);
                                            obs.disconnect();
                                        }
                                    });
                                }, { threshold: 0.12 });
                                observer.observe(container);
                            } else {
                                ensureGsap(animate);
                            }
                        }

                        revealOnScroll('#sisipedia-aportaciones-section', '.sisipedia-aportacion-item');
                        revealOnScroll('#sisipedia-sueltas-section', '.sisipedia-aportacion-item');
                    })();
                </script>

                <style>
                    @media (min-width: 768px) {
                        .border-end-md { border-right: 1px solid #dee2e6 !important; }
                    }
                    .hover-shadow:hover {
                        box-shadow: 0 4px 12px rgba(0,0,0,.1) !important;
                        border-color: #0d6efd !important;
                    }

                    /* ── Tarjetas de "Archivos adjuntos" ─────────────────── */
                    .sisipedia-file-card {
                        transition: transform .2s ease, box-shadow .2s ease;
                    }
                    .sisipedia-file-card:hover {
                        transform: translateY(-4px);
                        box-shadow: 0 14px 30px rgba(0,0,0,.12) !important;
                    }

                    /* ── Miniaturas de video (categoría y aportaciones) ──── */
                    .sisipedia-file-video-wrap,
                    .sisipedia-ap-video-preview {
                        cursor: pointer;
                    }
                    .sisipedia-load-category-video,
                    .sisipedia-ap-video-play-btn {
                        border: none;
                        transition: transform .18s ease, box-shadow .18s ease;
                    }
                    .sisipedia-file-video-wrap:hover .sisipedia-load-category-video,
                    .sisipedia-ap-video-preview:hover .sisipedia-ap-video-play-btn {
                        transform: translate(-50%, -50%) scale(1.1);
                        box-shadow: 0 6px 18px rgba(0,0,0,.3) !important;
                    }

                    /* ── Miniaturas de imagen (categoría y aportaciones) ─── */
                    .sisipedia-img-preview-trigger {
                        cursor: zoom-in;
                        display: block;
                        overflow: hidden;
                    }
                    .sisipedia-img-preview-trigger img {
                        transition: transform .3s ease;
                    }
                    .sisipedia-img-preview-trigger:hover img {
                        transform: scale(1.05);
                    }

                    /* ── Tarjetas de aportación (propias y de la comunidad) ── */
                    .sisipedia-aportacion-item {
                        transition: box-shadow .2s ease, transform .2s ease;
                    }
                    .sisipedia-aportacion-item:hover {
                        box-shadow: 0 10px 24px rgba(0,0,0,.08);
                    }

                    /* ── Registros heredados (sidebar) ───────────────────── */
                    .sisipedia-child-card {
                        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
                    }
                    .sisipedia-child-card:hover {
                        transform: translateX(3px);
                    }
                </style>

                {{-- Modal único de imagen: reutilizado por "Archivos adjuntos" y todas las aportaciones de la página --}}
                <div class="modal fade" id="sisipediaDetalleImageModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4 overflow-hidden">
                            <div class="modal-header border-0 pb-0">
                                <h6 class="modal-title text-truncate">
                                    <i class="fa fa-image text-info me-1"></i>
                                    <span id="sisipediaDetalleImageModalName"></span>
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body p-0 text-center bg-dark">
                                <img id="sisipediaDetalleImageModalImg" src="" alt=""
                                     class="img-fluid" style="max-height:80vh;">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Aportaciones recientes (sin registro) ────────────── --}}
    @if(isset($aportacionesSueltas) && $aportacionesSueltas->isNotEmpty())
        <div class="container" id="sisipedia-sueltas-section">
            <div class="row g-4 mt-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 pt-3 pb-0 px-3 px-md-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa fa-globe text-primary"></i>
                                <h2 class="h5 mb-0">Aportaciones recientes de la comunidad</h2>
                                <span class="badge bg-primary ms-1">{{ $aportacionesSueltas->count() }}</span>
                            </div>
                            <p class="text-muted small mb-0 mt-1">
                                <i class="fa fa-info-circle me-1"></i>Contribuciones de visitantes que aún no están vinculadas a un registro específico.
                            </p>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            @foreach($aportacionesSueltas as $aportacion)
                                <div class="card border rounded-3 mb-3 sisipedia-aportacion-item">
                                    <div class="card-body p-3">
                                        @php $archivos = $aportacion->relationLoaded('archivos') ? $aportacion->archivos : $aportacion->archivos()->get(); @endphp
                                        @include('sisichakuna.partials.aportacion-card', ['aportacion' => $aportacion, 'archivos' => $archivos])
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('partials.home-preview-modal')
@endsection
