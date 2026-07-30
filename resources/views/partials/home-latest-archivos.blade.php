{{--
    Tarjetas de "últimos registros del árbol" para un tipo de archivo (audio/pdf/imagen/video).
    Cada tarjeta separa dos acciones: previsualizar el archivo (audio se reproduce inline, video se
    reproduce inline sobre la miniatura, imagen/PDF abren una vista previa en modal) e ir al registro
    Sisipedia al que pertenece (o abrir el archivo directo si es un aporte suelto sin registro).
    Recibe: $tipo (string), $items (Collection de objetos con: tipo, drive_id, nombre, fecha, category, aportante)
--}}
@php
    $tipoConfig = [
        'audio' => ['icon' => 'fa-music', 'label' => 'Audios', 'color' => 'success', 'rgb' => '25,135,84', 'accion' => 'Escuchar', 'accionIcon' => 'fa-play'],
        'video' => ['icon' => 'fa-video', 'label' => 'Videos', 'color' => 'warning', 'rgb' => '255,193,7', 'accion' => 'Reproducir', 'accionIcon' => 'fa-play'],
        'imagen' => ['icon' => 'fa-image', 'label' => 'Imágenes', 'color' => 'info', 'rgb' => '13,202,240', 'accion' => 'Ampliar', 'accionIcon' => 'fa-expand'],
        'pdf' => ['icon' => 'fa-file-pdf', 'label' => 'PDF', 'color' => 'danger', 'rgb' => '220,53,69', 'accion' => 'Vista previa', 'accionIcon' => 'fa-eye'],
    ];
    $tc = $tipoConfig[$tipo] ?? ['icon' => 'fa-file', 'label' => ucfirst($tipo), 'color' => 'secondary', 'rgb' => '108,117,125', 'accion' => 'Ver', 'accionIcon' => 'fa-eye'];
    $conThumbnail = in_array($tipo, ['video', 'imagen'], true);
@endphp

@if ($items->isNotEmpty())
    <div class="row mt-3 mb-5 sisipedia-latest-section">
        <div class="col-lg-12">
            <h2 class="titulosDos">
                Últimas publicaciones en {{ $tc['label'] }}
                <a href="{{ route('public.sisi', ['tipo' => $tipo]) }}" class="verMas">
                    → Ver más {{ $tc['label'] }}
                </a>
            </h2>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 sisipedia-latest-anim">
            @foreach ($items as $item)
                @php
                    $cat = $item->category;
                    $pathParts = [];
                    $cur = $cat;
                    while ($cur) {
                        array_unshift($pathParts, $cur->display_name);
                        $cur = $cur->parent ?? null;
                    }
                    $ancestorPath = count($pathParts) > 1 ? implode(' › ', array_slice($pathParts, 0, -1)) : null;
                    $nombreMostrar = $item->nombre ?? ($tc['label'].' sin nombre');
                    $previewUrl = \App\Services\GoogleDriveService::getPreviewUrl($item->drive_id);
                    $driveUrl = \App\Services\GoogleDriveService::getUrl($item->drive_id);
                    $registroUrl = $cat ? route('sisipedia.categories.show', $cat) : null;
                    $thumbUrl = $conThumbnail
                        ? \App\Services\GoogleDriveService::getThumbnailUrl($item->drive_id, $tipo === 'video' ? 600 : 400)
                        : null;
                @endphp
                <div class="col">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 sisipedia-latest-card">
                        @if ($thumbUrl)
                            {{-- Video / Imagen: miniatura + botón de previsualización superpuesto --}}
                            <div class="ratio {{ $tipo === 'video' ? 'ratio-16x9' : 'ratio-4x3' }} bg-light position-relative sisipedia-latest-thumb">
                                <img src="{{ $thumbUrl }}" alt="{{ $nombreMostrar }}" class="w-100 h-100"
                                    style="object-fit:cover;" loading="lazy" onerror="this.style.display='none'">
                                <button type="button"
                                    class="sisipedia-latest-preview-btn position-absolute top-50 start-50 translate-middle btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                    data-tipo="{{ $tipo }}" data-preview="{{ $previewUrl }}" data-nombre="{{ $nombreMostrar }}"
                                    title="{{ $tc['accion'] }}" aria-label="{{ $tc['accion'] }} {{ $nombreMostrar }}">
                                    <i class="fa {{ $tc['accionIcon'] }} text-dark"></i>
                                </button>
                                @if ($tipo === 'video')
                                    <div class="sisipedia-latest-video-iframe-wrap d-none position-absolute top-0 start-0 w-100 h-100">
                                        <iframe src="" class="w-100 h-100 border-0" allow="autoplay; fullscreen"
                                            allowfullscreen loading="lazy"></iframe>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div style="height:4px;background:rgba({{ $tc['rgb'] }},.8);"></div>
                        @endif
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                @unless ($thumbUrl)
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                        style="width:32px;height:32px;background:rgba({{ $tc['rgb'] }},.12);">
                                        <i class="fa {{ $tc['icon'] }} text-{{ $tc['color'] }}" style="font-size:.8rem;"></i>
                                    </span>
                                @endunless
                                <h6 class="fw-semibold mb-0 text-truncate text-dark" style="font-size:.85rem;"
                                    title="{{ $nombreMostrar }}">
                                    {{ $nombreMostrar }}
                                </h6>
                            </div>
                            @if ($cat)
                                @if ($ancestorPath)
                                    <div class="text-muted text-truncate" style="font-size:.68rem;">{{ $ancestorPath }}</div>
                                @endif
                                <a href="{{ $registroUrl }}"
                                   class="d-block text-truncate text-decoration-none fw-semibold text-dark sisipedia-reg-link mb-2"
                                   style="font-size:.78rem;">
                                    <i class="fa fa-folder me-1 text-muted"></i>{{ $cat->display_name }}
                                </a>
                            @elseif ($item->aportante)
                                <div class="text-muted text-truncate mb-2" style="font-size:.75rem;">
                                    <i class="fa fa-user me-1"></i>Aporte de {{ $item->aportante }}
                                </div>
                            @endif

                            @if ($tipo === 'audio')
                                {{-- Reproductor propio (streaming vía servidor, no iframe de Drive) --}}
                                <div class="mt-auto pt-1">
                                    @include('partials.audio-player', [
                                        'src' => $item->stream_url,
                                        'title' => $nombreMostrar,
                                        'driveUrl' => $driveUrl,
                                    ])
                                </div>
                            @else
                                <div class="d-flex gap-2 mt-auto pt-1">
                                    @if ($conThumbnail)
                                        @if ($registroUrl)
                                            <a href="{{ $registroUrl }}" class="btn btn-sm btn-outline-secondary w-100">
                                                <i class="fa fa-folder-open me-1"></i>Ver registro
                                            </a>
                                        @else
                                            <a href="{{ $driveUrl }}" target="_blank" rel="noopener"
                                                class="btn btn-sm btn-outline-secondary w-100">
                                                <i class="fa fa-external-link-alt me-1"></i>Abrir archivo
                                            </a>
                                        @endif
                                    @else
                                        <button type="button"
                                            class="btn btn-sm btn-outline-{{ $tc['color'] }} flex-grow-1 sisipedia-latest-preview-btn"
                                            data-tipo="{{ $tipo }}" data-preview="{{ $previewUrl }}" data-nombre="{{ $nombreMostrar }}">
                                            <i class="fa {{ $tc['accionIcon'] }} me-1"></i>{{ $tc['accion'] }}
                                        </button>
                                        @if ($registroUrl)
                                            <a href="{{ $registroUrl }}" class="btn btn-sm btn-outline-secondary flex-shrink-0"
                                                title="Ver registro" aria-label="Ver registro de {{ $nombreMostrar }}">
                                                <i class="fa fa-folder-open"></i>
                                            </a>
                                        @else
                                            <a href="{{ $driveUrl }}" target="_blank" rel="noopener"
                                                class="btn btn-sm btn-outline-secondary flex-shrink-0" title="Abrir archivo"
                                                aria-label="Abrir archivo {{ $nombreMostrar }}">
                                                <i class="fa fa-external-link-alt"></i>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        (function() {
            var section = document.currentScript.previousElementSibling;
            if (!section || !section.classList.contains('sisipedia-latest-section')) return;

            // ── Entrada animada al hacer scroll ──
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
                var cards = section.querySelectorAll('.sisipedia-latest-anim > .col');
                if (cards.length) {
                    var reveal = function() {
                        if (typeof gsap === 'undefined') {
                            var s = document.createElement('script');
                            s.src = 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js';
                            s.onload = function() {
                                gsap.from(cards, { opacity: 0, y: 20, duration: .5, stagger: .08, ease: 'power2.out' });
                            };
                            document.head.appendChild(s);
                        } else {
                            gsap.from(cards, { opacity: 0, y: 20, duration: .5, stagger: .08, ease: 'power2.out' });
                        }
                    };
                    var observer = new IntersectionObserver(function(entries, obs) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                reveal();
                                obs.disconnect();
                            }
                        });
                    }, { threshold: .15 });
                    observer.observe(section);
                }
            }

            // ── Reproducción de video inline sobre la miniatura ──
            function playVideo(btn) {
                var thumb = btn.closest('.sisipedia-latest-thumb');
                if (!thumb) return;
                var iframeWrap = thumb.querySelector('.sisipedia-latest-video-iframe-wrap');
                var iframe = iframeWrap ? iframeWrap.querySelector('iframe') : null;
                if (iframe) iframe.src = btn.getAttribute('data-preview');
                btn.classList.add('d-none');
                if (iframeWrap) iframeWrap.classList.remove('d-none');
            }

            section.querySelectorAll('.sisipedia-latest-preview-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var tipo = this.getAttribute('data-tipo');
                    if (tipo === 'video') {
                        playVideo(this);
                    } else if (window.sisipediaOpenPreview) {
                        window.sisipediaOpenPreview(this.getAttribute('data-preview'), this.getAttribute('data-nombre'));
                    }
                });
            });
        })();
    </script>
@endif
