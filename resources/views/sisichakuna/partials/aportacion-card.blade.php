{{--
    Partial para mostrar una aportación en formato card.
    Recibe: $aportacion (model), $archivos (colección de AportacionArchivo)
--}}
<div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
    <div class="flex-grow-1">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <h3 class="h6 mb-0 fw-bold">{{ $aportacion->nombre_ol }}</h3>
            @if($aportacion->rol_nombre)
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
                        'Docente'         => 'fa-chalkboard-teacher',
                        'Líder'           => 'fa-star',
                        'Niño/Estudiante' => 'fa-child',
                        default           => 'fa-user',
                    };
                @endphp
                <span class="badge bg-{{ $rolColor }} bg-opacity-10 text-{{ $rolColor }} border border-{{ $rolColor }}">
                    <i class="fa {{ $rolIcono }} me-1"></i>{{ \App\Models\sisipedia\Aportacion::etiquetaRol($aportacion->rol_nombre) }}
                </span>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2 mb-2">
            @if($aportacion->titulo)
                <span class="badge bg-info bg-opacity-10 text-info border border-info">
                    <i class="fa fa-heading me-1"></i>{{ $aportacion->titulo }}
                </span>
            @endif
            @if($aportacion->institucion)
                <span class="badge bg-light text-dark border">
                    <i class="fa fa-building me-1"></i>{{ $aportacion->institucion }}
                </span>
            @endif
            @if($aportacion->lugar_trabajo)
                <span class="badge bg-light text-dark border">
                    <i class="fa fa-briefcase me-1"></i>{{ $aportacion->lugar_trabajo }}
                </span>
            @endif
            @if($aportacion->ubicacion)
                <span class="badge bg-light text-dark border">
                    <i class="fa fa-map-marker-alt me-1"></i>{{ $aportacion->ubicacion }}
                </span>
            @endif
        </div>
        @if($aportacion->detalle)
            <p class="text-muted small mb-2">{!! $aportacion->detalle !!}</p>
        @endif

        {{-- Archivos en grid con vista previa bajo demanda --}}
        @if($archivos->isNotEmpty())
            <div class="row g-2 mt-2">
                @foreach($archivos as $archivo)
                    @php
                        $color = match($archivo->tipo) {
                            'imagen' => 'info',
                            'pdf' => 'danger',
                            'doc' => 'primary',
                            'audio' => 'success',
                            'video' => 'warning',
                            default => 'secondary',
                        };
                        $icono = match($archivo->tipo) {
                            'imagen' => 'fa-image',
                            'pdf' => 'fa-file-pdf',
                            'doc' => 'fa-file-word',
                            'audio' => 'fa-music',
                            'video' => 'fa-video',
                            default => 'fa-file',
                        };
                        $driveUrl = $archivo->getUrl();
                        $previewUrl = $archivo->getPreviewUrl();
                        $label = ucfirst($archivo->tipo);
                        $showAudioPreview = $archivo->tipo === 'audio';
                        $thumbUrl = in_array($archivo->tipo, ['imagen', 'video'])
                            ? \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 400)
                            : null;
                        $fullImgUrl = $archivo->tipo === 'imagen'
                            ? \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 1200)
                            : null;
                    @endphp
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm rounded-3 overflow-hidden sisipedia-ap-file-card border-0">
                            <div class="rounded-top bg-{{ $color }}" style="height:3px;" aria-hidden="true"></div>
                            <div class="card-body p-2">
                                <div class="d-flex align-items-start justify-content-between gap-1 mb-1">
                                    <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} flex-shrink-0" style="font-size:.65rem;">
                                        <i class="fa {{ $icono }} me-1"></i>{{ $label }}
                                        @if($archivo->peso_mb)
                                            <small>({{ $archivo->peso_mb }}MB)</small>
                                        @endif
                                    </span>
                                    <a href="{{ $driveUrl }}" target="_blank" rel="noopener"
                                       class="btn btn-sm p-0 text-{{ $color }}" title="Descargar">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                                <div class="small text-muted text-truncate" title="{{ $archivo->nombre_original ?? '' }}">
                                    {{ $archivo->nombre_original ?? $archivo->tipo }}
                                </div>
                                @if($showAudioPreview)
                                    <div class="mt-1">
                                        @include('partials.audio-player', [
                                            'src' => route('sisipedia.audio.aportacion.stream', $archivo),
                                            'title' => $archivo->nombre_original ?? 'Audio',
                                            'driveUrl' => $driveUrl,
                                        ])
                                    </div>
                                @elseif($archivo->tipo === 'video')
                                    {{-- Video: miniatura real + play, iframe solo tras click --}}
                                    <div class="mt-1 sisipedia-ap-video-preview position-relative rounded overflow-hidden"
                                         data-preview-url="{{ $previewUrl }}">
                                        <div class="ratio ratio-16x9 bg-dark">
                                            <img src="{{ $thumbUrl }}" alt="{{ $archivo->nombre_original ?? 'Video' }}"
                                                 class="w-100 h-100" style="object-fit:cover;opacity:.85;"
                                                 loading="lazy" onerror="this.style.display='none'">
                                        </div>
                                        <button type="button"
                                                class="sisipedia-ap-video-play-btn position-absolute top-50 start-50 translate-middle btn btn-light rounded-circle shadow-sm"
                                                style="width:36px;height:36px;padding:0;">
                                            <i class="fa fa-play text-dark" style="font-size:.7rem;margin-left:1px;"></i>
                                        </button>
                                        <div class="sisipedia-ap-video-iframe-wrap d-none position-absolute top-0 start-0 w-100 h-100">
                                            <iframe title="{{ $archivo->nombre_original ?? 'Video' }}"
                                                    class="sisipedia-ap-iframe w-100 h-100 border-0"
                                                    src=""
                                                    allow="autoplay; fullscreen"
                                                    allowfullscreen></iframe>
                                        </div>
                                    </div>
                                @elseif($archivo->tipo === 'imagen')
                                    {{-- Imagen: solo miniatura ligera; ampliar abre modal (nunca iframe) --}}
                                    <button type="button"
                                            class="mt-1 w-100 p-0 border-0 bg-transparent sisipedia-img-preview-trigger"
                                            data-full-url="{{ $fullImgUrl }}"
                                            data-nombre="{{ $archivo->nombre_original ?? 'Imagen' }}">
                                        <div class="ratio ratio-4x3 bg-light rounded overflow-hidden">
                                            <img src="{{ $thumbUrl }}" alt="{{ $archivo->nombre_original ?? 'Imagen' }}"
                                                 class="w-100 h-100" style="object-fit:cover;"
                                                 loading="lazy"
                                                 onerror="this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center h-100 text-muted\'><i class=\'fa fa-image fa-2x opacity-25\'></i></div>'">
                                        </div>
                                    </button>
                                @else
                                    <div class="mt-1 text-center py-2">
                                        <a href="{{ $driveUrl }}" target="_blank" rel="noopener"
                                           class="btn btn-outline-{{ $color }} btn-xs">
                                            <i class="fa fa-external-link-alt me-1"></i>Abrir
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @hasanyrole('admin|sisicha')
    @if($aportacion->category_id)
        <form action="{{ route('sisipedia.categories.aportaciones.destroy', [$aportacion->category, $aportacion]) }}"
              method="POST" onsubmit="return confirm('¿Eliminar esta aportación?')" class="flex-shrink-0">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                <i class="fa fa-trash"></i>
            </button>
        </form>
    @else
        <form action="{{ route('sisipedia.aportaciones.destroy', $aportacion) }}"
              method="POST" onsubmit="return confirm('¿Eliminar esta aportación?')" class="flex-shrink-0">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                <i class="fa fa-trash"></i>
            </button>
        </form>
    @endif
    @endhasanyrole
</div>
