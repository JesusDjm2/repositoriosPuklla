{{--
    Una aportación dentro de la página de su registro (sisichakuna.detalle).
    Recibe: $aportacion (model), $archivos (colección de AportacionArchivo)

    Los estilos (.sisi-ap*) viven en sisichakuna.detalle. El título va como texto
    normal (no como badge): los badges de Bootstrap no hacen salto de línea y un
    título largo ensanchaba toda la página en celulares.
--}}
@php
    $rolMeta = match ($aportacion->rol_nombre) {
        'Equipo Puklla'   => ['color' => '#6f42c1', 'icon' => 'fa-users'],
        'Docente'         => ['color' => '#0d6efd', 'icon' => 'fa-chalkboard-teacher'],
        'Líder'           => ['color' => '#198754', 'icon' => 'fa-star'],
        'Niño/Estudiante' => ['color' => '#fd7e14', 'icon' => 'fa-child'],
        default           => ['color' => '#6c757d', 'icon' => 'fa-user'],
    };
    $iniciales = collect(preg_split('/\s+/', trim((string) $aportacion->nombre_ol)))
        ->filter()
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');
    $tipoMeta = [
        'imagen' => ['color' => 'info', 'icon' => 'fa-image', 'label' => 'Imagen'],
        'pdf'    => ['color' => 'danger', 'icon' => 'fa-file-pdf', 'label' => 'PDF'],
        'doc'    => ['color' => 'primary', 'icon' => 'fa-file-word', 'label' => 'Word'],
        'audio'  => ['color' => 'success', 'icon' => 'fa-music', 'label' => 'Audio'],
        'video'  => ['color' => 'warning', 'icon' => 'fa-video', 'label' => 'Video'],
    ];
@endphp

<article class="sisi-ap" style="--ap-color: {{ $rolMeta['color'] }};">
    <header class="sisi-ap__head">
        <span class="sisi-ap__avatar" aria-hidden="true">{{ $iniciales ?: '?' }}</span>

        <div class="sisi-ap__who">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h3 class="sisi-ap__name">{{ $aportacion->nombre_ol }}</h3>
                @if ($aportacion->rol_nombre)
                    <span class="sisi-ap__rol">
                        <i class="fa {{ $rolMeta['icon'] }} me-1" aria-hidden="true"></i>{{ \App\Models\sisipedia\Aportacion::etiquetaRol($aportacion->rol_nombre) }}
                    </span>
                @endif
            </div>
            <ul class="sisi-ap__meta">
                @if ($aportacion->institucion)
                    <li><i class="fa fa-building" aria-hidden="true"></i>{{ $aportacion->institucion }}</li>
                @endif
                @if ($aportacion->ubicacion)
                    <li><i class="fa fa-map-marker-alt" aria-hidden="true"></i>{{ $aportacion->ubicacion }}</li>
                @endif
                @if ($aportacion->created_at)
                    <li><i class="fa fa-calendar" aria-hidden="true"></i><time datetime="{{ $aportacion->created_at->toDateString() }}">{{ $aportacion->created_at->format('d/m/Y') }}</time></li>
                @endif
            </ul>
        </div>

        @hasanyrole('admin|sisicha')
            <form action="{{ $aportacion->category_id
                    ? route('sisipedia.categories.aportaciones.destroy', [$aportacion->category_id, $aportacion])
                    : route('sisipedia.aportaciones.destroy', $aportacion) }}"
                  method="POST" onsubmit="return confirm('¿Eliminar esta aportación?')" class="flex-shrink-0">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" title="Eliminar aportación" aria-label="Eliminar aportación">
                    <i class="fa fa-trash"></i>
                </button>
            </form>
        @endhasanyrole
    </header>

    @if ($aportacion->titulo)
        <h4 class="sisi-ap__title">{{ $aportacion->titulo }}</h4>
    @endif

    @if ($aportacion->detalle)
        {{-- Viene del editor enriquecido (summernote): puede traer <p>, listas, enlaces.
             Se limpia también aquí para cubrir aportes guardados antes del filtro. --}}
        <div class="sisi-ap__detalle">{!! \App\Services\HtmlSeguro::limpiar($aportacion->detalle) !!}</div>
    @endif

    @if ($archivos->isNotEmpty())
        <div class="sisi-ap__files">
            @foreach ($archivos as $archivo)
                @php
                    $m = $tipoMeta[$archivo->tipo] ?? ['color' => 'secondary', 'icon' => 'fa-file', 'label' => ucfirst($archivo->tipo)];
                    $nombre = $archivo->nombre_original ?? $m['label'];
                    // Archivo aún sin drive_id (pendiente en la cola o fallido): las
                    // URLs de Drive no se pueden construir todavía, ni hace falta —
                    // la tarjeta solo muestra el aviso de "Procesando"/"No se pudo subir".
                    $listo = (bool) $archivo->drive_id;
                    $driveUrl = $listo ? $archivo->getUrl() : null;
                    $downloadUrl = $listo ? route('sisipedia.descargar.aporte', $archivo) : null; // descarga contada
                    $previewUrl = $listo ? $archivo->getPreviewUrl() : null;
                    $thumbUrl = ($listo && in_array($archivo->tipo, ['imagen', 'video'], true))
                        ? \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 400)
                        : null;
                    $fullImgUrl = ($listo && $archivo->tipo === 'imagen')
                        ? \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 1200)
                        : null;
                    $esMedia = in_array($archivo->tipo, ['imagen', 'video'], true);
                @endphp
                <div class="sisi-ap-file {{ $esMedia ? 'sisi-ap-file--media' : 'sisi-ap-file--wide' }}">
                    @if ($archivo->pendiente)
                        {{-- Aún subiéndose a Drive en segundo plano (ver SubirArchivoAportacionJob) --}}
                        <div class="sisi-ap-file__state text-muted">
                            <i class="fa fa-spinner fa-spin me-1"></i>Procesando archivo…
                        </div>
                    @elseif ($archivo->fallo_en)
                        <div class="sisi-ap-file__state text-danger">
                            <i class="fa fa-triangle-exclamation me-1"></i>No se pudo subir
                        </div>
                    @elseif ($archivo->tipo === 'imagen')
                        {{-- Imagen: miniatura ligera; ampliar abre modal (nunca iframe) --}}
                        <button type="button" class="sisi-ap-file__thumb sisipedia-img-preview-trigger"
                                data-full-url="{{ $fullImgUrl }}" data-nombre="{{ $nombre }}"
                                aria-label="Ampliar {{ $nombre }}">
                            <img src="{{ $thumbUrl }}" alt="{{ $nombre }}" loading="lazy"
                                 onerror="this.replaceWith(Object.assign(document.createElement('i'),{className:'fa fa-image fa-2x opacity-25'}))">
                        </button>
                    @elseif ($archivo->tipo === 'video')
                        {{-- Video: miniatura + play, abre popup con <video controls> real --}}
                        <div class="sisi-ap-file__thumb sisi-ap-file__thumb--video sisipedia-ap-video-preview"
                             role="button" tabindex="0" aria-label="Reproducir {{ $nombre }}"
                             onclick="abrirVistaPreviaVideo({{ \Illuminate\Support\Js::from(route('sisipedia.video.aportacion.stream', $archivo)) }}, {{ \Illuminate\Support\Js::from($nombre) }}, {{ \Illuminate\Support\Js::from($driveUrl) }})"
                             onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
                            <img src="{{ $thumbUrl }}" alt="{{ $nombre }}" loading="lazy" onerror="this.style.display='none'">
                            <span class="sisipedia-ap-video-play-btn" aria-hidden="true"><i class="fa fa-play"></i></span>
                        </div>
                    @elseif ($archivo->tipo === 'audio')
                        <div class="p-2">
                            @include('partials.audio-player', [
                                'src' => route('sisipedia.audio.aportacion.stream', $archivo),
                                'title' => $nombre,
                                'driveUrl' => $driveUrl,
                                'downloadUrl' => $downloadUrl,
                            ])
                        </div>
                    @endif

                    <div class="sisi-ap-file__foot">
                        <span class="sisi-ap-file__icon text-{{ $m['color'] }}" aria-hidden="true"><i class="fa {{ $m['icon'] }}"></i></span>
                        <span class="sisi-ap-file__name" title="{{ $nombre }}">{{ $nombre }}</span>
                        @if ($listo)
                            @if ($archivo->tipo === 'pdf')
                                <button type="button" class="btn btn-sm btn-link p-0 text-{{ $m['color'] }} sisipedia-open-preview-modal"
                                        data-preview="{{ $previewUrl }}" data-nombre="{{ $nombre }}"
                                        data-drive="{{ $driveUrl }}" data-download="{{ $downloadUrl }}"
                                        title="Vista previa" aria-label="Vista previa de {{ $nombre }}">
                                    <i class="fa fa-eye"></i>
                                </button>
                            @elseif ($archivo->tipo === 'doc')
                                <a href="{{ $driveUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-link p-0 text-{{ $m['color'] }}"
                                   title="Abrir en Drive" aria-label="Abrir {{ $nombre }} en Drive">
                                    <i class="fa fa-external-link-alt"></i>
                                </a>
                            @endif
                            <a href="{{ $downloadUrl }}" class="btn btn-sm btn-link p-0 text-{{ $m['color'] }}"
                               title="Descargar" aria-label="Descargar {{ $nombre }}">
                                <i class="fa fa-download"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</article>
