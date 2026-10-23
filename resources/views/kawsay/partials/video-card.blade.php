{{--
    Tarjeta de video de Drive con reproductor propio, al estilo de las tarjetas
    de audio de Radio: cabecera de color con título y logo de Drive, y debajo
    un <video controls> que pasa por la ruta de streaming (con soporte de Range).
    Recibe: $src (ruta de streaming), $titulo, $driveId, $meta (opcional), $descripcion (opcional).
--}}
@php
    $meta = $meta ?? null;
    $descripcion = $descripcion ?? null;
@endphp
<div class="kawsay-video-card h-100">
    <div class="kawsay-video-card__head">
        <div class="min-w-0">
            <h6 class="kawsay-video-card__title" title="{{ $titulo }}">{{ $titulo }}</h6>
            @if ($meta)
                <p class="kawsay-video-card__meta">{{ $meta }}</p>
            @endif
        </div>
        <img src="{{ asset('img/min/logo-drive.png') }}" alt="Google Drive" class="kawsay-video-card__drive" width="34">
    </div>
    <div class="ratio ratio-16x9 kawsay-video-card__player">
        <video controls playsinline preload="none" class="kawsay-video-el"
            poster="{{ \App\Services\GoogleDriveService::getThumbnailUrl($driveId, 700) }}"
            src="{{ $src }}" aria-label="{{ $titulo }}"></video>
    </div>
    @if ($descripcion)
        <p class="kawsay-video-card__desc">{{ $descripcion }}</p>
    @endif
    <div class="kawsay-video-card__actions">
        <a href="{{ \App\Services\GoogleDriveService::getUrl($driveId) }}" target="_blank" rel="noopener" title="Abrir en Google Drive">
            <i class="fa fa-external-link-alt"></i> Abrir en Drive
        </a>
        <a href="{{ \App\Services\GoogleDriveService::getEmbedUrl($driveId) }}" title="Descargar">
            <i class="fa fa-download"></i> Descargar
        </a>
    </div>
</div>

@once
    <style>
        .kawsay-video-card {
            display: flex; flex-direction: column; gap: .75rem;
            background: var(--project-accent, #6e7d1e); color: #fff;
            border-radius: 12px; padding: 1rem; transition: box-shadow .3s ease;
        }
        .kawsay-video-card:hover { box-shadow: rgba(0, 0, 0, .3) 0 6px 16px; }
        .kawsay-video-card__head { display: flex; justify-content: space-between; align-items: flex-start; gap: .75rem; }
        .kawsay-video-card__title {
            color: #fff; font-size: .95rem; line-height: 1.3; margin: 0 0 .2rem;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .kawsay-video-card__meta, .kawsay-video-card__desc { font-size: .78rem; color: rgba(255, 255, 255, .8); margin: 0; }
        .kawsay-video-card__drive { flex-shrink: 0; background: #fff; border-radius: 8px; padding: 4px; }
        .kawsay-video-card__player { background: #000; border-radius: 8px; overflow: hidden; }
        .kawsay-video-card__player video { object-fit: contain; }
        .kawsay-video-card__actions { margin-top: auto; display: flex; gap: 1rem; font-size: .8rem; }
        .kawsay-video-card__actions a { color: rgba(255, 255, 255, .85); text-decoration: none; }
        .kawsay-video-card__actions a:hover { color: #fff; text-decoration: underline; }
    </style>
    <script>
        // Un solo video sonando a la vez, como los audios de Radio.
        document.addEventListener('play', function (e) {
            if (!e.target.classList || !e.target.classList.contains('kawsay-video-el')) return;
            document.querySelectorAll('.kawsay-video-el').forEach(function (v) {
                if (v !== e.target) v.pause();
            });
        }, true);
    </script>
@endonce
