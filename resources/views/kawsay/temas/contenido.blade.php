@extends('layouts.padre')
@section('titulo', '- Kawsay - ' . $tipo['label'] . ' - ' . $tema->titulo)

@section('contenido')
<div class="kawsay-area-header">
    <div class="container py-4 py-md-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('kawsay.tema', [$areaSlug, $temaSlug]) }}" class="kawsay-back-link">
                    <i class="fa fa-arrow-left"></i> {{ $tema->titulo }}
                </a>
                <div class="d-flex align-items-center gap-3 mt-2">
                    <span class="kawsay-area-header__icon"><i class="fa {{ $tipo['icono'] }}"></i></span>
                    <h1 class="kawsay-area-header__title mb-0">{{ $tipo['label'] }}</h1>
                </div>
                <p class="kawsay-area-header__desc mb-0">{{ $tema->titulo }} · {{ $archivos->count() }} {{ $archivos->count() === 1 ? 'archivo' : 'archivos' }}</p>
            </div>
        </div>
    </div>
</div>


<div class="container py-4 py-md-5">
    @php
        $formatoIconos = ['pdf' => 'fa-file-pdf', 'imagen' => 'fa-file-image', 'audio' => 'fa-file-audio', 'video' => 'fa-file-video', 'doc' => 'fa-file-word'];
        $pdfs = $archivos->where('tipo', 'pdf');
        $videos = $archivos->where('tipo', 'video');
        $audios = $archivos->where('tipo', 'audio');
        // Fotos, mapas y experiencias en imagen: miniaturas que abren el visor a pantalla completa.
        $imagenes = $archivos->where('tipo', 'imagen')->values();
        $otros = $archivos->whereNotIn('tipo', ['pdf', 'video', 'audio', 'imagen']);
        $esMapa = $tipoSlug === 'mapa';
    @endphp

    {{-- Video: tarjeta con reproductor propio (Drive) o miniatura que carga YouTube al pulsar --}}
    @if ($videos->isNotEmpty())
        <div class="row g-3 mb-4">
            @foreach ($videos as $archivo)
                <div class="col-md-6 col-lg-4">
                    @if ($archivo->esYoutube())
                        <div class="kawsay-video-card h-100">
                            <div class="kawsay-video-card__head">
                                <div class="min-w-0">
                                    <h6 class="kawsay-video-card__title" title="{{ $archivo->titulo }}">{{ $archivo->titulo }}</h6>
                                    <p class="kawsay-video-card__meta">YouTube{{ $archivo->nivel ? ' · ' . $archivo->nivel : '' }}</p>
                                </div>
                                <i class="fab fa-youtube kawsay-video-card__yt" aria-hidden="true"></i>
                            </div>
                            <div class="ratio ratio-16x9 kawsay-video-card__player">
                                <button type="button" class="kawsay-yt" data-kyt="{{ $archivo->youtube_id }}" data-kyt-titulo="{{ $archivo->titulo }}"
                                    data-kawsay-vista="{{ route('kawsay.archivo.vista', $archivo) }}" aria-label="Ver el video {{ $archivo->titulo }}">
                                    <img src="{{ $archivo->miniaturaUrl(700) }}" alt="" loading="lazy">
                                    <span class="kawsay-yt__play" aria-hidden="true"><i class="fa fa-play"></i></span>
                                </button>
                            </div>
                            @if ($archivo->descripcion)
                                <p class="kawsay-video-card__desc">{{ $archivo->descripcion }}</p>
                            @endif
                            <div class="kawsay-video-card__actions">
                                <a href="{{ $archivo->getUrl() }}" target="_blank" rel="noopener" title="Abrir en YouTube">
                                    <i class="fa fa-external-link-alt"></i> Abrir en YouTube
                                </a>
                            </div>
                        </div>
                    @else
                        @include('kawsay.partials.video-card', [
                            'src' => route('kawsay.archivo.video.stream', $archivo),
                            'titulo' => $archivo->titulo,
                            'driveId' => $archivo->drive_id,
                            'poster' => $archivo->miniaturaUrl(700),
                            'vistaUrl' => route('kawsay.archivo.vista', $archivo),
                            'descripcion' => $archivo->descripcion,
                            'meta' => strtoupper(pathinfo($archivo->nombre_original, PATHINFO_EXTENSION) ?: 'video')
                                . ($archivo->peso_mb ? ' · ' . $archivo->peso_mb . ' MB' : '')
                                . ($archivo->nivel ? ' · ' . $archivo->nivel : ''),
                        ])
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Audios y testimonios: el mismo reproductor de Radio (se escucha sin descargar todo) --}}
    @if ($audios->isNotEmpty())
        <div class="row g-3 mb-4">
            @foreach ($audios as $archivo)
                <div class="col-md-6">
                    <div class="kawsay-audio-card h-100" data-kawsay-vista-al-reproducir="{{ route('kawsay.archivo.vista', $archivo) }}">
                        <div class="d-flex align-items-start gap-3 mb-2">
                            <span class="kawsay-audio-card__icon" aria-hidden="true"><i class="fa fa-headphones"></i></span>
                            <div class="min-w-0">
                                <h3 class="kawsay-audio-card__title">{{ $archivo->titulo }}</h3>
                                <p class="kawsay-audio-card__meta mb-0">
                                    {{ strtoupper(pathinfo($archivo->nombre_original, PATHINFO_EXTENSION) ?: 'audio') }}
                                    @if ($archivo->peso_mb) · {{ $archivo->peso_mb }} MB @endif
                                    @if ($archivo->nivel) · {{ $archivo->nivel }} @endif
                                </p>
                            </div>
                        </div>
                        @if ($archivo->descripcion)
                            <p class="kawsay-archivo-desc">{{ $archivo->descripcion }}</p>
                        @endif
                        @include('partials.audio-player', [
                            'src' => route('kawsay.archivo.video.stream', $archivo),
                            'title' => $archivo->titulo,
                            'downloadUrl' => \App\Services\GoogleDriveService::getEmbedUrl($archivo->drive_id),
                        ])
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Imágenes: miniaturas; al tocar se abre el visor (en mapas, con zoom para leer los detalles) --}}
    @if ($imagenes->isNotEmpty())
        <div class="kawsay-img-grid {{ $esMapa ? 'kawsay-img-grid--mapa' : '' }} mb-4">
            @foreach ($imagenes as $i => $archivo)
                <figure class="kawsay-img-card">
                    <button type="button" class="kawsay-img-card__btn" data-kvis-indice="{{ $i }}"
                        data-kawsay-vista="{{ route('kawsay.archivo.vista', $archivo) }}"
                        aria-label="{{ $esMapa ? 'Ver el mapa' : 'Ver la imagen' }} {{ $archivo->titulo }} en pantalla completa">
                        <img src="{{ \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 600) }}" alt="" loading="lazy"
                            onerror="this.style.visibility='hidden'">
                        <span class="kawsay-img-card__lupa" aria-hidden="true"><i class="fa {{ $esMapa ? 'fa-search-plus' : 'fa-expand' }}"></i></span>
                    </button>
                    <figcaption>
                        <span class="kawsay-img-card__title">{{ $archivo->titulo }}</span>
                        @if ($archivo->nivel)
                            <span class="kawsay-nivel">{{ $archivo->nivel }}</span>
                        @endif
                        @if ($archivo->descripcion)
                            <span class="kawsay-archivo-desc d-block">{{ $archivo->descripcion }}</span>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
        @include('kawsay.temas.partials.visor-galeria', [
            'fotos' => $imagenes,
            'etiqueta' => $tipo['label'],
            'zoom' => $esMapa,
        ])
    @endif

    {{-- PDF: tarjeta con portada (primera página) + vista previa en popup, igual que los libros virtuales de la Escuela --}}
    @if ($pdfs->isNotEmpty())
        <div class="row g-3 mb-4">
            @foreach ($pdfs as $archivo)
                @php
                    $vistaPrevia = "abrirVistaPreviaLibro(" . \Illuminate\Support\Js::from($archivo->drive_id) . ", " . \Illuminate\Support\Js::from($archivo->titulo) . ", 'Abrir en nueva ventana')";
                @endphp
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="lib-book-card">
                        <div class="lib-book-card__cover kawsay-pdf-cover" role="button" tabindex="0"
                            data-kawsay-vista="{{ route('kawsay.archivo.vista', $archivo) }}"
                            aria-label="Vista previa de {{ $archivo->titulo }}"
                            onclick="{{ $vistaPrevia }}"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();{{ $vistaPrevia }}}">
                            <i class="fa fa-file-pdf kawsay-pdf-cover__ph" aria-hidden="true"></i>
                            <img src="{{ \App\Services\GoogleDriveService::getThumbnailUrl($archivo->drive_id, 600) }}"
                                alt="" loading="lazy" onerror="this.style.display='none'">
                            <div class="lib-book-card__overlay">
                                <span class="lib-book-card__btn"><i class="fa fa-eye"></i> Vista previa</span>
                            </div>
                        </div>
                        <div class="lib-book-card__info">
                            <h3 class="lib-book-card__title" title="{{ $archivo->titulo }}">{{ $archivo->titulo }}</h3>
                            <p class="lib-book-card__author">
                                <i class="fa fa-file-pdf"></i> PDF {{ $archivo->peso_mb ? '· ' . $archivo->peso_mb . ' MB' : '' }}
                            </p>
                            @if ($archivo->nivel)
                                <span class="kawsay-nivel">{{ $archivo->nivel }}</span>
                            @endif
                            @if ($archivo->descripcion)
                                <p class="kawsay-archivo-desc kawsay-archivo-desc--corta" title="{{ $archivo->descripcion }}">{{ $archivo->descripcion }}</p>
                            @endif
                            <div class="kawsay-pdf-card__actions">
                                <a href="{{ \App\Services\GoogleDriveService::getUrl($archivo->drive_id) }}" target="_blank"
                                    rel="noopener" title="Abrir en una nueva ventana">
                                    <i class="fa fa-external-link-alt"></i> Nueva ventana
                                </a>
                                <a href="{{ \App\Services\GoogleDriveService::getEmbedUrl($archivo->drive_id) }}"
                                    title="Descargar" aria-label="Descargar">
                                    <i class="fa fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @foreach ($otros as $archivo)
        <div class="kawsay-archivo-card">
            <span class="kawsay-archivo-card__icon"><i class="fa {{ $formatoIconos[$archivo->tipo] ?? 'fa-file' }}"></i></span>
            <div class="flex-grow-1 min-w-0">
                <div class="kawsay-archivo-card__title text-truncate">{{ $archivo->titulo }}</div>
                <div class="kawsay-archivo-card__meta">
                    {{ strtoupper($archivo->tipo) }}
                    @if ($archivo->peso_mb) · {{ $archivo->peso_mb }} MB @endif
                    @if ($archivo->nivel) · {{ $archivo->nivel }} @endif
                </div>
                @if ($archivo->descripcion)
                    <div class="kawsay-archivo-desc">{{ $archivo->descripcion }}</div>
                @endif
            </div>
            <div class="kawsay-archivo-card__actions">
                <button type="button" class="btn btn-sm btn-primary"
                    data-kawsay-vista="{{ route('kawsay.archivo.vista', $archivo) }}"
                    onclick="abrirVistaPreviaLibro({{ \Illuminate\Support\Js::from($archivo->drive_id) }}, {{ \Illuminate\Support\Js::from($archivo->titulo) }}, 'Abrir en nueva ventana')">
                    <i class="fa fa-eye"></i> Vista previa
                </button>
                <a href="{{ \App\Services\GoogleDriveService::getUrl($archivo->drive_id) }}" target="_blank" rel="noopener"
                    class="btn btn-sm btn-outline-secondary" title="Abrir en una nueva ventana">
                    <i class="fa fa-external-link-alt"></i> <span class="d-none d-md-inline">Nueva ventana</span>
                </a>
                <a href="{{ \App\Services\GoogleDriveService::getEmbedUrl($archivo->drive_id) }}"
                    class="btn btn-sm btn-outline-secondary" title="Descargar">
                    <i class="fa fa-download"></i> <span class="d-none d-md-inline">Descargar</span>
                </a>
            </div>
        </div>
    @endforeach

    @if ($archivos->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fa {{ $tipo['icono'] }} fa-3x mb-3 d-block opacity-25"></i>
            <p class="mb-0">Todavía no hay {{ strtolower($tipo['label']) }} en este tema.</p>
        </div>
    @endif
</div>

{{-- partials.libro-preview-modal ya lo incluye layouts.padre una sola vez --}}
@include('kawsay.partials.estilos')
{{-- Contador de vistas (data-kawsay-vista) y videos de YouTube que cargan al pulsar (data-kyt) --}}
<script src="{{ asset('js/kawsay.js') }}?v={{ @filemtime(public_path('js/kawsay.js')) }}" defer></script>
@endsection
