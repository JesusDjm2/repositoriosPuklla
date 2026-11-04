@extends('layouts.padre')
@section('titulo', '- ' . ($category->display_name ?? 'Detalle de registro'))

@section('estilos')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
@endsection

@section('contenido')
    @php
        // Ancestros (sin el registro actual) = la "herencia" del registro.
        $ancestros = $breadcrumbs->slice(0, -1);
        $subRegistros = $category->children; // ya filtrados a activos en el controlador
        $aportaciones = $category->aportaciones;

        $tipoMeta = [
            'pdf' => ['label' => 'PDF', 'color' => 'danger', 'icon' => 'fa-file-pdf'],
            'doc' => ['label' => 'Word', 'color' => 'primary', 'icon' => 'fa-file-word'],
            'audio' => ['label' => 'Audio', 'color' => 'success', 'icon' => 'fa-music'],
            'video' => ['label' => 'Video', 'color' => 'warning', 'icon' => 'fa-video'],
            'imagen' => ['label' => 'Imagen', 'color' => 'info', 'icon' => 'fa-image'],
        ];
        $roles = [
            'Equipo Puklla' => ['color' => '#6f42c1', 'icon' => 'fa-users', 'label' => 'Equipo Puklla', 'hint' => 'Institucional'],
            'Docente' => ['color' => '#0d6efd', 'icon' => 'fa-chalkboard-teacher', 'label' => 'Docente', 'hint' => 'Aporto como educador'],
            'Líder' => ['color' => '#198754', 'icon' => 'fa-star', 'label' => \App\Models\sisipedia\Aportacion::ROL_LIDER_ETIQUETA, 'hint' => 'Comunidad'],
            'Niño/Estudiante' => ['color' => '#fd7e14', 'icon' => 'fa-child', 'label' => 'Niño / Estudiante', 'hint' => 'Aporto como estudiante'],
        ];
        $idiomaLabel = $category->idioma ? (\App\Models\sisipedia\Category::IDIOMAS[$category->idioma] ?? ucfirst($category->idioma)) : null;
    @endphp

    @include('partials.lib-hero', [
        'heroBg' => asset('img/fondo/Fondo-sisipedia.png'),
        'heroBgAlt' => 'Fondo Sisipedia',
        'heroEyebrow' => 'Sisipedia',
        'heroTitle' => $category->display_name ?? 'Detalle del registro',
        'heroTitleSmall' => true,
        'heroFormatButtons' => true,
    ])

    <div class="container sisi-reg">
        {{-- ── Migas: desplazables en horizontal en celulares, nunca ensanchan la página ── --}}
        <nav aria-label="Ruta del registro" class="sisi-reg__crumbs sisipedia-reveal-onload">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('public.sisi') }}"><i class="fa fa-home me-1"></i>Registros</a>
                </li>
                @foreach ($breadcrumbs as $item)
                    @if ($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">{{ $item->display_name }}</li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ route('sisipedia.categories.show', $item) }}">{{ $item->display_name }}</a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>

        <div class="row g-4 align-items-start">
            {{-- ══ Columna principal ══════════════════════════════════════ --}}
            <div class="col-lg-8 sisi-min-0">

                {{-- Cabecera del registro --}}
                <section class="sisi-card sisi-reg__head sisipedia-reveal-onload">
                    @if ($ancestros->isNotEmpty())
                        <p class="sisi-reg__lineage">
                            <i class="fa fa-sitemap" aria-hidden="true"></i>
                            <span class="visually-hidden">Pertenece a:</span>
                            @foreach ($ancestros as $anc)
                                <a href="{{ route('sisipedia.categories.show', $anc) }}">{{ $anc->display_name }}</a>
                                @unless ($loop->last)<span aria-hidden="true">›</span>@endunless
                            @endforeach
                        </p>
                    @endif

                    <h1 class="sisi-reg__title">{{ $category->display_name }}</h1>

                    @if ($category->description)
                        <p class="sisi-reg__desc">{{ $category->description }}</p>
                    @endif

                    @if ($category->fecha || $idiomaLabel || $category->localidad)
                        <ul class="sisi-reg__facts">
                            @if ($category->fecha)
                                <li><i class="fa fa-calendar" aria-hidden="true"></i>{{ $category->fecha->format('d/m/Y') }}</li>
                            @endif
                            @if ($idiomaLabel)
                                <li><i class="fa fa-language" aria-hidden="true"></i>{{ $idiomaLabel }}</li>
                            @endif
                            @if ($category->localidad)
                                <li><i class="fa fa-map-marker-alt" aria-hidden="true"></i>{{ $category->localidad }}</li>
                            @endif
                        </ul>
                    @endif

                    <dl class="sisi-reg__stats">
                        <div>
                            <dt>Archivos</dt>
                            <dd>{{ $category->files->count() }}</dd>
                        </div>
                        <div>
                            <dt>Sub-registros</dt>
                            <dd>{{ $subRegistros->count() }}</dd>
                        </div>
                        <div>
                            <dt>Aportaciones</dt>
                            <dd><a href="#sisipedia-aportaciones-section">{{ $aportaciones->count() }}</a></dd>
                        </div>
                    </dl>
                </section>

                {{-- Archivos del registro (curados por el equipo) --}}
                @if ($category->files->isNotEmpty())
                    <section class="sisi-card mt-4 sisipedia-reveal-onload" aria-labelledby="sisi-files-title">
                        <h2 class="sisi-section-title" id="sisi-files-title">
                            <i class="fa fa-paperclip" aria-hidden="true"></i>Archivos del registro
                            <span class="sisi-count">{{ $category->files->count() }}</span>
                        </h2>
                        <div class="row g-3">
                            @foreach ($category->files as $archivo)
                                @php
                                    $m = $tipoMeta[$archivo->tipo] ?? ['label' => ucfirst($archivo->tipo), 'color' => 'secondary', 'icon' => 'fa-file'];
                                    $driveUrl = \App\Services\GoogleDriveService::getUrl($archivo->drive_id);
                                    $downloadUrl = \App\Services\GoogleDriveService::getEmbedUrl($archivo->drive_id);
                                    $previewUrl = \App\Services\GoogleDriveService::getPreviewUrl($archivo->drive_id);
                                @endphp
                                <div class="{{ $archivo->tipo === 'audio' ? 'col-12' : 'col-12 col-sm-6' }}">
                                    <div class="sisipedia-file-card">
                                        @if ($archivo->tipo === 'imagen')
                                            <button type="button" class="sisipedia-file-card__media sisipedia-img-preview-trigger"
                                                    data-full-url="{{ \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 1400) }}"
                                                    data-nombre="{{ $archivo->nombre_display }}"
                                                    aria-label="Ampliar {{ $archivo->nombre_display }}">
                                                <img src="{{ \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 500) }}"
                                                     alt="{{ $archivo->nombre_display }}" loading="lazy"
                                                     onerror="this.style.display='none'">
                                            </button>
                                        @elseif ($archivo->tipo === 'video')
                                            <div class="sisipedia-file-card__media sisipedia-file-card__media--video sisipedia-file-video-wrap"
                                                 role="button" tabindex="0"
                                                 aria-label="Reproducir {{ $archivo->nombre_display }}"
                                                 onclick="abrirVistaPreviaVideo({{ \Illuminate\Support\Js::from(route('sisipedia.video.stream', $archivo)) }}, {{ \Illuminate\Support\Js::from($archivo->nombre_display) }}, {{ \Illuminate\Support\Js::from($driveUrl) }})"
                                                 onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
                                                <img src="{{ \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 400) }}"
                                                     alt="{{ $archivo->nombre_display }}" loading="lazy"
                                                     onerror="this.style.display='none'">
                                                <span class="sisipedia-load-category-video" aria-hidden="true"><i class="fa fa-play"></i></span>
                                            </div>
                                        @elseif ($archivo->tipo === 'audio')
                                            <div class="p-2">
                                                @include('partials.audio-player', [
                                                    'src' => route('sisipedia.audio.stream', $archivo),
                                                    'title' => $archivo->nombre_display,
                                                    'driveUrl' => $driveUrl,
                                                    'downloadUrl' => $downloadUrl,
                                                ])
                                            </div>
                                        @endif

                                        <div class="sisipedia-file-card__body">
                                            <span class="sisipedia-file-card__icon bg-{{ $m['color'] }} bg-opacity-10 text-{{ $m['color'] }}" aria-hidden="true">
                                                <i class="fa {{ $m['icon'] }}"></i>
                                            </span>
                                            <div class="sisi-min-0 flex-grow-1">
                                                <div class="sisipedia-file-card__name" title="{{ $archivo->nombre_display }}">{{ $archivo->nombre_display }}</div>
                                                <div class="sisipedia-file-card__type">{{ $m['label'] }}</div>
                                            </div>
                                            <div class="sisipedia-file-card__actions">
                                                @if ($archivo->tipo === 'pdf')
                                                    <button type="button" class="btn btn-sm btn-outline-{{ $m['color'] }} sisipedia-open-preview-modal"
                                                            data-preview="{{ $previewUrl }}" data-nombre="{{ $archivo->nombre_display }}"
                                                            data-drive="{{ $driveUrl }}" data-download="{{ $downloadUrl }}"
                                                            title="Vista previa" aria-label="Vista previa de {{ $archivo->nombre_display }}">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                @elseif (! in_array($archivo->tipo, ['imagen', 'video', 'audio'], true))
                                                    <a href="{{ $driveUrl }}" target="_blank" rel="noopener"
                                                       class="btn btn-sm btn-outline-{{ $m['color'] }}"
                                                       title="Abrir en Google Drive" aria-label="Abrir {{ $archivo->nombre_display }} en Google Drive">
                                                        <i class="fa fa-external-link-alt"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ $downloadUrl }}" class="btn btn-sm btn-{{ $m['color'] }}"
                                                   title="Descargar" aria-label="Descargar {{ $archivo->nombre_display }}">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- ══ Lateral: sub-registros (herencia hacia abajo) ═══════════ --}}
            <aside class="col-lg-4 sisi-min-0 sisi-reg__aside" aria-labelledby="sisi-subs-title">
                <section class="sisi-card sisi-card--flush sisipedia-reveal-onload">
                    <header class="sisi-subs__head">
                        <h2 class="sisi-section-title mb-0" id="sisi-subs-title">
                            <i class="fa fa-sitemap" aria-hidden="true"></i>Sub-registros
                        </h2>
                        @if ($subRegistros->isNotEmpty())
                            <span class="sisi-count">{{ $subRegistros->count() }}</span>
                        @endif
                    </header>

                    @if ($subRegistros->isNotEmpty())
                        <ul class="sisi-subs">
                            @foreach ($subRegistros as $child)
                                @php $tiposHijo = $child->files->pluck('tipo')->unique(); @endphp
                                <li>
                                    <a href="{{ route('sisipedia.categories.show', $child) }}" class="sisipedia-child-card">
                                        <span class="sisi-subs__icon" aria-hidden="true"><i class="fa fa-folder"></i></span>
                                        <span class="sisi-min-0 flex-grow-1">
                                            <span class="sisi-subs__name">{{ $child->display_name }}</span>
                                            @if ($child->description)
                                                <span class="sisi-subs__desc">{{ Str::limit($child->description, 70) }}</span>
                                            @endif
                                            @if ($tiposHijo->isNotEmpty() || $child->aportaciones_count)
                                                <span class="sisi-subs__tags">
                                                    @foreach ($tiposHijo as $t)
                                                        @php $tm = $tipoMeta[$t] ?? null; @endphp
                                                        @if ($tm)
                                                            <span class="text-{{ $tm['color'] }}" title="{{ $tm['label'] }}"><i class="fa {{ $tm['icon'] }}"></i></span>
                                                        @endif
                                                    @endforeach
                                                    @if ($child->aportaciones_count)
                                                        <span class="sisi-subs__ap" title="Aportaciones">
                                                            <i class="fa fa-comments"></i>{{ $child->aportaciones_count }}
                                                        </span>
                                                    @endif
                                                </span>
                                            @endif
                                        </span>
                                        <i class="fa fa-chevron-right sisi-subs__chev" aria-hidden="true"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="sisi-empty sisi-empty--sm">
                            <i class="fa fa-folder-open" aria-hidden="true"></i>
                            Este registro no tiene sub-registros.
                        </p>
                    @endif

                    @if ($category->parent)
                        <a href="{{ route('sisipedia.categories.show', $category->parent) }}" class="sisi-subs__up">
                            <i class="fa fa-arrow-up me-1" aria-hidden="true"></i>Volver a {{ $category->parent->display_name }}
                        </a>
                    @endif
                </section>
            </aside>
        </div>

        {{-- ══ Aportaciones de ESTE registro ═════════════════════════════ --}}
        <section class="sisi-card mt-4" id="sisipedia-aportaciones-section" aria-labelledby="sisi-ap-title">
            <header class="sisi-ap-head">
                <div class="sisi-min-0">
                    <h2 class="sisi-section-title mb-1" id="sisi-ap-title">
                        <i class="fa fa-layer-group" aria-hidden="true"></i>Aportaciones
                        @if ($aportaciones->isNotEmpty())
                            <span class="sisi-count">{{ $aportaciones->count() }}</span>
                        @endif
                    </h2>
                    <p class="text-muted small mb-0">
                        Conocimientos compartidos por la comunidad sobre <strong>{{ $category->display_name }}</strong>.
                    </p>
                </div>
                <button type="button" class="btn sisi-btn-cta" id="btnAbrirRoles" onclick="mostrarRoles()">
                    <i class="fa fa-plus-circle me-2" aria-hidden="true"></i>Agregar aportación
                </button>
            </header>

            {{-- Paso 1: Selector de rol --}}
            <div id="rolSelector" class="sisi-ap-step" style="display:none;">
                <p class="fw-semibold mb-3 text-center">¿Cómo deseas aportar?</p>
                <div class="row row-cols-2 row-cols-md-4 g-2 g-md-3 mb-3">
                    @foreach ($roles as $valor => $r)
                        <div class="col">
                            <button type="button" class="rol-card" style="--rol-color: {{ $r['color'] }};"
                                    onclick="seleccionarRol(@js($valor))">
                                <span class="rol-card__icon"><i class="fa {{ $r['icon'] }} fa-lg"></i></span>
                                <span class="rol-card__label">{{ $r['label'] }}</span>
                                <small class="text-muted">{{ $r['hint'] }}</small>
                            </button>
                        </div>
                    @endforeach
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancelarAportacion()">
                        <i class="fa fa-times me-1"></i>Cancelar
                    </button>
                </div>
            </div>

            {{-- Paso 2: Formulario de aportación --}}
            <div id="formAportacionWrap" class="sisi-ap-step" style="display:none;">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                    <span class="badge fs-6 px-3 py-2" id="rolBadge" style="background:#0d6efd;">
                        <i class="fa fa-user me-1"></i><span id="rolTexto"></span>
                    </span>
                    <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="volverARoles()">
                        <i class="fa fa-arrow-left me-1"></i>Cambiar rol
                    </button>
                </div>

                <form action="{{ route('sisipedia.categories.aportaciones.store', $category) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="rol_nombre" id="inputRolNombre" value="{{ old('rol_nombre') }}">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="ap_nombre">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" id="ap_nombre" name="nombre_ol" class="form-control @error('nombre_ol') is-invalid @enderror"
                                   value="{{ old('nombre_ol') }}" required>
                            @error('nombre_ol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="ap_institucion">Institución</label>
                            <input type="text" id="ap_institucion" name="institucion" class="form-control @error('institucion') is-invalid @enderror"
                                   value="{{ old('institucion') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="ap_ubicacion">Ubicación</label>
                            <input type="text" id="ap_ubicacion" name="ubicacion" class="form-control @error('ubicacion') is-invalid @enderror"
                                   value="{{ old('ubicacion') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="ap_titulo">Título</label>
                            <input type="text" id="ap_titulo" name="titulo" class="form-control @error('titulo') is-invalid @enderror"
                                   value="{{ old('titulo') }}">
                            @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Detalle</label>
                            <textarea name="detalle" rows="3"
                                      class="form-control summernote-editor @error('detalle') is-invalid @enderror">{{ old('detalle') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="archivosInput">
                                <i class="fa fa-upload text-primary me-1"></i>Archivos (puedes subir varios)
                            </label>
                            <input type="file" name="archivos[]" multiple
                                   accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.mp3,.wav,.ogg,.mp4,.webm,.mov,.m4v"
                                   class="form-control @error('archivos') is-invalid @enderror @error('archivos.*') is-invalid @enderror"
                                   id="archivosInput">
                            <small class="text-muted">
                                <i class="fa fa-info-circle me-1"></i>Límites: Imagen 10MB · PDF/Word 100MB · Audio 150MB · Video 300MB
                            </small>
                            @error('archivos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @error('archivos.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div id="archivosPreview" class="mt-2 d-none">
                                <div class="d-flex flex-wrap gap-2" id="archivosPreviewList"></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i>Guardar aportación
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="cancelarAportacion()">Cancelar</button>
                    </div>
                </form>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3 mb-0">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            {{-- Lista --}}
            <div class="sisi-ap-list">
                @forelse ($aportaciones as $aportacion)
                    <div class="sisipedia-aportacion-item">
                        @include('sisichakuna.partials.aportacion-card', [
                            'aportacion' => $aportacion,
                            'archivos' => $aportacion->archivos,
                        ])
                    </div>
                @empty
                    <div class="sisi-empty">
                        <i class="fa fa-comments" aria-hidden="true"></i>
                        <p class="mb-1 fw-semibold">Aún no hay aportaciones en este registro.</p>
                        <p class="small mb-0">¿Conoces algo sobre este tema? Sé la primera persona en compartirlo.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Modal único de imagen: lo usan "Archivos del registro" y las aportaciones --}}
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
                    <img id="sisipediaDetalleImageModalImg" src="" alt="" class="img-fluid" style="max-height:80vh;">
                </div>
            </div>
        </div>
    </div>

    <script>
        const rolColors = @json(collect($roles)->map(fn ($r) => $r['color']));
        const rolTextoMostrar = @json(collect($roles)->map(fn ($r) => $r['label']));

        function mostrarRoles() {
            document.getElementById('rolSelector').style.display = 'block';
            document.getElementById('formAportacionWrap').style.display = 'none';
            document.getElementById('btnAbrirRoles').style.display = 'none';
        }

        function seleccionarRol(rol) {
            document.getElementById('inputRolNombre').value = rol;
            document.getElementById('rolTexto').textContent = rolTextoMostrar[rol] ?? rol;
            document.getElementById('rolBadge').style.background = rolColors[rol] ?? '#6c757d';
            document.getElementById('rolSelector').style.display = 'none';
            document.getElementById('formAportacionWrap').style.display = 'block';
            document.getElementById('btnAbrirRoles').style.display = 'none';
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

        @if ($errors->any() && old('rol_nombre'))
            window.addEventListener('DOMContentLoaded', function () {
                seleccionarRol(@js(old('rol_nombre')));
            });
        @endif

        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('archivosInput');
            if (!input) return;
            input.addEventListener('change', function () {
                var preview = document.getElementById('archivosPreview');
                var list = document.getElementById('archivosPreviewList');
                list.innerHTML = '';
                if (!this.files.length) { preview.classList.add('d-none'); return; }
                preview.classList.remove('d-none');
                Array.from(this.files).forEach(function (file) {
                    var icon = 'fa-file', color = 'secondary';
                    if (file.type.startsWith('image/')) { icon = 'fa-image'; color = 'info'; }
                    else if (file.type === 'application/pdf') { icon = 'fa-file-pdf'; color = 'danger'; }
                    else if (file.type.includes('word')) { icon = 'fa-file-word'; color = 'primary'; }
                    else if (file.type.startsWith('audio/')) { icon = 'fa-music'; color = 'success'; }
                    else if (file.type.startsWith('video/')) { icon = 'fa-video'; color = 'warning'; }
                    var badge = document.createElement('span');
                    badge.className = 'badge text-wrap text-start bg-' + color + ' bg-opacity-10 text-' + color + ' border border-' + color;
                    var i = document.createElement('i');
                    i.className = 'fa ' + icon + ' me-1';
                    badge.appendChild(i);
                    badge.appendChild(document.createTextNode(file.name + ' (' + (file.size / 1048576).toFixed(1) + 'MB)'));
                    list.appendChild(badge);
                });
            });
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
                        onInit: function () {
                            $('.note-editable').css('font-size', '14px');
                            $('.summernote-editor').addClass('summernote-initialized');
                        }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            initSummernote();
            const originalSeleccionarRol = window.seleccionarRol;
            window.seleccionarRol = function (rol) {
                originalSeleccionarRol(rol);
                setTimeout(initSummernote, 100);
            };
        });

        /** Imágenes (registro y aportaciones): miniatura + modal, nunca iframe */
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('.sisipedia-img-preview-trigger');
            if (!trigger) return;
            e.preventDefault();
            var modalEl = document.getElementById('sisipediaDetalleImageModal');
            if (!modalEl) return;
            document.getElementById('sisipediaDetalleImageModalImg').src = trigger.getAttribute('data-full-url');
            document.getElementById('sisipediaDetalleImageModalName').textContent = trigger.getAttribute('data-nombre') || 'Imagen';
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        document.addEventListener('hidden.bs.modal', function (e) {
            if (e.target && e.target.id === 'sisipediaDetalleImageModal') {
                document.getElementById('sisipediaDetalleImageModalImg').src = '';
            }
        });
    </script>

    {{-- Animaciones GSAP: entrada al cargar + revelado al hacer scroll --}}
    <script>
        (function () {
            var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function ensureGsap(callback) {
                if (typeof gsap !== 'undefined') { callback(); return; }
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
                script.onload = callback;
                document.head.appendChild(script);
            }

            if (prefersReducedMotion) return;

            var onloadTargets = document.querySelectorAll('.sisipedia-reveal-onload');
            if (onloadTargets.length) {
                ensureGsap(function () {
                    gsap.from(onloadTargets, { opacity: 0, y: 18, duration: 0.55, stagger: 0.1, ease: 'power2.out', clearProps: 'all' });
                    var cards = document.querySelectorAll('.sisipedia-file-card, .sisipedia-child-card');
                    if (cards.length) {
                        gsap.from(cards, { opacity: 0, y: 14, duration: 0.4, stagger: 0.05, ease: 'power2.out', delay: 0.25, clearProps: 'all' });
                    }
                });
            }

            var section = document.getElementById('sisipedia-aportaciones-section');
            var items = section ? section.querySelectorAll('.sisipedia-aportacion-item') : [];
            if (!items.length) return;
            var animate = function () {
                gsap.from(items, { opacity: 0, y: 24, duration: 0.5, stagger: 0.08, ease: 'power2.out', clearProps: 'all' });
            };
            if ('IntersectionObserver' in window) {
                new IntersectionObserver(function (entries, obs) {
                    if (entries.some(function (en) { return en.isIntersecting; })) {
                        ensureGsap(animate);
                        obs.disconnect();
                    }
                }, { threshold: 0.12 }).observe(section);
            } else {
                ensureGsap(animate);
            }
        })();
    </script>

    <style>
        /* ── Base ─────────────────────────────────────────────── */
        .sisi-reg { --sisi-radius: 1rem; --sisi-line: #e9ecef; --sisi-accent: #0d6efd; padding-bottom: 3rem; }
        /* Hijos flex/grid con texto largo: sin esto no se encogen y ensanchan la página */
        .sisi-min-0 { min-width: 0; }
        .sisi-card {
            background: #fff; border-radius: var(--sisi-radius);
            box-shadow: 0 1px 2px rgba(16,24,40,.05), 0 4px 16px rgba(16,24,40,.06);
            padding: 1.25rem;
        }
        .sisi-card--flush { padding: 0; overflow: hidden; }
        @media (min-width: 768px) { .sisi-card { padding: 1.75rem; } .sisi-card--flush { padding: 0; } }
        .sisi-section-title {
            display: flex; align-items: center; gap: .5rem;
            font-size: 1.05rem; font-weight: 700; color: #212529; margin-bottom: 1rem;
        }
        .sisi-section-title > i { color: var(--sisi-accent); }
        .sisi-count {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 1.6rem; height: 1.6rem; padding: 0 .45rem; border-radius: 99px;
            background: rgba(13,110,253,.1); color: var(--sisi-accent); font-size: .75rem; font-weight: 700;
        }
        .sisi-empty { text-align: center; color: #6c757d; padding: 2rem 1rem; }
        .sisi-empty > i { display: block; font-size: 1.75rem; opacity: .4; margin-bottom: .5rem; }
        .sisi-empty--sm { padding: 1.5rem 1rem; margin: 0; font-size: .875rem; }

        /* ── Migas ────────────────────────────────────────────── */
        .sisi-reg__crumbs {
            margin: 1.5rem 0 1.25rem; background: #fff; border-radius: .75rem;
            box-shadow: 0 1px 2px rgba(16,24,40,.06); padding: .55rem 1rem;
            overflow-x: auto; scrollbar-width: none;
        }
        .sisi-reg__crumbs::-webkit-scrollbar { display: none; }
        .sisi-reg__crumbs .breadcrumb { flex-wrap: nowrap; white-space: nowrap; font-size: .875rem; }
        .sisi-reg__crumbs .breadcrumb-item { flex-shrink: 0; }
        .sisi-reg__crumbs a { text-decoration: none; }
        @media (min-width: 768px) { .sisi-reg__crumbs { margin-top: 2.5rem; } }

        /* ── Cabecera del registro ────────────────────────────── */
        .sisi-reg__lineage {
            display: flex; flex-wrap: wrap; align-items: center; gap: .35rem;
            font-size: .8rem; color: #6c757d; margin-bottom: .5rem;
        }
        .sisi-reg__lineage > i { color: var(--sisi-accent); margin-right: .15rem; }
        .sisi-reg__lineage a { color: inherit; font-weight: 600; text-decoration: none; }
        .sisi-reg__lineage a:hover { color: var(--sisi-accent); text-decoration: underline; }
        .sisi-reg__title {
            font-size: clamp(1.35rem, 1.1rem + 1.2vw, 1.9rem); font-weight: 800;
            line-height: 1.2; margin-bottom: .5rem; overflow-wrap: anywhere;
        }
        .sisi-reg__desc { color: #495057; margin-bottom: 0; overflow-wrap: anywhere; }
        .sisi-reg__facts {
            list-style: none; padding: 0; margin: 1rem 0 0; display: flex; flex-wrap: wrap; gap: .5rem;
        }
        .sisi-reg__facts li {
            display: inline-flex; align-items: center; gap: .4rem; font-size: .8rem;
            padding: .3rem .7rem; border-radius: 99px; background: #f1f3f5; color: #495057;
        }
        .sisi-reg__stats {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem;
            margin: 1.25rem 0 0; padding-top: 1.25rem; border-top: 1px solid var(--sisi-line);
        }
        .sisi-reg__stats > div { text-align: center; }
        .sisi-reg__stats dt { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #868e96; }
        .sisi-reg__stats dd { font-size: 1.4rem; font-weight: 800; margin: 0; color: #212529; }
        .sisi-reg__stats dd a { color: inherit; text-decoration: none; }

        /* ── Archivos del registro ────────────────────────────── */
        .sisipedia-file-card {
            height: 100%; border: 1px solid var(--sisi-line); border-radius: .75rem; overflow: hidden;
            background: #fff; transition: transform .2s ease, box-shadow .2s ease;
        }
        .sisipedia-file-card:hover { transform: translateY(-3px); box-shadow: 0 12px 24px rgba(16,24,40,.1); }
        .sisipedia-file-card__media {
            position: relative; display: block; width: 100%; aspect-ratio: 16 / 10;
            padding: 0; border: 0; background: #f1f3f5; overflow: hidden; cursor: zoom-in;
        }
        .sisipedia-file-card__media img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
        .sisipedia-file-card__media:hover img { transform: scale(1.04); }
        .sisipedia-file-card__media--video { background: #212529; cursor: pointer; }
        .sisipedia-file-card__media--video img { object-fit: contain; opacity: .85; }
        .sisipedia-file-card__body { display: flex; align-items: center; gap: .75rem; padding: .75rem; }
        .sisipedia-file-card__icon {
            flex-shrink: 0; width: 2.25rem; height: 2.25rem; border-radius: .6rem;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .sisipedia-file-card__name {
            font-size: .875rem; font-weight: 600; color: #212529;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .sisipedia-file-card__type { font-size: .72rem; color: #868e96; }
        .sisipedia-file-card__actions { display: flex; gap: .35rem; flex-shrink: 0; }

        /* Botón de play compartido (archivos del registro y aportaciones) */
        .sisipedia-load-category-video,
        .sisipedia-ap-video-play-btn {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 3rem; height: 3rem; border-radius: 50%; background: #fff; color: #212529;
            display: inline-flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(0,0,0,.25); transition: transform .18s ease;
        }
        .sisipedia-load-category-video i, .sisipedia-ap-video-play-btn i { margin-left: 2px; }
        .sisipedia-file-video-wrap:hover .sisipedia-load-category-video,
        .sisipedia-ap-video-preview:hover .sisipedia-ap-video-play-btn { transform: translate(-50%, -50%) scale(1.1); }

        /* ── Sub-registros ────────────────────────────────────── */
        @media (min-width: 992px) { .sisi-reg__aside { position: sticky; top: 5.5rem; } }
        .sisi-subs__head {
            display: flex; align-items: center; justify-content: space-between; gap: .5rem;
            padding: 1rem 1.25rem; border-bottom: 1px solid var(--sisi-line);
        }
        .sisi-subs { list-style: none; margin: 0; padding: .5rem; max-height: 60vh; overflow-y: auto; }
        @media (max-width: 991.98px) { .sisi-subs { max-height: none; } }
        .sisipedia-child-card {
            display: flex; align-items: center; gap: .75rem; padding: .65rem .75rem;
            border-radius: .65rem; color: inherit; text-decoration: none;
            transition: background-color .15s ease;
        }
        .sisipedia-child-card:hover, .sisipedia-child-card:focus-visible { background: #f1f5ff; }
        .sisipedia-child-card:hover .sisi-subs__chev { transform: translateX(3px); color: var(--sisi-accent); }
        .sisi-subs__icon {
            flex-shrink: 0; width: 2.25rem; height: 2.25rem; border-radius: .6rem;
            background: rgba(13,110,253,.1); color: var(--sisi-accent);
            display: inline-flex; align-items: center; justify-content: center;
        }
        .sisi-subs__name { display: block; font-weight: 600; font-size: .9rem; color: #212529; overflow-wrap: anywhere; }
        .sisi-subs__desc { display: block; font-size: .75rem; color: #868e96; overflow-wrap: anywhere; }
        .sisi-subs__tags { display: flex; flex-wrap: wrap; align-items: center; gap: .55rem; margin-top: .3rem; font-size: .75rem; }
        .sisi-subs__ap { display: inline-flex; align-items: center; gap: .25rem; color: #6c757d; font-weight: 600; }
        .sisi-subs__chev { flex-shrink: 0; font-size: .7rem; color: #adb5bd; transition: transform .15s ease, color .15s ease; }
        .sisi-subs__up {
            display: block; padding: .8rem 1.25rem; border-top: 1px solid var(--sisi-line);
            font-size: .85rem; font-weight: 600; text-decoration: none; overflow-wrap: anywhere;
        }

        /* ── Aportaciones ─────────────────────────────────────── */
        .sisi-ap-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem; }
        .sisi-btn-cta {
            border-radius: 2rem; border: 0; color: #fff; font-weight: 600; padding: .6rem 1.4rem;
            background: linear-gradient(135deg, #0d6efd, #6610f2); box-shadow: 0 4px 14px rgba(13,110,253,.3);
        }
        .sisi-btn-cta:hover, .sisi-btn-cta:focus { color: #fff; filter: brightness(1.08); }
        @media (max-width: 575.98px) { .sisi-btn-cta { width: 100%; } }
        .sisi-ap-step { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--sisi-line); }
        .rol-card {
            width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; gap: .35rem;
            padding: 1rem .5rem; border: 2px solid var(--rol-color); border-radius: 1rem; background: #fff;
            text-align: center; transition: transform .15s ease, box-shadow .15s ease;
        }
        .rol-card:hover, .rol-card:focus-visible { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.1); }
        .rol-card__icon {
            width: 3.25rem; height: 3.25rem; border-radius: 50%; background: var(--rol-color); color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .rol-card__label { font-weight: 700; font-size: .9rem; color: #212529; }
        .sisi-ap-list { margin-top: 1.25rem; display: grid; gap: 1rem; }

        /* Tarjeta de aportación (partials.aportacion-card) */
        .sisi-ap {
            border: 1px solid var(--sisi-line); border-left: 4px solid var(--ap-color, #6c757d);
            border-radius: .85rem; padding: 1rem; background: #fff; min-width: 0;
            transition: box-shadow .2s ease;
        }
        .sisi-ap:hover { box-shadow: 0 10px 24px rgba(16,24,40,.08); }
        @media (min-width: 768px) { .sisi-ap { padding: 1.25rem 1.5rem; } }
        .sisi-ap__head { display: flex; align-items: flex-start; gap: .75rem; }
        .sisi-ap__avatar {
            flex-shrink: 0; width: 2.6rem; height: 2.6rem; border-radius: 50%;
            background: var(--ap-color, #6c757d); color: #fff; font-weight: 700; font-size: .9rem;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .sisi-ap__who { flex: 1 1 auto; min-width: 0; }
        .sisi-ap__name { font-size: 1rem; font-weight: 700; margin: 0; overflow-wrap: anywhere; }
        .sisi-ap__rol {
            display: inline-flex; align-items: center; font-size: .72rem; font-weight: 600;
            padding: .15rem .55rem; border-radius: 99px; color: var(--ap-color);
            border: 1px solid var(--ap-color); background: #fff;
        }
        .sisi-ap__meta {
            list-style: none; padding: 0; margin: .3rem 0 0; display: flex; flex-wrap: wrap; gap: .25rem .9rem;
            font-size: .78rem; color: #6c757d;
        }
        .sisi-ap__meta li { display: inline-flex; align-items: baseline; gap: .35rem; overflow-wrap: anywhere; }
        .sisi-ap__title { font-size: 1.05rem; font-weight: 700; margin: .9rem 0 .35rem; line-height: 1.35; overflow-wrap: anywhere; }
        .sisi-ap__detalle { font-size: .9rem; color: #495057; overflow-wrap: anywhere; margin-top: .5rem; }
        .sisi-ap__detalle > :last-child { margin-bottom: 0; }
        /* Contenido del editor enriquecido: nada puede salirse del ancho */
        .sisi-ap__detalle img, .sisi-ap__detalle video, .sisi-ap__detalle iframe { max-width: 100% !important; height: auto !important; }
        .sisi-ap__detalle table { display: block; max-width: 100%; overflow-x: auto; }
        .sisi-ap__detalle pre { white-space: pre-wrap; }
        .sisi-ap__files {
            display: grid; gap: .65rem; margin-top: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        @media (min-width: 576px) { .sisi-ap__files { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 992px) { .sisi-ap__files { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .sisi-ap-file {
            min-width: 0; border: 1px solid var(--sisi-line); border-radius: .65rem; overflow: hidden; background: #fff;
            display: flex; flex-direction: column;
        }
        /* Audio, PDF y Word necesitan ancho para su reproductor/nombre */
        .sisi-ap-file--wide { grid-column: 1 / -1; }
        @media (min-width: 576px) { .sisi-ap-file--wide { grid-column: span 3; } }
        @media (min-width: 992px) { .sisi-ap-file--wide { grid-column: span 2; } }
        .sisi-ap-file__thumb {
            position: relative; display: flex; align-items: center; justify-content: center;
            width: 100%; aspect-ratio: 4 / 3; padding: 0; border: 0; background: #f1f3f5;
            overflow: hidden; cursor: zoom-in; color: #adb5bd;
        }
        .sisi-ap-file__thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
        .sisi-ap-file__thumb:hover img { transform: scale(1.05); }
        .sisi-ap-file__thumb--video { aspect-ratio: 16 / 9; background: #212529; cursor: pointer; }
        .sisi-ap-file__thumb--video img { object-fit: contain; opacity: .85; }
        .sisi-ap-file__thumb--video .sisipedia-ap-video-play-btn { width: 2.25rem; height: 2.25rem; font-size: .7rem; }
        .sisi-ap-file__state { padding: 1.25rem .5rem; text-align: center; font-size: .8rem; }
        .sisi-ap-file__foot { display: flex; align-items: center; gap: .45rem; padding: .45rem .6rem; margin-top: auto; }
        .sisi-ap-file__icon { flex-shrink: 0; font-size: .8rem; }
        .sisi-ap-file__name {
            flex: 1 1 auto; min-width: 0; font-size: .75rem; color: #495057;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .sisi-ap-file__foot .btn-link { flex-shrink: 0; font-size: .85rem; text-decoration: none; }
    </style>

    @include('partials.home-preview-modal')
@endsection
